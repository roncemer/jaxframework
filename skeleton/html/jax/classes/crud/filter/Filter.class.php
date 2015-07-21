<?php
// Copyright (c) 2011-2012 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// This file is part of the jaxframework project.

abstract class Filter {
	public function __construct($params = array()) {
	}

	public abstract function filter($db, &$row);
}
