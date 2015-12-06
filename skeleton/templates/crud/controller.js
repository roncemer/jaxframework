{{generatedFileMessage}}
var SEARCH_MODE = 0, ADD_MODE = 1, EDIT_MODE = 2, DELETE_MODE = 3, VIEW_MODE = 4;
var mode = -1;
var formLoading = false, formLoadingAddMode = false, formLoadingEditMode = false, formLoadingDeleteMode = false, formLoadingViewMode = false, formLoadingMode = -1;
var submittingForm = false;
// In the customized controller, set enableViewMode to false to disable non-editable viewing of rows.
var enableViewMode;
if (typeof(enableViewMode) == 'undefined') enableViewMode = true;

var searchModeHotKeyHelp = '';

{{if_searchPresentation_dataTables}}
var {{tableName}}sDataTable_aoColumnDefs, {{tableName}}sDataTable;
(function() {
	var ci = 0;
	{{tableName}}sDataTable_aoColumnDefs = [
{{crudSearchTableDisplayColumns}}
		{ sName:'actions', sTitle:_t('crud.crudSearchActionsHeader'), aTargets:[ci++], bSortable:false, bUseRendered:false, sType:'html', fnRender:function(oObj) {
			var id = parseInt(oObj.aData[findDataTableColIdx({{tableName}}sDataTable_aoColumnDefs, '{{idCol}}')]) || 0;
			var html = '';
			if (typeof getAdditionalRowSearchActionLinksPre == 'function') {
				var preHTML = getAdditionalRowSearchActionLinksPre(id, oObj.aData);
				if (preHTML != '') html += preHTML;
			}
			// open button toolbar and group on first button
			var sep = '<div class="btn-toolbar"><div class="btn-group">';
			if (enableViewMode &&
				((typeof canViewRow != 'function') || (canViewRow(id, oObj.aData)))) {
				html += sep+'<a href="#" title="'+_t('crud.crudViewLinkTitle')+'" class="btn btn-default" onclick="view{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-eye-open"></i></a>';
				sep = '';
			}
			if ((typeof canEditRow != 'function') || (canEditRow(id, oObj.aData))) {
				html += sep+'<a href="#" title="'+_t('crud.crudEditLinkTitle')+'" class="btn btn-default" onclick="edit{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-pencil"></i></a>';
				sep = '';
			}
			if (({{allowAddSimilar}}) &&
				((typeof canAddSimilarRow != 'function') || (canAddSimilarRow(id, oObj.aData)))) {
				html += sep+'<a href="#" title="'+_t('crud.crudAddSimilarLinkTitle')+'" class="btn btn-default" onclick="addSimilar{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-plus-sign"></i></a>';
				sep = '';
			}
			if ((typeof canDeleteRow != 'function') || (canDeleteRow(id, oObj.aData))) {
				html += sep+'<a href="#" title="'+_t('crud.crudDeleteLinkTitle')+'" class="btn btn-danger" onclick="delete{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-trash"></i></a>';
				sep = '';
			}
			if (sep == '') html += '</div></div>';	// close button group and toolbar
			if (typeof getAdditionalRowSearchActionLinksPost == 'function') {
				var postHTML = getAdditionalRowSearchActionLinksPost(id, oObj.aData);
				if (postHTML != '') html += postHTML;
			}
			return html;
		}}
	];
})();
{{/if_searchPresentation_dataTables}}
{{if_searchPresentation_AJAXSearchGrid}}
var {{tableName}}sSearchGrid;

