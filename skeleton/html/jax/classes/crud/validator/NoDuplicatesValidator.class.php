<?php
// Copyright (c) 2011-2016 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Validator', false)) include dirname(__FILE__).'/Validator.class.php';
loadResourceBundle(__FILE__);

class NoDuplicatesValidator extends Validator {
	protected $table;
	protected $fields;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->table = isset($params['table']) ? (string)$params['table'] : '';
		if ($this->table == '') throw new Exception('Missing or empty table parameter');

		$this->fields = array();
		$idx = 0;
		$flds = isset($params['fields']) && is_array($params['fields']) ?
			$params['fields'] : array();
		foreach ($flds as $fld) {
			$idx++;
			$tp = isset($fld['type']) ? $this->cleanUpPreparedStatementType((string)$fld['type']) : 'int';
			if ($tp == false) {
				throw new Exception(sprintf('Invalid type parameter in fields entry #%d', $idx));
			}
			$field = isset($fld['field']) ? (string)$fld['field'] : '';
			if ($field == '') {
				throw new Exception(sprintf('Missing or empty field parameter in fields entry #%d', $idx));
			}
			$queryOperator = isset($fld['queryOperator']) ? (string)$fld['queryOperator'] : '=';
			if ($queryOperator == '') {
				throw new Exception(sprintf('Missing or empty queryOperator parameter in fields entry #%d', $idx));
			}
			if (!in_array($queryOperator, self::$ALLOWED_QUERY_OPERATORS)) {
				throw new Exception(sprintf("Invalid queryOperator '%s' in fields entry #%d", $queryOperator, $idx));
			}
			switch ($tp) {
			case 'int':
			case 'float':
			case 'double':
			case 'boolean':
				if (!in_array($queryOperator, self::$ALLOWED_NUMERIC_QUERY_OPERATORS)) {
					throw new Exception(sprintf("Invalid queryOperator '%s' for numeric type in fields entry #%d", $queryOperator, $idx));
				}
				break;
			case 'string':
				if (!in_array($queryOperator, self::$ALLOWED_STRING_QUERY_OPERATORS)) {
					throw new Exception(sprintf("Invalid queryOperator '%s' for string type in fields entry #%d", $queryOperator, $idx));
				}
				break;
			case 'binary':
				if (!in_array($queryOperator, self::$ALLOWED_BINARY_QUERY_OPERATORS)) {
					throw new Exception(sprintf("Invalid queryOperator '%s' for binary type in fields entry #%d", $queryOperator, $idx));
				}
				break;
			default:
				throw new Exception(sprintf('Unexpected PreparedStatement data type: %s', $fld->type));
			}
			$this->fields[] = (object)array(
				'type'=>$tp,
				'field'=>$field,
				'queryOperator'=>$queryOperator,
			);
		}
		if (empty($this->fields)) {
			throw new Exception('Missing, empty or invalid fields parameter');
		}
	}

	public function validate($db, &$row) {
		$sql = sprintf(
			'select %s from %s',
			$this->fields[0]->field,
			$this->table
		);
		$sep = ' where ';
		foreach ($this->fields as $fld) {
			$qo = $fld->queryOperator;
			if (($qo == 'beginsWith') || ($qo == 'contains') || ($qo == 'endsWith')) $qo = 'like';
			$sql .= sprintf('%s%s %s ?', $sep, $fld->field, $qo);
			if ($sep != ' and ') $sep = ' and ';
		}
		$ps = new PreparedStatement($sql, 0, 1);
		foreach ($this->fields as $fld) {
			$vn = $fld->field;
			$val = property_exists($row, $vn) ? $row->$vn : '';
			// If we're set to allow nulls and any value is null, don't validate.
			if ($this->allowNULL && ($val === null)) return '';
			switch ($fld->type) {
			case 'int':
				$ps->setInt($val);
				break;
			case 'float':
				$ps->setFloat($val);
				break;
			case 'double':
				$ps->setDouble($val);
				break;
			case 'boolean':
				$ps->setBoolean($val);
				break;
			case 'string':
				switch ($fld->queryOperator) {
				case 'beginsWith':
					$ps->setString($val.'%');
					break;
				case 'contains':
					$ps->setString('%'.$val.'%');
					break;
				case 'endsWith':
					$ps->setString('%'.$val);
					break;
				default:
					$ps->setString($val);
					break;
				}
				break;
			case 'binary':
				$ps->setBinary($val);
				break;
			default:
				throw new Exception(sprintf('Unexpected PreparedStatement data type: %s', $fld->type));
			}
		}

		if ($db->fetchObject($db->executeQuery($ps), true)) {
			if ($this->errorMsg != '') return $this->errorMsg;
			return _t('NoDuplicatesValidator.class.errorMsg.anEntryAlreadyExistsWithThisValue');
		}
		return '';
	}
}
