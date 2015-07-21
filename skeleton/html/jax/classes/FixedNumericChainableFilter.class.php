<?php
// Copyright (c) 2010 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!class_exists('ChainableFilter', false)) include dirname(__FILE__).'/ChainableFilter.class.php';

class FixedNumericChainableFilter extends ChainableFilter {
	public function filter($text, $filterSpec) {
		if (strncasecmp($filterSpec, 'fixednumeric', 12) == 0) {
			$scale = (int)trim(substr($filterSpec, 12), "() \t\n\r\x0B\0");
			return number_format((float)$text, $scale, '.', '');
		}
		if ( ($this->nextFilter !== null) && ($this->nextFilter !== false) ) {
			return $this->nextFilter->filter($text, $filterSpec);
		}
		return $text;
	}
}
