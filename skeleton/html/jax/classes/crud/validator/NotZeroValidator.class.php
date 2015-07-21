<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Validator', false)) include dirname(__FILE__).'/Validator.class.php';
class NotZeroValidator extends Validator {
	protected $type, $valueName;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->type = $this->cleanUpPHPType(isset($params['type']) ? strtolower($params['type']) : '');
		if ($this->type === false) throw new Exception('Missing or invalid type parameter');
		if ($this->type == 'string') throw new Exception('Type of "string" is not supported by this Validator');
		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
	}

	public function validate($db, &$row) {
		$vn = $this->valueName;
		$val = property_exists($row, $vn) ? $row->$vn : '';

		if (($this->allowNULL) && ($val === null)) return '';

		$outOfRange = false;
		$isZero = false;
		switch ($this->type) {
		case 'int':
			if (((int)$val) == 0) $isZero = true;
			break;
		case 'double':
			if (((double)$val) == 0.0) $isZero = true;
			break;
		case 'boolean':
			if (((boolean)$val) == false) $isZero = true;
			break;
		}
		if ($isZero) {
			if ($this->errorMsg != '') return $this->errorMsg;
			return 'Cannot be zero.';
		}
		return '';
	}
}
