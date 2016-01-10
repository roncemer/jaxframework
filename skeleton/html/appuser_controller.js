// Copyright (c) 2010-2016 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

var template_rolesTR;

var rowFetcher = new RowFetcher();

function preInitHook() {
	template_rolesTR = $('#cont_template_rolesTR').text();
	$('#cont_template_rolesTR').remove();

	$('#added_by_user_id, #last_updated_by_user_id').change(function() {
		var fld = $(this);
		var fieldName = fld.attr('name');
		var descfld = $('#'+(fieldName.replace(/_id$/, '_full_name')));
		var id = parseInt($.trim(fld.val())) || 0;
		var row = rowFetcher.getRowForId('loadAppuser', 'id', id);
		rowFetcher.getRowForId(
			function(row) {
				if (row !== null) {
					descfld.val($.trim(row.first_name+' '+row.last_name));
				} else {
					descfld.val(sprintf(_t('crud.appuser.userIdNotFound'), id));
				}
			},
			'loadAppuser',
			'id',
			id
		);
	});
}

function canDeleteRow(id, rowData) {
	if ((id > 0) && (id <= MAX_RESERVED_ID)) return false;
	return true;
}

function postLoadFormHook(id, newMode, allowEditing, row) {
	// Load role checkboxes into form, but not if this is a reserved id.
	if ((id > 0) && (id <= MAX_RESERVED_ID)) {
		$('#rolesTbody').children().remove();
		$('#rolesContainerTR').hide();
	} else {
		loadRolesIntoForm(row.roles, newMode);
		$('#rolesContainerTR').show();
	}

	// These should only be visible for existing rows.
	if (newMode != ADD_MODE) $('#idTR, #when_addedTR').show(); else $('#idTR, #when_addedTR').hide();

	// The password entry fields should only be visible if we're in an editable mode.
	if (allowEditing) {
		$('#passwordTr, #reEnterPasswordTr').show();
	} else {
		$('#passwordTr, #reEnterPasswordTr').hide();
	}

	// Always start with empty password fields.
	$('#password, #reEnterPassword').val('');

	// Update password prompts as needed.
	switch (newMode) {
	case ADD_MODE:
		$("label[for='password']").text(_t('crud.appuser.form.input.password.label'));
		$("label[for='reEnterPassword']").text(_t('crud.appuser.form.input.reEnterPassword.label'));
		break;
	case EDIT_MODE:
		$("label[for='password']").text(_t('crud.appuser.form.input.password.existing.label'));
		$("label[for='reEnterPassword']").text(_t('crud.appuser.form.input.reEnterPassword.existing.label'));
		break;
	}

	$('#added_by_user_id, #last_updated_by_user_id').trigger('change');
}

function loadRolesIntoForm(roles, newMode) {
	var rolesTbody = $('#rolesTbody');
	rolesTbody.children().remove();
	for (var i = 0; i < ALL_ROLE_NAMES.length; i++) {
		var role_name = ALL_ROLE_NAMES[i];
		var roleDescription = ALL_ROLE_DESCRIPTIONS_BY_ROLE_NAMES[role_name];
		var checked = false;
		for (var j = 0; j < roles.length; j++) {
			if (roles[j].role_name == role_name) {
				checked = true;
				break;
			}
		}
		$(replaceTokens(
			template_rolesTR,
			{
				role_name:role_name,
				roleDescription:roleDescription
			}
		)).appendTo(rolesTbody);
		if (checked) {
			$('#role_nameSelected_'+htmlencode(role_name)).attr('checked', 'checked');
		}
	}
	if ((newMode != ADD_MODE) && (newMode != EDIT_MODE)) {
		$('.role_nameSelected').attr('disabled', 'disabled');
	}
}
