<?php
{{generatedFileMessage}}
include {{docRootPath}}.'/{{jaxInclude}}/l10n.include.php';
loadResourceBundle({{docRootPath}}.'/jax/resources/system');
loadResourceBundle({{docRootPath}}.'/resources/system');
loadResourceBundle(__FILE__);
{{phpIncludes}}
if (isset($command) && ($command == '{{searchCommand}}_getSearchableColumns')) {
	header('Content-Type: application/json');
	$searchableColumns = {{searchableColumnsPHPArray}};
	echo json_encode($searchableColumns);
	exit();
}
if (isset($command) && ($command == '{{searchCommand}}')) {
	header('Content-Type: application/json');
	$db = ConnectionFactory::getConnection();
	$returnColumns = isset($params['sColumns']) ?
		explode(',', preg_replace('/[^a-zA-Z0-9_$,]/', '', $params['sColumns'])) : array();
	$sEcho = isset($params['sEcho']) ? (int)$params['sEcho'] : 0;
	$offset = isset($params['iDisplayStart']) ? (int)$params['iDisplayStart'] : 0;
	$limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 0;
	if ( ($limit < 1) || ($limit > 100) ) $limit = 100;
	$orderBy = '';
	if ( (isset($params['iSortingCols'])) && (($nsc = (int)$params['iSortingCols']) > 0) ) {
		for ($i = 0; $i < $nsc; $i++) {
			$sci = isset($params['iSortCol_'.$i]) ? (int)$params['iSortCol_'.$i] : 0;
			if ( ($sci < 0) || ($sci >= count($returnColumns)) ) $sci = 0;
			$scd = isset($params['sSortDir_'.$i]) ? strtolower($params['sSortDir_'.$i]) : 'asc';
			if ( ($scd != 'asc') && ($scd != 'desc') ) $scd = 'asc';
			$orderBy .= sprintf(
				'%s%s %s',
				($i == 0) ? ' order by ' : ', ',
				$returnColumns[$sci],
				$scd
			);
		}
	}
	if ($orderBy == '') $orderBy = ' order by {{idCol}}';
	$query = isset($params['sSearch']) ? trim($params['sSearch']) : '';
	$queryCol = isset($params['sSearchCol']) ? $params['sSearchCol'] : '';
	$canDoFulltextSearch = {{canDoFulltextSearchPHP}};
{{if_haveAnyFulltextQueryOperators}}
	$ftquery = $query;
	if ($canDoFulltextSearch) {
		switch ($db->getDialect()) {
		case 'mysql':
			$__mysqlPreprocessBooleanModeFulltextQuery = function($query) {
				$pieces = array();
				$startidx = -1;
				$quoted = false;
				$pfx = '';
				for ($i = 0, $len = strlen($query); $i < $len; $i++) {
					$c = $query[$i];
					if ($startidx < 0) {
						if (!ctype_space($c)) {
							if (($c == '-') || ($c == '+') || ($c == '~')) {
								$pfx = $c;
								$i++;
								$c = ($i < $len) ? $query[$i] : '';
							}
							$startidx = $i;
							$quoted = ($c == '"');
						}
					} else { // if ($startidx < 0)
						if (($quoted && ($c == '"')) || ((!$quoted) && (ctype_space($c) || ($c == '"'))) || (($i+1) >= $len)) {
							$endidx = $i+1;
							$s = trim(
								str_replace(
									array('+', '-', '*', '@', '<', '>', '(', ')', '~', '"'),
									'', 
									substr($query, $startidx, $endidx-$startidx)
								)
							);
							if ($s == '') continue;
							if ($quoted) $s = '"'.$s.'"';
							$pieces[] = $pfx.$s.($quoted ? '' : '*');
							// Reset state for next chunk.
							$startidx = -1;
							$quoted = false;
							$pfx = '';
						}
					} // if ($startidx < 0) ... else
				} // for ($i = 0, $len = strlen($query); $i < $len; $i++)
				return implode(' ', $pieces);
			};
			$ftquery = $__mysqlPreprocessBooleanModeFulltextQuery($query);
			unset($__mysqlPreprocessBooleanModeFulltextQuery);
			break;	// case 'mysql'
		} // switch ($db->getDialect())
	} // if ($canDoFulltextSearch)
{{/if_haveAnyFulltextQueryOperators}}
	$whereClause = {{searchWhereClausePHP}};
	$sqlTail = <<<EOF
 from {{tableName}} pri
 {{joins}}
 where ($whereClause){{andWhere}}
 {{groupBy}}
EOF
	;

	// Get row count.
	$ps = new PreparedStatement('select count(*) as rowCount'.$sqlTail);
{{searchWhereAssignments}}
{{andWhereAssignments}}
	$row = $db->fetchObject($db->executeQuery($ps), true);
	$rowCount = isset($row->rowCount) ? (int)$row->rowCount : 0;

	printf(
		'{"sEcho": %d, "iTotalRecords": %d, "iTotalDisplayRecords": %d, "aaData": [',
		$sEcho,
		$rowCount,
		$rowCount
	);

	// Get actual rows.
	$ps = new PreparedStatement(<<<EOF
select pri.*{{extraSelectColumns}}
EOF
		.$sqlTail.$orderBy, $offset, $limit
	);
{{searchWhereAssignments}}
{{andWhereAssignments}}
	$rows = $db->fetchAllObjects($db->executeQuery($ps), true);

	$sep = '';
	foreach ($rows as $row) {
{{rowProcessingPHPCode}}
{{unsetForbiddenColumns}}
		$arr = array();
		foreach ($returnColumns as $dc) {
			$arr[] = isset($row->$dc) ? $row->$dc : '';
		}
		echo $sep;
		echo json_encode($arr);
		if ($sep == '') $sep = ',';
	}

	echo '] }';

	$db->close();
	exit();
}
