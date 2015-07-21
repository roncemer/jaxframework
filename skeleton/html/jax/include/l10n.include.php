<?php
// Copyright (c) 2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!function_exists('loadResourceBundle')) {

$resourceStrings = array();

function loadResourceBundle($pageName) {
	global $resourceStrings;

	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$langs = array_reverse($langs);
		for ($i = 0, $n = count($langs); $i < $n; $i++) {
			$lng = strtolower(trim($langs[$i]));
			if (($semiidx = strpos($lng, ';')) !== false) {
				$lng = trim(substr($lng, 0, $semiidx));
			}
			$langs[$i] = $lng;
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

	foreach ($langs as $lang) {
		$pieces = explode('_', $lang);
		$suffixes = array('');
		for ($i = 0; $i < count($pieces); $i++) {
			$suffixes[] = '.'.implode('_', array_slice($pieces, 0, $i+1));
		}
		foreach ($suffixes as $suffix) {
			$fn = $pageName.$suffix.'.strings';
			if (($fp = @fopen($fn, 'r')) !== false) {
				$lineno = 0;
				while (($line = @fgets($fp)) !== false) {
					$lineno++;
					$line = trim($line);
					if (($line == '') || ($line[0] == '#') || ($line[0] == ';')) continue;
					if (($equalidx = strpos($line, '=')) === false) {
						error_log("Missing equal sign on line $lineno in resource bundle $fn.\n");
						continue;
					}

					$key = trim(substr($line, 0, $equalidx));
					if ($key == '') {
						error_log("Missing property name on line $lineno in resource bundle $fn.\n");
						continue;
					}

					$val = trim(substr($line, $equalidx+1));
					$heredocDelim = ((strlen($val) > 3) && (strncmp($val, '<<<', 3) == 0)) ?
						trim(substr($val, 3)) : '';
					if ($heredocDelim != '') {
						$val = '';
						while (true) {
							if (($moreval = @fgets($fp)) === false) break;
							if ((strlen($moreval) >= 2) &&
								(substr($moreval, strlen($moreval)-2) == "\r\n")) {
								$moreval = substr($moreval, 0, strlen($moreval)-2);
							} else if ((strlen($moreval) >= 1) &&
										(($moreval[strlen($moreval)-1] == "\r") ||
										 ($moreval[strlen($moreval)-1] == "\n"))) {
								$moreval = substr($moreval, 0, strlen($moreval)-1);
							}
							$lineno++;
							if (trim($moreval) === $heredocDelim) break;
							if ($val == '') $val = $moreval; else $val .= "\n".$moreval;
						}
					} else {
						while (substr($val, -1) == '\\') {
							if (($moreval = @fgets($fp)) === false) {
								$val = trim(substr($val, 0, strlen($val)-1));
								break;
							}
							$lineno++;
							$val = trim(substr($val, 0, strlen($val)-1))."\n".rtrim($moreval);
						}
					}

					$resourceStrings[$key] = $val;
				}
				@fclose($fp);
			}
		}	// foreach ($suffixes as $suffix)
	}	// foreach ($langs as $lang)
}

function _t($resourceId, $defaultText = null) {
	global $resourceStrings;

	if (isset($resourceStrings[$resourceId])) {
		return $resourceStrings[$resourceId];
	}
	if ($defaultText === null) {
		return "Missing language resource: $resourceId";
	}
	return $defaultText;
}

function _e($resourceId, $defaultText = null) {
	echo _t($resourceId, $defaultText);
}

}	// if (!function_exists('loadResourceBundle'))
