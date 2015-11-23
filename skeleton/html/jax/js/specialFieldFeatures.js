var __specialFieldFeatures_lastFocusField__ = null;

// Attach special field features to input elements based on their CSS classes.
// Date pickers get attached to any input elements with class "date" or "datetime" which
// don't already have date pickers attached and are not disabled or readonly.
// Date input filtering gets attached to any input elements with class "date" or "datetime"
// which don't already have date filtering attached.
// Numeric filtering gets attached to any input elements with class "numeric-scale0" through
// "numeric-scale10" which don't already have numeric filtering attached.
// If any arguments are passed to this function, each argument must be a jQuery collection
// containing parents whose descendents will be filtered, or a DOM element whose descendents
// will be filtered.
// If no arguments are passed to this function, all applicable elements in the current
// document will be filtered.
function attachSpecialFieldFeatures() {
	if (typeof preAttachSpecialFieldFeatures == 'function') {
		preAttachSpecialFieldFeatures();
	}

	var selector, elems;

	var roots = (arguments.length > 0) ? arguments : [null];

	for (var ri = 0; ri < roots.length; ri++) {
		var root = (roots[ri] != null) ? $(roots[ri]) : null;

		// Attach a date picker to all non-disabled, non-readonly date input fields.
		selector = 'input.date:not(.hasDatepicker):not([disabled]):not([readonly])';
		elems = (root != null) ? root.find(selector) : $(selector);
		var arr = {
			dateFormat:'yy-mm-dd',
			constrainInput:false
		};
		if (typeof($.mobile) !== 'undefined') {
			arr.showOn = 'focus';
		} else {
			arr.showOn = 'button';
			arr.buttonImage = 'jax/images/calendar_19x16.png';
			arr.buttonImageOnly = true;
		}
		elems.datepicker(arr);

		// Attach a date picker to all non-disabled datetime input fields,
		// with time set to 00:00:00.
		selector = 'input.datetime:not(.hasDatepicker):not([disabled]):not([readonly])';
		elems = (root != null) ? root.find(selector) : $(selector);
		elems.datepicker({
			dateFormat:'yy-mm-dd 00:00:00',
			showOn:'button',
			buttonImage:'jax/images/calendar_19x16.png',
			buttonImageOnly:true,
			constrainInput:false
		});

		// When a date input field's value changes, filter and format the date and put it back.
		selector = 'input.date:not(.hasDateFilter)';
		elems = (root != null) ? root.find(selector) : $(selector);
		elems.change(function() {
			filterDateInput($(this));
		});
		elems.addClass('hasDateFilter');

		// When a datetime input field's value changes, filter and format the datetime and
		// put it back.
		selector = 'input.datetime:not(.hasDatetimeFilter)';
		elems = (root != null) ? root.find(selector) : $(selector);
		elems.change(function() {
			filterDatetimeInput($(this));
		});
		elems.addClass('hasDatetimeFilter');

		// When the value of a numeric input field with scale 0 to 10 changes,
		// filter and format the value and put it back.
		for (var scale = 0; scale <= 10; scale++) {
			selector = 'input.numeric-scale'+scale+':not(.hasNumericFilter)';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.change(function() {
				$(this).each(function(idx, e) {
					// Extract the numeric scale from the input element's numeric-scale* class,
					// and then use that to filter the value to the correct scale.
					var elem = $(e);
					$(elem.attr('class').split(' ')).each(function() { 
						if ((this !== '') && (this.match(/^numeric-scale[0-9]+$/) != null)) {
							var scale = parseInt(this.replace(/^numeric-scale/, ''));
							if ((!isNaN(scale)) && (scale >= 0)) {
								filterNumericInput(elem, scale);
							}
						}
					});
				});
			});
			elems.addClass('hasNumericFilter');
		}

		// When a text or password input element receives the focus, we want to select
		// its entire text content.  There are some competing events in the mouse event
		// handlers which seem to de-select the text when an input element is clicked,
		// so we use a global variable to hold the instance and use setTimeout() with a
		// callback to make the text selection occur after all other events have been
		// handled.
		selector = "input[type='text'], input[type='password']";
		elems = (root != null) ? root.find(selector) : $(selector);
		elems.focus(function() {
			if (__specialFieldFeatures_lastFocusField__ !== this) {
				__specialFieldFeatures_lastFocusField__ = this;
				setTimeout('__specialFieldFeatures_lastFocusField__.select();', 1);
			}
		});
	}	// for (var ri = 0; ri < roots.length; ri++)

	if (typeof postAttachSpecialFieldFeatures == 'function') {
		postAttachSpecialFieldFeatures();
	}
}

