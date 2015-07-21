<?php
include dirname(__FILE__).'/jax/classes/FrontController.class.php';

$__frontController__ = new FrontController();
$__bareRequestURI__ = $__frontController__->getBareRequestURI();

// If we have a direct request for index.php or index, redirect to the parent directory,
// but keep the query string if it exists.
if (preg_match('/((\\/index\\.php$)|(\\/index$))/', $__bareRequestURI__)) {
	header('HTTP/1.1 301 Found');
	header('Location: '.preg_replace('/((\\/index\\.php$)|(\\/index$))/', '/', $__bareRequestURI__).((isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] != '')) ? ('?'.$_SERVER['QUERY_STRING']) : ''));
	exit();
}

// See if it matches any PHP scripts, either directly, or by adding a .php extension at
// any level along the path.
// If we find a PHP page, override the values in $_SERVER to make it look as if the server
// executed the PHP page directly, then include the PHP page and exit.
if (($__res_9q929123342__ = $__frontController__->findPHPScript($__bareRequestURI__)) !== false) {
	$__frontController__->overrideServerVars(
		$__res_9q929123342__->fn,
		$__res_9q929123342__->uri,
		$__res_9q929123342__->pathinfo,
		isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''
	);
	unset($__frontController__);
	unset($__bareRequestURI__);
	if (realpath($__res_9q929123342__->fn) != realpath(__FILE__)) {
		@chdir(dirname($__res_9q929123342__->fn));
		include $__res_9q929123342__->fn;
	}

	exit();
}
unset($__res_9q929123342__);

// No PHP page found for handling the URI.
// If there's a custom front controller plugin include file, execute it now.
if (@file_exists(dirname(__FILE__).'/frontcontroller_custom.include.php')) {
	@include dirname(__FILE__).'/frontcontroller_custom.include.php';
}

// Issue a 404 Not Found error and exit.
include dirname(__FILE__).'/jax/include/autoload.include.php';
include dirname(__FILE__).'/error_404.php';
