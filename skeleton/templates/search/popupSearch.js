{{generatedFileMessage}}

{{if_searchPresentation_dataTables}}
var {{popupSearchName}}_aoColumnDefs, {{popupSearchName}};
(function() {
	var ci = 0;
	{{popupSearchName}}_aoColumnDefs = [
{{popupSearchColumns}}
	];

	{{popupSearchName}} = new PopupSearch(
		'{{popupSearchName}}Table',
		{
			bProcessing: true,
			bServerSide: true,
			sAjaxSource: getBaseURL()+'?command={{searchCommand}}',
			sPaginationType: 'full_numbers',
			aoColumnDefs: {{popupSearchName}}_aoColumnDefs,
			bAutoWidth: false
{{popupSearchTableCallbacks}}
		}
	);
})();
{{/if_searchPresentation_dataTables}}
{{if_searchPresentation_AJAXSearchGrid}}
var {{popupSearchName}} = new PopupSearch(
	'{{popupSearchName}}Table',
	{
		searchPresentation:'AJAXSearchGrid',
		searchCommand:'{{searchCommand}}',
		columnNames:{{popupSearchColumnNamesJSON}},
		columnFilters:{{popupSearchColumnFilters}},
		extraQueryParams:{{popupSearchExtraQueryParamsJSON}},
		idColumn:{{idColumnJSON}},
		headerColumnsHTML:{{popupSearchGridHeaderColumnsHTMLJSON}},
		bodyColumnsHTML:{{popupSearchGridBodyColumnsHTMLJSON}},
		defaultSorts:{{popupSearchDefaultSortsJSON}}{{popupSearchCallbacks}},
		rowSelectCallbackFunction:{{rowSelectJavaScriptCallbackFunctionJSON}}
	}
);
{{/if_searchPresentation_AJAXSearchGrid}}
