<?php
if ((!defined('APP_ROOT_DIR')) || (!defined('APP_ROOT_URL')) || (!defined('APP_ROOT_URI'))) include dirname(dirname(__FILE__)).'/jax/include/appRoot.include.php';
if (!function_exists('loadResourceBundle')) include dirname(dirname(__FILE__)).'/jax/include/l10n.include.php';

// Base URL for accessing CSS files, images, etc.
if (!isset($HEAD_BASE_URL)) $HEAD_BASE_URL = APP_ROOT_URL;

// Determine whether there is an HTML help document for this page.
$havePageHelp = @file_exists(preg_replace('/\.php$/i', '', $_SERVER['SCRIPT_FILENAME']).'_help.html');

// Determine which type of client we're talking to.
include dirname(dirname(__FILE__)).'/jax/classes/Mobile_Detect.class.php';
$mobileDetect = new Mobile_Detect;
$isMobile = $mobileDetect->isMobile();
$isTablet = $mobileDetect->isTablet();
$isDesktop = !$isMobile;

function __header__loadJSResources() {
	global $headerScripts;

	// Include resources for any JavaScript files, EXCLUDING JavaScript files which
	// are specified by full URL (look for the '://' protocol-host separator).
	if (isset($headerScripts) && is_array($headerScripts)) {
		$hsdr = rtrim(dirname(dirname(__FILE__)), "/\\");
		foreach ($headerScripts as $headerScript) {
			// Skip URIs with protocols; those are (probably) external to this application.
			if (strpos($headerScript, '://') !== false) continue;
			$fn = ((strlen($headerScript) > 0) && ($headerScript[0] == '/')) ?
				($_SERVER['DOCUMENT_ROOT'].$headerScript) :
				(APP_ROOT_DIR.'/'.$headerScript);
			loadResourceBundle($hsdr.'/'.ltrim($headerScript, "/\\"));
		}
	}
}
__header__loadJSResources();

function __header__emitStylesheetOrScriptTags($tagFirstPart, $tagLastPart, $uris1, $uris2 = null) {
	$uris = array_unique(is_array($uris2) ? array_merge($uris1, $uris2) : $uris1);
	foreach ($uris as $uri) {
		if (strlen($uri) == 0) continue;
		if (strpos($uri, '://') === false) {
			// No protocol.  Add modification time of existing file as unique identifier.
			$fn = ((strlen($uri) > 0) && ($uri[0] == '/')) ?
				($_SERVER['DOCUMENT_ROOT'].$uri) :
				(APP_ROOT_DIR.'/'.$uri);
			if (($mtime = @filemtime($fn)) !== false) {
				$uri .=
					((strpos($uri, '?') !== false) ? '&' : '?').
					'__fnuniq__='.urlencode($mtime);
			}
		}
		echo $tagFirstPart.$uri.$tagLastPart."\n";
	}
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
  <script type="text/javascript">
var havePageHelp = <?php echo $havePageHelp ? 'true' : 'false'; ?>;
<?php if (!isset($resourceStrings)) $resourceStrings = array(); ?>
var resourceStrings = <?php echo json_encode($resourceStrings); ?>;

var isMobile = <?php echo $isMobile ? 'true' : 'false'; ?>;
var isTablet = <?php echo $isTablet ? 'true' : 'false'; ?>;
var isDesktop = <?php echo $isDesktop ? 'true' : 'false'; ?>;
  </script>
<?php if (isset($HEAD_BASE_URL) && ($HEAD_BASE_URL != '')) { ?>
  <base href="<?php echo $HEAD_BASE_URL.(($HEAD_BASE_URL[strlen($HEAD_BASE_URL)-1] != '/') ? '/' : ''); ?>"/>
<?php } ?>
  <title><?php if (isset($pageTitle)) echo htmlspecialchars($pageTitle); ?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Stylesheet files -->
<?php
	__header__emitStylesheetOrScriptTags(
		'  <link href="',
		'" rel="stylesheet" type="text/css"/>',
		array(
			'jax/bootstrap/css/bootstrap.min.css',
			'jax/jquery/css/datatables/datatables.css',
			'jax/jquery/css/smoothness/jquery-ui-1.10.4.custom.min.css',
			'jax/jquery/css/colorbox/colorbox.css',
			'jax/jax-grid/jax-grid.css',
			'jax/css/base-style.css',
			'css/style.css',
		),
		(isset($headerStylesheets) && is_array($headerStylesheets)) ? $headerStylesheets : null
	);
?>

<!-- JavaScript files -->
<?php
	__header__emitStylesheetOrScriptTags(
		'  <script src="',
		'" type="text/javascript"></script>',
		array(
			'jax/jquery/js/jquery-2.1.1.min.js',
			'jax/jquery/js/jquery-migrate-1.2.1.min.js',
			'jax/angularjs/angular.min.js',
			'jax/bootstrap/js/bootstrap.min.js',
			'jax/bootbox/js/bootbox.min.js',
			'jax/jquery/js/jquery-ui-1.10.4.custom.min.js',
			'jax/jquery/js/jquery.field.min.js',
			'jax/jquery/js/jquery.colorbox-min.js',
			'jax/jquery/js/jquery.dataTables.min.js',
			'jax/bootstrap-tabcollapse/bootstrap-tabcollapse.js',
			'jax/js/dataTablesSupport.js',
			'jax/js/l10n.js',
			'jax/js/sprintf.js',
			'jax/jax-grid/jax-grid.js',
			'jax/js/rowFetcher.js',
			'jax/js/ajaxSearchGrid.js',
		),
		(isset($headerScripts) && is_array($headerScripts)) ? $headerScripts : null
	);
?>

 </head>
 <body>
<?php if ((!isset($barePage)) || (!$barePage)) { ?>
  <div id="topNavCont">
<?php include dirname(__FILE__).'/topNav.include.php'; ?>
  </div>
  <div id="bodyLRLayoutTable" class="bodyLRLayoutTable">
   <div class="bodyLRLayoutTR">
    <div class="bodyLRLayoutTD bodyLRLayoutTD-leftNav">
     <div id="leftNavCont" class="leftSide">
<?php include dirname(__FILE__).'/leftNav.include.php'; ?>
     </div>
    </div>
    <div class="bodyLRLayoutTD bodyLRLayoutTD-content">
<?php } ?>
     <div id="hotKeyHelp" class="hotKeyHelp"></div>
     <div id="content" class="content">
