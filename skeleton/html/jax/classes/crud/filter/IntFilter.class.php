<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

if (!class_exists('Filter', false)) include dirname(__FILE__).'/Filter.class.php';
class IntFilter extends Filter {
	protected $valueName;
	protected $convertZeroToNULL;

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->valueName = isset($params['valueName']) ? $params['valueName'] : '';
		if ($this->valueName == '') throw new Exception('Missing or empty valueName parameter');
		$this->convertZeroToNULL =
			isset($params['convertZeroToNULL']) ? (boolean)$params['convertZeroToNULL'] : false;
	}

	public function filter($db, &$row) {
		$vn = $this->valueName;
		$row->$vn = property_exists($row, $vn) ? (int)$row->$vn : 0;
		if (($this->convertZeroToNULL) && ($row->$vn == 0)) $row->$vn = null;
	}
}
