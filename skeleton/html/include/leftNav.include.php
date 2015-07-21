<?php
if ((!defined('APP_ROOT_DIR')) || (!defined('APP_ROOT_URL')) || (!defined('APP_ROOT_URI'))) include dirname(dirname(__FILE__)).'/jax/include/appRoot.include.php';

if (!class_exists('Permissions', false)) include dirname(dirname(__FILE__)).'/classes/Permissions.class.php';

// These must be declared global, because sometimes this include file is included
// from within the inScriptPermissionsCheck() function in Permissions class.
global $loginRequired, $loggedInUser;
if ((!isset($loginRequired)) || (!$loginRequired) || (isset($loggedInUser) && ($loggedInUser !== null))) {

$LEFT_NAV_MENU_GROUPS = array(
	array(
		'name'=>'Administration',
		'items'=>array(
			array('name'=>'Users', 'page'=>'appuser'),
			array('name'=>'Roles', 'page'=>'approle'),
		),
	),
	array(
		'name'=>'Data Entry',
		'items'=>array(
			array('name'=>'Put Data Entry Apps Here', 'page'=>'#'),
		),
	),
	array(
		'name'=>'Reports',
		'items'=>array(
			array('name'=>'Put Report Apps Here', 'page'=>'#'),
		),
	),
);

function emitMenuGroup(
	$menuGroup,
	$groupClass = 'leftNavGroup',
	$liClass_active = 'leftNavActivePageLink',
	$liClass_inactive = '') {

	emitMenu($menuGroup, $groupClass, $liClass_active, $liClass_inactive);
}

function emitMenu($menuGroup, $groupClass, $liClass_active, $liClass_inactive) {
	global $loggedInUser;

	$user_id = (isset($loggedInUser) && $loggedInUser) ? $loggedInUser->id : 0;

	if (isset($menuGroup['children'])) {
		foreach($menuGroup['children'] as $childMenu) {
			emitMenu($childMenu, $groupClass, $liClass_active, $liClass_inactive);
		}
	}

	$currentURI = preg_replace('/(\\/index$)|(\\/index\.php$)|(\\.php$)/', '', $_SERVER['SCRIPT_NAME']);
	if (isset($menuGroup['items']) && (!empty($menuGroup['items']))) {
		$haveGroupName = false;
		foreach ($menuGroup['items'] as $menuGroupItem) {
			if (!Permissions::hasPermissionsForScript(
				$user_id,
				$menuGroupItem['page'])) {
				continue;
			}
			if (!$haveGroupName) {
				$haveGroupName = true;
				echo "<div class=\"$groupClass\">".htmlspecialchars($menuGroup['name'])."</div>\n<ul>\n";
			}
			$itemURI = APP_ROOT_URI.$menuGroupItem['page'];
			if ($currentURI == preg_replace('/(\\/index$)|(\\/index\.php$)|(\\.php$)/', '', $itemURI)) {
				echo "<li class=\"$liClass_active\">- ".htmlspecialchars($menuGroupItem['name'])."</li>\n";
			} else {
				echo "<li class=\"$liClass_inactive\"><a href=\"".
					htmlspecialchars($itemURI)."\">- ".
					htmlspecialchars($menuGroupItem['name'])."</a></li>\n";
			}
		}
		if ($haveGroupName) echo "</ul>\n";
	}
}

function havePermissionsForAnyMenuGroupLinks($menuGroup) {
	global $loggedInUser;

	$user_id = (isset($loggedInUser) && $loggedInUser) ? $loggedInUser->id : 0;

	if ((!isset($menuGroup['items'])) || empty($menuGroup['items'])) {
		if (isset($menuGroup['children'])) {
			foreach ($menuGroup['children'] as $child) {
				if (havePermissionsForAnyMenuGroupLinks($child)) {
					return true;
				}
			}
		}
		return false;
	}

	foreach ($menuGroup['items'] as $menuGroupItem) {
		if (Permissions::hasPermissionsForScript(
			$user_id,
			$menuGroupItem['page'])) {
			return true;
		}
	}
	return false;
}
?>

<a href="<?php echo APP_ROOT_URI; ?>">Home</a><br/>
<?php
foreach ($LEFT_NAV_MENU_GROUPS as $__mg__) {
	if (havePermissionsForAnyMenuGroupLinks($__mg__)) {
		emitMenuGroup($__mg__);
	}
}
unset($__mg__);
?>
<?php
}	// if ((!isset($loginRequired)) || (!$loginRequired) || ((isset($loggedInUser)) && ($loggedInUser !== null)))
?>
