<?php
// Copyright (c) 2010-2014 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

include './jax/include/autoload.include.php';

if ((!defined('APP_ROOT_DIR')) || (!defined('APP_ROOT_URL')) || (!defined('APP_ROOT_URI'))) include dirname(__FILE__).'/jax/include/appRoot.include.php';

// If we have a "keep me logged in" cookie, remove that cookie's
// unique Id from the user, and then unset the cookie.
// This will disable "keep me logged in" before we log out.
if (isset($_COOKIE['kmliuid']) && (trim($_COOKIE['kmliuid']) != '')) {
	$db = ConnectionFactory::getConnection();
	$persistentloginDAO = new AppuserpersistentloginDAO($db);

	$kmlis = $persistentloginDAO->findByKeep_me_logged_in_uniqid(trim($_COOKIE['kmliuid']));
	foreach ($kmlis as $kmli) {
		try {
			$persistentloginDAO->delete($kmli->id);
		} catch (Exception $ex) {}
	}

	$db->close();

	setcookie('kmliuid', '', 0, '/');
}

// Wipe out the session cookie.
@session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		time()-42000,
		$params["path"],
		$params["domain"],
		$params["secure"]/*,
		$params["httponly"]*/
// NOTE: the httponly parameter is added in PHP 5.2.0.
    );
}

// Destroy the session.
session_destroy();

// Redirect to the home page.
header('Location: '.APP_ROOT_URI);
