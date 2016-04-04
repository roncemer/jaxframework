<?php
// Copyright (c) 2011-2016 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

include dirname(__FILE__).'/gen.include.php';

$processAllTablesByDefault = false;
$loadYAMLGeneratorConfig = false;

$usageDescription = 'Generate default generator configuration YAML files for table(s).';

$searchInclude = '';
$searchJS = 'js/search';
$loggedInId = '$loggedInUser->id';
$jaxInclude = '';
$jaxJQuery = '';
$jaxJS = '';
$classAutoloadPaths = '';

function moreUsage() {
	fputs(
		STDERR,
		"    -search-include <path> Specify where the search include files belong.\n".
		"                     Defaults to empty, which causes the default\n".
		"                     location to be used.\n".
		"    -search-js <path> Specify where the search JavaScript files belong.\n".
		"                     Defaults to empty, which causes the default\n".
		"                     location to be used.\n".
		"    -logged-in-id <expression> Specify the loggedInId PHP expression.\n".
		"                     Defaults to \"\$loggedInUser->id\".\n".
		"    -jax-inc <path>  Specify the path to the jax framework includes.\n".
		"                     Defaults to empty, which causes the default\n".
		"                     location to be used.\n".
		"    -jax-jquery <path> Specify the path to the jax framework jquery scripts.\n".
		"                     Defaults to empty, which causes the default\n".
		"                     location to be used.\n".
		"    -jax-js <path>   Specify the path to the jax framework JavaScript files.\n".
		"                     Defaults to empty, which causes the default\n".
		"                     location to be used.\n".
		"    -class-autoload-paths <expression> Specify a PHP expression for an array\n".
		"                     of paths for the class autoloader to search when looking\n".
		"                     for classes and interfaces.\n".
		"                     Defaults to empty, which causes the default\n".
		"                     location to be used.\n"
	);
}

function parseArg($arg, $ai) {
	global $argc, $argv;
	global $searchInclude, $searchJS, $loggedInId, $jaxInclude, $jaxJQuery, $jaxJS,
		$classAutoloadPaths;

	switch ($arg) {
	case '-search-include':
		$ai++;
		if ($ai >= $argc) {
			usage();
			exit(1);
		}
		$searchInclude = trim($argv[$ai]);
		return 1;
	case '-search-js':
		$ai++;
		if ($ai >= $argc) {
			usage();
			exit(1);
		}
		$searchJS = trim($argv[$ai]);
		return 1;
	case '-logged-in-id':
		$ai++;
		if ($ai >= $argc) {
			usage();
			exit(1);
		}
		$loggedInId = trim($argv[$ai]);
		return 1;
	case '-jax-inc':
		$ai++;
		if ($ai >= $argc) {
			usage();
			exit(1);
		}
		$jaxInclude = trim($argv[$ai]);
		return 1;
	case '-jax-jquery':
		$ai++;
		if ($ai >= $argc) {
			usage();
			exit(1);
		}
		$jaxJQuery = trim($argv[$ai]);
		return 1;
	case '-jax-js':
		$ai++;
		if ($ai >= $argc) {
			usage();
			exit(1);
		}
		$jaxJS = trim($argv[$ai]);
		return 1;
	case '-class-autoload-paths':
		$ai++;
		if ($ai >= $argc) {
			usage();
			exit(1);
		}
		$classAutoloadPaths = trim($argv[$ai]);
		return 1;
	}

	return false;
}

process();

