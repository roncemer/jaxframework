<?php
// Copyright (c) 2011-2014 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('DDL', false)) include dirname(dirname(__FILE__)).'/phpdaogen/DDL.class.php';
if (!class_exists('Spyc', false)) include dirname(dirname(__FILE__)).'/phpdaogen/spyc/spyc.php';

$ALLOWED_QUERY_OPERATORS = array('=', '<>', '<', '<=', '>', '>=', 'beginsWith', 'contains', 'endsWith');
$ALLOWED_NUMERIC_QUERY_OPERATORS = array('=', '<>', '<', '<=', '>', '>=');
$ALLOWED_STRING_QUERY_OPERATORS = array('=', '<>', '<', '<=', '>', '>=', 'beginsWith', 'contains', 'endsWith', 'fulltext');
$ALLOWED_BINARY_QUERY_OPERATORS = array('=', '<>');
$ALLOWED_PS_TYPES = array('boolean', 'int', 'float', 'double', 'string', 'match', 'binary');

$scriptDir = realpath(dirname($argv[0]));
$docroot = dirname($scriptDir).'/html';
$ddlDir = dirname($scriptDir).'/ddl';
$cfgDir = dirname($scriptDir).'/gencfg';

$usageDescription = 'Put usage description here.';

$processAllTablesByDefault = true;
$loadYAMLGeneratorConfig = true;

$currentGeneratorConfigFilename = '';

class GenCfgValidation {
	public $nodePath;
	public $allowedValueTypes;
	public $allowedChildNodeNameTypes;

	public function GenCfgValidation($nodePath, $allowedValueTypesCSV, $allowedChildNodeNameTypesCSV = 'string') {
		$this->nodePath = $nodePath;
		$this->allowedValueTypes = explode(',', $allowedValueTypesCSV);
		$this->allowedChildNodeNameTypes = explode(',', $allowedChildNodeNameTypesCSV);
	}
}

