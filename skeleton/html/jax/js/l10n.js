// Translate a string into the client's language.
// resourceId is the resource id.
// Second parameter is optional default text to use if the resourceId is not found.
function _t(resourceId) {
	if (typeof(resourceStrings[resourceId]) != 'undefined') return resourceStrings[resourceId];
	if (arguments.length >= 2) return arguments[1];
	return '';
}
