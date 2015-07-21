<?php
// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

$mainOk = true;
include dirname(__FILE__).'/generated/apppageuri_generated.include.php';

function initHook() {
	global $params, $command, $RESERVED_PAGE_URIS;


	$RESERVED_PAGE_URIS = array(
		'appuser.php',
		'approle.php',
		'appperm.php',
		'apppageuri.php',
	);
}

function validationHook() {
	global $db, $row, $result, $RESERVED_PAGE_URIS;

	if ($row->id > 0) {
		$apppageuriDAO = new ApppageuriDAO($db);
		if (!($pageuri = $apppageuriDAO->load($row->id))) {
			$result->errorMsg .= "This Page URI cannot be updated because it does not exist.\n";
			return;
		}
		if (in_array($pageuri->page_uri, $RESERVED_PAGE_URIS)) {
			$result->errorMsg .= "This is a reserved Page URI, and cannot be edited.\n";
			return;
		}
	}
}

function preUpdateHook() {
	global $db, $row, $oldRow;

	// If the page_uri changed, the old permissions will be tied to the old page_uri,
	// so we have to delete them, otherwise the save will fail.  The current list of
	// permissions will be added back to the table.
	if ($row->page_uri != $oldRow->page_uri) {
		deletePerms($oldRow->id);
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
	global $db, $id, $result, $RESERVED_PAGE_URIS;

	$apppageuriDAO = new ApppageuriDAO($db);
	if (!($pageuri = $apppageuriDAO->load($id))) {
		$result->errorMsg .= "This Page URI cannot be deleted because it does not exist.\n";
		return;
	}

	if (in_array($pageuri->page_uri, $RESERVED_PAGE_URIS)) {
		$result->errorMsg .= "This is a reserved Page URI, and cannot be deleted.\n";
		return;
	}
}

function preDeleteHook() {
	global $db, $id;

	deletePerms($id);
}

function savePerms() {
	global $db, $row;

	$apppageuripermDAO = new ApppageuripermDAO($db);
	$apppermDAO = new ApppermDAO($db);
	$perms = array();
	foreach ($apppermDAO->findAll('perm_name') as $perm) {
		$valname = 'perm_nameSelected_'.$perm->perm_name;
		if (isset($_POST[$valname]) && (((int)$_POST[$valname]) != 0)) {
			$obj = new stdClass();
			$obj->page_uri = $row->page_uri;
			$obj->perm_name = $perm->perm_name;
			$perms[] = $obj;
		}
	}

	ChildRowUpdater::updateChildRows(
		$db,
		'Apppageuriperm',
		$perms,
		array('page_uri'=>$row->page_uri),
		array('page_uri', 'perm_name')
	);
}

function deletePerms($id) {
	global $db;

	$apppageuriDAO = new ApppageuriDAO($db);
	if ($pageuri = $apppageuriDAO->load($id)) {
		$apppageuripermDAO = new ApppageuripermDAO($db);
		foreach ($apppageuripermDAO->findByPage_uri($pageuri->page_uri) as $permrow) {
			$apppageuripermDAO->delete($permrow->id);
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
