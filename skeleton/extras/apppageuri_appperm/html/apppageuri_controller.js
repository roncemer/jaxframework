// Copyright (c) 2010-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

var template_permsTR;

function preInitHook() {
	template_permsTR = $('#cont_template_permsTR').text();
	$('#cont_template_permsTR').remove();
}

function canEditRow(id, rowData) {
	var page_uri = rowData[findDataTableColIdx(apppageurisDataTable_aoColumnDefs, 'page_uri')];
	if ($.inArray(page_uri, RESERVED_PAGE_URIS) >= 0) return false;
	return true;
}

function canDeleteRow(id, rowData) {
	if (!canEditRow(id, rowData)) return false;
	return true;
}

function postLoadFormHook(id, newMode, allowEditing, row) {
	// Load perm checkboxes into form.
	loadPermsIntoForm(row.perms, newMode);
	$('#permsContainerTR').show();

	// These should only be visible for existing rows.
	if (newMode != ADD_MODE) $("#idTR, #when_addedTR").show(); else $("#idTR, #when_addedTR").hide();
}

function loadPermsIntoForm(perms, newMode) {
	var permsTbody = $('#permsTbody');
	permsTbody.children().remove();
	for (var i = 0; i < ALL_PERM_NAMES.length; i++) {
		if (ALL_PERM_NAMES[i] == 'all') continue;
		var perm_name = ALL_PERM_NAMES[i];
		var permDescription = ALL_PERM_DESCRIPTIONS_BY_PERM_NAMES[perm_name];
		var checked = false;
		for (var j = 0; j < perms.length; j++) {
			if (perms[j].perm_name == perm_name) {
				checked = true;
				break;
			}
		}
		$(replaceTokens(
			template_permsTR,
			{
				perm_name:perm_name,
				permDescription:permDescription
			}
		)).appendTo(permsTbody);
		if (checked) {
			$('#perm_nameSelected_'+htmlencode(perm_name)).attr('checked', 'checked');
		}
	}
	if ((newMode != ADD_MODE) && (newMode != EDIT_MODE)) {
		$('.perm_nameSelected').attr('disabled', 'disabled');
	}
}