// Show or hide special input field features, such as trigger buttons for pop-up
// searches and date pickers, based on whether the form as a whole is editable,
// and whether each individual input field is readonly or disabled.
// If hideAll is true, it is assumed that the entire form is readonly, and all
// applicable trigger buttons (and possibly other features) will be hidden.
// If hideAll is omitted, it defaults to false.
// If two or more arguments are passed to this function, each argument after the first (hideAll)
// argument must be a jQuery collection containing parents whose descendents will be affected, or
// a DOM element whose descendents will be affected.
// If only the first (hideAll) argument is passed to this function, all applicable elements in the
// current document will be affected.
function autoShowOrHideSpecialFieldFeatures(hideAll) {
	if (typeof(hideAll) == 'undefined') hideAll = false;

	var selector, elems;

	var roots;
	if (arguments.length > 1) {
		roots = Array.prototype.slice.call(arguments);
		roots = roots.slice(1);
	} else {
		roots = [null];
	}

	for (var ri = 0; ri < roots.length; ri++) {
		var root = (roots[ri] != null) ? $(roots[ri]) : null;

		if (hideAll) {
			// Hide date picker and pop-up search icons.
			selector = '.popupSearchLink, .ui-datepicker-trigger';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.hide();

			// Disable autocompletes.
			selector = '';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems = (root != null) ?
				root.find('input.ui-autocomplete-input[disabled], input.ui-autocomplete-input[readonly]') :
				$('input.ui-autocomplete-input[disabled], input.ui-autocomplete-input[readonly]');
			elems.autocomplete('option', 'disabled', true);
		} else {	// if (hideAll)
			// Show pop-up search icons for inputs which have them, when they are
			// not readonly or disabled.
			selector = 'input:not([disabled]):not([readonly])';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.nextAll('.popupSearchLink').show();

			// Hide pop-up search icons for inputs which have them, when they are
			// readonly or disabled.
			selector = 'input[disabled], input[readonly]';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.nextAll('.popupSearchLink').hide();

			// Show date picker icons for inputs which have date pickers
			// and are not readonly or disabled.
			selector = 'input.date.hasDatepicker:not([disabled]):not([readonly]), input.datetime.hasDatepicker:not([disabled]):not([readonly])';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.datepicker(true);

			// Hide date picker icons for elements which have date pickers
			// but are readonly or disabled.
			selector = 'input.date.hasDatepicker[readonly], input.datetime.hasDatepicker[readonly], input.date.hasDatepicker[disabled], input.datetime.hasDatepicker[disabled]';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.datepicker(false);

			// Enable autocomplete for input elements which have it, when they
			// are not readonly or disabled.
			selector = 'input.ui-autocomplete-input:not([disabled]):not([readonly])';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.autocomplete('option', 'disabled', false);

			// Disable autocomplete for input elements which have it, when they
			// are readonly or disabled.
			selector = 'input.ui-autocomplete-input[disabled], input.ui-autocomplete-input[readonly]';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.autocomplete('option', 'disabled', true);

			// For all select2 containers, enable or disable the corresponding select2 based on
			// whether its original input component is readonly or disabled.
			selector = '.select2-container';
			elems = (root != null) ? root.find(selector) : $(selector);
			elems.each(function(index) {
				var elem = $(this).prev('input, textarea, select');
				if (elem.length == 1) {
					if ((typeof(elem.attr('readonly')) !== 'undefined') ||
						(typeof(elem.attr('disabled')) !== 'undefined')) {
						elem.select2('disable');
					} else {
						elem.select2('enable');
					}
				}
			});
		}	// if (hideAll) ... else
	}	// for (var ri = 0; ri < roots.length; ri++)
}

