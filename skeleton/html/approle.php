<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

$mainOk = true;
include dirname(__FILE__).'/generated/approle_generated.include.php';

function initHook() {
	global $params, $command, $RESERVED_ROLE_NAMES;

	$RESERVED_ROLE_NAMES = array(
		'super',
		'security_admin',
	);
}

function validationHook() {
	global $db, $row, $result, $RESERVED_ROLE_NAMES;

	if ($row->id > 0) {
		$approleDAO = new ApproleDAO($db);
		if (!($role = $approleDAO->load($row->id))) {
			$result->errorMsg .= "This Role cannot be updated because it does not exist.\n";
			return;
		}
		if (in_array($role->role_name, $RESERVED_ROLE_NAMES)) {
			$result->errorMsg .= "This is a reserved Role, and cannot be edited.\n";
			return;
		}
	}
}

function postUpdateHook() {
	global $db, $row, $oldRow;

	savePerms();
}

function preInsertHook() {
	global $db, $row, $newRow;

	$newRow->when_added = date('Y-m-d H:i:s');
}

function postInsertHook() {
	global $db, $row, $newRow;

	savePerms();
}

function deleteCheckHook() {
	global $db, $id, $result, $RESERVED_ROLE_NAMES;

	$approleDAO = new ApproleDAO($db);
	if (!($role = $approleDAO->load($id))) {
		$result->errorMsg .= "This Role cannot be deleted because it does not exist.\n";
		return;
	}

	if (in_array($role->role_name, $RESERVED_ROLE_NAMES)) {
		$result->errorMsg .= "This is a reserved Role, and cannot be deleted.\n";
		return;
	}
}

function preDeleteHook() {
	global $db, $id;

	deleteRolePerms();
	deleteUserRoles();
}

function savePerms() {
	global $db, $row;

	if ($row->id == 1) return;

	$approlepermDAO = new ApprolepermDAO($db);
	$apppermDAO = new ApppermDAO($db);
	$perms = array();
	foreach ($apppermDAO->findAll('perm_name') as $perm) {
		$valname = 'perm_nameSelected_'.$perm->perm_name;
		if (isset($_POST[$valname]) && (((int)$_POST[$valname]) != 0)) {
			$obj = new stdClass();
			$obj->role_name = $row->role_name;
			$obj->perm_name = $perm->perm_name;
			$perms[] = $obj;
		}
	}

	ChildRowUpdater::updateChildRows(
		$db,
		'Approleperm',
		$perms,
		array('role_name'=>$row->role_name),
		array('role_name', 'perm_name')
	);
}

function deleteRolePerms() {
	global $db, $id;

	$approleDAO = new ApproleDAO($db);
	if ($role = $approleDAO->load($id)) {
		$approlepermDAO = new ApprolepermDAO($db);
		foreach ($approlepermDAO->findByRole_name($role->role_name) as $permrow) {
			$approlepermDAO->delete($permrow->id);
		}
	}
}

function deleteUserRoles() {
	global $db, $id;

	$approleDAO = new ApproleDAO($db);
	if ($role = $approleDAO->load($id)) {
		$appuserroleDAO = new AppuserroleDAO($db);
		foreach ($appuserroleDAO->findByRole_name($role->role_name) as $permrow) {
			$appuserroleDAO->delete($permrow->id);
		}
	}
}

function preViewOutputHook() {
	global $ALL_PERM_NAMES, $ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES;

	$db = ConnectionFactory::getConnection();

	$ALL_PERM_NAMES = array();
	$ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES = array();
	$ps = new PreparedStatement('select perm_name, description from appperm order by perm_name');
	$rs = $db->executeQuery($ps);
	while ($r = $db->fetchObject($rs)) {
		$ALL_PERM_NAMES[] = $r->perm_name;
		$ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES[$r->perm_name] = $r->description;
	}
	$db->freeResult($rs);

	$db->close();
}
