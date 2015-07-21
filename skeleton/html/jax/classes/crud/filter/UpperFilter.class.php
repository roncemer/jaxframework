<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Filter', false)) include dirname(__FILE__).'/Filter.class.php';
class UpperFilter extends Filter {
	protected $valueName;
	protected $convertEmptyToNULL;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
		$this->convertEmptyToNULL =
			isset($params['convertEmptyToNULL']) ? (boolean)$params['convertEmptyToNULL'] : false;
	}

	public function filter($db, &$row) {
		$vn = $this->valueName;
		$row->$vn = strtoupper(property_exists($row, $vn) ? (string)$row->$vn : '');
		if (($this->convertEmptyToNULL) && ($row->$vn == '')) $row->$vn = null;
	}
}
