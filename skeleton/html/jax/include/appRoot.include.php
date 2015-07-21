<?php
if ((!defined('APP_ROOT_DIR')) || (!defined('APP_ROOT_URL')) || (!defined('APP_ROOT_URI'))) {
	call_user_func(function() {

		// If there's a customized version of this file in the application's include
		// directory, run it instead of the default version.
		$customfn = dirname(dirname(dirname(__FILE__))).'/include/appRoot_custom.include.php';
		if (@file_exists($customfn)) {
			include($customfn);
			return;
		}

		// ----------------------------------
		// Calculate and define APP_ROOT_DIR.
		// ----------------------------------

		$dir = dirname(dirname(dirname(__FILE__)));
		// Look for a file named __jax_approot__ in an ascendent directory.
		// If found, use that directory as the application root.
		while (true) {
			$fn = $dir.'/__jax_approot__';
			if (file_exists($fn)) {
				define('APP_ROOT_DIR', $dir);
				break;
			}
			$parentdir = dirname($dir);
			if (($parentdir == '/') || ($parentdir == '') || ($parentdir == $dir)) {
				// We ran out of ascendent directories to try.
				// Use the directory two levels up from the include directory instead.
				define('APP_ROOT_DIR', dirname(dirname(dirname(__FILE__))));
				break;
			}
		}

		// ---------------------------------------------------
		// Calculate and define APP_ROOT_URL and APP_ROOT_URI.
		// ---------------------------------------------------

		// Find out how deep under the application root directory the current running script is.
		$rootdir = preg_replace('/\\/+/', '/', rtrim(str_replace('\\', '/', APP_ROOT_DIR), '/'));
		$dir = preg_replace('/\\/+/', '/', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])), '/\\'));
		$depth = 0;
		$depthOk = true;
		if ((strlen($dir) > strlen($rootdir)) &&
			(strncmp($rootdir, $dir, strlen($rootdir)) == 0)) {
			while (strlen($dir) > strlen($rootdir)) {
				if (($idx = strrpos($dir, '/')) === false) {
					$depth = 0;
					$depthOk = false;
					break;
				}
				$dir = substr($dir, 0, $idx);
				$depth++;
			}
		}

		if ($depthOk) {
			// Remove path components removed above, plus the name of the script.
			$rootURI = preg_replace('/\\/+/', '/', rtrim(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/\\'));
			if (($rootURI == '') || ($rootURI[0] != '/')) $rootURI = '/'.$rootURI;
			for ($i = -1; $i < $depth; $i++) {
				if (($idx = strrpos($rootURI, '/')) === false) {
					$rootURI = '/';
					break;
				}
				$rootURI = substr($rootURI, 0, $idx);
			}
		} else {
			$rootURI = '/';
		}
		if (($rootURI == '') || ($rootURI[strlen($rootURI)-1] != '/')) $rootURI .= '/';

		$host = (array_key_exists('HTTP_HOST', $_SERVER)) ?
			$_SERVER['HTTP_HOST'] :
			((array_key_exists('SERVER_NAME', $_SERVER)) ? $_SERVER['SERVER_NAME'] : '');
		if ((array_key_exists('SERVER_PORT', $_SERVER) && ($_SERVER['SERVER_PORT'] == '443')) ||
			(array_key_exists('HTTPS', $_SERVER) && (strtolower($_SERVER['HTTPS']) == 'on'))) {
			$protocol = 'https';
		} else {
			$protocol = 'http';
		}

		define('APP_ROOT_URL', $protocol . '://' . $host.$rootURI);
		define('APP_ROOT_URI', $rootURI);
	});
}