function __getSearchActionColumnHTML(row) {
	var id = parseInt(row.{{idCol}}) || 0;
	var html = '';
	if (typeof getAdditionalRowSearchActionLinksPre == 'function') {
		var preHTML = getAdditionalRowSearchActionLinksPre(id, row);
		if (preHTML != '') html += preHTML;
	}
	// open button toolbar and group on first button
	var sep = '<div class="btn-toolbar"><div class="btn-group">';
	if (enableViewMode &&
		((typeof canViewRow != 'function') || (canViewRow(id, row)))) {
		html += sep+'<a href="#" title="'+_t('crud.crudViewLinkTitle')+'" class="btn btn-default" onclick="view{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-eye-open"></i></a>';
		sep = '';
	}
	if ((typeof canEditRow != 'function') || (canEditRow(id, row))) {
		html += sep+'<a href="#" title="'+_t('crud.crudEditLinkTitle')+'" class="btn btn-default" onclick="edit{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-pencil"></i></a>';
		sep = '';
	}
	if (({{allowAddSimilar}}) &&
		((typeof canAddSimilarRow != 'function') || (canAddSimilarRow(id, row)))) {
		html += sep+'<a href="#" title="'+_t('crud.crudAddSimilarLinkTitle')+'" class="btn btn-default" onclick="addSimilar{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-plus-sign"></i></a>';
		sep = '';
	}
	if ((typeof canDeleteRow != 'function') || (canDeleteRow(id, row))) {
		html += sep+'<a href="#" title="'+_t('crud.crudDeleteLinkTitle')+'" class="btn btn-danger" onclick="delete{{uTableName}}('+id+'); return false;"><i class="glyphicon glyphicon-trash"></i></a>';
		sep = '';
	}
	if (sep == '') html += '</div></div>';	// close button group and toolbar
	if (typeof getAdditionalRowSearchActionLinksPost == 'function') {
		var postHTML = getAdditionalRowSearchActionLinksPost(id, row);
		if (postHTML != '') html += postHTML;
	}
	return html;
} // __getSearchActionColumnHTML()
{{/if_searchPresentation_AJAXSearchGrid}}

