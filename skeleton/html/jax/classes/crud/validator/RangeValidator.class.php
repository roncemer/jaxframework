<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Validator', false)) include dirname(__FILE__).'/Validator.class.php';
class RangeValidator extends Validator {
	protected $type, $valueName;
	protected $minVal, $maxVal, $caseInsensitive;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->type = $this->cleanUpPHPType(isset($params['type']) ? strtolower($params['type']) : '');
		if ($this->type === false) throw new Exception('Missing or invalid type parameter');
		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
		$this->minVal = isset($params['min']) ? $params['min'] : null;
		$this->maxVal = isset($params['max']) ? $params['max'] : null;
		if (($this->minVal === null) && ($this->maxVal === null)) {
			throw new Exception('Missing min and max parameter; at least one is required.');
		}
		$this->caseInsensitive =
			(isset($params['caseInsensitive']) && is_bool($params['caseInsensitive'])) ?
				$params['caseInsensitive'] : false;
	}

	public function validate($db, &$row) {
		$vn = $this->valueName;
		$val = property_exists($row, $vn) ? $row->$vn : '';

		if (($this->allowNULL) && ($val === null)) return '';

		$outOfRange = false;
		switch ($this->type) {
		case 'int':
			$val = (int)$val;
			if ((($this->minVal !== null) && ($val < (int)$this->minVal)) ||
				(($this->maxVal !== null) && ($val > (int)$this->maxVal))) {
				$outOfRange = true;
			}
			break;
		case 'double':
			$val = (double)$val;
			if ((($this->minVal !== null) && ($val < (double)$this->minVal)) ||
				(($this->maxVal !== null) && ($val > (double)$this->maxVal))) {
				$outOfRange = true;
			}
			break;
		case 'boolean':
			$val = (boolean)$val;
			if ((($this->minVal !== null) && ($val < (boolean)$this->minVal)) ||
				(($this->maxVal !== null) && ($val > (boolean)$this->maxVal))) {
				$outOfRange = true;
			}
			break;
		case 'string':
			$val = (string)$val;
			if ($this->caseInsensitive) {
				if ((($this->minVal !== null) && (strcasecmp($val, (string)$this->minVal) < 0)) ||
					(($this->maxVal !== null) && (strcasecmp($val, (string)$this->maxVal) > 0))) {
					$outOfRange = true;
				}
			} else {
				if ((($this->minVal !== null) && (strcmp($val, (string)$this->minVal) < 0)) ||
					(($this->maxVal !== null) && (strcmp($val, (string)$this->maxVal) > 0))) {
					$outOfRange = true;
				}
			}
			break;
		default:
			throw new Exception("Internal Error: unexpected type");
		}
		if ($outOfRange) {
			if ($this->errorMsg != '') return $this->errorMsg;
			$quote = ($this->type == 'string') ? '"' : '';
			if ($this->minVal !== null) {
				if ($this->maxVal !== null) {
					return sprintf(
						"Must be between %s%s%s and %s%s%s.",
						$quote,
						$this->minVal,
						$quote,
						$quote,
						$this->maxVal,
						$quote
					);
				}
				return sprintf(
					"Must be greater than or equal to %s%s%s.",
					$quote,
					$this->minVal,
					$quote
				);
			} else if ($this->maxVal !== null) {
				return sprintf(
					"Must be less than or equal to %s%s%s.",
					$quote,
					$this->maxVal,
					$quote
				);
			}
		}
		return '';
	}
}
