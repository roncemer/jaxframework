<?php
{{generatedFileMessage}}
{{mainOkCheck}}{{classAutoloadPathsInit}}
include {{docRootPath}}.'/{{jaxInclude}}/autoload.include.php';
include {{docRootPath}}.'/include/requireLogin.include.php';
include {{docRootPath}}.'/{{jaxInclude}}/validation.include.php';
include {{docRootPath}}.'/{{jaxInclude}}/l10n.include.php';
loadResourceBundle({{docRootPath}}.'/jax/resources/system');
loadResourceBundle({{docRootPath}}.'/resources/system');
loadResourceBundle({{docRootPath}}.'/jax/resources/crud');
loadResourceBundle({{docRootPath}}.'/resources/crud');
loadResourceBundle($_SERVER['SCRIPT_FILENAME']);

Permissions::inScriptPermissionsCheck({{loggedInId}}, true);

$params = FrontController::getRequestParams();
$command = isset($params['command']) ? $params['command'] : '';

{{phpIncludes}}

{{mainHooksInclude}}

if (function_exists('initHook')) initHook();

{{postInitPHPIncludes}}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($command) {
	case 'save{{uTableName}}':
		$justInsertedRowId = 0;

		$db = ConnectionFactory::getConnection();
		$db->beginTransaction();
		$committed = false;

		${{tableName}}DAO = new {{uTableName}}DAO($db);

		$result = createMsgResultObj();

		// Convert the entire POST body into a value object.
		$row = (object)$_POST;

		if (function_exists('preFilterHook')) preFilterHook();

{{filterCode}}

{{validationCode}}

		if (function_exists('validationHook')) validationHook();

		if (($result->errorMsg == '') && empty($result->fieldErrors)) {
			if ($row->{{idCol}} > 0) {
				if (($oldRow = ${{tableName}}DAO->load($row->{{idCol}})) === false) {
					$result->errorMsg .= sprintf(
						_t('crud.idNotFoundChangesNotSaved'),
						_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}'),
						$row->{{idCol}}
					)."\n";
				} else {
					if (($result->errorMsg == '') && empty($result->fieldErrors)) {

						$onlyUpdateColumns = {{onlyUpdateColumns}};
						$neverUpdateColumns = {{neverUpdateColumns}};

						if (function_exists('preUpdateHook')) preUpdateHook();

						$__colsToUpdate = empty($onlyUpdateColumns) ?
							array_keys((array)$oldRow) : $onlyUpdateColumns;
						foreach ($__colsToUpdate as $name) {
							// Use property_exists() because isset() returns false for null
							// values, and we want to copy null values.
							if (property_exists($row, $name) &&
								(!in_array($name, $neverUpdateColumns))) {
								$oldRow->$name = $row->$name;
							}
						}

						try {
							$success = ${{tableName}}DAO->update($oldRow);
						} catch (Exception $ex) {
							$success = false;
						}

						if ($success) {
							if (function_exists('postUpdateHook')) postUpdateHook();

							if ($success) {
								$db->commitTransaction();
								$committed = true;
								$result->successMsg .= sprintf(
									_t('crud.idUpdated'),
									_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}'),
									$row->{{idCol}}
								)."\n";
							}
						}
						if (!$success) {
							if (($result->errorMsg == '') && (empty($result->fieldErrors))) {
								$result->errorMsg .= sprintf(
									_t('crud.rowCouldNotBeUpdated'),
									_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}')
								)."\n";
							}
						}
					}
				}
			} else {	// if ($row->{{idCol}} > 0)

				// Convert the value object into the actual entity.
				$newRow = new {{uTableName}}();
				$newRow->loadFromArray((array)$row);

				if (function_exists('preInsertHook')) preInsertHook();

				try {
					$success = ${{tableName}}DAO->insert($newRow);
				} catch (Exception $ex) {
					$success = false;
				}

				if ($success) {
					$row->{{idCol}} = $newRow->{{idCol}};
					$justInsertedRowId = $newRow->{{idCol}};

					if (function_exists('postInsertHook')) postInsertHook();

					if ($success) {
						$db->commitTransaction();
						$committed = true;
						$result->successMsg .= sprintf(
							_t('crud.newRowAdded'),
							_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}')
						)."\n";
					}
				}
				if (!$success) {
					if (($result->errorMsg == '') && (empty($result->fieldErrors))) {
						$result->errorMsg .= sprintf(
							_t('crud.rowCouldNotBeAdded'),
							_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}')
						)."\n";
					}
				}
			}	// if ($row->{{idCol}} > 0) ... else
		}	// if (($result->errorMsg == '') && empty($result->fieldErrors))

		if (!$committed) {
			$db->rollbackTransaction();
		}
		$db->close();

		header('Content-Type: text/html');
?>
<html><head></head><body><script type="text/javascript">
parent.parseMsgsFromJSON(<?php echo json_encode(json_encode($result)); ?>);
<?php if (($result->errorMsg == '') && empty($result->fieldErrors)) { ?>
parent.justInsertedRowId = <?php echo json_encode($justInsertedRowId); ?>;
var returnToSearchMode = true;
if (typeof parent.postSaveHook == 'function') {
	if (parent.postSaveHook() === false) {
		returnToSearchMode = false;
	}
}
if (returnToSearchMode) {
	parent.setMode(parent.SEARCH_MODE, false);
}
<?php } ?>
</script></body></html>
<?php
		exit();

	case 'delete{{uTableName}}':
		$db = ConnectionFactory::getConnection();
		$db->beginTransaction();
		$committed = false;

		${{tableName}}DAO = new {{uTableName}}DAO($db);

		$result = createMsgResultObj();
		$id = isset($_POST['{{idCol}}']) ? (int)trim($_POST['{{idCol}}']) : 0;

		if (${{tableName}}DAO->load($id) === false) {
			$result->errorMsg .= sprintf(
				_t('crud.idNotFoundRowNotDeleted'),
				_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}'),
				$id
			)."\n";
		} else {

			if (function_exists('deleteCheckHook')) deleteCheckHook();

			if (($result->errorMsg == '') && empty($result->fieldErrors)) {

				if (function_exists('preDeleteHook')) preDeleteHook();

				try {
					$success = ${{tableName}}DAO->delete($id);
				} catch (Exception $ex) {
					$success = false;
				}

				if ($success) {
					if (function_exists('postDeleteHook')) postDeleteHook();

					$db->commitTransaction();
					$committed = true;
					$result->successMsg .= sprintf(
						_t('crud.rowDeleted'),
						_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}'),
						$id
					)."\n";
				} else {
					$result->errorMsg .= sprintf(
						_t('crud.rowCouldNotBeDeleted'),
						_t('crud.{{crudName}}.tableDescription', '{{tableDescription}}')
					)."\n";
				}
			}
		}

		if (!$committed) {
			$db->rollbackTransaction();
		}
		$db->close();

		header('Content-Type: application/json');
		echo json_encode($result);
		exit();
	}
}

if (function_exists('preViewOutputHook')) preViewOutputHook();

$viewOk = true;
include {{viewInclude}};