$(document).ready(function() {
	if (typeof preInitHook == 'function') {
		preInitHook();
	}

{{if_searchPresentation_dataTables}}
	var url = getBaseURL()+'?command={{crudSearchCommand}}';
	if (typeof fixupAJAXURL == 'function') {
		url = fixupAJAXURL(url);
	}
	{{tableName}}sDataTable = $('#{{tableName}}sTable').dataTable({
		bProcessing: true,
		bServerSide: true,
		sAjaxSource: url,
		sPaginationType: 'full_numbers',
		aoColumnDefs: {{tableName}}sDataTable_aoColumnDefs,
		bAutoWidth: false
{{crudSearchTableCallbacks}}
	});
	PopupSearch.prototype.addFilterColSelectFromBackendSearchableColumns({{tableName}}sDataTable.fnSettings().sAjaxSource, '{{tableName}}sTable');
{{/if_searchPresentation_dataTables}}
{{if_searchPresentation_AJAXSearchGrid}}
	{{tableName}}sSearchGrid = new AJAXSearchGrid(
		'#{{tableName}}sSearchGridCont',
		{
			searchCommand:'{{crudSearchCommand}}',
			columnNames:{{crudSearchColumnNamesJSON}},
			columnFilters:{{crudSearchColumnFilters}},
			extraQueryParams:{{crudSearchExtraQueryParamsJSON}},
			defaultSorts:{{crudSearchDefaultSortsJSON}}{{crudSearchCallbacks}}
{{if_searchPresentation_AJAXSearchGrid}}
			,hotKeyActionMap: [
				{
					which:13, // Enter
					callback:function(ajaxGrid, evt) {
						if (ajaxGrid.$scope.highlightedRowIdx >= 0) {
							var row = ajaxGrid.$scope.rows[ajaxGrid.$scope.highlightedRowIdx];
							var id = parseInt(row.{{idCol}}) || 0;
							if ((typeof canEditRow != 'function') || (canEditRow(id, row))) {
								edit{{uTableName}}(id);
							}
						}
					}
				},
				{
					which:46, // Delete
					callback:function(ajaxGrid, evt) {
						if (ajaxGrid.$scope.highlightedRowIdx >= 0) {
							var row = ajaxGrid.$scope.rows[ajaxGrid.$scope.highlightedRowIdx];
							var id = parseInt(row.{{idCol}}) || 0;
							if ((typeof canDeleteRow != 'function') || (canDeleteRow(id, row))) {
								delete{{uTableName}}(id);
							}
						}
					}
				},
				{
					which:45, // Insert
					altKey: false,
					callback:function(ajaxGrid, evt) {
						add{{uTableName}}();
					}
				},
				{
					which:45, // Alt+Insert
					altKey: true,
					callback:function(ajaxGrid, evt) {
						if (ajaxGrid.$scope.highlightedRowIdx >= 0) {
							var row = ajaxGrid.$scope.rows[ajaxGrid.$scope.highlightedRowIdx];
							var id = parseInt(row.{{idCol}}) || 0;
							if (({{allowAddSimilar}}) &&
								((typeof canAddSimilarRow != 'function') || (canAddSimilarRow(id, row)))) {
								addSimilar{{uTableName}}(id);
							} else {
								add{{uTableName}}();
							}
						}
					}
				},
				{
					which:86, // Alt+V
					altKey: true,
					callback:function(ajaxGrid, evt) {
						if (ajaxGrid.$scope.highlightedRowIdx >= 0) {
							var row = ajaxGrid.$scope.rows[ajaxGrid.$scope.highlightedRowIdx];
							var id = parseInt(row.{{idCol}}) || 0;
							if ((typeof canViewRow != 'function') || (canViewRow(id, row))) {
								view{{uTableName}}(id);
							}
						}
					}
				}
			]
{{/if_searchPresentation_AJAXSearchGrid}}
		}
	);
	{{tableName}}sSearchGrid.$scope.getSearchActionColumnHTML = __getSearchActionColumnHTML;
{{/if_searchPresentation_AJAXSearchGrid}}

{{autocompleteInitJS}}

	attachSpecialFieldFeatures();
	installPageHelp();

	$('body').keydown(function(evt) {
		if ((evt.keyCode == 27) && // Esc
			(!(evt.altKey || evt.ctrlKey || evt.shiftKey || evt.metaKey))) {
			var d = evt.srcElement || evt.target;
			if ((d !== undefined) && (d !== null)) {
				switch (d.tagName.toUpperCase()) {
				case 'INPUT':
					if ((d.type !== undefined) && (d.type !== null)) {
						switch (d.type.toUpperCase()) {
						case 'TEXT':
						case 'PASSWORD':
						case 'FILE':
						case 'SEARCH':
						case 'EMAIL':
						case 'NUMBER':
						case 'DATE':
							abandon{{uTableName}}();
							return;
						}
					}
					break;
				case 'TEXTAREA':
					abandon{{uTableName}}();
					return;
				}
			}
		}
	});

	setMode(SEARCH_MODE);

{{if_searchPresentation_AJAXSearchGrid}}
	// Install blur event listeners to change the page help when in search mode, based on whether the search input has focus.
	$('#{{tableName}}sSearchGridCont .jax-grid-pager-search-text-input').focus(function() {
		if (mode == SEARCH_MODE) {
			$('#hotKeyHelp').html(searchModeHotKeyHelp);
		}
	});
	$('#{{tableName}}sSearchGridCont .jax-grid-pager-search-text-input').blur(function() {
		if (mode == SEARCH_MODE) {
			$('#hotKeyHelp').html('');
		}
	});
{{/if_searchPresentation_AJAXSearchGrid}}

	if (typeof postInitHook == 'function') {
		postInitHook();
	}
});

