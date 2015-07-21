// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

function canEditRow(id, rowData) {
	var perm_name = rowData[findDataTableColIdx(apppermsDataTable_aoColumnDefs, 'perm_name')];
	if ($.inArray(perm_name, RESERVED_PERM_NAMES) >= 0) return false;
	return true;
}

function canDeleteRow(id, rowData) {
	if (!canEditRow(id, rowData)) return false;
	return true;
}

function postLoadFormHook(id, newMode, allowEditing, row) {
	// These should only be visible for existing rows.
	if (newMode != ADD_MODE) $("#idTR, #when_addedTR").show(); else $("#idTR, #when_addedTR").hide();
}
