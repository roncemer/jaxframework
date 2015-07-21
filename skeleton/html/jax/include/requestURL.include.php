<?php
// Copyright (c) 2011 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!isset($__requestURL_include_php_included)) {
	$__requestURL_include_php_included = true;

function getRequestURL($includeQueryString = true) {
	$protocol = 'http';
	if ((isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) ||
		(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on'))) {
		$protocol = 'https';
	}
	$host = isset($_SERVER['HTTP_HOST']) ?
		$_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
	$url = $protocol.'://'.$host.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
	if ((!$includeQueryString) && (($qi = strpos($url, '?')) !== false)) {
		$url = substr($url, 0, $qi);
	}
	return $url;
}

}	// if (!isset($__requestURL_include_php_included))