// Filter every field which has a special-feature filter attached to it.
// If any arguments are passed to this function, each argument must be a jQuery collection
// containing parents whose descendents will be filtered, or a DOM element whose descendents
// will be filtered.
// If no arguments are passed to this function, all applicable elements in the current
// document will be filtered.
function filterFieldsWithSpecialFeatures() {
	var selector, elems;

	var roots = (arguments.length > 0) ? arguments : [null];

	for (var ri = 0; ri < roots.length; ri++) {
		var root = (roots[ri] != null) ? $(roots[ri]) : null;

		selector = 'input.date.hasDateFilter';
		elems = (root != null) ? root.find(selector) : $(selector);
		filterDateInput(elems);

		selector = 'input.datetime.hasDatetimeFilter';
		elems = (root != null) ? root.find(selector) : $(selector);
		filterDatetimeInput(elems);

		for (var scale = 0; scale <= 10; scale++) {
			selector = 'input.numeric-scale'+scale+'.hasNumericFilter';
			elems = (root != null) ? root.find(selector) : $(selector);
			filterNumericInput(elems, scale);
		}
	}	// for (var ri = 0; ri < roots.length; ri++)
}

// Filter a date input field.
// jqFieldSet is a jQuery instance containing one or more date fields to filter.
function filterDateInput(jqFieldSet) {
	$.each(jqFieldSet, function(index) {
		var elem = $(this);
		var dt = Date.parse(elem.val());
		elem.val((dt != null) ? dt.toString('yyyy-MM-dd') : '');
	});
}

// Filter a datetime input field.
// jqFieldSet is a jQuery instance containing one or more date fields to filter.
function filterDatetimeInput(jqFieldSet) {
	$.each(jqFieldSet, function(index) {
		var elem = $(this);
		var dt = Date.parse(elem.val());
		elem.val((dt != null) ? dt.toString('yyyy-MM-dd HH:mm:ss') : '');
	});
}

// Filter a numeric input field.
// jqFieldSet is a jQuery instance containing one or more date fields to filter.
// scale is the number of fractional digits.
function filterNumericInput(jqFieldSet, scale) {
	$.each(jqFieldSet, function(index) {
		var elem = $(this);
		var val = Number($.trim(elem.val()));
		if (isNaN(val)) val = 0.0;
		val = val.toFixed(scale);
		elem.val(val);
	});
}

