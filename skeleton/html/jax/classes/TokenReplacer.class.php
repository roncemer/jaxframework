<?php
// Copyright (c) 2010 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

class TokenReplacer {
	public $tokenBeginMarker = '${';
	public $tokenEndMarker = '}';
	protected $firstFilter = null;

	public function TokenReplacer($firstFilter = null) {
		if ( ($firstFilter !== null) && ($firstFilter !== false) ) {
			$this->firstFilter = $firstFilter;
		}
	}

	public function appendFilter($filter) {
		if ( ($filter !== null) && ($filter !== false) ) {
			if ( ($this->firstFilter === null) || ($this->firstFilter === false) ) {
				$this->firstFilter = $filter;
			} else {
				$this->firstFilter->append($filter);
			}
		}
	}

	// Replace tokens.
	// $text: the template text.
	// $tokenValues: an associative array of token names to corresponding values.
	// Tokens are in the format ${tokenName[|filter[|filter]...]}.
	// Note that ${ and } are set by the $tokenBeginMarker and $tokenEndMarker instance variables,
	// respectively.  The defaults are '${' and '}', respectively.
	// Returns the text, with tokens replaced.
	public function replaceTokens($text, $tokenValues) {
		$idx1 = 0;
		$blen = strlen($this->tokenBeginMarker);
		$elen = strlen($this->tokenEndMarker);
		$len = strlen($text);
		while (($idx1 = strpos($text, $this->tokenBeginMarker, $idx1)) !== false) {
			$depth = 1;
			$bidx = $idx1+$blen;
			$eidx = $len;
			for ($idx2 = $bidx; $idx2 < $len;) {
				if ((($idx2+$blen) <= $len) &&
					(substr_compare(
						$text,
						$this->tokenBeginMarker,
						$idx2,
						$blen) == 0)) {
					$idx2 += $blen;
					$depth++;
				} else if ((($idx2+$elen) <= $len) &&
						   (substr_compare(
								$text,
								$this->tokenEndMarker,
								$idx2,
								$elen) == 0)) {
					$idx2 += $elen;
					$depth--;
					if ($depth <= 0) {
						$eidx = $idx2-$elen;
						break;
					}
				} else {
					$idx2++;
				}
			}
			$pieces = explode('|', substr($text, $bidx, $eidx-$bidx));
			if (count($pieces) > 0) {
				for ($i = 0, $n = count($pieces); $i < $n; $i++) {
					$pieces[$i] = $this->replaceTokens($pieces[$i], $tokenValues);
				}
				$pieces[0] = trim($pieces[0]);
				$val = isset($tokenValues[$pieces[0]]) ? $tokenValues[$pieces[0]] : '';
				if ( ($this->firstFilter !== null) && ($this->firstFilter !== false) ) {
					for ($j = 1; $j < count($pieces); $j++) {
						$val = $this->firstFilter->filter($val, strtolower(trim($pieces[$j])));
					}
				}
			} else {
				$val = '';
			}
			$text =
				substr($text, 0, $idx1) .
				$val .
				(($idx2 < $len) ? substr($text, $idx2) : '');
			$idx1 += strlen($val);
			$len = strlen($text);
		}
		return $text;
	}
}

/*
$tokenValues = array(
	'abc'=>'123',
	'def'=>'456',
	'ghi'=>'789',
	'j'=>'0',
);
$tr = new TokenReplacer();
echo '['.$tr->replaceTokens('${abc} hello', $tokenValues)."]\n";
echo '['.$tr->replaceTokens('hello ${def}', $tokenValues)."]\n";
echo '['.$tr->replaceTokens('hello ${ghi} hello', $tokenValues)."]\n";
echo '['.$tr->replaceTokens('${j}', $tokenValues)."]\n";
*/