$GENERATOR_CFG_VALIDATIONS = array(
	new GenCfgValidation('', 'string,array'),

	// Top-level sections.
	new GenCfgValidation('tableDescription', 'string'),
	new GenCfgValidation('tableDescriptions', 'string'),
	new GenCfgValidation('loggedInId', 'string'),
	new GenCfgValidation('classAutoloadPaths', 'string'),
	new GenCfgValidation('jaxInclude', 'string'),
	new GenCfgValidation('jaxJQuery', 'string'),
	new GenCfgValidation('jaxJS', 'string'),

	// Searches top-level section.
	new GenCfgValidation('searches', 'array,NULL'),
	new GenCfgValidation('searches/*', 'array,NULL'),
	new GenCfgValidation('searches/*/outputPath', 'string'),
	new GenCfgValidation('searches/*/docRootPath', 'string'),
	new GenCfgValidation('searches/*/searchCommand', 'string'),
	new GenCfgValidation('searches/*/searchTemplate', 'string'),
	new GenCfgValidation('searches/*/phpClasses', 'array'),
	new GenCfgValidation('searches/*/phpClasses/*', 'string'),
	new GenCfgValidation('searches/*/phpIncludes', 'array', 'string,integer'),
	new GenCfgValidation('searches/*/phpIncludes/*', 'string'),
	new GenCfgValidation('searches/*/extraSelectColumns', 'string,array', 'string,integer'),
	new GenCfgValidation('searches/*/extraSelectColumns/*', 'string'),
	new GenCfgValidation('searches/*/joins', 'string'),
	new GenCfgValidation('searches/*/searchableColumns', 'array', 'string,integer'),
	new GenCfgValidation('searches/*/searchableColumns/*', 'array'),
	new GenCfgValidation('searches/*/searchableColumns/*/columnName', 'string'),
	new GenCfgValidation('searches/*/searchableColumns/*/tableAlias', 'string'),
	new GenCfgValidation('searches/*/searchableColumns/*/title', 'string'),
	new GenCfgValidation('searches/*/searchableColumns/*/sqlType', 'string'),
	new GenCfgValidation('searches/*/searchableColumns/*/queryOperator', 'string'),
	new GenCfgValidation('searches/*/searchableColumns/*/unsignedSearch', 'boolean'),
	new GenCfgValidation('searches/*/andWhere', 'string'),
	new GenCfgValidation('searches/*/andWhereAssignments', 'array', 'string,integer'),
	new GenCfgValidation('searches/*/andWhereAssignments/*', 'array'),
	new GenCfgValidation('searches/*/andWhereAssignments/*/expression', 'string'),
	new GenCfgValidation('searches/*/andWhereAssignments/*/psType', 'string'),
	new GenCfgValidation('searches/*/groupBy', 'string'),
	new GenCfgValidation('searches/*/rowProcessingPHPCode', 'string'),
	new GenCfgValidation('searches/*/forbiddenColumns', 'array,NULL', 'integer'),
	new GenCfgValidation('searches/*/forbiddenColumns/*', 'string'),

	// autocompleteSearches top-level section.
	new GenCfgValidation('autocompleteSearches', 'array,NULL'),
	new GenCfgValidation('autocompleteSearches/*', 'array,NULL'),
	new GenCfgValidation('autocompleteSearches/*/outputPath', 'string'),
	new GenCfgValidation('autocompleteSearches/*/docRootPath', 'string'),
	new GenCfgValidation('autocompleteSearches/*/idColumn', 'string'),
	new GenCfgValidation('autocompleteSearches/*/idColumnPSType', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchCommand', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchTemplate', 'string'),
	new GenCfgValidation('autocompleteSearches/*/phpClasses', 'array'),
	new GenCfgValidation('autocompleteSearches/*/phpClasses/*', 'string'),
	new GenCfgValidation('autocompleteSearches/*/phpIncludes', 'array', 'string,integer'),
	new GenCfgValidation('autocompleteSearches/*/phpIncludes/*', 'string'),
	new GenCfgValidation('autocompleteSearches/*/extraSelectColumns', 'string,array', 'string,integer'),
	new GenCfgValidation('autocompleteSearches/*/extraSelectColumns/*', 'string'),
	new GenCfgValidation('autocompleteSearches/*/joins', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchableColumns', 'array', 'string,integer'),
	new GenCfgValidation('autocompleteSearches/*/searchableColumns/*', 'array'),
	new GenCfgValidation('autocompleteSearches/*/searchableColumns/*/columnName', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchableColumns/*/tableAlias', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchableColumns/*/sqlType', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchableColumns/*/queryOperator', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchableColumns/*/unsignedSearch', 'boolean'),
	new GenCfgValidation('autocompleteSearches/*/andWhere', 'string'),
	new GenCfgValidation('autocompleteSearches/*/andWhereAssignments', 'array', 'string,integer'),
	new GenCfgValidation('autocompleteSearches/*/andWhereAssignments/*', 'array'),
	new GenCfgValidation('autocompleteSearches/*/andWhereAssignments/*/expression', 'string'),
	new GenCfgValidation('autocompleteSearches/*/andWhereAssignments/*/psType', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchResultLabelExpression', 'string'),
	new GenCfgValidation('autocompleteSearches/*/searchResultValueExpression', 'string'),

	// popupSearches top-level section.
	new GenCfgValidation('popupSearches', 'array,NULL'),
	new GenCfgValidation('popupSearches/*', 'array,NULL'),
	new GenCfgValidation('popupSearches/*/likePopupSearch', 'string'),
	new GenCfgValidation('popupSearches/*/outputPath', 'string'),
	new GenCfgValidation('popupSearches/*/popupSearchTemplate', 'string'),
	new GenCfgValidation('popupSearches/*/searchPresentation', 'string'),
	new GenCfgValidation('popupSearches/*/searchCommand', 'string'),
	new GenCfgValidation('popupSearches/*/defaultSorts', 'array,NULL', 'string,integer'),
	new GenCfgValidation('popupSearches/*/defaultSorts/*', 'array'),
	new GenCfgValidation('popupSearches/*/defaultSorts/*/attr', 'string'),
	new GenCfgValidation('popupSearches/*/defaultSorts/*/dir', 'integer'),
	new GenCfgValidation('popupSearches/*/idColumn', 'string'),
	new GenCfgValidation('popupSearches/*/beforeSearchCallback', 'string'),
	new GenCfgValidation('popupSearches/*/modifyURLCallback', 'string'),
	new GenCfgValidation('popupSearches/*/afterSearchCallback', 'string'),
	new GenCfgValidation('popupSearches/*/rowSelectJavaScriptCallbackFunction', 'string'),
	new GenCfgValidation('popupSearches/*/columns', 'array'),
	new GenCfgValidation('popupSearches/*/columns/*', 'array'),
	new GenCfgValidation('popupSearches/*/columns/*/displayType', 'string'),
	new GenCfgValidation('popupSearches/*/columns/*/sortable', 'boolean'),
	new GenCfgValidation('popupSearches/*/columns/*/heading', 'string'),
	new GenCfgValidation('popupSearches/*/columns/*/headerCSSClass', 'string'),
	new GenCfgValidation('popupSearches/*/columns/*/columnCSSClass', 'string'),
	new GenCfgValidation('popupSearches/*/columns/*/headerTemplate', 'string'),
	new GenCfgValidation('popupSearches/*/columns/*/columnTemplate', 'string'),
	new GenCfgValidation('popupSearches/*/columns/*/columnFilters', 'string'),
	new GenCfgValidation('popupSearches/*/columns/*/fnRender', 'string'),
	new GenCfgValidation('popupSearches/*/invisibleColumns', 'array', 'string,integer'),
	new GenCfgValidation('popupSearches/*/invisibleColumns/*', 'string'),
	new GenCfgValidation('popupSearches/*/columnFilters', 'array,NULL', 'string'),
	new GenCfgValidation('popupSearches/*/columnFilters/*', 'string'),
	new GenCfgValidation('popupSearches/*/extraQueryParams', 'array,NULL', 'string,integer'),
	new GenCfgValidation('popupSearches/*/extraQueryParams/*', 'string,integer,float,double,boolean'),
	new GenCfgValidation('popupSearches/*/fnDrawCallback', 'string'),
	new GenCfgValidation('popupSearches/*/fnServerData', 'string'),

	// loaders top-level section.
	new GenCfgValidation('loaders', 'array,NULL'),
	new GenCfgValidation('loaders/*', 'array,NULL'),
	new GenCfgValidation('loaders/*/outputPath', 'string'),
	new GenCfgValidation('loaders/*/docRootPath', 'string'),
	new GenCfgValidation('loaders/*/idColumn', 'string'),
	new GenCfgValidation('loaders/*/idColumnPSType', 'string'),
	new GenCfgValidation('loaders/*/searchCommand', 'string'),
	new GenCfgValidation('loaders/*/loaderTemplate', 'string'),
	new GenCfgValidation('loaders/*/phpClasses', 'array'),
	new GenCfgValidation('loaders/*/phpClasses/*', 'string'),
	new GenCfgValidation('loaders/*/phpIncludes', 'array', 'string,integer'),
	new GenCfgValidation('loaders/*/phpIncludes/*', 'string'),
	new GenCfgValidation('loaders/*/andWhere', 'string'),
	new GenCfgValidation('loaders/*/andWhereAssignments', 'array', 'string,integer'),
	new GenCfgValidation('loaders/*/andWhereAssignments/*', 'array'),
	new GenCfgValidation('loaders/*/andWhereAssignments/*/expression', 'string'),
	new GenCfgValidation('loaders/*/andWhereAssignments/*/psType', 'string'),
	new GenCfgValidation('loaders/*/relations', 'array,NULL'),
	new GenCfgValidation('loaders/*/relations/*', 'array'),
	new GenCfgValidation('loaders/*/relations/*/table', 'string'),
	new GenCfgValidation('loaders/*/relations/*/useDAO', 'boolean'),
	new GenCfgValidation('loaders/*/relations/*/relationType', 'string'),
	new GenCfgValidation('loaders/*/relations/*/offset', 'integer'),
	new GenCfgValidation('loaders/*/relations/*/limit', 'integer'),
	new GenCfgValidation('loaders/*/relations/*/sqlQuery', 'string'),
	new GenCfgValidation('loaders/*/relations/*/sqlQueryAssignments', 'array', 'string,integer'),
	new GenCfgValidation('loaders/*/relations/*/sqlQueryAssignments/*', 'array'),
	new GenCfgValidation('loaders/*/relations/*/sqlQueryAssignments/*/expression', 'string'),
	new GenCfgValidation('loaders/*/relations/*/sqlQueryAssignments/*/psType', 'string'),
	new GenCfgValidation('loaders/*/relations/*/queryOperator', 'string'),
	new GenCfgValidation('loaders/*/relations/*/local', 'string'),
	new GenCfgValidation('loaders/*/relations/*/foreign', 'string'),
	new GenCfgValidation('loaders/*/relations/*/orderBy', 'string'),
	new GenCfgValidation('loaders/*/forbiddenColumns', 'array,NULL', 'integer'),
	new GenCfgValidation('loaders/*/forbiddenColumns/*', 'string'),

	// cruds top-level section.
	new GenCfgValidation('cruds', 'array,NULL'),
	new GenCfgValidation('cruds/*', 'array'),
	new GenCfgValidation('cruds/*/outputPath', 'string'),
	new GenCfgValidation('cruds/*/docRootPath', 'string'),
	new GenCfgValidation('cruds/*/phpClasses', 'array'),
	new GenCfgValidation('cruds/*/phpClasses/*', 'string'),
	new GenCfgValidation('cruds/*/postInitPHPIncludes', 'array', 'string,integer'),
	new GenCfgValidation('cruds/*/postInitPHPIncludes/*', 'string'),
	new GenCfgValidation('cruds/*/javaScriptFiles', 'array,NULL', 'integer'),
	new GenCfgValidation('cruds/*/javaScriptFiles/*', 'string'),
	new GenCfgValidation('cruds/*/cssFiles', 'array,NULL', 'integer'),
	new GenCfgValidation('cruds/*/cssFiles/*', 'string'),
	new GenCfgValidation('cruds/*/allowAddSimilar', 'boolean'),
	new GenCfgValidation('cruds/*/addFocusField', 'string'),
	new GenCfgValidation('cruds/*/editFocusField', 'string'),
	new GenCfgValidation('cruds/*/onlyUpdateColumns', 'array', 'integer'),
	new GenCfgValidation('cruds/*/onlyUpdateColumns/*', 'string'),
	new GenCfgValidation('cruds/*/neverUpdateColumns', 'array', 'integer'),
	new GenCfgValidation('cruds/*/neverUpdateColumns/*', 'string'),
	// crudSearch subsection under cruds top-level section.
	new GenCfgValidation('cruds/*/crudSearch', 'array'),
	new GenCfgValidation('cruds/*/crudSearch/likePopupSearch', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/searchPresentation', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/searchCommand', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/defaultSorts', 'array,NULL', 'string,integer'),
	new GenCfgValidation('cruds/*/crudSearch/defaultSorts/*', 'array'),
	new GenCfgValidation('cruds/*/crudSearch/defaultSorts/*/attr', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/defaultSorts/*/dir', 'integer'),
	new GenCfgValidation('cruds/*/crudSearch/beforeSearchCallback', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/modifyURLCallback', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/afterSearchCallback', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns', 'array'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*', 'array'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/displayType', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/sortable', 'boolean'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/heading', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/headerCSSClass', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/columnCSSClass', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/headerTemplate', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/columnTemplate', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/columnFilters', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columns/*/fnRender', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/invisibleColumns', 'array', 'string,integer'),
	new GenCfgValidation('cruds/*/crudSearch/invisibleColumns/*', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/columnFilters', 'array,NULL', 'string,integer'),
	new GenCfgValidation('cruds/*/crudSearch/columnFilters/*', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/extraQueryParams', 'array,NULL', 'string,integer'),
	new GenCfgValidation('cruds/*/crudSearch/extraQueryParams/*', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/crudSearch/fnDrawCallback', 'string'),
	new GenCfgValidation('cruds/*/crudSearch/fnServerData', 'string'),
	// crudLoad subsection under cruds top-level section.
	new GenCfgValidation('cruds/*/crudLoad', 'array'),
	new GenCfgValidation('cruds/*/crudLoad/loadCommand', 'string'),
	// formFields subsection under cruds top-level section.
	new GenCfgValidation('cruds/*/formFields', 'array'),
	new GenCfgValidation('cruds/*/formFields/*', 'array'),
	new GenCfgValidation('cruds/*/formFields/*/title', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/formFields/*/placeholder', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/inputType', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/onclick', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/onchange', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/ajaxAutocompleteCommand', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/ajaxAutocompleteMinLength', 'integer'),
	new GenCfgValidation('cruds/*/formFields/*/onPopupSearch', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/descriptionField', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/descriptionFieldSize', 'integer'),
	new GenCfgValidation('cruds/*/formFields/*/descriptionFieldMaxLength', 'integer'),
	new GenCfgValidation('cruds/*/formFields/*/size', 'integer'),
	new GenCfgValidation('cruds/*/formFields/*/maxlength', 'integer'),
	new GenCfgValidation('cruds/*/formFields/*/cssClass', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/readonly', 'boolean'),
	new GenCfgValidation('cruds/*/formFields/*/disabled', 'boolean'),
	new GenCfgValidation('cruds/*/formFields/*/rows', 'integer'),
	new GenCfgValidation('cruds/*/formFields/*/cols', 'integer'),
	new GenCfgValidation('cruds/*/formFields/*/accept', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/multiple', 'boolean'),
	new GenCfgValidation('cruds/*/formFields/*/options', 'array', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/formFields/*/options/*', 'array'),
	new GenCfgValidation('cruds/*/formFields/*/options/*/title', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/formFields/*/title', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/formFields/*/optionsFromAssociativeArray', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/value', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/html', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/tabsPosition', 'string'),
	new GenCfgValidation('cruds/*/formFields/*/autocompleteSingleRowSelector', 'array'),
	new GenCfgValidation('cruds/*/formFields/*/autocompleteSingleRowSelector/*', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/formFields/*/autocompleteSingleRowSelector/rowFetcherOptionalParameters', 'array'),
	new GenCfgValidation('cruds/*/formFields/*/autocompleteSingleRowSelector/rowFetcherOptionalParameters/*', 'string,integer,float,double,boolean'),
	// filters subsection under cruds top-level section.
	new GenCfgValidation('cruds/*/filters', 'array,NULL'),
	new GenCfgValidation('cruds/*/filters/*', 'array,NULL', 'string,integer'),
	new GenCfgValidation('cruds/*/filters/*/*', 'array,NULL', 'string,integer'),
	new GenCfgValidation('cruds/*/filters/*/*/class', 'string'),
	new GenCfgValidation('cruds/*/filters/*/*/include', 'string'),
	new GenCfgValidation('cruds/*/filters/*/*/params', 'array,NULL', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/filters/*/*/params/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/filters/*/*/params/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/filters/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/filters/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/filters/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/filters/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	// validators subsection under cruds top-level section.
	new GenCfgValidation('cruds/*/validators', 'array,NULL'),
	new GenCfgValidation('cruds/*/validators/*', 'array,NULL', 'string,integer'),
	new GenCfgValidation('cruds/*/validators/*/*', 'array,NULL', 'string,integer'),
	new GenCfgValidation('cruds/*/validators/*/*/class', 'string'),
	new GenCfgValidation('cruds/*/validators/*/*/include', 'string'),
	new GenCfgValidation('cruds/*/validators/*/*/phpCondition', 'string'),
	new GenCfgValidation('cruds/*/validators/*/*/params', 'array,NULL', 'string,integer,float,double,boolean'),
	new GenCfgValidation('cruds/*/validators/*/*/params/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/validators/*/*/params/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/validators/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/validators/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/validators/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
	new GenCfgValidation('cruds/*/validators/*/*/params/*/*/*', 'string,integer,float,double,boolean,array,NULL', 'string,integer,float,double,boolean,array'),
);

$GENERATOR_CFG_VALIDATIONS_BY_PATH = array();
foreach ($GENERATOR_CFG_VALIDATIONS as $__val) {
	$GENERATOR_CFG_VALIDATIONS_BY_PATH[$__val->nodePath] = $__val;
}
unset($__val);

$SEARCH_PRESENTATION_DATATABLES_ONLY_PARAMS = array(
	'fnDrawCallback',
	'fnServerData',
);
$SEARCH_PRESENTATION_DATATABLES_ONLY_COLUMN_PARAMS = array(
	'fnRender',
);

$SEARCH_PRESENTATION_AJAXSEARCHGRID_ONLY_PARAMS = array(
	'defaultSorts',
	'beforeSearchCallback',
	'modifyURLCallback',
	'afterSearchCallback',
	'invisibleColumns',
	'columnFilters',
	'extraQueryParams',
);
$SEARCH_PRESENTATION_AJAXSEARCHGRID_ONLY_COLUMN_PARAMS = array(
	'columnFilters',
	'headerTemplate',
	'columnTemplate',
);

function usage() {
	global $argv, $usageDescription, $docroot, $processAllTablesByDefault;

	fputs(
		STDERR,
		"Usage: php ".basename($argv[0])." [options] [<tableName> [<tableName> ...]]\n".
		"    tableName  - Table name(s) to process.\n".
		($processAllTablesByDefault ?
			"                 If no table names are specified, all tables will be processed.\n" :
			'').
		"$usageDescription\n".
		"Options:\n".
		"    -ddlfromdb       Load the table DDL (schema) from a database.\n".
		"    -dbclass <type>  Connection class for fetching DDL.  Defaults to MySQLiConnection.\n".
		"    -dbhost <host>   Database host for fetching DDL.  Defaults to localhost.\n".
		"    -dbuser <user>   Database user for fetching DDL.  Defaults to root.\n".
		"    -dbpassword <password>   Database password for fetching DDL.  Defaults to empty.\n".
		"    -dbdatabase <database>   Database name for fetching DDL.  Defaults to empty.\n".
		"    -docroot <dir>   Specify the path to the document root.\n".
		"                     (defaults to $docroot)\n".
		"    -nolang          Don't generate or update language resource files.\n"
	);
	if (function_exists('moreUsage')) moreUsage();
}

function process() {
	global $argc, $argv, $exitVal, $tableNames, $docroot, $ddlDir,
		$tableNames, $ddlFromDB, $dbClass, $dbHost, $dbUser, $dbPassword, $dbDatabase,
		$processAllTablesByDefault, $enableLangFiles;

	$tableNames = array();
	$ddlFromDB = false;
	$dbClass = 'MySQLiConnection';
	$dbHost = 'localhost';
	$dbUser = 'root';
	$dbPassword = '';
	$dbDatabase = '';
	$enableLangFiles = true;

	for ($ai = 1; $ai < $argc; $ai++) {
		$arg = $argv[$ai];
		if ( (strlen($arg) > 0) && ($arg[0] == '-') ) {
			switch ($arg) {
			case '-ddlfromdb':
				$ddlFromDB = true;
				break;
			case '-dbclass':
				$ai++;
				if ($ai >= $argc) {
					usage();
					exit(1);
				}
				$dbClass = $argv[$ai];
				break;
			case '-dbhost':
				$ai++;
				if ($ai >= $argc) {
					usage();
					exit(1);
				}
				$dbHost = $argv[$ai];
				break;
			case '-dbuser':
				$ai++;
				if ($ai >= $argc) {
					usage();
					exit(1);
				}
				$dbUser = $argv[$ai];
				break;
			case '-dbpassword':
				$ai++;
				if ($ai >= $argc) {
					usage();
					exit(1);
				}
				$dbPassword = $argv[$ai];
				break;
			case '-dbdatabase':
				$ai++;
				if ($ai >= $argc) {
					usage();
					exit(1);
				}
				$dbDatabase = $argv[$ai];
				break;
			case '-docroot':
				$ai++;
				if ($ai >= $argc) {
					usage();
					exit(1);
				}
				$docroot = $argv[$ai];
				break;
			case '-nolang':
				$enableLangFiles = false;
				break;
			default:
				if (function_exists('parseArg')) {
					// Returns the # of ADDITIONAL arguments it used, or false if usage error.
					if (($res = parseArg($arg, $ai)) !== false) {
						if ($res > 0) $ai += $res;
						break;
					}
				}
				fprintf(STDERR, "Unrecognized command line switch: %s.\n", $arg);
				usage();
				exit(1);
			}
			continue;
		}	// if ( (strlen($arg) > 0) && ($arg[0] == '-') )
		$tableNames[] = $arg;
	}

	if (empty($tableNames)) {
		if ($processAllTablesByDefault) {
			$tableNames = array('all');
		}
	} else {
		$tableNames = array_unique($tableNames);
	}

	if (empty($tableNames)) {
		usage();
		exit(1);
	}

	$exitval = 0;
	foreach ($tableNames as $tableName) {
		$failed = false;
		if ($ddlFromDB) {
			if (!processTableFromDB($tableName)) $failed = true;
		} else {
			if (!processTableInDDLDir($tableName, $ddlDir)) $failed = true;
		}
		if ($failed) {
			fprintf(STDERR, "Table not processed: %s\n", $tableName);
			if ($exitval == 0) $exitval = 3;
		}
	}
	exit($exitval);
}

function processTableFromDB($tableName) {
	global $exitval, $dbClass, $dbHost, $dbUser, $dbPassword, $dbDatabase;

	if (!class_exists($dbClass, false)) {
		include dirname(dirname(__FILE__)).'/phpdaogen/'.$dbClass.'.class.php';
	}
	$db = new $dbClass($dbHost, $dbUser, $dbPassword, $dbDatabase);
	$loader = new ConnectionDDLLoader();
	$ddl = $loader->loadDDL($db, false, ($tableName == 'all') ? array() : array($tableName));
	$db->close();
	if ($tableName == 'all') {
		$anySuccess = false;
		for ($tidx = 0, $n = count($ddl->topLevelEntities); $tidx < $n; $tidx++) {
			if ($ddl->topLevelEntities[$tidx] instanceof DDLTable) {
				$retval = processTableWithTableDDL($ddl->topLevelEntities[$tidx]);
				if ($retval) $anySuccess = true;
			}
		}
		if ($anySuccess) return true;
	} else {
		$tidx = $ddl->getTableIdxInTopLevelEntities($tableName);
		if ($tidx !== false) {
			return processTableWithTableDDL($ddl->topLevelEntities[$tidx]);
		}
	}
	return false;
}

function processTableInDDLDir($tableName, $ddlDir) {
	global $exitval;

	$anyProcessed = false;
	if (($dp = @opendir($ddlDir)) !== false) {
		while (($fn = readdir($dp)) !== false) {
			if (($fn == '.') || ($fn == '..')) continue;
			$fn = $ddlDir.'/'.$fn;
			if (is_dir($fn)) {
				if (processTableInDDLDir($tableName, $fn)) {
					closedir($dp);
					$anyProcessed = true;
				}
			}
			if (is_file($fn)) {
				try {
					$ddl = false;
					if ((strtolower(substr($fn, -5)) == '.yaml') ||
						(strtolower(substr($fn, -4)) == '.yml')) {
						$parser = new YAMLDDLParser();
						$ddl = $parser->parseFromYAML(file_get_contents($fn));
					} else if (strtolower(substr($fn, -4)) == '.xml') {
						$parser = new XMLDDLParser();
						$ddl = $parser->parseFromXML(file_get_contents($fn));
					}
					if ($ddl !== false) {
						if ($tableName == 'all') {
							$anySuccess = false;
							for ($tidx = 0, $n = count($ddl->topLevelEntities); $tidx < $n; $tidx++) {
								if ($ddl->topLevelEntities[$tidx] instanceof DDLTable) {
									$retval = processTableWithTableDDL($ddl->topLevelEntities[$tidx]);
									if ($retval) $anySuccess = true;
								}
							}
							if ($anySuccess) $anyProcessed = true;
						} else {
							$tidx = $ddl->getTableIdxInTopLevelEntities($tableName);
							if ($tidx !== false) {
								if (processTableWithTableDDL($ddl->topLevelEntities[$tidx])) {
									$anyProcessed = true;
								}
							}
						}
					}
				} catch (Exception $ex) {
					fprintf(STDERR, "%s\n%s\n", $ex->getMessage(), $ex->getTrace());
					if ($exitval == 0) $exitval = 2;
				}
			}
		}
		closedir($dp);
	}
	return $anyProcessed;
}

function processTableWithTableDDL($table) {
	global $loadYAMLGeneratorConfig;

	if ($loadYAMLGeneratorConfig) {
		$cfg = loadGeneratorConfig($table);
		if (empty($cfg)) return false;
		if (checkGeneratorConfig($cfg) === false) return false;
	} else {
		$cfg = null;
	}

	if (!$table->primaryKey) {
		fprintf(STDERR, "Table %s has no primary key.\n", $table->tableName);
		return false;
	}
	$pkColIdx = -1;
	if (count($table->primaryKey->columns) == 1) {
		if (($pkColIdx = $table->getColumnIdx($table->primaryKey->columns[0]->name)) >= 0) {
			if (!in_array($table->columns[$pkColIdx]->type, array('integer', 'smallint', 'bigint'))) {
				$pkColIdx = -1;
			}
		}
	}
	if ($pkColIdx < 0) {
		fprintf(STDERR, "Table %s primary key is incompatible or is not configured.\nThe primary key must be a single column of type integer, smallint or bigint.\n", $table->tableName);
		return false;
	}
	return processTable($table, $table->columns[$pkColIdx]->name, $cfg);
}

// Get DDL for a specific table.
// Returns a DDL instance, or false if not found.
// The returned DDL instance will NOT include inserts (DDLInsert).
function getTableDDL($tableName, $_ddlDir = null) {
	global $ddlFromDB, $dbClass, $dbHost, $dbUser, $dbPassword, $dbDatabase, $ddlDir;

	$ddl = new DDL();

	if ($ddlFromDB) {
		// Load DDL from database.
		if (!class_exists($dbClass, false)) {
			include dirname(dirname(__FILE__)).'/phpdaogen/'.$dbClass.'.class.php';
		}
		$db = new $dbClass($dbHost, $dbUser, $dbPassword, $dbDatabase);
		$loader = new ConnectionDDLLoader();
		$tmpddl = $loader->loadDDL($db, false, array($tableName));
		$db->close();
		mergeDDLForTable($ddl, $tmpddl, $tableName);
		unset($tmpddl);
	} else {
		// Load DDL from DDL file(s).

		if ($_ddlDir === null) $_ddlDir = $ddlDir;

		if (($dp = @opendir($_ddlDir)) !== false) {
			while (($fn = readdir($dp)) !== false) {
				if (($fn == '.') || ($fn == '..')) continue;
				$fn = $_ddlDir.'/'.$fn;
				if (is_dir($fn)) {
					if ((($tmpddl = getTableDDL($tableName, $fn)) !== false)) {
						mergeDDLForTable($ddl, $tmpddl, $tableName);
						unset($tmpddl);
					}
					continue;
				}
				if ((is_file($fn)) &&
					(strtolower(substr($fn, -9)) == '.ddl.yaml') ||
					(strtolower(substr($fn, -8)) == '.ddl.yml')) {
					try {
						$parser = new YAMLDDLParser();
						if (($tmpddl = $parser->parseFromYAML(file_get_contents($fn))) !== false) {
							mergeDDLForTable($ddl, $tmpddl, $tableName);
							unset($tmpddl);
						}
						unset($tmpddl);
						continue;
					} catch (Exception $ex) {
						fprintf(STDERR, "%s\n%s\n", $ex->getMessage(), $ex->getTrace());
						if ($exitval == 0) $exitval = 2;
					}
				}
			}
			closedir($dp);
		}
	}

	return $ddl;
}

function mergeDDLForTable($destddl, $srcddl, $tableName) {
	foreach ($srcddl->topLevelEntities as $obj) {
		if ($obj instanceof DDLInsert) continue;
		if ((($obj instanceof DDLForeignKey) && ($obj->localTableName == $tableName)) ||
			(isset($obj->tableName) && ($obj->tableName == $tableName))) {
			$destddl->topLevelEntities[] = $obj;
		}
	}
}

function loadGeneratorConfig($table) {
	global $cfgDir, $currentGeneratorConfigFilename;

	$tn = $table->tableName;
	$utn = ucfirst($tn);

	if (($fn = findGeneratorConfig($cfgDir, $tn)) !== false) {
		$currentGeneratorConfigFilename = $fn;
		$cfg = @Spyc::YAMLLoad($fn);
		if (!is_array($cfg)) $cfg = array();
	} else {
		$cfg = array();
	}
	if (empty($cfg)) return $cfg;

	if ((!isset($cfg['tableDescription'])) ||
		(!is_string($cfg['tableDescription'])) ||
		($cfg['tableDescription'] == '')) {
		$cfg['tableDescription'] = ucwords(str_replace('_', ' ', $tn));
	}
	if ((!isset($cfg['tableDescriptions'])) ||
		(!is_string($cfg['tableDescriptions'])) ||
		($cfg['tableDescriptions'] == '')) {
		$cfg['tableDescriptions'] = $cfg['tableDescription'].'s';
	}
	if ((!isset($cfg['jaxInclude'])) ||
		(!is_string($cfg['jaxInclude'])) ||
		($cfg['jaxInclude'] == '')) {
		$cfg['jaxInclude'] = 'jax/include';
	}
	if ((!isset($cfg['jaxJQuery'])) ||
		(!is_string($cfg['jaxJQuery'])) ||
		($cfg['jaxJQuery'] == '')) {
		$cfg['jaxJQuery'] = 'jax/jquery';
	}
	if ((!isset($cfg['jaxJS'])) ||
		(!is_string($cfg['jaxJS'])) ||
		($cfg['jaxJS'] == '')) {
		$cfg['jaxJS'] = 'jax/js';
	}
	if ((!isset($cfg['loggedInId'])) ||
		(!is_string($cfg['loggedInId'])) ||
		($cfg['loggedInId'] == '')) {
		$cfg['loggedInId'] = '$loggedInUser->id';
	}

	return $cfg;
}

function findGeneratorConfig($dir, $tableName) {
	$fn = $dir.'/'.$tableName.'.yaml';
	if (file_exists($fn)) return $fn;
	$fn = $dir.'/'.$tableName.'.yml';
	if (file_exists($fn)) return $fn;
	if (($dp = @opendir($dir)) !== false) {
		while (($fn = readdir($dp)) !== false) {
			if (($fn == '.') || ($fn == '..')) continue;
			$fn = $dir.'/'.$fn;
			if (is_dir($fn)) {
				if (($fn2 = findGeneratorConfig($fn, $tableName)) !== false) {
					@closedir($dp);
					return $fn2;
				}
			}
		}
		@closedir($dp);
	}
	return false;
}

function checkGeneratorConfig($nodeOrValue, $nodePath = '') {
	global $GENERATOR_CFG_VALIDATIONS_BY_PATH, $currentGeneratorConfigFilename;

	$nodePathSearch = str_replace("\t", '/', $nodePath);
	$origNodePathSearch = $nodePathSearch;
	if (!isset($GENERATOR_CFG_VALIDATIONS_BY_PATH[$nodePathSearch])) {
		$pieces = explode("\t", $nodePath);
		$pieces[count($pieces)-1] = '*';
		$wildcardNodePath = implode("\t", $pieces);
		$wildcardNodePathSearch = str_replace("\t", '/', $wildcardNodePath);
		if (!isset($GENERATOR_CFG_VALIDATIONS_BY_PATH[$wildcardNodePathSearch])) {

			fprintf(STDERR, "Invalid node path %s in generator config file %s.\n", ($origNodePathSearch != '') ? $origNodePathSearch : '(top level)', $currentGeneratorConfigFilename);
			return false;
		}
		$nodePath = $wildcardNodePath;
		$nodePathSearch = $wildcardNodePathSearch;
		unset($wildcardNodePath);
		unset($wildcardNodePathSearch);
	}

	$validation = $GENERATOR_CFG_VALIDATIONS_BY_PATH[$nodePathSearch];
	$tp = gettype($nodeOrValue);
	if ((!in_array($tp, $validation->allowedValueTypes)) &&
		(!in_array('*', $validation->allowedValueTypes))) {
		fprintf(STDERR, "Invalid node type (%s) in generator config file %s, node path %s (must be one of [%s]).\n", $tp, $currentGeneratorConfigFilename, ($origNodePathSearch != '') ? $origNodePathSearch : '(top level)', implode(',', $validation->allowedValueTypes));
		return false;
	}

	if (is_array($nodeOrValue)) {
		$result = true;
		foreach ($nodeOrValue as $key=>$val) {
			$keytp = gettype($key);
			if ((!in_array($keytp, $validation->allowedChildNodeNameTypes)) &&
				(!in_array('*', $validation->allowedChildNodeNameTypes))) {
				fprintf(STDERR, "Invalid node name type (%s) in generator config file %s, node path %s (must be one of [%s]).\n", $keytp, $currentGeneratorConfigFilename, ($nodePathSearch != '') ? $nodePathSearch : '(top level)', implode(',', $validation->allowedChildNodeNameTypes));
				return false;
			}
			$newNodePath = $nodePath.(($nodePath != '') ? "\t" : '').$key;
			if (($res = checkGeneratorConfig($val, $newNodePath)) === false) {
				$result = false;
			}
		}
		if ($result === false) return $result;
	}

	return true;
}

function getPHPClassesAndIncludes($yamlEntity, $numDirsDeepUnderHTML = 0) {
	$result = '';
	if ((isset($yamlEntity['phpClasses'])) && (is_array($yamlEntity['phpClasses']))) {
		foreach ($yamlEntity['phpClasses'] as $cls=>$params) {
			$path = (isset($params['path']) && is_string($params['path'])) ?
				$params['path'] : 'classes';
			$result .= "if (!class_exists('$cls', false)) include dirname(";
			for ($i = 0; $i < $numDirsDeepUnderHTML; $i++) $result .= 'dirname(';
			$result .= '__FILE__';
			for ($i = 0; $i < $numDirsDeepUnderHTML; $i++) $result .= ')';
			$result .= ").'/$path';\n";
		}
	}
	if ((isset($yamlEntity['phpIncludes'])) && (is_array($yamlEntity['phpIncludes']))) {
		foreach ($yamlEntity['phpIncludes'] as $path) {
			$result .= "include dirname(";
			for ($i = 0; $i < $numDirsDeepUnderHTML; $i++) $result .= 'dirname(';
			$result .= '__FILE__';
			for ($i = 0; $i < $numDirsDeepUnderHTML; $i++) $result .= ')';
			$result .= ").'/$path';\n";
		}
	}
	return $result;
}

function getPostInitPHPIncludes($yamlEntity, $numDirsDeepUnderHTML = 0) {
	$result = '';
	if ((isset($yamlEntity['postInitPHPIncludes'])) && (is_array($yamlEntity['postInitPHPIncludes']))) {
		foreach ($yamlEntity['postInitPHPIncludes'] as $path) {
			$result .= "include dirname(";
			for ($i = 0; $i < $numDirsDeepUnderHTML; $i++) $result .= 'dirname(';
			$result .= '__FILE__';
			for ($i = 0; $i < $numDirsDeepUnderHTML; $i++) $result .= ')';
			$result .= ").'/$path';\n";
		}
	}
	return $result;
}

// Returns the number of directories deep for $relpath.
// WARNING: Returns a negative number if $relpath has more ..'s in it than non-.. dir names.
function calcDirDepth($relpath) {
	$relpath = trim(str_replace('\\', '/', $relpath), '/');
	$depth = 0;
	foreach (explode('/', $relpath) as $s) {
		switch ($s) {
		case '.':
		case '':
			break;
		case '..':
			$depth--;
			break;
		default:
			$depth++;
			break;
		}
	}
	return $depth;
}

/*function cleanName($s) {
	$news = '';
	$prevc = '';
	for ($i = 0, $n = strlen($s); $i < $n; $i++) {
		$c = $s[$i];
		if ((!ctype_alnum($c)) && ($c != '_')) $c = '_';
		if (($c == '_') && ($prevc == '_')) continue;
		$news .= $c;
		$prevc = $c;
	}
	return $news;
}*/

// Given the value of the 'columns' subsection under a popupSearch or crudSearch
// subsection, the prefix for the language keys, and an array to be populated
// with language keys and their values, return the JavaScript code for the
// corresponding elements in the DataTable's columns array.
function getDataTableColumns($columns, $langKeyPrefix, &$langKeys) {
	$dataTableColumns = '';
	$sep = '';
	foreach ($columns as $colName=>$col) {
		$user_fnRender = (isset($col['fnRender']) && is_string($col['fnRender'])) ?
			trim($col['fnRender']) : '';
		$displayType = isset($col['displayType']) ? $col['displayType'] : 'string';
		if ($displayType == 'html') {
			$fnRender = $user_fnRender;
		} else {
			$fnRender = sprintf(<<<EOF
function(oObj) {
%s
	return $('<div></div>').text(text).html();
}
EOF
				,
				($user_fnRender != '') ? sprintf(<<<EOF
	var user_fnRender = %s
	var text = user_fnRender(oObj);
EOF
						,
						$user_fnRender
					) : <<<EOF
	var text = oObj.aData[oObj.iDataColumn];
EOF
			);
   		}
		$langKey = $langKeyPrefix.'.search.'.$colName.'.heading';
		$heading = isset($col['heading']) ? $col['heading'] : ucwords(str_replace('_', ' ', $colName));
		$langKeys[$langKey] = $heading;

		$dataTableColumns .=
			"$sep\t\t{ sName:'".$colName.
			"', sTitle:_t('".$langKey."', ".json_encode($heading).
			"), aTargets:[ci++]".
			", bSortable:".((isset($col['sortable']) && $col['sortable']) ? 'true' : 'false').
			", bUseRendered:false, sType:'".$displayType."'".
			", sHeaderClass:'".(isset($col['headerCSSClass']) ? $col['headerCSSClass'] : '')."'".
			", sClass:'".(isset($col['columnCSSClass']) ? $col['columnCSSClass'] : '')."'".
			(($fnRender != '') ? ", fnRender:$fnRender" : '').
			" }";
		$sep= ",\n";
	}
	return $dataTableColumns;
}

// Given the value of the 'columns' subsection under a popupSearch or crudSearch
// subsection, the prefix for the language keys, and an array to be populated
// with language keys and their values, return the HTML code (including AngularJS
// tags) for the corresponding header cell and body cell elements in the form of
// a two-element linear array where element [0] is the HTML code for the header
// columns and element [1] is the HTML code for the body columns.
// NOTE: Localized strings will be wrapped in <<langkey>>...<</langkey>>
// placeholders.  These placeholders will need to be replaced with the appropriate
// code to emit the localized text for the contained string resource identifier
// (language key).
// For PHP, this looks something like (remove spaces around < >): < ?php _e('...'); ? >
// For JavaScript, the contents should first be passed through json_encode(), then
// the placeholders replaced with something like this: "+_t('...')+"
function getAJAXSearchGridColumns($columns, $langKeyPrefix, &$langKeys) {
	$headerColumns = '';
	$bodyColumns = '';
	foreach ($columns as $colName=>$col) {
		$langKey = $langKeyPrefix.'.search.'.$colName.'.heading';
/// TODO: Provide specific tags to override header cell HTML and body cell HTML for each column.
		$displayType = isset($col['displayType']) ? $col['displayType'] : 'string';
		$heading = isset($col['heading']) ? $col['heading'] : ucwords(str_replace('_', ' ', $colName));
		$langKeys[$langKey] = $heading;

		$sortable = (isset($col['sortable']) && $col['sortable']);
		$headerCSSClass = isset($col['headerCSSClass']) ? $col['headerCSSClass'] : '';
		$columnCSSClass = isset($col['columnCSSClass']) ? $col['columnCSSClass'] : '';

		if (isset($col['headerTemplate'])) {
			$headerColumns .= $col['headerTemplate'];
		} else {
			$headerColumns .= sprintf(
				"<th%s%s><<langkey>>%s<</langkey>>%s</th>\n"
				,($headerCSSClass != '') ? sprintf(' class="%s"', $headerCSSClass) : ''
				,$sortable ? sprintf(' ng-class="getSortHeadingClass(\'%s\')"', $colName) : ''
				,$langKey
				,$sortable ? sprintf('<a href="" class="jax-grid-sort-icon" ng-click="toggleSort(\'%s\', $event.shiftKey)"></a>', $colName) : ''
			);
		}

		if (isset($col['columnTemplate'])) {
			$bodyColumns .= $col['columnTemplate'];
		} else {
			$valexpr = 'rows[i].'.$colName;

			if (isset($col['columnFilters'])) {
				foreach (explode(',', $col['columnFilters']) as $filter) {
					if (($filter = trim($filter)) != '') {
						$valexpr = sprintf('columnFilters.%s(%s, rows[i], i)', $filter, $valexpr);
					}
				}
			}

			$bodyColumns .= sprintf(
				"<td%s ng-bind%s=\"%s%s%s\"></td>\n"
				,($columnCSSClass != '') ? sprintf(' class="%s"', $columnCSSClass) : ''
				,($displayType == 'html') ? '-html' : ''
				,($displayType == 'html') ? 'toTrustedHTML(' : ''
				,$valexpr
				,($displayType == 'html') ? ')' : ''
			);
		}
	}
	return array($headerColumns, $bodyColumns);
}

// This function is patterned after the loadResourceBundle() function in l10n.include.php,
// but with more functionality to enable modifying an existing resource file programmatically.
// Parse the contents of a language resource file down to its keys, values and comments.
// Given a resource filename, returns a linear array of objects.  Each object has a
// "t" attribute which is "comment" for comment, "blank" for blank line, "junk" for a line
// which contains text but is not a comment and contains no equal sign, and "keyval" for
// a key/value pair.  For comment, blank and junk type entries, there will be a "text"
// attribute which contains the comment line or the blank line.  For key/value pairs, there
// will be a "key" attribute and a "value" attribute, and a boolean "isHeredoc" attribute.
// The value attribute can span multiple lines. In the resource file, for non-heredoc strings,
// newlines within a value are prefixed by a single backslash (\) character, which is stripped
// when reading the values back.  Heredoc strings are represented in the file using heredoc
// syntax.
function loadLangResourceFileContents($fn) {
	$results = array();
	if (($fp = @fopen($fn, 'r')) !== false) {
		$lineno = 0;
		while (($line = @fgets($fp)) !== false) {
			$lineno++;

			$line = rtrim($line, "\r\n");
			$lineTrimmed = trim($line);
			if ($lineTrimmed == '') {
				$obj = new stdClass();
				$obj->t = 'blank';
				$obj->text = $line;
				$results[] = $obj;
				continue;
			}
			if (($lineTrimmed[0] == '#') || ($lineTrimmed[0] == ';')) {
				$obj = new stdClass();
				$obj->t = 'comment';
				$obj->text = $line;
				$results[] = $obj;
				continue;
			}

			if (($equalidx = strpos($lineTrimmed, '=')) === false) {
				$obj = new stdClass();
				$obj->t = 'junk';
				$obj->text = $line;
				$results[] = $obj;
				continue;
			}

			$key = trim(substr($lineTrimmed, 0, $equalidx));
			if ($key == '') {
				$obj = new stdClass();
				$obj->t = 'junk';
				$obj->text = $line;
				$results[] = $obj;
				continue;
			}

			$val = trim(substr($lineTrimmed, $equalidx+1));
			$heredocDelim = ((strlen($val) > 3) && (strncmp($val, '<<<', 3) == 0)) ?
				trim(substr($val, 3)) : '';
			if ($heredocDelim != '') {
				$val = '';
				while (true) {
					if (($moreval = @fgets($fp)) === false) break;
					if ((strlen($moreval) >= 2) &&
						(substr($moreval, strlen($moreval)-2) == "\r\n")) {
						$moreval = substr($moreval, 0, strlen($moreval)-2);
					} else if ((strlen($moreval) >= 1) &&
								(($moreval[strlen($moreval)-1] == "\r") ||
								 ($moreval[strlen($moreval)-1] == "\n"))) {
						$moreval = substr($moreval, 0, strlen($moreval)-1);
					}
					$lineno++;
					if (trim($moreval) === $heredocDelim) break;
					if ($val == '') $val = $moreval; else $val .= "\n".$moreval;
				}
			} else {
				while (substr($val, -1) == '\\') {
					if (($moreval = @fgets($fp)) === false) {
						$val = trim(substr($val, 0, strlen($val)-1));
						break;
					}
					$lineno++;
					$val = trim(substr($val, 0, strlen($val)-1))."\n".rtrim($moreval);
				}
			}
			$obj = new stdClass();
			$obj->t = 'keyval';
			$obj->isHeredoc = ($heredocDelim != '') ? true : false;
			$obj->key = $key;
			$obj->val = $val;
			$results[] = $obj;
		}
		@fclose($fp);
	}
	return $results;
}

// Save a set of entries back to a resource file.
// The entries must be in the same format as that returned from the
// loadLangResourceFileContents() function.
function saveLangResourceFileContents($fn, $entries) {
	if (($fp = @fopen($fn, 'w+')) !== false) {
		$prevPrevType = '';
		$prevType = '';
		foreach ($entries as $entry) {
			switch ($entry->t) {
			case 'blank':
				// Never output more than two blank lines in a row.
				if (($prevPrevType == 'blank') && ($prevType == 'blank')) break;
			case 'comment':
			case 'junk':
				fprintf($fp, "%s\n", $entry->text);
				break;
			case 'keyval':
				if ($entry->isHeredoc) {
					// Find a delimiter which is not found in the string.
					$delim = 'EOF';
					$sfx = 0;
					while (strpos($entry->val, $delim) !== false) {
						$sfx++;
						$delim = 'EOF'.$sfx;
					}
					// Output the entry in heredoc format.
					fprintf($fp, "%s = <<<%s\n%s\n%s\n\n", $entry->key, $delim, $entry->val, $delim);
				} else {
					fprintf($fp, "%s = %s\n", $entry->key, str_replace("\n", "\\\n", $entry->val));
				}
				break;
			}
			$prevPrevType = $prevType;
			$prevType = $entry->t;
		}
		fclose($fp);
	}
}

function getLangResource(&$entries, $key) {
	for ($i = count($entries)-1; $i >= 0; $i--) {
		if (($entries[$i]->t == 'keyval') && ($entries[$i]->key == $key)) {
			return $entries[$i]->val;
		}
	}
	return false;
}

function setLangResource(&$entries, $key, $val) {
	// Only change last instance of $key; all others are insignificant.
	for ($i = count($entries)-1; $i >= 0; $i--) {
		if (($entries[$i]->t == 'keyval') && ($entries[$i]->key == $key)) {
			$entries[$i]->isHeredoc = ((strpos($val, "\r") !== false) || (strpos($val, "\n") !== false)) ? true : false;
			$entries[$i]->val = $val;
			return;
		}
	}
	$obj = new stdClass();
	$obj->t = 'keyval';
	$obj->isHeredoc = ((strpos($val, "\r") !== false) || (strpos($val, "\n") !== false)) ? true : false;
	$obj->key = $key;
	$obj->val = $val;
	$entries[] = $obj;
}

function removeLangResource(&$entries, $key) {
	$anyRemoved = false;
	for ($i = 0, $n = count($entries); $i < $n; $i++) {
		if ($entries[$i]->t == 'keyval') {
			if ($entries[$i]->key == $key) {
				unset($entries[$i]);
				$anyRemoved = true;
			}
		}
	}
	if ($anyRemoved) {
		// Re-index the array.
		$entries = array_slice($entries, 0);
	}
}

// To come up with a human-readable description from an identifier, replace all runs of spaces
// and/or underscores with single spaces; trim leading/trailing whitespace; insert a
// space before each capital leter which is not preceded by a capital letter or a space.
function identifierToHumanReadable($identifier) {
	return ucwords(trim(preg_replace(
		'/([^A-Z ])([A-Z])/',
		'\\1 \\2',
		trim(preg_replace('/[ _]+/', ' ', $identifier))
	)));
}

function plural($identifier) {
	if (strtolower(substr($identifier, -1)) == 'y') {
		return substr($identifier, 0, strlen($identifier)-1).'ies';
	}
	return $identifier.'s';
}

// This function is used for server-side searches and autocompletes.
function filterFullTextSearchCode($template, $haveAnyFulltextQueryOperators) {
	if (!$haveAnyFulltextQueryOperators) {
		$template = preg_replace('/\{\{if_haveAnyFulltextQueryOperators\}\}.*?\{\{\\/if_haveAnyFulltextQueryOperators\}\}/s', '', $template);
	}
	$template = preg_replace('/\{\{if_haveAnyFulltextQueryOperators\}\}/', '', $template);
	$template = preg_replace('/\{\{\\/if_haveAnyFulltextQueryOperators\}\}/', '', $template);
	return $template;
}

// This function is only used for popup searches.
function filterSearchPresentation($template, $crudSearchPresentation) {
	if ($crudSearchPresentation != 'dataTables') {
		$template = preg_replace('/\{\{if_searchPresentation_dataTables\}\}.*?\{\{\\/if_searchPresentation_dataTables\}\}/s', '', $template);
	}
	if ($crudSearchPresentation != 'AJAXSearchGrid') {
		$template = preg_replace('/\{\{if_searchPresentation_AJAXSearchGrid\}\}.*?\{\{\\/if_searchPresentation_AJAXSearchGrid\}\}/s', '', $template);
	}
	$template = preg_replace('/\{\{if_searchPresentation_.*?\}\}/', '', $template);
	$template = preg_replace('/\{\{\\/if_searchPresentation_.*?\}\}/', '', $template);
	return $template;
}

function var_export_normal_precision($var, $return = false) {
	$orig_serialize_precision = ini_get('serialize_precision');
	ini_set('serialize_precision', ini_get('precision'));
	$result = var_export($var, $return);
	ini_set('serialize_precision', $orig_serialize_precision);
	return $result;
} // var_export_normal_precision()
