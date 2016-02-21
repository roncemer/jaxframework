<?php
// Copyright (c) 2011-2016 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Validator', false)) include dirname(__FILE__).'/Validator.class.php';
loadResourceBundle(__FILE__);

class NotEmptyValidator extends Validator {
	protected $valueName;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
	}

	public function validate($db, &$row) {
		$vn = $this->valueName;
		$val = property_exists($row, $vn) ? $row->$vn : '';

		if (($this->allowNULL) && ($val === null)) return '';

		$outOfRange = false;
		if ($val == '') {
			if ($this->errorMsg != '') return $this->errorMsg;
			return _t('NotEmptyValidator.class.errorMsg.cannotBeEmpty');
		}
		return '';
	}
}