function processTable($table, $idCol, $cfg) {
	global $cfgDir, $searchInclude, $searchJS, $loggedInId, $jaxInclude, $jaxJQuery, $jaxJS,
		$classAutoloadPaths;

	if (file_exists($cfgDir.'/'.$table->tableName.'.yml')) {
		fprintf(STDERR, "%s already exists; not processing %s table!\n", $outputFile, $table->tableName);
		return false;
	}
	$outputFile = $cfgDir.'/'.$table->tableName.'.yaml';
	if (file_exists($outputFile)) {
		fprintf(STDERR, "%s already exists; not processing %s table!\n", $outputFile, $table->tableName);
		return false;
	}

	$fp = fopen($outputFile, 'w+');

	$tn = $table->tableName;
	$tns = plural($tn);
	$utn = ucfirst($tn);
	$utns = ucfirst($tns);

	$tdesc = identifierToHumanReadable($tn);
	$tdescs = identifierToHumanReadable($tns);

	$tableDDL = getTableDDL($tn);


	// --------------------------------
	// top section and searches section
	// --------------------------------

	$initEntries = '';
	if ($jaxInclude != '') $initEntries .= sprintf("jaxInclude: %s\n", $jaxInclude);
	if ($jaxJQuery != '') $initEntries .= sprintf("jaxJQuery: %s\n", $jaxJQuery);
	if ($jaxJS != '') $initEntries .= sprintf("jaxJS: %s\n", $jaxJS);
	if ($classAutoloadPaths != '') $initEntries .= sprintf("classAutoloadPaths: %s\n", $classAutoloadPaths);

	fprintf($fp, <<<EOF
tableDescription: %s
tableDescriptions: %s
loggedInId: %s
%s

searches:
  %s:%s
    searchCommand: search%s
    searchableColumns:

EOF
		,
		$tdesc,
		$tdescs,
		$loggedInId,
		$initEntries,
		$tn,
		($searchInclude != '') ? sprintf("\n    outputPath: %s", $searchInclude) : '',
		$utns
	);

	foreach ($table->columns as $column) {
		$cn = $column->name;

		if ($cn == $idCol) {
			$searchable = true;
		} else {
			$searchable = false;
			switch ($column->type) {
			case 'char':
			case 'varchar':
			case 'text':
				$searchable = true;
				break;
			}
		}
		if (!$searchable) continue;

		$sqlType = $column->type;
		switch ($sqlType) {
		case 'integer':
		case 'smallint':
		case 'bigint':
		case 'decimal':
			$queryOperator = '=';
			$emitUnsignedSearch = true;
			break;
		case 'char':
		case 'varchar':
		case 'text':
			$queryOperator = 'contains';
			$emitUnsignedSearch = false;
			break;
		default:
			$searchable = false;
			break;
		}
		if (!$searchable) continue;

		fprintf($fp, <<<EOF
      - { columnName: %s, title: "%s", sqlType: %s, queryOperator: %s%s }

EOF
			,
			$cn,
			identifierToHumanReadable($cn),
			$sqlType,
			$queryOperator,
			$emitUnsignedSearch ? ', unsignedSearch: No' : ''
		);
	}	// foreach ($table->columns as $column)



	// ----------------------------
	// autocompleteSearches section
	// ----------------------------

	fprintf($fp, <<<EOF


autocompleteSearches:
  %s:%s
    searchCommand: autocomplete%s
    searchableColumns:

EOF
		,
		$tn,
		($searchInclude != '') ? sprintf("\n    outputPath: %s", $searchInclude) : '',
		$utns
	);

	foreach ($table->columns as $column) {
		$cn = $column->name;

		if ($cn == $idCol) {
			$searchable = true;
		} else {
			$searchable = false;
			switch ($column->type) {
			case 'char':
			case 'varchar':
			case 'text':
				$searchable = true;
				break;
			}
		}
		if (!$searchable) continue;

		$sqlType = $column->type;
		switch ($sqlType) {
		case 'integer':
		case 'smallint':
		case 'bigint':
		case 'decimal':
			$queryOperator = '=';
			$emitUnsignedSearch = true;
			break;
		case 'char':
		case 'varchar':
		case 'text':
			$queryOperator = 'contains';
			$emitUnsignedSearch = false;
			break;
		default:
			$searchable = false;
			break;
		}
		if (!$searchable) continue;

		fprintf($fp, <<<EOF
      - { columnName: %s, sqlType: %s, queryOperator: %s%s }

EOF
			,
			$cn,
			$sqlType,
			$queryOperator,
			$emitUnsignedSearch ? ', unsignedSearch: No' : ''
		);
	}	// foreach ($table->columns as $column)
	fputs($fp, '    searchResultLabelExpression: "');
	$sep = '';
	foreach ($table->columns as $column) {
		$cn = $column->name;
		if ($cn == $idCol) {
			fprintf($fp, '$row->%s', $column->name);
			$sep = ".': '.";
			break;
		}
	}
	foreach ($table->columns as $column) {
		$cn = $column->name;
		if ($cn != $idCol) {
			switch ($column->type) {
			case 'char':
			case 'varchar':
			case 'text':
				fprintf($fp, '%s$row->%s', $sep, $column->name);
				$sep = ".' '.";
				break;
			}
		}
	}
	fputs($fp, "\"\n");



	// ---------------------
	// popupSearches section
	// ---------------------

	fprintf($fp, <<<EOF


popupSearches:
  %sPopupSearch:%s
    searchCommand: search%s
    searchPresentation: AJAXSearchGrid
    defaultSorts:
      - { attr: '%s', dir: 1 }
    idColumn: %s
    rowSelectJavaScriptCallbackFunction: %sSelected
    columns:

EOF
		,
		$tn,
		($searchJS != '') ? sprintf("\n    outputPath: %s", $searchJS) : '',
		$utns,
		$idCol,
		$idCol,
		$tn
	);
	outputSearchColumns($fp, $table, '      ');



	// ---------------
	// loaders section
	// ---------------

	fprintf($fp, <<<EOF


loaders:
  %s:%s
    searchCommand: load%s

EOF
		,
		$tn,
		($searchInclude != '') ? sprintf("\n    outputPath: %s", $searchInclude) : '',
		$utn
	);



	// -------------
	// cruds section
	// -------------

	// If the column has a foreign key consisting of exactly one field,
	// store that foreign key away, indexed by column name.
	// We'll use this to automatically include searches, autocomplates and popup searches,
	// and to create ajaxComboBox row selection components and popup search icons.
	$relationColFKsByColName = array();
	$relatedTableNames = array();
	foreach ($table->columns as $column) {
		$cn = $column->name;
		$fks = findForeignKeys($tableDDL, $tn, $cn, 1);
		if (!empty($fks)) {
			$relationColFKsByColName[$cn] = $fks[0];
			if (!in_array($fks[0]->foreignTableName, $relatedTableNames)) {
				$relatedTableNames[] = $fks[0]->foreignTableName;
			}
		}
	}
	sort($relatedTableNames, SORT_STRING);

	fprintf($fp, <<<EOF


cruds:
  %s:
    postInitPHPIncludes:
      %s/%s_search.include.php
      %s/%s_load.include.php

EOF
		,
		$tn,
		($searchInclude != '') ? $searchInclude : 'include/search',
		$tn,
		($searchInclude != '') ? $searchInclude : 'include/search',
		$tn
	);

	foreach ($relatedTableNames as $rtn) {
		fprintf($fp, <<<EOF
      %s/%s_search.include.php
      %s/%s_autocomplete.include.php

EOF
			,
			($searchInclude != '') ? $searchInclude : 'include/search',
			$rtn,
			($searchInclude != '') ? $searchInclude : 'include/search',
			$rtn
		);
	}

	if (!empty($relatedTableNames)) {
		// Emit javaScriptFiles section with popup search JS files.
		fprintf($fp, <<<EOF
    javaScriptFiles:

EOF
		);

		foreach ($relatedTableNames as $rtn) {
			fprintf($fp, <<<EOF
      %s/%sPopupSearch.js

EOF
				,
				($searchJS != '') ? $searchJS : 'js/search',
				$rtn
			);
		}
	} else {
		// Emit empty javaScriptFiles section.
		fprintf($fp, <<<EOF
    javaScriptFiles: ~

EOF
		);
	}

	fprintf($fp, <<<EOF
    cssFiles: ~

    allowAddSimilar: No

    crudSearch:
      likePopupSearch: %sPopupSearch

EOF
		,
		$tn
	);

	fprintf($fp, <<<EOF

    crudLoad:
      loadCommand: load%s


EOF
   		,
		$utn
	);

			// Emit form fields.

	fprintf($fp, <<<EOF
    formFields:

EOF
	);
	foreach ($table->columns as $column) {
		$cn = $column->name;

		// If the column has a foreign key consisting of exactly one field,
		// create an ajaxComboBox component which the user can use to select the related row's id,
		// along with a pop-up search which the user can use for more detailed searching.
		if (isset($relationColFKsByColName[$cn])) {
			$fk = $relationColFKsByColName[$cn];

			fprintf($fp, <<<EOF
      %s:
        title: %s
        inputType: text
        size: 40
        ajaxCombobox:
          autocompleteCommand: autocomplete%s
          idColumn: %s
          idIsString: %s
          minimumInputLength: 1
          allowClear: %s
          selectPlaceholder: %s
          notFoundMessage: *** INVALID %s ***
        onPopupSearch: if ( (mode == ADD_MODE) || (mode == EDIT_MODE) ) { %sPopupSearch.selectCopyValues = { %s:'#%s' }; %sPopupSearch.show('%s'); }

EOF
				,
				$cn,
				identifierToHumanReadable(preg_replace('/_id$/', '', $cn)),
				ucfirst(plural($fk->foreignTableName)),
				$fk->columns[0]->foreignName,
				in_array($column->type, array('integer', 'smallint', 'bigint', 'decimal')) ? 'No' : 'Yes',
				$column->allowNull ? 'Yes' : 'No',
				$column->allowNull ? '(none)' : ('Select a '.identifierToHumanReadable($fk->foreignTableName)),
				strtoupper(identifierToHumanReadable($cn)),
				$fk->foreignTableName,
				$fk->columns[0]->foreignName,
				$cn,
				$fk->foreignTableName,
				$cn
			);

		} else {	// if (isset($relationColFKsByColName[$cn]))

			// No single-column foreign key.  Proceed with regular input.

			$canInput = true;
			$inputType = 'text';
			$cssClass = '';

			switch ($column->type) {
			case 'integer':
				$size = 11;
				$maxlength = 11;
				$cssClass = 'right numeric-scale0';
				break;
			case 'smallint':
				$size = 6;
				$maxlength = 6;
				$cssClass = 'right numeric-scale0';
				break;
			case 'bigint':
				$size = 22;
				$maxlength = 22;
				$cssClass = 'right numeric-scale0';
				break;
			case 'decimal':
				$size = $column->size;
				if ($column->scale > 0) $size++;
				$maxlength = $size;
				$size = min(30, $size);
				$cssClass = sprintf('right numeric-scale%d', $column->scale);
				break;
			case 'char':
			case 'varchar':
			case 'text':
				if (($column->type == 'text') || ($column->size > 255)) {
					$inputType = 'textarea';
					$rows = 12;
					$cols = 80;
				} else {
					$size = $column->size;
					$maxlength = $size;
					$size = min(40, $size);
				}
				break;
			case 'time':
				$size = 12;
				$maxlength = 12;
				break;
			case 'date':
				$size = 10;
				$maxlength = 10;
				$cssClass = 'date';
				break;
			case 'datetime':
				$size = 24;
				$maxlength = 24;
				$cssClass = 'datetime';
				break;
			default:
				$canInput = false;
			}
			if (!$canInput) continue;

			if ($cn == $idCol) {
				$readonlyAttr = ', readonly: Yes';
			} else {
				$readonlyAttr = '';
			}
			if ($cssClass != '') {
				$cssClassAttr = ', cssClass: '.$cssClass;
			} else {
				$cssClassAttr = '';
			}

			fprintf($fp, <<<EOF
      %s: { title: %s, inputType: %s, %s%s }

EOF
				,
				$cn,
				identifierToHumanReadable($cn),
				$inputType,
				($inputType == 'textarea') ?
					sprintf('rows: %d, cols: %d', $rows, $cols) :
					sprintf('size: %d, maxlength: %d', $size, $maxlength),
				$readonlyAttr.$cssClassAttr
			);
		}	// if (isset($relationColFKsByColName[$cn])) ... else
	}	// foreach ($table->columns as $column)

			// Emit filters.

	fputs($fp, <<<EOF

    filters:

EOF
	);
	foreach ($table->columns as $column) {
		$cn = $column->name;

		switch ($column->type) {
		case 'integer':
		case 'smallint':
		case 'bigint':
			fprintf($fp, <<<EOF
      %s:
        trim:
          class: TrimFilter
          params: { }
        int:
          class: IntFilter
          params: { %s }

EOF
				,
				$cn,
				$column->allowNull ? 'convertZeroToNULL: Yes' : ''
			);
			break;
		case 'decimal':
			fprintf($fp, <<<EOF
      %s:
        trim:
          class: TrimFilter
          params: { }
        decimal:
          class: DecimalFilter
          params: { fractionalDigits: %d%s }

EOF
				,
				$cn,
				$column->scale,
				$column->allowNull ? ', convertZeroToNULL: Yes' : ''
			);
			break;
		case 'char':
		case 'varchar':
		case 'text':
		case 'date':
		case 'time':
		case 'datetime':
			fprintf($fp, <<<EOF
      %s:
        trim:
          class: TrimFilter
          params: { %s }

EOF
				,
				$cn,
				$column->allowNull ? 'convertEmptyToNULL: Yes' : ''
			);
			break;
		}
	}	// foreach ($table->columns as $column)

			// Emit validators.

	fputs($fp, <<<EOF

    validators:

EOF
	);
	foreach ($table->columns as $column) {
		$cn = $column->name;

		fprintf($fp, <<<EOF
      %s:
EOF
			,
			$cn
		);

		$anyValidators = false;
		switch ($column->type) {
		case 'char':
		case 'varchar':
			if ($column->size > 0) {
				$anyValidators = true;
				fprintf($fp, <<<EOF

# If the %s column should not be allowed to be empty, uncomment this.
#        notempty:
#          class: NotEmptyValidator
#          params: { %s }
        length:
          class: LengthValidator
          params: { maxLength: %d%s }

EOF
					,
					$cn,
					$column->allowNull ? 'allowNULL: Yes' : '',
					$column->size,
					$column->allowNull ? ', allowNULL: Yes' : ''
				);
			}
			break;
		case 'date':
			$anyValidators = true;
			fprintf($fp, <<<EOF

        date:
          class: DateValidator
          params: { %s }

EOF
				,
				$column->allowNull ? 'allowNULL: Yes' : ''
			);
			break;
		case 'time':
			$anyValidators = true;
			fprintf($fp, <<<EOF

        time:
          class: TimeValidator
          params: { %s }

EOF
				,
				$column->allowNull ? 'allowNULL: Yes' : ''
			);
			break;
		case 'datetime':
			$anyValidators = true;
			fprintf($fp, <<<EOF

        datetime:
          class: DateTimeValidator
          params: { %s }

EOF
				,
				$column->allowNull ? 'allowNULL: Yes' : ''
			);
			break;
		case 'integer':
		case 'smallint':
		case 'bigint':
		case 'decimal':
		case 'text':
			break;
		}

		// For each foreign key in this table which begins with the current column,
		// create a ForeignKeyValidator instance.
		$fks = findForeignKeys($tableDDL, $tn, $cn);
		for ($fki = 0, $nfks = count($fks); $fki < $nfks; $fki++) {
			$fk = $fks[$fki];

			if (!$anyValidators) {
				$anyValidators = true;

				fprintf($fp, <<<EOF


EOF
				);
			}

			// Begin the ForeignKeyValidator instance.
			fprintf($fp, <<<EOF
        foreignkey%s:
          class: ForeignKeyValidator
          params:

EOF
				,
				($fki > 0) ? ($fki+1) : ''
			);

			// If all local columns allow NULLs, set allowNULL: Yes on the validator.
			$allAllowNull = true;
			foreach ($fk->columns as $fkc) {
				$tc = $table->columns[$table->getColumnIdx($fkc->localName)];
				if (!$tc->allowNull) {
					$allAllowNull = false;
					break;
				}
			}
			if ($allAllowNull) {
				fprintf($fp, <<<EOF
            allowNULL: Yes

EOF
				);
			}

			// Emit foreignTable attribute, and begin foreignKeyMapping section.
			fprintf($fp, <<<EOF
            foreignTable: %s
            foreignKeyMapping:

EOF
				,
				$fk->foreignTableName
			);

			// Emit the columns.
			$dcg = new DAOClassGenerator();
			foreach ($fk->columns as $fkc) {
				$tc = $table->columns[$table->getColumnIdx($fkc->localName)];

				fprintf($fp, <<<EOF
              -
                type: %s
                local: %s
                foreign: %s

EOF
					,
					$dcg->getPHPDataType($tc),
					$fkc->localName,
					$fkc->foreignName
				);
			}

			// Emit the error message.
			fprintf($fp, <<<EOF
            errorMsg: Invalid %s.

EOF
				,
				identifierToHumanReadable(preg_replace('/_id$/', '', $fkc->localName))
			);
		}	// for ($fki = 0, $nfks = count($fks); $fki < $nfks; $fki++)

		if (!$anyValidators) {
			// If we have no validators, put the tilde at the end of the column name entry
			// under the validators secton to indicate an empty array of validators.
			fprintf($fp, <<<EOF
 ~

EOF
			);
		}

	}	// foreach ($table->columns as $column)

	$focusField = '';
	foreach ($table->columns as $column) {
		$cn = $column->name;
		if ($cn != $idCol) {
			$focusField = $cn;
			break;
		}
	}
	if (($focusField == '') && (!empty($table->columns))) {
		$focusField = $table->columns[0]->name;
	}

	fprintf($fp, <<<EOF


    addFocusField: %s
    editFocusField: %s
    neverUpdateColumns: [ %s ]
EOF
		,
		$focusField,
		$focusField,
		$idCol
	);

	fclose($fp);

	return true;
}

