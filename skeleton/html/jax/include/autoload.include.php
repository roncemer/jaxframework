<?php
// Copyright (c) 2012-2013 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

// If this is set externally prior to including this file, it will be left alone.
// This is just an array of strings, with each string being a path to a directory
// to recursively search for classes.  The directories will be searched in the
// order in which they appear in this array.  When a class or interface is found,
// the search terminates.
if ((!defined('APP_ROOT_DIR')) || (!defined('APP_ROOT_URL')) || (!defined('APP_ROOT_URI'))) include dirname(__FILE__).'/appRoot.include.php';
if (!isset($__CLASS_AUTO_LOAD_CLASS_PATHS)) {
	$__CLASS_AUTO_LOAD_CLASS_PATHS = array(APP_ROOT_DIR);
}
$__CLASS_AUTO_LOAD_PATH_CACHE_TIMEOUT = 60;

if (!function_exists('__jax__loadClass')) {

function __jax__loadClass($class_name) {
	global $__CLASS_AUTO_LOAD_CLASS_PATHS, $__CLASS_AUTO_LOAD_PATH_CACHE_TIMEOUT;

	$cacheKey = 'classAutoload:'.sha1($_SERVER['DOCUMENT_ROOT']).':'.$class_name;
	$path = @apc_fetch($cacheKey, $success);
	if (!$success) {
		foreach ($__CLASS_AUTO_LOAD_CLASS_PATHS as $dir) {
			$path = __jax__classAutoloadFindClassFile($class_name, $dir);
			if ($path !== false) {
				if (function_exists('apc_add')) {
					@apc_add($cacheKey, $path, $__CLASS_AUTO_LOAD_PATH_CACHE_TIMEOUT);
				} else {
					@apc_store($cacheKey, $path, $__CLASS_AUTO_LOAD_PATH_CACHE_TIMEOUT);
				}
				break;
			}
		}
	}
	if ($path !== false) {
		include $path;
	}
}

function __jax__classAutoloadFindClassFile($class_name, $dir) {
	$cnlen = strlen($class_name);
	$anyProcessed = false;
	if (($dp = @opendir($dir)) !== false) {
		while (($fn = readdir($dp)) !== false) {
			if (($fn == '.') || ($fn == '..')) continue;
			$path = $dir.'/'.$fn;
			if (is_dir($path)) {
				if (($foundfn = __jax__classAutoloadFindClassFile($class_name, $path)) !== false) {
					return $foundfn;
				}
			}
			if (is_file($path)) {
				$fnlen = strlen($fn);
				if (($fnlen > $cnlen) &&
					(strncmp($fn, $class_name, $cnlen) == 0) &&
					(((($cnlen+10) == $fnlen) &&
					  (substr_compare($fn, '.class.php', $cnlen) == 0)) ||
					 ((($cnlen+14) == $fnlen) &&
					  (substr_compare($fn, '.interface.php', $cnlen) == 0)))) {
					return $path;
				}
			}
		}
		closedir($dp);
	}
	return false;
}

spl_autoload_register('__jax__loadClass');

}	// if (!function_exists('__jax__loadClass'))
