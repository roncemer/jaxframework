<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Filter', false)) include dirname(__FILE__).'/Filter.class.php';
class DecimalFilter extends Filter {
	protected $valueName;
	protected $fractionalDigits;
	protected $convertZeroToNULL;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
		$this->fractionalDigits = isset($params['fractionalDigits']) ? (int)$params['fractionalDigits'] : 0;
		$this->convertZeroToNULL =
			isset($params['convertZeroToNULL']) ? (boolean)$params['convertZeroToNULL'] : false;
	}

	public function filter($db, &$row) {
		$vn = $this->valueName;
		if ($this->fractionalDigits !== false) {
			$row->$vn = round(property_exists($row, $vn) ? (double)$row->$vn : 0.0, $this->fractionalDigits);
		}
		$row->$vn = property_exists($row, $vn) ? (double)$row->$vn : 0.0;
		if (($this->convertZeroToNULL) && ($row->$vn == 0)) $row->$vn = null;
	}
}
