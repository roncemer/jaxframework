<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!isset($__validation_include_php_included)) {
	$__validation_include_php_included = true;

function createMsgResultObj() {
	$result = new stdClass();
	$result->errorMsg = '';
	$result->successMsg = '';
	$result->fieldErrors = array();
	return $result;
}

// $dt must be in the form of YYYY-MM-DD.
function isValidDate($dt) {
	if (strlen($dt) != 10) return false;
	$pieces = explode('-', str_replace('/', '-', $dt));
	if (count($pieces) != 3) return false;
	if ((strlen($pieces[0])!=4)||(strlen($pieces[1])!=2)||(strlen($pieces[2])!=2)) return false;
	$y = (int)ltrim($pieces[0], '0');
	$m = (int)ltrim($pieces[1], '0');
	$d = (int)ltrim($pieces[2], '0');
	return checkdate($m, $d, $y);
}

function isValidEmailAddress($emailAddress) {
// Don't use deprecated eregi() function. Instead, use preferred preg_match() function.
//	if (!eregi("^[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*(\\.[_A-Za-z0-9-]+)$", $emailAddress)) {
//		return false;
//	}
	if (!preg_match(
		"/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",
		$emailAddress)) {
		return false;
	}
	return true;
}

function getMinPasswordLength() {
	return 6;
}

function isValidPassword($password) {
	if (strlen($password) < getMinPasswordLength()) return false;
	$haveAlpha = $haveDigit = false;
	for ($i = 0; $i < strlen($password); $i++) {
		$c = $password[$i];
		if (ctype_alpha($c)) {
			$haveAlpha = true;
		} else if (ctype_digit($c)) {
			$haveDigit = true;
		} else if (!ctype_punct($c)) {
			return false;
		}
	}
	return ($haveAlpha && $haveDigit) ? true : false;
}

}	// if (!isset($__validation_include_php_included))
