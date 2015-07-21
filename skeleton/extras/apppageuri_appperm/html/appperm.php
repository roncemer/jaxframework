<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

$mainOk = true;
include dirname(__FILE__).'/generated/appperm_generated.include.php';

function initHook() {
	global $params, $command, $RESERVED_PERM_NAMES;

	$RESERVED_PERM_NAMES = array(
		'all',
		'appuser',
		'approle',
		'appperm',
		'apppageuri',
	);
}

function validationHook() {
	global $db, $row, $result, $RESERVED_PERM_NAMES;

	if ($row->id > 0) {
		$apppermDAO = new ApppermDAO($db);
		if (!($perm = $apppermDAO->load($row->id))) {
			$result->errorMsg .= "This Permission cannot be updated because it does not exist.\n";
			return;
		}
		if (in_array($perm->perm_name, $RESERVED_PERM_NAMES)) {
			$result->errorMsg .= "This is a reserved Permission, and cannot be edited.\n";
			return;
		}
	}
}

function preInsertHook() {
	global $db, $row, $newRow;

	$newRow->when_added = date('Y-m-d H:i:s');
}

function deleteCheckHook() {
	global $db, $id, $result, $RESERVED_PERM_NAMES;

	$apppermDAO = new ApppermDAO($db);
	if (!($perm = $apppermDAO->load($id))) {
		$result->errorMsg .= "This Permission cannot be deleted because it does not exist.\n";
		return;
	}

	if (in_array($perm->perm_name, $RESERVED_PERM_NAMES)) {
		$result->errorMsg .= "This is a reserved Permission, and cannot be deleted.\n";
		return;
	}

	$ps = new PreparedStatement('select * from apppageuriperm where perm_name = ?', 0, 1);
	$ps->setString($perm->perm_name);
	if ($db->fetchObject($db->executeQuery($ps), true)) {
		$result->errorMsg .= "This Permission cannot be deleted because it is referenced by one or more Page URIs.\n";
	}

	$ps = new PreparedStatement('select * from approleperm where perm_name = ?', 0, 1);
	$ps->setString($perm->perm_name);
	if ($db->fetchObject($db->executeQuery($ps), true)) {
		$result->errorMsg .= "This Permission cannot be deleted because it is referenced by one or more Roles.\n";
	}
}
