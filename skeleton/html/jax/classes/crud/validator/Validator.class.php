<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

abstract class Validator {
	public static $ALLOWED_QUERY_OPERATORS = array('=', '<>', '<', '<=', '>', '>=', 'beginsWith', 'contains', 'endsWith');
	public static $ALLOWED_NUMERIC_QUERY_OPERATORS = array('=', '<>', '<', '<=', '>', '>=');
	public static $ALLOWED_STRING_QUERY_OPERATORS = array('=', '<>', '<', '<=', '>', '>=', 'beginsWith', 'contains', 'endsWith');
	public static $ALLOWED_BINARY_QUERY_OPERATORS = array('=', '<>');

	protected $allowNULL;
	protected $errorMsg;

	public function __construct($params = array()) {
		$this->allowNULL =
			(isset($params['allowNULL']) && is_bool($params['allowNULL'])) ?
				$params['allowNULL'] : false;
		$this->errorMsg = isset($params['errorMsg']) ? (string)$params['errorMsg'] : '';
	}

	// Clean up a PHP data type.
	// Valid data types are: integer, int, float, double, bool, boolean, char, string.
	// Output data types (afer clean-up) are: int, double, boolean, string.
	// Returns false $type is not valid.
	protected function cleanUpPHPType($type) {
		switch ($type) {
		case 'integer':
		case 'int':
			return 'int';
		case 'float':
		case 'double':
			return 'double';
		case 'bool':
		case 'boolean':
			return 'boolean';
		case 'char':
		case 'string':
			return 'string';
		default:
			return false;
		}
	}

	// Clean up a PreparedStatement data type.
	// Valid data types are: integer, int, float, double, bool, boolean, char, string, binary.
	// Output data types (afer clean-up) are: int, float, double, boolean, string, binary.
	// Returns false $type is not valid.
	protected function cleanUpPreparedStatementType($type) {
		if (($type == 'float') || ($type == 'binary')) return $type;
		return $this->cleanUpPHPType($type);
	}

	// Validate value(s) based on the rules of this validator.
	// $db is an open database Connection object.
	// $row is an object whose attributes contain at least the value(s) being validated by this
	// validator.
	// Returns a string containing the error message, or an empty string if no error.
	public abstract function validate($db, &$row);
}