function setMode(newMode) {
	if (typeof preSetModeHook == 'function') {
		preSetModeHook(newMode);
	}

	if ( (arguments.length < 2) || (arguments[1]) ) clearMsgs();

	if (mode != newMode) {
		mode = newMode;

		var saveButton = $('#save{{uTableName}}Button');
		var abandonButton = $('#abandon{{uTableName}}Button');

		saveButton.removeClass('btn btn-primary btn-success btn-warning btn-danger');
		abandonButton.removeClass('btn btn-primary btn-success btn-warning btn-danger');

		var hotKeyHelp = '';

		switch (mode) {
		case SEARCH_MODE:
			$('#existing{{uTableName}}sCont').show('fast');
			$('#{{tableName}}FormCont').hide('fast');
			$('#crudFormButtons').hide();
			{{tableName}}sUpdateTable();
			$("#{{tableName}}sSearchGridCont input.jax-grid-pager-search-text-input:first").focus();
			hotKeyHelp +=
				' <strong>'+_t('crud.hotkey.enter')+'</strong>:'+_t('crud.crudEditLinkTitle')+
				' <strong>'+_t('crud.hotkey.ins')+'</strong>:'+_t('crud.crudAddShortLinkTitle');
			if ({{allowAddSimilar}}) {
				hotKeyHelp += ' <strong>'+_t('crud.hotkey.alt_ins')+'</strong>:'+_t('crud.crudAddSimilarLinkTitle');
			}
			hotKeyHelp +=
				' <strong>'+_t('crud.hotkey.del')+'</strong>:'+_t('crud.crudDeleteLinkTitle')+
				' <strong>'+_t('crud.hotkey.alt_v')+'</strong>:'+_t('crud.crudViewLinkTitle');
			break;
		case ADD_MODE:
			$('#existing{{uTableName}}sCont').hide('fast');
			$('#{{tableName}}FormCont').show('fast');
			$('#crudFormButtons').show();
			saveButton.attr('value', _t('crud.addSaveLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}')).addClass('btn btn-success').show();
			abandonButton.attr('value', _t('crud.addAbandonLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}')).addClass('btn btn-warning').show();
			hotKeyHelp +=
				' <strong>'+_t('crud.hotkey.esc')+'</strong>:'+_t('crud.addAbandonLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}');
{{addFocusJS}}
			break;
		case EDIT_MODE:
			$('#existing{{uTableName}}sCont').hide('fast');
			$('#{{tableName}}FormCont').show('fast');
			$('#crudFormButtons').show();
			saveButton.attr('value', _t('crud.editSaveLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}')).addClass('btn btn-success').show();
			abandonButton.attr('value', _t('crud.editAbandonLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}')).addClass('btn btn-warning').show();
			hotKeyHelp +=
				' <strong>'+_t('crud.hotkey.esc')+'</strong>:'+_t('crud.editAbandonLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}')+
				' <strong>'+_t('crud.hotkey.f7')+'</strong>:'+_t('crud.searchRelevantEntries');
{{editFocusJS}}
			break;
		case DELETE_MODE:
			$('#existing{{uTableName}}sCont').hide('fast');
			$('#{{tableName}}FormCont').show('fast');
			$('#crudFormButtons').show();
			saveButton.attr('value', _t('crud.deleteSaveLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}')).addClass('btn btn-danger').show();
			abandonButton.attr('value', _t('crud.deleteAbandonLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}')).addClass('btn btn-primary').show();
			hotKeyHelp +=
				' <strong>'+_t('crud.hotkey.esc')+'</strong>:'+_t('crud.deleteAbandonLinkTitleBase')+' '+_t('tableDescription', '{{tableDescription}}');
{{editFocusJS}}
			break;
		case VIEW_MODE:
			$('#existing{{uTableName}}sCont').hide('fast');
			$('#{{tableName}}FormCont').show('fast');
			$('#crudFormButtons').show();
			saveButton.attr('value', '').hide();
			abandonButton.attr('value', _t('crud.viewDoneLinkTitle')).addClass('btn btn-primary').show();
			hotKeyHelp += ' <strong>'+_t('crud.hotkey.esc')+'</strong>:'+_t('crud.viewDoneLinkTitle');
{{editFocusJS}}
			break;
		}

		hotKeyHelp = $.trim(hotKeyHelp);
		searchModeHotKeyHelp = (mode == SEARCH_MODE) ? hotKeyHelp : '';
		$('#hotKeyHelp').html(hotKeyHelp);
	}

	if (typeof postSetModeHook == 'function') {
		postSetModeHook(newMode);
	}
} // setMode()

