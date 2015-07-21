<?php
// Copyright (c) 2010 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!class_exists('ChainableFilter', false)) include dirname(__FILE__).'/ChainableFilter.class.php';

class URLEncodeChainableFilter extends ChainableFilter {
	public function filter($text, $filterSpec) {
		if (strcasecmp(trim($filterSpec), 'urlencode') == 0) {
			return urlencode($text);
		}
		if (strcasecmp(trim($filterSpec), 'rawurlencode') == 0) {
			return rawurlencode($text);
		}
		if ( ($this->nextFilter !== null) && ($this->nextFilter !== false) ) {
			return $this->nextFilter->filter($text, $filterSpec);
		}
		return $text;
	}
}