function outputSearchColumns($fp, $table, $indent = '') {
	foreach ($table->columns as $column) {
		$cn = $column->name;

		$canShow = true;
		switch ($column->type) {
		case 'integer':
		case 'smallint':
		case 'bigint':
		case 'decimal':
			$displayType = 'numeric';
			$columnCSSClass = 'right';
			break;
		case 'char':
		case 'varchar':
		case 'text':
		case 'time':
			$displayType = 'string';
			$columnCSSClass = 'left';
			break;
		case 'date':
		case 'datetime':
			$displayType = 'date';
			$columnCSSClass = 'left';
			break;
		default:
			$canShow = false;
		}
		if (!$canShow) continue;

		fprintf($fp, <<<EOF
%s%s: { heading: %s, displayType: %s, columnCSSClass: %s, sortable: Yes }

EOF
			,
			$indent,
			$cn,
			identifierToHumanReadable($cn),
			$displayType,
			$columnCSSClass
		);
	}	// foreach ($table->columns as $column)
}

// Find one or more foreign keys.
// $tableDDL: the DDL for the entire table; retrieved by calling getTableDDL($tableName).
// $tableName: the name of the (local) table.
// $columnName: the name of the column in the local table.  Only foreign keys whose first
//     column's local column name equals this name, will be selected.
// $exactColumnCount: The optional exact column count which the foreign key must contain in
//     order to qualify.  If this is less than 1, then any foreign key can qualify as long
//     as it contains at least one column (subject to other matching qualifications).  If this
//     is greater than or equal to one, then only foreign keys which have exactly the specified
//     number of columns can qualify.
// Returns an array containing the matching foreign keys, or an empty array if no matches.
function findForeignKeys($tableDDL, $tableName, $columnName, $exactColumnCount = 0) {
	$fks = array();
	foreach ($tableDDL->topLevelEntities as $tle) {
		if (($tle instanceof DDLForeignKey) &&
			($tle->localTableName == $tableName) &&
			(count($tle->columns) >= 1) &&
			(($exactColumnCount < 1) || (count($tle->columns) == $exactColumnCount)) &&
			($tle->columns[0]->localName == $columnName)) {
			$fks[] = $tle;
		}
	}
	return $fks;
}
