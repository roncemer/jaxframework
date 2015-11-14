// DO NOT EDIT THIS FILE.
// This file is part of the Jax Framework.
// If you edit this file, your changes will be lost when framework updates are applied.

// Copyright (c) 2010-2015 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

var __pageHelpTopicsSoFar__ = [];

function installPageHelp(pageURL) {
	// havePageHelp is set in header.include.php.  It will be true if there is an HTML help
	// file for this page, or false if not.  If this JS file is used in a page which does
	// not include header.include.php, havePageHelp will not be set, so we do a fail-safe
	// in that case and act as if we have page help.  However, keep in mind that this may
	// result in JS warnings because the AJAX request will fail.
	if ((typeof(havePageHelp) == 'undefined') || (!havePageHelp)) return;

	var url = (pageURL == undefined) ? window.location.href : pageURL;

	// Strip query string, anchor, trailing slashes, .php extension, and any remaining trailing slashes.
	url = url.replace(/[?#].*$/, '').replace(/\/+$/, '').replace(/\.php$/, '').replace(/\/+$/, '');

	helpURIBase = getHelpURIBase(pageURL);

	url += '_help.html';

	$.ajax({
		type:'GET',
		url:url,
		async:true,
		cache:false,
		global:false,
		processData:false,
		success:function(data) {
			// When we convert the HTML document into a jQuery object, what we get back is
			// a jQuery object containing the direct children of the <head> and <body> elements
			// in the HTML document.  As a result, we have to append it that list to a temporary
			// <div> container in order to be able to find both top-level <a> elements and <a>
			// elements which are deeper in the DOM tree.  JQuery's find() method would not
			// find top-level elements because they're listed in the source array.  JQuery's
			// filter() method would find ONLY top-level elements which are listed in the
			// source array.  By appending the elements in the source array to a single
			// container element, we can use find() to find all <a> elements in the document
			// which match our search criteria (must have a name attribute which is not empty).
			var cont = $('<div></div>');
			$(data).appendTo(cont);
			$.each(cont.find('a[name][name!=""]'), function(index, value) {
				var id = $(value).attr('name');
				if ((id != '') && (__pageHelpTopicsSoFar__.indexOf(id) < 0)) {
					// Attach the help topic to any element which has an id attribute or a data-help-topic
					// attribute which matches the name of the name attribute in the <a> element.
					var elems = $.unique($('#'+id+':not([data-has-help]):not([data-help-topic]), [data-help-topic=\''+id+'\']:not([data-has-help])'));
					if (elems.length > 0) {
						__pageHelpTopicsSoFar__[__pageHelpTopicsSoFar__.length] = id;
						installComponentHelp(elems, helpURIBase+'#'+id);
					}
				}
			});
			var re = /\sname="[^"]*"/g;
			while (m = re.exec(data)) {
				var id = m.toString().replace(/^\s*name="/, '').replace(/"$/, '');
				var helpURI = helpURIBase+'#'+id;
			}
		}
	});
} // installPageHelp()

function installComponentHelp(selectorOrCollection, helpURI) {
	if ((typeof(havePageHelp) == 'undefined') || (!havePageHelp)) return;

	var html = '<a href="#" onclick="showHelp(\''+helpURI+'\'); return false;"><i class="glyphicon glyphicon-info-sign"/></a>';
	$(html).insertAfter($(selectorOrCollection));
	$(selectorOrCollection).attr('data-has-help', '');
} // installComponentHelp()

function showHelp(helpURI) {
	var win = $(window);
	var w = Math.round(win.width()*.667);
	var h = Math.round(win.height()*.8);
	window.open(helpURI, 'helpWindow', 'fullscreen=no,width='+w+',height='+h, false);
} // showHelp()

function getHelpURIBase(pageURL) {
	var url = (pageURL == undefined) ? window.location.href : pageURL;

	var baseHref = $('base:first').attr('href').replace(/\/+$/, '');
	var helpURIBase;
	if ((typeof(baseHref) != 'undefined') && (baseHref != '') && (url.substr(0, baseHref.length) == baseHref)) {
		helpURIBase = baseHref.replace(/\/+$/, '')+'/help?path='+encodeURIComponent(url.substr(baseHref.length));
	} else {
		helpURIBase = url.replace(/\/$/, '');
		var pieces = helpURIBase.split('/');
		var lastPiece = pieces.pop();
		pieces.push('help');
		helpURIBase = pieces.join('/');
		if (typeof(lastPiece) != 'undefined') helpURIBase += '?path=%2F'+lastPiece;
	}
	return helpURIBase;
} // getHelpURIBase()
