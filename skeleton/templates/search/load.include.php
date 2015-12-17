<?php
{{generatedFileMessage}}
{{phpIncludes}}
if (isset($command) && ($command == '{{searchCommand}}')) {
	header('Content-Type: application/json');
	$db = ConnectionFactory::getConnection();
	${{tableName}}DAO = new {{uTableName}}DAO($db);
{{initRelationDAOs}}
{{getIdParam}}
	if ({{emptyIdCheck}}) {
		$rows = array({{uTableName}}::createDefault());
	} else {
		$sql = <<<EOF
select * from {{tableName}} pri where pri.{{idCol}} = ?{{andWhere}}
EOF
		;
		$ps = new PreparedStatement($sql, 0, 1);
		$ps->set{{uIdColumnPSType}}(${{idCol}});
{{andWhereAssignments}}
		$rows = ${{tableName}}DAO->findWithPreparedStatement($ps);
	}
{{loadRelations}}
{{rowProcessingPHPCode}}
{{unsetForbiddenColumns}}
	echo json_encode($rows);
	$db->close();
	exit();
}
