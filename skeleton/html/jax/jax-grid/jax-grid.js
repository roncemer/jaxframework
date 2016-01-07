// Copyright (c) 2014-2016 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// Wrapper function for _t() in l10.js.  If we don't have l10n.js included, fake it.
function __jax_grid_pager_t(resourceId, defaultText) {
	if (typeof(_t) == 'function') {
		return _t(resourceId, defaultText);
	}
	return defaultText;
}

angular.module('JaxGridApp', [])
.controller('Controller', function($scope, $sce) {

	$scope.clearContents = function(goToFirstPage) {
		$scope.clearTrustedHTMLCache();
		$scope.rows = [];
		if ((typeof(goToFirstPage) == 'undefined') || (goToFirstPage)) {
			$scope.pageIdx = 0;
		}
	}

	$scope.reset = function() {
		$scope.containerElement = null;

		$scope.rowsPerPage = 10;
		$scope.numPagesToShowInPager = 10;
		$scope.externalPager = false;
		$scope.externalPager_numPages = 1;
		$scope.blankRow = {};

		$scope.searchText = '';
		$scope.searchBy = '';

		$scope.sorts = [];
		$scope.maxSorts = 4;

		// Each entry in this array must be an object with a searchBy attribute which becomes
		// the $scope.searchBy value when the option is selected, and a description attribute
		// which contains the human-readable description for the option.
		//     {searchBy:'name', description:'Name'},
		//     {searchBy:'address', description:'Address'},
		$scope.searchByOptions = [];

		$scope.userGestureSearchTimeoutMS = 1;
		$scope.typingSearchTimeoutMS = 500;
		$scope.searchCallbackThis = null;
		$scope.searchCallback = null;

		if ((typeof($scope.searchTimer) != 'undefined') && ($scope.searchTimer !== null)) {
			clearTimeout($scope.searchTimer);
		}
		$scope.searchTimer = null;
		$scope.searchTimer_goToFirstPage = false;

		$scope.fieldErrorsPrefixTemplate = '';

		$scope.rebuildFieldErrors();

		$scope.clearContents();
	}

	$scope.getNumPages = function() {
		if (!$scope.externalPager) {
			return Math.max(1, Math.ceil($scope.rows.length/$scope.rowsPerPage));
		} else {
			return Math.max(1, $scope.externalPager_numPages);
		}
	}

	$scope.getPagerPageIndexes = function(currentPageIdx) {
		var numPages = $scope.getNumPages();
		var firstPage = Math.floor(currentPageIdx/$scope.numPagesToShowInPager)*$scope.numPagesToShowInPager;
		var lastPage = Math.min(firstPage+($scope.numPagesToShowInPager-1), numPages-1);
		var pageIndexes = [];
		for (var i = firstPage; i <= lastPage; i++) pageIndexes.push(i);
		return pageIndexes;
	}

	$scope.setPageIdx = function(newPageIdx) {
		$scope.pageIdx = Math.max(0, Math.min($scope.getNumPages()-1, newPageIdx));
	}

	$scope.getRowIndexes = function() {
		var start = (!$scope.externalPager) ? $scope.pageIdx*$scope.rowsPerPage : 0;
		var aend = start+$scope.rowsPerPage;
		if (aend > $scope.rows.length) aend = $scope.rows.length;
		var arr = [];
		for (var i = start; i < aend; i++) arr.push(i);
		return arr;
	}

	$scope.rowNumberFromIndex = function(rowIdx) {
		return (!$scope.externalPager) ?
			(rowIdx+1) : (($scope.pageIdx*$scope.rowsPerPage)+rowIdx+1);
	}

	$scope.addRow = function(row, goToPage) {
		var idx = $scope.rows.length;
		var newrow = {};
		for (var i in $scope.blankRow) newrow[i] = $scope.blankRow[i];
		if (typeof(row) == 'object') {
			for (var i in row) newrow[i] = row[i];
		}
		$scope.rows.push(newrow);
		if (!$scope.externalPager) {
			if ((typeof(goToPage) == 'undefined') || (goToPage)) {
				$scope.pageIdx = Math.floor(idx/$scope.rowsPerPage);
			}
		} else {
			if ((typeof(goToPage) != 'undefined') && (goToPage)) {
				$scope.pageIdx = Math.floor(idx/$scope.rowsPerPage);
			}
		}
	}

	$scope.replaceRow = function(row, idx, goToPage) {
		if ((idx < 0) || (idx >= $scope.rows.length)) return;

		var newrow = {};
		for (var i in $scope.blankRow) newrow[i] = $scope.blankRow[i];
		if (typeof(row) == 'object') {
			for (var i in row) newrow[i] = row[i];
		}
		$scope.rows[idx] = newrow;
		if (!$scope.externalPager) {
			if ((typeof(goToPage) == 'undefined') || (goToPage)) {
				$scope.pageIdx = Math.floor(idx/$scope.rowsPerPage);
			}
		} else {
			if ((typeof(goToPage) != 'undefined') && (goToPage)) {
				$scope.pageIdx = Math.floor(idx/$scope.rowsPerPage);
			}
		}
	}

	$scope.insertRow = function(row, idx, goToPage) {
		if (idx < 0) idx = 0;
		if (idx >= $scope.rows.length) {
			$scope.addRow(row, goToPage);
			return;
		}

		var newrow = {};
		for (var i in $scope.blankRow) newrow[i] = $scope.blankRow[i];
		if (typeof(row) == 'object') {
			for (var i in row) newrow[i] = row[i];
		}
		$scope.rows.splice(idx, 0, newrow);
		if (!$scope.externalPager) {
			if ((typeof(goToPage) == 'undefined') || (goToPage)) {
				$scope.pageIdx = Math.floor(idx/$scope.rowsPerPage);
			}
		} else {
			if ((typeof(goToPage) != 'undefined') && (goToPage)) {
				$scope.pageIdx = Math.floor(idx/$scope.rowsPerPage);
			}
		}

		// Re-sequence field errors to accommodate new numbering above the inserted row.
		var anyRenamed = false;
		if ($scope.fieldErrorsPrefixTemplate != '') {
			for (var i = $scope.rows.length; i > idx; i--) {
				if ($scope.renameFieldErrorsByPrefix(
					$scope.fieldErrorsPrefixTemplate.replace('{{i}}', i-1),
					$scope.fieldErrorsPrefixTemplate.replace('{{i}}', i)
				)) {
					anyRenamed = true;
				}
			}
		}
		if (anyRenamed) {
			setTimeout(function() { if (!$scope.$$phase) $scope.$apply(); }, 1);
		}
	}

	$scope.deleteRow = function(idx) {
		if ((idx < 0) || (idx >= $scope.rows.length)) return;

		$scope.rows.splice([idx], 1);

		// Re-sequence field errors to accommodate new numbering at and above the deleted row.
		var anyRenamed = false;
		if ($scope.fieldErrorsPrefixTemplate != '') {
			for (var i = idx; i < $scope.rows.length; i++) {
				if ($scope.renameFieldErrorsByPrefix(
					$scope.fieldErrorsPrefixTemplate.replace('{{i}}', i+1),
					$scope.fieldErrorsPrefixTemplate.replace('{{i}}', i)
				)) {
					anyRenamed = true;
				}
			}
		}
		if (anyRenamed) {
			setTimeout(function() { if (!$scope.$$phase) $scope.$apply(); }, 1);
		}
	}

	$scope.swapRows = function(idx1, idx2) {
		if ((idx1 >= 0) && (idx1 < $scope.rows.length) &&
			(idx2 >= 0) && (idx2 < $scope.rows.length) &&
			(idx1 != idx2)) {
			var tmp = $scope.rows[idx1];
			$scope.rows[idx1] = $scope.rows[idx2];
			$scope.rows[idx2] = tmp;
		}
		$scope.swapFieldErrorsByPrefix(
			$scope.fieldErrorsPrefixTemplate.replace('{{i}}', idx1),
			$scope.fieldErrorsPrefixTemplate.replace('{{i}}', idx2)
		);
		setTimeout(function() { if (!$scope.$$phase) $scope.$apply(); }, 1);
	}

	$scope.triggerSearch = function(timeoutMS, goToFirstPage) {
		if ($scope.searchTimer !== null) clearTimeout($scope.searchTimer);
		if (goToFirstPage) $scope.searchTimer_goToFirstPage = true;
		$scope.searchTimer = setTimeout(
			function() {
				$scope.clearTrustedHTMLCache();
				if (($scope.searchTimer_goToFirstPage) && ($scope.pageIdx != 0)) $scope.pageIdx = 0;
				$scope.searchTimer = null;
				$scope.searchTimer_goToFirstPage = false;
				if (typeof($scope.searchCallback) == 'function') {
					$scope.searchCallback.call($scope.searchCallbackThis);
				}
				$scope.rebuildFieldErrors();
			},
			timeoutMS
		);
	}

	$scope.toggleSort = function(attributeName, append) {
		if (attributeName != '') {
			if (typeof(append) == 'undefined') append = false;

			if (!append) {
				if (($scope.sorts.length == 1) && ($scope.sorts[0].attr == attributeName)) {
					if ($scope.sorts[0].dir > 0) {
						$scope.sorts[0].dir = -$scope.sorts[0].dir;
					} else if ($scope.sorts[0].dir < 0) {
						$scope.sorts.splice(0, 1);
					}
				} else {
					$scope.sorts = [{attr:attributeName, dir:1}];
				}
			} else {
				if ($scope.sorts.length > 0) {
					var idx = $scope.sorts.length-1;
					if ($scope.sorts[idx].attr == attributeName) {
						if ($scope.sorts[idx].dir > 0) {
							$scope.sorts[idx].dir = -$scope.sorts[idx].dir;
						} else if ($scope.sorts[idx].dir < idx) {
							$scope.sorts.splice(idx, 1);
						}
					} else {
						for (var i = 0; i < $scope.sorts.length;) {
							if ($scope.sorts[i].attr == attributeName) {
								$scope.sorts.splice(i, 1);
							} else {
								i++;
							}
						}
						if ($scope.sorts.length < $scope.maxSorts) {
							$scope.sorts.push({attr:attributeName, dir:1});
						}
					}
				} else {
					$scope.sorts.push({attr:attributeName, dir:1});
				}
			}
		}
	}

	$scope.getSortHeadingClass = function(attributeName) {
		var cls = 'jax-grid-sortable-heading';
		var clsappend = ' jax-grid-sort-none';
		for (var i = 0; i < $scope.sorts.length; i++) {
			if ($scope.sorts[i].attr == attributeName) {
				if ($scope.sorts[i].dir != 0) {
					clsappend =
						' jax-grid-sort-'+i+
						' jax-grid-sort-'+(($scope.sorts[i].dir > 0) ? 'asc' : 'desc');
				}
				break;
			}
		}
		cls += clsappend;
		return cls;
	}

	// Functions to interface to specialFieldFeatures.js.

	$scope.filterFieldsWithSpecialFeatures = function() {
		if (($scope.containerElement !== null) &&
			(typeof($scope.containerElement) == 'object') &&
			(typeof(window.filterFieldsWithSpecialFeatures) == 'function')) {
			window.filterFieldsWithSpecialFeatures($scope.containerElement);
		}
	}

	$scope.syncAutocompleteSingleRowSelectorValuesFromInputs = function() {
		if (($scope.containerElement !== null) && (typeof($scope.containerElement) == 'object')) {
			// Update combobox values which don't match the values of their input elements.
			$('input[data-combobox-seq]', $scope.containerElement).each(function(idx, el) {
				var elem = $(el);
				var idval = elem.attr('data-idval');
				if ((idval === undefined) || (idval != elem.val())) {
					elem.trigger('lookupDescription');
				}
			});
			// Update select2 values which don't match the values of their input elements.
			$('input.select2-offscreen', $scope.containerElement).each(function(idx, el) {
				var elem = $(el);
				if (elem.select2('data') != elem.val()) elem.select2('val', elem.val());
			});
		}
	}

	// Functions to interface to lastParsedFieldErrors in parsemsgs.js.

	$scope.renameFieldErrorsByPrefix = function(oldPrefix, newPrefix) {
		var oldkeys = [];
		for (var key in window.lastParsedFieldErrors) {
			if (key.substr(0, oldPrefix.length) == oldPrefix) oldkeys.push(key);
		}
		if (oldkeys.length == 0) return false;
		for (var i = 0; i < oldkeys.length; i++) {
			var oldkey = oldkeys[i];
			var newkey = newPrefix+key.substr(oldPrefix.length);
			window.lastParsedFieldErrors[newkey] = window.lastParsedFieldErrors[oldkey];
			// Set it to empty for now.  We'll delete it in $scope.rebuildFieldErrors().
			window.lastParsedFieldErrors[oldkey] = '';
		}
		$scope.rebuildFieldErrors();
		return true;
	}

	$scope.swapFieldErrorsByPrefix = function(pfx1, pfx2) {
		if (pfx1 == pfx2) return false;
		var newKeysToVals = {};
		for (var key in window.lastParsedFieldErrors) {
			var newkey;
			if (key.substr(0, pfx1.length) == pfx1) {
				newkey = pfx2+key.substr(pfx1.length);
			} else if (key.substr(0, pfx2.length) == pfx2) {
				newkey = pfx1+key.substr(pfx2.length);
			}
			newKeysToVals[newkey] = window.lastParsedFieldErrors[key];
			if (typeof(window.lastParsedFieldErrors[newkey]) == 'undefined') {
				window.lastParsedFieldErrors[key] = '';
			}
		}
		for (var key in newKeysToVals) {
			window.lastParsedFieldErrors[key] = newKeysToVals[key];
		}
		$scope.rebuildFieldErrors();
		return true;
	}

	$scope.rebuildFieldErrors = function() {
		$scope.fieldErrorsHTML = {};
		$scope.fieldErrorsStyle = {};
		if ($scope.fieldErrorsPrefixTemplate != '') {
			var shortpfx = $scope.fieldErrorsPrefixTemplate;
			var idx = shortpfx.indexOf('{{i}}');
			if (idx >= 0) shortpfx = shortpfx.substr(0, idx);
			if (shortpfx != '') {
				for (var key in window.lastParsedFieldErrors) {
					if (key.substr(0, shortpfx.length) == shortpfx) {
						if (window.lastParsedFieldErrors[key] != '') {
							$scope.fieldErrorsHTML[key] = $scope.toTrustedHTML(window.lastParsedFieldErrors[key]);
							$scope.fieldErrorsStyle[key] = {display:'block'};
						} else {
							$scope.fieldErrorsHTML[key] = $scope.toTrustedHTML('');
							$scope.fieldErrorsStyle[key] = {display:'none'};
							// Delete empty messages after clearing them.
							delete window.lastParsedFieldErrors[key];
						}
					}
				}
			}
		}
	}

	// Watches.

	$scope.$watch(
		'pageIdx',
		function(newValues, oldValues, scope) {
			// Trigger a search to be executed very soon; don't change pages.
			$scope.triggerSearch($scope.userGestureSearchTimeoutMS, false);
			// Automatically attach any class-driven input field features/filtering when the page changes.
			setTimeout(
				function() {
					$scope.syncAutocompleteSingleRowSelectorValuesFromInputs();
					$scope.filterFieldsWithSpecialFeatures();
				},
				1
			);
		}
	);

	$scope.$watch(
		'sorts',
		function(newValues, oldValues, scope) {
			// Trigger a search to be executed very soon; don't change pages.
			$scope.triggerSearch($scope.userGestureSearchTimeoutMS, false);
			// Automatically attach any class-driven input field features/filtering when the page changes.
			setTimeout(
				function() {
					$scope.syncAutocompleteSingleRowSelectorValuesFromInputs();
					$scope.filterFieldsWithSpecialFeatures();
				},
				1
			);
		},
		true
	);

	$scope.$watch('rowsPerPage', function() {
		// Trigger a search to be executed very soon; go to first page.
		$scope.triggerSearch($scope.userGestureSearchTimeoutMS, true);
		// Automatically attach any class-driven input field features/filtering when the page changes.
		setTimeout(function() {
			$scope.syncAutocompleteSingleRowSelectorValuesFromInputs();
			$scope.filterFieldsWithSpecialFeatures();
		}, 1);
	});

	$scope.$watchCollection('rows', function(newCollection, oldCollection, scope) {
		// Automatically attach any class-driven input field features/filtering when the page changes.
		setTimeout(function() {
			$scope.syncAutocompleteSingleRowSelectorValuesFromInputs();
			$scope.filterFieldsWithSpecialFeatures();
		}, 1);
	}, true);

	// Functions to create and cache trusted HTML.

	$scope.toTrustedHTML = function(html) {
		return $scope.trustedHTMLCache[html] || ($scope.trustedHTMLCache[html] = $sce.trustAsHtml(html));
	}

	$scope.clearTrustedHTMLCache = function() {
		$scope.trustedHTMLCache = {};
	}

	// Utility functions.

	$scope._t = function(resourceId, defaultText) {
		if (typeof(_t) == 'function') {
			return _t(resourceId, defaultText);
		}
		return (typeof(defaultText) != 'undefined') ? defaultText : '';
	}

	// Reset everything.
	$scope.reset();
})
// Add a directive to render all of the pagination and search elements on any element
// which has the jax-grid-pager class.
.directive('jaxGridPager', function() {
	return {
		// restrict: 'A'=match attribute name, 'E'=match element name, 'C'=match class name.
		// Multiple letters can be combined to match more than one.
		restrict: 'C',

		compile:function(element, attrs) {
			var html =
'<div class="jax-grid-pager-page-number-rows-per-page">\
 '+__jax_grid_pager_t('jax.grid.pager.page_number_rows_per_page.page.label', 'Page')+' {{pageIdx+1}} '+__jax_grid_pager_t('jax.grid.pager.page_number_rows_per_page.num_pages.label', 'of')+' {{getNumPages()}}\
 <span class="jax-grid-pager-rows-per-page-label">\
 Show\
  <select ng-model="rowsPerPage" ng-change="pageIdx = 0">\
   <option value="10">10</option>\
   <option value="25">25</option>\
   <option value="50">50</option>\
   <option value="100">100</option>\
  </select>\
 '+__jax_grid_pager_t('jax.grid.pager.page_number_rows_per_page.rows_per_page.label', 'rows/page')+'</span>\
</div>\
\
';

			if (attrs.hasOwnProperty('hasSearchBox')) {
				html +=
'<div class="jax-grid-pager-search-box">\
 <span class="jax-grid-pager-search-text-input-label">'+__jax_grid_pager_t('jax.grid.pager.search_text_input.label', 'Search')+':</span>\
 <input type="text" class="jax-grid-pager-search-text-input" ng-model="searchText" ng-change="triggerSearch(typingSearchTimeoutMS, true)"/>\
';

				if (attrs.hasOwnProperty('hasSearchBy')) {
					html +=
' <span class="jax-grid-pager-search-by-label">'+__jax_grid_pager_t('jax.grid.pager.search_by_select.label', 'by')+':</span>\
 <select class="jax-grid-pager-search-by-select" ng-model="searchBy" ng-options="searchByOption.searchBy as searchByOption.description for searchByOption in searchByOptions" ng-change="triggerSearch(userGestureSearchTimeoutMS, true)">\
  <option value="">'+__jax_grid_pager_t('jax.grid.pager.search_by_select.any_option.label', '(any)')+'</option>\
 </select>\
\
';
				}
				html += '</div>\
';
			}

			html +=
'<div class="jax-grid-pager-pager">\
 <ul class="pagination pagination-sm">\
  <li><a href="" ng-show="pageIdx > 0" ng-click="setPageIdx(0)">&laquo;</a></li>\
  <li><a href="" ng-show="pageIdx >= 100000" ng-click="setPageIdx(pageIdx-1000)">&lt; 1000</a></li>\
  <li><a href="" ng-show="pageIdx >= 1000" ng-click="setPageIdx(pageIdx-100)">&lt; 100</a></li>\
  <li><a href="" ng-show="pageIdx >= 10" ng-click="setPageIdx(pageIdx-10)">&lt; 10</a></li>\
  <li ng-repeat="i in getPagerPageIndexes(pageIdx)" ng-class="((i == pageIdx) ? \'active\' : \'\')"><a href="" ng-click="setPageIdx(i)">{{i+1}}</a></li>\
  <li><a href="" ng-show="((getNumPages()-1)-pageIdx) >= 10" ng-click="setPageIdx(pageIdx+10)">10 &gt;</a></li>\
  <li><a href="" ng-show="((getNumPages()-1)-pageIdx) >= 1000" ng-click="setPageIdx(pageIdx+100)">100 &gt;</a></li>\
  <li><a href="" ng-show="((getNumPages()-1)-pageIdx) >= 100000" ng-click="setPageIdx(pageIdx+1000)">1000 &gt;</a></li>\
  <li><a href="" ng-show="pageIdx < (getNumPages()-1)" ng-click="setPageIdx(getNumPages()-1)">&raquo;</a></li>\
 </ul>\
</div>';
			element.html(html);
		}
	};
})
// Add the jax-grid-table class to any table elements within the container (should be just one).
// Add the jax-grid-container class to any table element's parent (should be the container).
.directive('table', function() {
	return {
		// restrict: 'A'=match attribute name, 'E'=match element name, 'C'=match class name.
		// Multiple letters can be combined to match more than one.
		restrict: 'E',

		compile:function(element, attrs) {
			element.addClass('jax-grid-table');
			element.parent().addClass('jax-grid-container');
		}
	};
});

