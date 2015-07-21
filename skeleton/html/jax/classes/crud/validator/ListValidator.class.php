<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Validator', false)) include dirname(__FILE__).'/Validator.class.php';
class ListValidator extends Validator {
	protected $type, $valueName;
	protected $validValues, $caseInsensitive;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->type = $this->cleanUpPHPType(isset($params['type']) ? strtolower($params['type']) : '');
		if ($this->type === false) throw new Exception('Missing or invalid type parameter');
		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
		$this->validValues = (isset($params['validValues']) && is_array($params['validValues'])) ?
				$params['validValues'] : array();
		if (empty($this->validValues)) throw new Exception('Missing or empty validValues parameter');
		$this->caseInsensitive =
			(isset($params['caseInsensitive']) && is_bool($params['caseInsensitive'])) ?
				$params['caseInsensitive'] : false;
	}

	public function validate($db, &$row) {
		$vn = $this->valueName;
		$val = property_exists($row, $vn) ? $row->$vn : '';

		if (($this->allowNULL) && ($val === null)) return '';

		$notInList = true;
		switch ($this->type) {
		case 'int':
			$val = (int)$val;
			foreach ($this->validValues as $vv) {
				if ($val == (int)$vv) {
					$notInList = false;
					break;
				}
			}
			break;
		case 'double':
			$val = (double)$val;
			foreach ($this->validValues as $vv) {
				if ($val == (double)$vv) {
					$notInList = false;
					break;
				}
			}
			break;
		case 'boolean':
			$val = (boolean)$val;
			foreach ($this->validValues as $vv) {
				if ($val == (boolean)$vv) {
					$notInList = false;
					break;
				}
			}
			break;
		case 'string':
			$val = (string)$val;
			if ($this->caseInsensitive) {
				foreach ($this->validValues as $vv) {
					if (strcasecmp($val, (string)$vv) == 0) {
						$notInList = false;
						break;
					}
				}
			} else {
				foreach ($this->validValues as $vv) {
					if ($val == (string)$vv) {
						$notInList = false;
						break;
					}
				}
			}
			break;
		default:
			throw new Exception("Internal Error: unexpected type");
		}
		if ($notInList) {
			if ($this->errorMsg != '') return $this->errorMsg;
			return "Not one of the allowed values.";
		}
		return '';
	}
}