function {{tableName}}sUpdateTable() {
{{if_searchPresentation_dataTables}}
	{{tableName}}sDataTable.fnDraw(false);
{{/if_searchPresentation_dataTables}}
{{if_searchPresentation_AJAXSearchGrid}}
	{{tableName}}sSearchGrid.$scope.triggerSearch({{tableName}}sSearchGrid.$scope.userGestureSearchTimeoutMS, false);
{{/if_searchPresentation_AJAXSearchGrid}}
} // {{tableName}}sUpdateTable()

function add{{uTableName}}() {
	$('#{{tableName}}FormModeDisplay').text(_t('crud.crudAddModeTitleBase')+' '+_t('tableDescription', '{{tableDescription}}'));
	if (load{{uTableName}}IntoForm(0, ADD_MODE)) setMode(ADD_MODE);
} // add{{uTableName}}()

function edit{{uTableName}}(id) {
	$('#{{tableName}}FormModeDisplay').text(_t('crud.crudEditModeTitleBase')+' '+_t('tableDescription', '{{tableDescription}}'));
	if (load{{uTableName}}IntoForm(id, EDIT_MODE)) setMode(EDIT_MODE);
} // edit{{uTableName}}()

function addSimilar{{uTableName}}(id) {
	$('#{{tableName}}FormModeDisplay').text(_t('crud.crudAddModeTitleBase')+' '+_t('tableDescription', '{{tableDescription}}'));
	if (load{{uTableName}}IntoForm(id, ADD_MODE)) {
		$("#{{tableName}}Form [name='{{idCol}}']").setValue('0');
		setMode(ADD_MODE);
	}
} // addSimilar{{uTableName}}()

function delete{{uTableName}}(id) {
	$('#{{tableName}}FormModeDisplay').text(_t('crud.crudDeleteModeTitleBase')+' '+_t('tableDescription', '{{tableDescription}}'));
	if (load{{uTableName}}IntoForm(id, DELETE_MODE)) setMode(DELETE_MODE);
} // delete{{uTableName}}()

function view{{uTableName}}(id) {
	$('#{{tableName}}FormModeDisplay').text(_t('crud.crudViewModeTitleBase')+' '+_t('tableDescription', '{{tableDescription}}'));
	if (load{{uTableName}}IntoForm(id, VIEW_MODE)) setMode(VIEW_MODE);
} // view{{uTableName}}()

