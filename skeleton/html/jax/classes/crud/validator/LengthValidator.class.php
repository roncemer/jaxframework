<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Validator', false)) include dirname(__FILE__).'/Validator.class.php';
class LengthValidator extends Validator {
	protected $valueName;
	protected $minLength, $maxLength;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
		$this->minLength = isset($params['minLength']) ? $params['minLength'] : null;
		$this->maxLength = isset($params['maxLength']) ? $params['maxLength'] : null;
		if (($this->minLength === null) && ($this->maxLength === null)) {
			throw new Exception('Missing minLength and maxLength parameter; at least one is required.');
		}
	}

	public function validate($db, &$row) {
		$vn = $this->valueName;
		$val = property_exists($row, $vn) ? $row->$vn : '';

		if (($this->allowNULL) && ($val === null)) return '';

		$len = strlen($val);
		if ((($this->minLength !== null) && ($len < (int)$this->minLength)) ||
			(($this->maxLength !== null) && ($len > (int)$this->maxLength))) {
			if ($this->errorMsg != '') return $this->errorMsg;
			if ($this->minLength !== null) {
				if ($this->maxLength !== null) {
					return sprintf(
						"Must be between %d and %d characters in length.",
						$this->minLength,
						$this->maxLength
					);
				}
				return sprintf(
					"Must be at least %d characters in length.",
					$this->minLength
				);
			} else if ($this->maxLength !== null) {
				return sprintf(
					"Cannot exceed %d characters in length.",
					$this->maxLength
				);
			}
		}
		return '';
	}
}