// Hook a single row selector with autocomplete for a related table to an input element.
// The input element would typically be a text input.
// Options: An associative array, with the following keys:
//     inputElement: A CSS selector, a single DOM element, or a jQuery collection containing a
//         single DOM element for the input element which is to have the autocomplete row selector
//         hooked to it.
//         Required.
//     autocompleteCommand: The autocomplete command to use for searching rows on the server.
//         Required.
//     idColumn: The name of the primary key/unique identifying column for the table being searched.
//         Optional.  Defaults to 'id'.
//     idIsString: true if the primary key/unique identifying column is a string (character) column,
//         false if it is an integer column.
//         Optional.  Defaults to false.
//     minimumInputLength: The minimum number of characters the user must enter in order to be able
//         to search rows in the table.
//         Optional.  Defaults to 1.
//     allowClear: true to display a clear button when the select box has a selection. The button,
//         when clicked, resets the value of the select box back to the placeholder, thus this
//         option is only available when selectPlaceholder is specified as a non-empty placeholder
//         (or left at its default, non-empty placeholder string).
//         Optional.  Defaults to false.
//     selectPlaceholder: The placeholder text to be put into the select box if the id is zero
//         (integer id) or empty (string id).
//         Optional.  Defaults to 'Select an item'.
//     notFoundMessage: The text to be put into the select box if the currently selected id is
//         invalid, but not zero (integer id) or empty (string id).
//         Optional.  Defaults to '*** INVALID ***'.
//     maxRowsPerPage: The maximum number of rows to retrieve at a time from the server (rows per
//         page).
//         Optional.  Defaults to 100.
//     rowFetcher: The RowFetcher instance to use for fetching rows from the server.
//         Optional.  If not specified, this function will look for a global variable named
//         rowFetcher and, if set, will use that; otherwise, a new instance of RowFetcher will be
//         created automatically.
//     rowFetcherOptionalParameters: Optional associative array of additional parameters to be
//         added to the query string by passing in as the fourth argument to the
//         RowFetcher::get*forId*() function.
function hookAutocompleteSingleRowSelectorToInput(options) {
	// Required parameters.
	if (typeof(options.inputElement) == 'undefined') return;
	if (typeof(options.autocompleteCommand) == 'undefined') return;
	var autocompleteCommand = options.autocompleteCommand;

	// Optional parameters (defaulted if not specfied).
	var idColumn = (typeof(options.idColumn) != 'undefined') ? options.idColumn : 'id';
	var idIsString = (typeof(options.idIsString) != 'undefined') ? options.idIsString : false;
	var minimumInputLength = (typeof(options.minimumInputLength) != 'undefined') ? options.minimumInputLength : 1;
	var allowClear = (typeof(options.allowClear) != 'undefined') ? options.allowClear : false;
	var selectPlaceholder = (typeof(options.selectPlaceholder) != 'undefined') ? options.selectPlaceholder : 'Select an item';
	var notFoundMessage = (typeof(options.notFoundMessage) != 'undefined') ? options.notFoundMessage : '*** INVALID ***';
	var maxRowsPerPage = (typeof(options.maxRowsPerPage) != 'undefined') ? options.maxRowsPerPage : 100;
	var myRowFetcher;
	if (typeof(options.rowFetcher) != 'undefined') {
		myRowFetcher = options.rowFetcher;
	} else if (typeof(rowFetcher) != 'undefined') {
		myRowFetcher = rowFetcher;
	} else {
		myRowFetcher = new RowFetcher();
	}

	var rowFetcherOptionalParameters = (typeof(options.rowFetcherOptionalParameters) != 'undefined') ?
		options.rowFetcherOptionalParameters : null;

	if ((typeof(__jaxUseCombobox__) != 'undefined') && __jaxUseCombobox__) {

		// New combobox implementation.

		var origInput = $(options.inputElement);
		if (!origInput.parent().hasClass('combobox-wrapper')) origInput.wrap('<div class="combobox-wrapper"></div>');

		var url = getBaseURL()+'?command='+encodeURIComponent(autocompleteCommand);
		if (typeof fixupAJAXURL == 'function') {
			url = fixupAJAXURL(url);
		}
		if (typeof(rowFetcherOptionalParameters) == 'object') {
			for (var k in rowFetcherOptionalParameters) {
				url += '&'+encodeURIComponent(k)+'='+encodeURIComponent(rowFetcherOptionalParameters[k]);
			}
		}

		var comboboxSeq = undefinedToEmpty(origInput.attr('data-combobox-seq'));
		if (comboboxSeq == '') {
			if (window.__nextComboboxSeq__ === undefined) window.__nextComboboxSeq__ = 0;
			comboboxSeq = ++window.__nextComboboxSeq__;
			origInput.attr('data-combobox-seq', comboboxSeq);
		}

		// Restore the original CSS settings for the original input, if we saved them before.
		if (origInput.attr('data-combobox-orig-style') !== undefined) {
			origInput.attr('style', undefinedToEmpty(origInput.attr('data-combobox-orig-style')));
		}

		// Remove any old components which were inserted after the original input.
		origInput.nextAll("input.combobox-search[data-combobox-seq='"+comboboxSeq+"']").remove();
		origInput.nextAll("a.combobox-clear[data-combobox-seq='"+comboboxSeq+"']").remove();
		origInput.nextAll("div.combobox-chevron-down[data-combobox-seq='"+comboboxSeq+"']").remove();

		// Create a search input after the original input.
		var search = origInput.clone();
		search.removeAttr('data-combobox-orig-style');
		search.attr('id', '__combobox-search__'+new Date().getTime()+'-'+comboboxSeq);
		search.attr('data-combobox-seq', comboboxSeq);
		search.removeAttr('name');
		search.addClass('combobox-search');
		search.attr('tabindex', '-1');
		search.insertAfter(origInput);

		// Save the original CSS settings for the original input, if we haven't already.
		if (origInput.attr('data-combobox-orig-style') === undefined) {
			origInput.attr('data-combobox-orig-style', undefinedToEmpty(origInput.attr('style')));
		}

		// Make the original input of zero size so we won't see it, but it can still get the focus.
		// If we hide it or put it inside an invisible container, it can't receive the focus programmatically.
		// We hook its focus event below, and transfer the focus to the search component.
		origInput.css('width', '0px');
		origInput.css('height', '0px');
		origInput.css('border', 'none');
		origInput.css('margin', '0px');
		origInput.css('padding', '0px');

		var clearLink = null;
		if (allowClear) {
			clearLink = $('<a class="btn-default combobox-clear" href="#" onclick="return false;" tabindex="-1"><i class="glyphicon glyphicon-remove-circle"></i></a>');
			clearLink.attr('data-combobox-seq', comboboxSeq);
			clearLink.insertAfter(search);
			clearLink.click(function(evt) {
				if ((!origInput.is('[readonly]')) && (!origInput.is('[disabled]'))) {
					origInput.val(idIsString ? '' : '0').trigger('change');
				}
				if (!origInput.is('[disabled]')) origInput.focus();
			});
		}

		var chevronDown = $('<div class="combobox-chevron-down"><i class="glyphicon glyphicon-chevron-down" tabindex="-1"></i></div>');
		chevronDown.insertAfter(allowClear ? clearLink : search);
		chevronDown.attr('data-combobox-seq', comboboxSeq);

		function undefinedToEmpty(val) {
			if ((val === undefined) || (val === null)) return '';
			return val;
		}

		function isClearLinkVisible() {
			var display = clearLink.css('display');
			return ((display == undefined) || (display != 'none'));
		}

		function getIdValue() {
			var idval = origInput.val();
			if (!idIsString) {
				var idvalint = parseInt($.trim(idval)) || 0;
				// Fix incorrectly formatted integer values.
				var idvalstr = ''+idvalint;
				if (idvalstr != idval) origInput.val(idvalstr).trigger('change');
				if (idvalint == 0) {
					if (allowClear) {
						if (isClearLinkVisible()) clearLink.hide();
					}
					return null;
				}
			} else {
				if (idval == '') {
					if (allowClear) {
						if (isClearLinkVisible()) clearLink.hide();
					}
					return null;
				}
			}
			if (allowClear && (!origInput.is('[readonly]')) && (!origInput.is('[disabled]'))) {
				if (!isClearLinkVisible()) clearLink.show();
			} else {
				if (isClearLinkVisible()) clearLink.hide();
			}
			return idval;
		}

		function setLabel(label) {
       		search.val(label).trigger('change');
			search.attr('data-current-label', label);
			search.removeAttr('data-manually-entered');
		}

		function origInputChanged() {
			var idval = getIdValue();
			if (idval === null) {
        		setLabel(selectPlaceholder);
			} else {
				var row = idIsString ?
					myRowFetcher.getRowForIdString(
						autocompleteCommand,
						idColumn,
						idval
					) :
					myRowFetcher.getRowForId(
						autocompleteCommand,
						idColumn,
						idval
					);
				if (row !== null) {
        			setLabel(row.label);
				} else {
        			setLabel(notFoundMessage);
				}
			}
		}

		function trackReadonlyDisabledState() {
			var oreadonly = origInput.is('[readonly]');
			var odisabled = origInput.is('[disabled]');
			var sreadonly = search.is('[readonly]');
			var sdisabled = search.is('[disabled]');

			if (sreadonly != oreadonly) {
				if (oreadonly) search.attr('readonly', true); else search.removeAttr('readonly');
			}
			if (sdisabled != odisabled) {
				if (odisabled) search.attr('disabled', true); else search.removeAttr('disabled');
			}
			getIdValue();
		}

		try {
			var mutationObserver = new MutationObserver(function(mutations) {
				if (!$.contains(document, search[0])) {
					mutationObserver.disconnect();
					return;
				}
				trackReadonlyDisabledState();
			});
			mutationObserver.observe(origInput[0], { attributes: true, childList: false, characterData: false });
		} catch (ex) {}

		origInput.focus(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			search.addClass('active');
			trackReadonlyDisabledState();
		});
		origInput.blur(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			if (typeof(origInput.attr('data-transferring-focus')) != 'undefined') {
				origInput.removeAttr('data-transferring-focus');
			}
			search.removeClass('active');
			trackReadonlyDisabledState();
		});
		origInput.keypress(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			if ((!origInput.is('[readonly]')) && (!origInput.is('[disabled]'))) {
				origInput.attr('data-transferring-focus', true);
				search.val(String.fromCharCode(evt.which)).trigger('change');
				search.attr('data-manually-entered', true);
				search.focus();
			}
			evt.preventDefault();
			trackReadonlyDisabledState();
			return false;
		});
		origInput.change(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			origInputChanged();
			trackReadonlyDisabledState();
		});

		search.focus(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			search.removeClass('active');
			trackReadonlyDisabledState();
		});
		search.blur(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			var oldval = search.val();
			var isManuallyEntered = (typeof(search.attr('data-manually-entered')) != 'undefined');
			if (isManuallyEntered) search.removeAttr('data-manually-entered');
			if ((oldval != '') && isManuallyEntered) {
				// If we lose focus on the search input when its value is not empty, plug that value into the original
				// input and search for its label.
				origInput.val(search.val()).trigger('change');
			} else {
				// If we lose focus on the search input when its value is empty, restore the current label as its value.
				search.val((typeof(search.attr('data-current-label')) != 'undefined') ? search.attr('data-current-label') : '').trigger('change');
			}
			trackReadonlyDisabledState();
		});
		search.keypress(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			// As the value in the search box changes, show or hide the clear button.
			setTimeout(getIdValue, 1);
			search.attr('data-manually-entered', true);
			trackReadonlyDisabledState();
		});
		search.mousedown(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			if (!origInput.is('[disabled]')) origInput.focus();
			evt.stopPropagation();
			return false;
		});

		chevronDown.mousedown(function(evt) {
			if (!$.contains(document, search[0])) {
				origInput.off(this);
				return;
			}
			if (!origInput.is('[disabled]')) origInput.focus();
			evt.stopPropagation();
			return false;
		});

		search.autocomplete({
			minLength:minimumInputLength,
    		source:url,
			autoFocus:false,
    		select:function(event, ui) {
				setLabel(ui.item.label);
        		origInput.val(ui.item.value).trigger('change');
				getIdValue();
				if (!origInput.is('[disabled]')) origInput.focus();
				trackReadonlyDisabledState();
				return false;
    		},
			focus:function(event, ui) {
				return false; 
			}
		});

		// Set up the initial state for the value in the original input.
		origInputChanged();

	} else { // if ((typeof(__jaxUseCombobox__) != 'undefined') && __jaxUseCombobox__)

		// Old select2-based implementation.

		var elem = $(options.inputElement);
		var placeholder = {value: '', label:selectPlaceholder};

		elem.select2('destroy');
		var params = {
			allowClear: allowClear,
			initSelection: function(elem, callback) {
				var id = elem.val();
				if (!idIsString) id = parseInt($.trim(id)) || 0;
				if ((id == '') || ((!idIsString) && (id == 0))) {
					callback(placeholder);
					return;
				}
				var row = idIsString ?
					myRowFetcher.getRowForIdString(
						autocompleteCommand,
						idColumn,
						id,
						rowFetcherOptionalParameters
					) :
						myRowFetcher.getRowForId(
						autocompleteCommand,
						idColumn,
						id,
						rowFetcherOptionalParameters
					);
				var data = (row !== null) ? row : {value: id, label: ''+id+': '+notFoundMessage};
				callback(data);
				return;
			},
			minimumInputLength: minimumInputLength,
			query: function(options) {
				var results = myRowFetcher.getRowArrayForIdString(
					autocompleteCommand,
					'term',
					options.term,
					$.extend({}, rowFetcherOptionalParameters, {offset:(options.page-1)*maxRowsPerPage, limit:maxRowsPerPage})
				);
				options.callback({results:results, more: (results.length >= maxRowsPerPage) });
			},
			formatResult: function(item) {
				return item.label;
			},
			formatSelection: function(item) {
				return item.label;
			},
			id: function(item) {
				return item.value;
			}
		};
		if (selectPlaceholder != '') params.placeholder = placeholder.label;
		// Create the select2 component; hook it to the input element.
		elem.select2(params);
		// Remove the bogus 'width:0px' CSS style which seems to be automatically added
		// by select2 to its own containers.
		elem.siblings('.select2-container').css('width', '');
// select2 3.4.8 handles this automatically at creation time, and has support for both
// readonly and disabled attributes on the original input element.
//		// Disable the select2 if the input element is readonly or disabled.
//		if ((typeof(elem.attr('readonly')) !== 'undefined') ||
//			(typeof(elem.attr('disabled')) !== 'undefined')) {
//			elem.select2('disable');
//		}

	} // if ((typeof(__jaxUseCombobox__) != 'undefined') && __jaxUseCombobox__) ... else
}

