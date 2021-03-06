<?php
// Copyright (c) 2010-2016 Ronald B. Cemer
// All rights reserved worldwide.
//
// DO NOT EDIT THIS FILE.
// This file is part of the Jax Framework.
// If you edit this file, your changes will be lost when framework updates are applied.

include dirname(__FILE__).'/jax/include/autoload.include.php';

$params = array_merge($_GET, $_POST);
$path = isset($params['path']) ? $params['path'] : '';
if (($path == '') && isset($_SERVER['PATH_INFO']) && ($_SERVER['PATH_INFO'] != '')) {
	$path = $_SERVER['PATH_INFO'];
}

$path = fixPath($path);
$basefilepath = dirname(__FILE__).$path;

$filepath = $basefilepath.'_help.html';
if (!file_exists($filepath)) {
	header('HTTP/1.1 404 Not Found');
	echo '404 Not Found';
	exit();
}

// Load the main help HTML file.
$dom = new DOMDocument();
$dom->loadHTML(file_get_contents($filepath));

// Load the language-specific help HTML files.
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	for ($i = 0, $n = count($langs); $i < $n; $i++) {
		$lng = strtolower(trim($langs[$i]));
		if (($semiidx = strpos($lng, ';')) !== false) {
			$lng = trim(substr($lng, 0, $semiidx));
		}
		if ($lng != '') {
			$lng = str_replace('-', '_', $lng);
			$langs[$i] = $lng;
		}
	}
	unset($seimiidx);
	unset($lng);
} else {
	$lng = getenv('LANG');
	if (($dotidx = strpos($lng, '.')) !== false) $lng = substr($lng, 0, $dotidx);
	$lng = strtolower(trim($lng));
	$langs = array($lng);
	unset($dotidx);
	unset($lng);
}
$suffixes = array();
foreach ($langs as $lang) {
	$pieces = explode('_', $lang);
	// Generate language-specific suffixes in order of most- to least-specific.
	for ($i = count($pieces); $i > 0; $i--) {
		$suffix = '_help-'.implode('_', array_slice($pieces, 0, $i)).'-translated.html';
		if (!in_array($suffix, $suffixes)) $suffixes[] = $suffix;
	}
}
$langdoms = array();
foreach ($suffixes as $suffix) {
	$langfilepath = $basefilepath.$suffix;
	if (file_exists($langfilepath)) {
		$langdom = new DOMDocument();
		$langdom->loadHTML(file_get_contents($langfilepath));
		$langdoms[] = $langdom;
	}
}

// If we have at least one language preference, set the lang attribute on the opening html tag to that.
// Note that the lang attribute only accepts a language code, not a country code, so we take the minus
// and the country code off before setting the attribute.
if ((!empty($langs)) && ($langs[0] != '')) {
	$pieces = explode('-', $langs[0]);
	if ((!empty($pieces)) && ($pieces[0] != '')) {
		$dom->documentElement->setAttribute('lang', $pieces[0]);
	}
}

if (!empty($langdoms)) {
	// Replace title in original document with the most specific title from the language-specific documents.
	$title = $dom->getElementsByTagName('title');
	$title = ($title->length > 0) ? $title->item(0) : null;

	foreach ($langdoms as $langdom) {
		$newtitle = $langdom->getElementsByTagName('title');
		$newtitle = ($newtitle->length > 0) ? $newtitle->item(0) : null;
		if ($newtitle !== null) {
			$title->parentNode->replaceChild($dom->importNode($newtitle, true), $title);
			break;
		}
	}

	// For each element in the document which has an id, try to find an element with the same id in the
	// translated document(s).  If found, replace it with the translated version.
	replaceIdNodes($dom->documentElement);

	// For each a element in the document which has a name, try to find an element with the a element of the same name in the
	// translated document(s).  If found, replace it with the translated version.
	replaceNamedANodes();
} // if (!empty($langdoms))

$html = $dom->saveHTML();
header('Content-Type: text/html');
header('Content-Length: '.strlen($html));
echo $html;
exit();

function fixPath($path) {
	$path = str_replace('\\', '/', $path);
	$path = trim($path, "/");
	$path = preg_replace('/\/+/', '/', $path);
	$pieces = explode('/', $path);
	for ($i = 0, $n = count($pieces); $i < $n;) {
		if ($pieces[$i] == '') {
			unset($pieces[$i]);
			$pieces = array_slice($pieces, 0);
			$n--;
			if ($i > 0) $i--;
		} else if ($pieces[$i] == '..') {
			unset($pieces[$i]);
			$pieces = array_slice($pieces, 0);
			$n--;
			if ($i > 0) $i--;

			unset($pieces[$i]);
			$pieces = array_slice($pieces, 0);
			$n--;
			if ($i > 0) $i--;
		} else {
			$i++;
		}
	}
	return '/'.$path;
}

function replaceIdNodes($node) {
	global $dom, $langdoms;

	if ($node instanceof DOMElement) {
		if (($id = $node->getAttribute('id')) != '') {
			foreach ($langdoms as $langdom) {
				if (($langelem = $langdom->getElementById($id)) !== null) {
					$node->parentNode->replaceChild($dom->importNode($langelem, true), $node);
					return;
				}
			}
		}
	}

	if ($node->hasChildNodes()) {
		foreach ($node->childNodes as $child) {
			replaceIdNodes($child);
		}
	}
} // replaceIdNodes()

function replaceNamedANodes() {
	global $dom, $langdoms;

	$langanodes = array();
	foreach ($langdoms as $langdom) {
		$lanodes = $langdom->getElementsByTagName('a');
		foreach ($lanodes as $langanode) {
			if ($langanode->hasAttribute('name')) {
				$langanodes[] = $langanode;
			}
		}
	}

	$anodes = $dom->getElementsByTagName('a');
	foreach ($anodes as $anode) {
		if ($anode->hasAttribute('name')) {
			if (($name = $anode->getAttribute('name')) != '') {
				foreach ($langanodes as $langanode) {
					if ($langanode->getAttribute('name') == $name) {
						$anode->parentNode->replaceChild($dom->importNode($langanode, true), $anode);
						break;
					}
				}
			}
		}
	}
} // replaceNamedANodes()