function load{{uTableName}}IntoForm(id, newMode) {
	var allowEditing = (newMode == ADD_MODE) || (newMode == EDIT_MODE);
	formLoading = true;
	formLoadingAddMode = (newMode == ADD_MODE);
	formLoadingEditMode = (newMode == EDIT_MODE);
	formLoadingDeleteMode = (newMode == DELETE_MODE);
	formLoadingViewMode = (newMode == VIEW_MODE);
	formLoadingMode = newMode;

	try {
		if (typeof preLoadFormHook == 'function') {
			preLoadFormHook(id, newMode, allowEditing);
		}
		document.{{tableName}}Form.reset();
		var url = getBaseURL()+'?command={{crudLoadCommand}}&{{idCol}}='+encodeURIComponent(id);
		if (typeof fixupAJAXURL == 'function') {
			url = fixupAJAXURL(url);
		}
		var json = $.ajax({
			type:'GET',
			url:url,
			async:false,
			cache:false,
			global:false,
			processData:false
		}).responseText;
		var rows = [];
		if (json != '') eval('rows = '+json+';');
		if (rows.length != 1) return false;
		var row = rows[0];

		if (newMode == ADD_MODE) row.{{idCol}} = 0;

		if (typeof midLoadFormHook == 'function') {
			midLoadFormHook(id, newMode, allowEditing, row);
		}
	
		$('#{{tableName}}Form').formHash(row);

		if (typeof mid2LoadFormHook == 'function') {
			mid2LoadFormHook(id, newMode, allowEditing, row);
		}
	
		// Set all fields readonly/disabled if not editable, not readonly/disabled if editable.
		$('#{{tableName}}Form input').attr('readOnly', !allowEditing);
		$("#{{tableName}}Form input[type='checkbox'], #{{tableName}}Form input[type='file'], #{{tableName}}Form input[type='radio'], #{{tableName}}Form select").attr('disabled', !allowEditing);
		// The id field is never editable.
		$("#{{tableName}}Form input[name='{{idCol}}']").attr('readOnly', true);

		// These id field and its label should not be visible in add mode.
		if (newMode != ADD_MODE) $('#{{idCol}}TR').show(); else $('#{{idCol}}TR').hide();

		setTimeout('scroll(0,0);', 10);

		if (typeof postLoadFormHook == 'function') {
			postLoadFormHook(id, newMode, allowEditing, row);
		}

		// Attach any special input field features which have not yet been attached.
		attachSpecialFieldFeatures();
		// Filter any input fields which have special filters attached.
		filterFieldsWithSpecialFeatures();
		// Show or hide special features, such as pop-up query and date picker triggers.
		autoShowOrHideSpecialFieldFeatures(!allowEditing);

{{hookAutocompleteSingleRowSelectorsToInputsJS}}

		return true;
	} finally {
		formLoading = formLoadingAddMode = formLoadingEditMode = formLoadingDeleteMode = formLoadingViewMode = false;
		formLoadingMode = -1;
	}
} // load{{uTableName}}IntoForm()

function save{{uTableName}}() {
	if (typeof preSaveHook == 'function') {
		var preSaveHookResult = preSaveHook();
		if ((typeof preSaveHookResult  == 'boolean') && (preSaveHookResult == false)) {
			return;
		}
	}

	clearMsgs();

	switch (mode) {
	case ADD_MODE:
	case EDIT_MODE:
		$("#{{tableName}}Form input[name='command']").val('save{{uTableName}}');
		submittingForm = true;
		try {
			$("#{{tableName}}Form").submit();
		} finally {
			submittingForm = false;
		}
		break;
	case DELETE_MODE:
		bootbox.confirm(
			_t('crud.deleteConfirmMsgBase').replace('tableDescription', _t('tableDescription', '{{tableDescription}}')),
			function(result) {
				if (!result) return;
				var url = getBaseURL();
				if (typeof fixupAJAXURL == 'function') {
					url = fixupAJAXURL(url);
				}
				var json = $.ajax({
					type:'POST',
					url:url,
					async:false,
						cache:false,
					global:false,
					processData:true,
					data:{command:'delete{{uTableName}}', {{idCol}}:$("#{{tableName}}Form input[name='{{idCol}}']").getValue()}
				}).responseText;
				if (json != '') {
					parseMsgsFromJSON(json);
				if ($('#errorMsg').text() == '') setMode(SEARCH_MODE, false);
				}
			}
		);
		break;
	default:
		setMode(SEARCH_MODE, false);
	}
} // save{{uTableName}}()

function abandon{{uTableName}}() {
	if ((mode == ADD_MODE) || (mode == EDIT_MODE)) {
		bootbox.confirm(
			_t('crud.abandonConfirmMsgBase').replace('tableDescription', _t('tableDescription', '{{tableDescription}}')),
			function(result) {
				if (result) {
					setMode(SEARCH_MODE);
				}
			}
		);
	} else {
		setMode(SEARCH_MODE);
	}
} // abandon{{uTableName}}()