// Hook an autocomplete drop-down list for a related table to an input element.
// The input element would typically be a text input.
// Options: An associative array, with the following keys:
//     inputElement: A CSS selector, a single DOM element, or a jQuery collection containing a
//         single DOM element for the input element which is to have the autocomplete row selector
//         hooked to it.
//         Required.
//     autocompleteCommand: The autocomplete command to use for searching rows on the server.
//         Required.
//     minimumInputLength: The minimum number of characters the user must enter in order to be able
//         to search rows in the table.
//         Optional.  Defaults to 1.
function hookAutocompleteToInput(options) {
	// Required parameters.
	if (typeof(options.inputElement) == 'undefined') return;
	if (typeof(options.autocompleteCommand) == 'undefined') return;
	var elem = $(options.inputElement);
	var autocompleteCommand = options.autocompleteCommand;
	var minimumInputLength = (typeof(options.minimumInputLength) != 'undefined') ? options.minimumInputLength : 1;

	var url = getBaseURL()+'?command='+encodeURIComponent(autocompleteCommand);
	if (typeof fixupAJAXURL == 'function') {
		url = fixupAJAXURL(url);
	}
	elem.autocomplete({
		minLength:minimumInputLength,
		source:url,
		select:function(event, ui) {
			window.__lastAutocompleteField__ = $(event.target);
			setTimeout('window.__lastAutocompleteField__.change().focus().select();', 1);
		},
		focus:function(event, ui) {
			$(this).val(ui.item.value).trigger('change');
			return false; 
		}
	});
}
