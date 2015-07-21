<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!isset($__requireLogin_include_php_included)) {
	$__requireLogin_include_php_included = true;

// If you want to force pages which require login to also be accessible only by https,
// uncomment this include.
//include dirname(__FILE__).'/requireHTTPS.include.php';

session_start();

$loginRequired = true;

$KEEP_ME_LOGGED_IN_TIME = (3*24*60*60);
$MAX_KEEP_ME_LOGGED_IN = 20;
$MAX_LOGIN_FAILURES = 5;
$LOGIN_FAILURE_LOCKOUT_TIME = (5*60);

function __accountLocked() {
	echo "Your login account is locked due to too many login failures.";
	exit();
}

function __updateKeepMeLoggedInId() {
	global $loggedInUser, $__loginAppuserpersistentloginDAO,
		$KEEP_ME_LOGGED_IN_TIME, $MAX_KEEP_ME_LOGGED_IN;

	// Create a new unique identifier and expiration time for automatic login.
	// Check that the unique identifier we assigned is actually unique and
	// only exists on this user, to prevent account hijacking.
	// Set a cookie containing the identifier.
	for ($tries = 1; $tries <= 10; $tries++) {
		// Be sure there is at least one available slot in this user.
		// Delete the oldest slots, if necessary.
		$kmlis = $__loginAppuserpersistentloginDAO->
			findByUser_id($loggedInUser->id, '=', 'last_used');
		if (count($kmlis) >= $MAX_KEEP_ME_LOGGED_IN) {
			$numToDel = count($kmlis)-($MAX_KEEP_ME_LOGGED_IN-1);
			for ($i = 0; $i < $numToDel; $i++) {
				$__loginAppuserpersistentloginDAO->delete($kmlis[$i]->id);
			}
		}

		// Add a persistent login row for this user, with a unique Id.
		$kmli = Appuserpersistentlogin::createDefault();
		$kmli->user_id = $loggedInUser->id;
		$kmli->keep_me_logged_in_uniqid = uniqid('', true);
		try {
			@$__loginAppuserpersistentloginDAO->insert($kmli);
		} catch (Exception $ex) {}

		// Confirm that we were able to add the row successfully.
		$kmlis = $__loginAppuserpersistentloginDAO->
			findByKeep_me_logged_in_uniqid($kmli->keep_me_logged_in_uniqid);
		if ( (count($kmlis) == 1) && ($kmlis[0]->user_id == $loggedInUser->id) ) {
			// Success. Set the cookie and stop trying new unique Ids.
			setcookie(
				'kmliuid',
				$kmli->keep_me_logged_in_uniqid,
				time()+$KEEP_ME_LOGGED_IN_TIME,
				'/'
			);
			return true;
		}
	}
	return false;
}

$__loginErrorMsg = '';
$__loginDB = ConnectionFactory::getConnection();
$__loginAppuserDAO = new AppuserDAO($__loginDB);
$__loginAppuserpersistentloginDAO = new AppuserpersistentloginDAO($__loginDB);


$loggedInUser = null;

// Try logging in with username and password from a login form POST.
if (($loggedInUser === null) &&
	($_SERVER['REQUEST_METHOD'] == 'POST') &&
	isset($_POST['loginUserName']) &&
	isset($_POST['loginPassword'])) {
	$__loginUsers = $__loginAppuserDAO->findByUser_name(trim($_POST['loginUserName']));
	if (empty($__loginUsers)) {
		$__loginUsers = $__loginAppuserDAO->findByEmail_addr(trim($_POST['loginUserName']));
	}
	if ((count($__loginUsers) == 1) &&
		($__loginUsers[0]) &&
		($__loginUsers[0]->is_active)) {

		if (($__loginUsers[0]->login_failures >= $MAX_LOGIN_FAILURES) &&
			(strtotime($__loginUsers[0]->last_login_failure) >= (time()-$LOGIN_FAILURE_LOCKOUT_TIME))) {
			__accountLocked();
		}

		$loggedInUser = $__loginUsers[0];

		$saltidx = strrpos($loggedInUser->password_hash, '{');
		$saltendidx = ($saltidx !== false) ? strpos($loggedInUser->password_hash, '}', $saltidx) : false;
		if (($saltidx !== false) && ($saltendidx !== false) && ($saltendidx > $saltidx)) {
			// sha512 algorithm.
			$salt = substr($loggedInUser->password_hash, $saltidx+1, $saltendidx-($saltidx+1));
			if (hash('sha512', $_POST['loginPassword'].'{'.$salt.'}').'{'.$salt.'}' != $loggedInUser->password_hash) {
				if (strtotime($__loginUsers[0]->last_login_failure) >= (time()-$LOGIN_FAILURE_LOCKOUT_TIME)) {
					// Most recent login failure as within lockout time; so increment failure count.
					$loggedInUser->login_failures++;
				} else {
					// Most recent login failure as too long ago to consider; start counting at 1.
					$loggedInUser->login_failures = 1;
				}
				$loggedInUser->last_login_failure = date('Y-m-d H:i:s');
				try {
					$__loginAppuserDAO->update($loggedInUser);
				} catch (Exception $ex) {}
				$loggedInUser = null;
			}
		} else {
			// md5 algorithm.
			$salt = substr($loggedInUser->password_hash, 0, 2);
			if (($salt.md5($salt.$_POST['loginPassword'])) != $loggedInUser->password_hash) {
				if (strtotime($__loginUsers[0]->last_login_failure) >= (time()-$LOGIN_FAILURE_LOCKOUT_TIME)) {
					// Most recent login failure as within lockout time; so increment failure count.
					$loggedInUser->login_failures++;
				} else {
					// Most recent login failure as too long ago to consider; start counting at 1.
					$loggedInUser->login_failures = 1;
				}
				$loggedInUser->last_login_failure = date('Y-m-d H:i:s');
				try {
					$__loginAppuserDAO->update($loggedInUser);
				} catch (Exception $ex) {}
				$loggedInUser = null;
			}
		}
		unset($salt);

		if ($loggedInUser !== null) {
			$_SESSION['user_id'] = $loggedInUser->id;

			$loggedInUser->login_failures = 0;
			$loggedInUser->last_login = date('Y-m-d H:i:s');
			try {
				$__loginAppuserDAO->update($loggedInUser);
			} catch (Exception $ex) {}
		}
	}
	if ($loggedInUser === null) {
		$__loginErrorMsg = 'Invalid username or password.';
	} else {
		if (isset($_POST['keepMeLoggedIn']) && (((int)trim($_POST['keepMeLoggedIn'])) != 0)) {
			// Generate the keep-me-logged-in Id and the expiration time.
			__updateKeepMeLoggedInId();
		} else {
			// The user did not request to stay logged in.
			// Remove any existing cookie.
			setcookie('kmliuid', '', 0, '/');
		}
	}
}

// If we're not logged in, see if we have a valid session with a valid
// userId which refers to an active user.
if (($loggedInUser === null) && isset($_SESSION['user_id'])) {
	$loggedInUser = $__loginAppuserDAO->load($_SESSION['user_id']);
	if ((!$loggedInUser) || (!$loggedInUser->is_active)) {
		$loggedInUser = null;
	}
}

// If we're not logged in, try recovering a persistent login
// unique Id and relating it to a user.
if (($loggedInUser === null) &&
	isset($_COOKIE['kmliuid']) &&
	(trim($_COOKIE['kmliuid']) != '')) {
	$__kmliexp = time()-$KEEP_ME_LOGGED_IN_TIME;
	$__kmlis = $__loginAppuserpersistentloginDAO->
		findByKeep_me_logged_in_uniqid(trim($_COOKIE['kmliuid']));
	if ((count($__kmlis) == 1) &&
		($__kmlis[0]->user_id > 0) &&
		(strtotime($__kmlis[0]->last_used) >= $__kmliexp)) {
		$loggedInUser = $__loginAppuserDAO->load($__kmlis[0]->user_id);
		if ((!$loggedInUser) || (!$loggedInUser->is_active)) $loggedInUser = null;
		if ($loggedInUser !== null) {
			$_SESSION['user_id'] = $loggedInUser->id;
			$__kmlis[0]->last_used = date('Y-m-d H:i:s');
			$__loginAppuserpersistentloginDAO->update($__kmlis[0]);
		}
	}
}

if ($loggedInUser === null) {
	// Calling scripts can set $enableLoginForm to false before including this
	// include file, and if the user is not logged in, the script will simply
	// exit silently instead of presenting a login form.
	if ( (isset($enableLoginForm)) && (!$enableLoginForm) ) {
		$__loginDB->close();
		exit();
	}
	require dirname(__FILE__).'/header.include.php';
	require dirname(__FILE__).'/loginForm.include.php';
	require dirname(__FILE__).'/footer.include.php';
	$__loginDB->close();
	exit();
}

unset($__loginUsers);
unset($__loginErrorMsg);
unset($__loginAppuserDAO);
unset($__loginAppuserpersistentloginDAO);

$__loginDB->close();
unset($__loginDB);

}	// if (!isset($__requireLogin_include_php_included))
