<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

$mainOk = true;
include dirname(__FILE__).'/generated/appuser_generated.include.php';

function initHook() {
	global $params, $command, $MAX_RESERVED_ID;

	$MAX_RESERVED_ID = 1;
}

function validationHook() {
	global $db, $row, $result, $MAX_RESERVED_ID, $dtm;

	$dtm = date('Y-m-d H:i:s');

	if (($row->first_name == '') && ($row->last_name == '')) {
		if (!isset($result->fieldErrors['first_name'])) {
			$result->fieldErrors['first_name'] = "First Name or Last Name is required.\n";
		}
	}

	$row->password_hash = '';
	$password = isset($row->password) ? $row->password : '';
	$reEnterPassword = isset($row->reEnterPassword) ? $row->reEnterPassword : '';
	if ($password != '') {
		if ($reEnterPassword != $password) {
			if (!isset($result->fieldErrors['password'])) {
				$result->fieldErrors['password'] = "Passwords don't match.\n";
			}
		} else if (strlen($password) < getMinPasswordLength()) {
			if (!isset($result->fieldErrors['password'])) {
				$result->fieldErrors['password'] = sprintf(
					"Password must be at least %d characters.\n",
					getMinPasswordLength()
				);
			}
		} else if (!isValidPassword($password)) {
			$result->fieldErrors['password'] = "Password is too weak, or contains invalid characters.\n";
		} else {
			$saltchrs = '0123456789abcdefghijklmnopqrstuvwxyz';
			$salt = '';
			for ($i = 0; $i < 31; $i++) {
				$salt .= $saltchrs[mt_rand(0, strlen($saltchrs)-1)];
			}
			$row->password_hash = hash('sha512', $password.'{'.$salt.'}').'{'.$salt.'}';
		}
	} else if ($row->id <= 0) {
		$result->fieldErrors['password'] = "Please enter a Password for this new user.\n";
	}
}

function preInsertHook() {
	global $db, $row, $newRow, $dtm, $loggedInUser;

	$newRow->when_added = $dtm;
	$newRow->added_by_user_id = $loggedInUser->id;
	$newRow->last_updated = $dtm;
	$newRow->last_updated_by_user_id = $loggedInUser->id;
}

function postInsertHook() {
	global $db, $row, $newRow;

	saveRoles($newRow->id);
}

function preUpdateHook() {
	global $db, $row, $oldRow, $neverUpdateColumns, $dtm, $loggedInUser;

	$oldRow->last_updated = $dtm;
	$oldRow->last_updated_by_user_id = $loggedInUser->id;

	// Only update password_hash if a new password was entered.
	if ($row->password_hash == '') $neverUpdateColumns[] = 'password_hash';
}

function postUpdateHook() {
	global $db, $row, $oldRow, $loggedInUser;

	saveRoles($oldRow->id);
}

function deleteCheckHook() {
	global $id, $result, $MAX_RESERVED_ID;

	if (($id > 0) && ($id <= $MAX_RESERVED_ID)) {
		$result->errorMsg .= "This is a reserved User, and cannot be deleted.\n";
		return;
	}
}

function preDeleteHook() {
	global $db, $id;

	deleteRoles();
}

function saveRoles($user_id) {
	global $db, $row, $MAX_RESERVED_ID;

	if (($row->id > 0) && ($row->id <= $MAX_RESERVED_ID)) return;

	$appuserroleDAO = new AppuserroleDAO($db);
	$approleDAO = new ApproleDAO($db);
	$roles = array();
	foreach ($approleDAO->findAll('sort_order, role_name') as $role) {
		$valname = 'role_nameSelected_'.$role->role_name;
		if (isset($_POST[$valname]) && (((int)$_POST[$valname]) != 0)) {
			$obj = new stdClass();
			$obj->user_id = $user_id;
			$obj->role_name = $role->role_name;
			$roles[] = $obj;
		}
	}

	ChildRowUpdater::updateChildRows(
		$db,
		'Appuserrole',
		$roles,
		array('user_id'=>$user_id),
		array('user_id', 'role_name')
	);
}

function deleteRoles() {
	global $db, $id;

	$appuserroleDAO = new AppuserroleDAO($db);
	foreach ($appuserroleDAO->findByUser_id($id) as $rolerow) {
		$appuserroleDAO->delete($rolerow->id);
	}
}

function preViewOutputHook() {
	global $ALL_ROLE_NAMES, $ALL_ROLE_DESCRIPTIONS_BY_ROLE_NAMES;

	$db = ConnectionFactory::getConnection();

	$ALL_ROLE_NAMES = array();
	$ALL_ROLE_DESCRIPTIONS_BY_ROLE_NAMES = array();
	$ps = new PreparedStatement('select role_name, description from approle order by sort_order, role_name');
	$rs = $db->executeQuery($ps);
	while ($r = $db->fetchObject($rs)) {
		$ALL_ROLE_NAMES[] = $r->role_name;
		$ALL_ROLE_DESCRIPTIONS_BY_ROLE_NAMES[$r->role_name] = $r->description;
	}
	$db->freeResult($rs);

	$db->close();
}
