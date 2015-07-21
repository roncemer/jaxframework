<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Validator', false)) include dirname(__FILE__).'/Validator.class.php';
class ForeignKeyValidator extends Validator {
	protected $foreignTable;
	protected $foreignKeyMapping;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->foreignTable = isset($params['foreignTable']) ? (string)$params['foreignTable'] : '';
		if ($this->foreignTable == '') throw new Exception('Missing or empty foreignTable parameter');

		$this->foreignKeyMapping = array();
		$idx = 0;
		$fkms = (isset($params['foreignKeyMapping']) && is_array($params['foreignKeyMapping'])) ?
			$params['foreignKeyMapping'] : array();
		foreach ($fkms as $fkm) {
			$idx++;
			$tp = isset($fkm['type']) ? $this->cleanUpPreparedStatementType((string)$fkm['type']) : 'int';
			if ($tp == false) {
				throw new Exception(sprintf('Invalid type parameter in foreignKeyMapping entry #%d', $idx));
			}
			$local = isset($fkm['local']) ? (string)$fkm['local'] : '';
			if ($local == '') {
				throw new Exception(sprintf('Missing or empty local parameter in foreignKeyMapping entry #%d', $idx));
			}
			$foreign = isset($fkm['foreign']) ? (string)$fkm['foreign'] : '';
			if ($foreign == '') {
				throw new Exception(sprintf('Missing or empty foreign parameter in foreignKeyMapping entry #%d', $idx));
			}
			$this->foreignKeyMapping[] = (object)array(
				'type'=>$tp,
				'local'=>$local,
				'foreign'=>$foreign,
			);
		}
		if (empty($this->foreignKeyMapping)) {
			throw new Exception('Missing, empty or invalid foreignKeyMapping parameter');
		}
	}

	public function validate($db, &$row) {
		$sql = sprintf(
			'select %s from %s',
			$this->foreignKeyMapping[0]->foreign,
			$this->foreignTable
		);
		$sep = ' where ';
		foreach ($this->foreignKeyMapping as $fkm) {
			$sql .= sprintf('%s%s = ?', $sep, $fkm->foreign);
			if ($sep != ' and ') $sep = ' and ';
		}
		$ps = new PreparedStatement($sql, 0, 1);
		foreach ($this->foreignKeyMapping as $fkm) {
			$vn = $fkm->local;
			$val = property_exists($row, $vn) ? $row->$vn : '';
			// If we're set to allow nulls and any value is null, don't validate.
			if ($this->allowNULL && ($val === null)) return '';
			switch ($fkm->type) {
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
				$ps->setString($val);
				break;
			case 'binary':
				$ps->setBinary($val);
				break;
			default:
				throw new Exception(sprintf('Unexpected PreparedStatement data type: %s', $fkm->type));
			}
		}

		if (!$db->fetchObject($db->executeQuery($ps), true)) {
			if ($this->errorMsg != '') return $this->errorMsg;
			return 'Must match an existing entry.';
		}
		return '';
	}
}
