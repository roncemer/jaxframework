<?php
// Copyright (c) 2010 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

abstract class ChainableFilter {
	protected $nextFilter = null;

	// Append a filter to the end of the filter chain.
	public function append($filter) {
		$lastFilter = $this;
		while ( ($lastFilter->nextFilter !== null) && ($lastFilter->nextFilter !== false) ) {
			$lastFilter = $lastFilter->nextFilter;
		}
		$lastFilter->nextFilter = $filter;
		return $filter;
	}

	// Filter $text according to $filterSpec, which is a filter name and optional
	// filter parameters, enclosed in parentheses and separated by commas.
	// If there are no parameters, it's just a filter name.
	public abstract function filter($text, $filterSpec);
}
