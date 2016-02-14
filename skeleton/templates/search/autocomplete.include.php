<?php
{{generatedFileMessage}}
{{phpIncludes}}
if (isset($command) && ($command == '{{searchCommand}}')) {
	header('Content-Type: application/json');
	$db = ConnectionFactory::getConnection();
	$query = isset($params['term']) ? trim($params['term']) : '';
{{if_haveAltIdColumn}}
{{getAltIdParam}}
{{/if_haveAltIdColumn}}
	if ($query != '') {
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
EOF
		;
		$offset = isset($params['offset']) ? (int)$params['offset'] : 0;
		$limit = isset($params['limit']) ? (int)$params['limit'] : 0;
		if ( ($limit < 1) || ($limit > 100) ) $limit = 100;
{{if_haveAltIdColumn}}
	} else if (${{altIdCol}} !== null) {
		$sqlTail = <<<EOF
 from {{tableName}} pri
 {{joins}}
 where pri.{{altIdCol}} = ?{{andWhere}}
EOF
		;
		$offset = 0;
		$limit = 1;
{{/if_haveAltIdColumn}}
	} else {
{{getIdParam}}
		$sqlTail = <<<EOF
 from {{tableName}} pri
 {{joins}}
 where pri.{{idCol}} = ?{{andWhere}}
EOF
		;
		$offset = 0;
		$limit = 1;
	}

	$ps = new PreparedStatement(<<<EOF
select pri.*{{extraSelectColumns}}
EOF
		.$sqlTail,
		$offset,
		$limit
	);
	if ($query != '') {
{{searchWhereAssignments}}
{{if_haveAltIdColumn}}
	} else if (${{altIdCol}} !== null) {
		$ps->set{{uAltIdColumnPSType}}(${{altIdCol}});
{{/if_haveAltIdColumn}}
	} else {
		$ps->set{{uIdColumnPSType}}(${{idCol}});
	}
{{andWhereAssignments}}
	$rs = $db->executeQuery($ps);
	$results = array();
	while ($row = $db->fetchObject($rs)) {
		$results[] = array(
			'label'=>{{searchResultLabelExpression}},
			'value'=>{{searchResultValueExpression}}
		);
	}
	$db->freeResult($rs);

	echo json_encode($results);

	$db->close();
	exit();
}
