<?php
// Permissions.class.php
// Copyright (c) 2010-2011 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!isset($__Permissions_class_php_included)) {
	$__Permissions_class_php_included = true;

if (!class_exists('ConnectionFactory', false)) include(dirname(__FILE__).'/ConnectionFactory.class.php');
if (!class_exists('FileCache', false)) include(dirname(__FILE__).'/dao/FileCache.class.php');

class Permissions {
	// Returns true if the specified user has all of the permissions listed in $permissions;
	// false if the user is missing one or more of the permissions.
	// $permissions can be either an array, or a comma-separated list, of permissions.
	public static function hasPermissions($user_id, $permissions) {
		if (!is_array($permissions)) {
			$permissions = explode(',', $permissions);
		}
		$fileCache = self::createFileCache();
		$cacheKey = sprintf('appuser%dHasPerms%s', $user_id, implode(',', $permissions));
		if (($val = $fileCache->get($cacheKey)) !== false) {
			return ($val != 0) ? true : false;
		}

		$db = ConnectionFactory::getConnection();
		$ps = new PreparedStatement(<<<EOF
select distinct p.perm_name from appuserrole u_r
 inner join approle r on r.role_name = u_r.role_name
 inner join approleperm r_p on r_p.role_name = r.role_name
 inner join appperm p on p.perm_name = r_p.perm_name
 where u_r.user_id = ?
 order by p.perm_name
EOF
		);
		$ps->setInt($user_id);
		$userPerms = array();
		$rs = $db->executeQuery($ps);
		while ($r = $db->fetchObject($rs)) $userPerms[] = $r->perm_name;
		$db->freeResult($rs);
		$db->close();

		$hasAllPermissions = true;
		if (!in_array('all', $userPerms)) {
			foreach ($permissions as $perm) {
				$perm = trim($perm);
				if (($perm != '') && (!in_array($perm, $userPerms))) {
					$hasAllPermissions = false;
					break;
				}
			}
		}
		$fileCache->set($cacheKey, $hasAllPermissions ? 1 : 0);

		return $hasAllPermissions;
	}

	public static function getRequiredPermissionsForScript($pageURI) {
		$pageURI = trim($pageURI, '/');
		do {
			$anyTrimmed = false;
			if (($idx = strpos($pageURI, '?')) !== false) {
				$pageURI = trim(substr($pageURI, 0, $idx), '/');
				$anyTrimmed = true;
			}
			if (($idx = strpos($pageURI, '#')) !== false) {
				$pageURI = trim(substr($pageURI, 0, $idx), '/');
				$anyTrimmed = true;
			}
		} while ($anyTrimmed);
		if ($pageURI == '') $pageURI = '/';

		$fileCache = self::createFileCache();
		$cacheKey = sprintf('apppageuriPerms%s', $pageURI);
		if (($val = $fileCache->get($cacheKey)) !== false) {
			return $val;
		}

		$perms = array();
		$db = ConnectionFactory::getConnection();
		$ps = new PreparedStatement(<<<EOF
select distinct pm.perm_name from apppageuri pg
 inner join apppageuriperm pp on pp.page_uri = pg.page_uri
 inner join appperm pm on pm.perm_name = pp.perm_name
 where pg.page_uri = ?
EOF
		);
		$ps->setString($pageURI);
		$rs = $db->executeQuery($ps);
		while ($row = $db->fetchObject($rs)) $perms[] = $row->perm_name;
		$db->freeResult($rs);
		$db->close();

		// If we have a page URI which does NOT end with .php, also include permissions
		// for the same page URI with .php appended to it.
		// This enables us to use a front controller which serves php pages without
		// including the .php extension in the URL.
		if (!preg_match('/\\.php$/', $pageURI)) {
			$perms = array_unique(array_merge($perms, self::getRequiredPermissionsForScript($pageURI.'.php')));
		}

		$fileCache->set($cacheKey, $perms);

		return $perms;
	}

	public static function hasPermissionsForScript($user_id, $pageURI) {
		return Permissions::hasPermissions(
			$user_id,
			Permissions::getRequiredPermissionsForScript($pageURI)
		);
	}

	public static function inScriptPermissionsCheck($user_id, $showMenuIfFailed) {
		$pageURI = $_SERVER['REQUEST_URI'];
		if (!Permissions::hasPermissionsForScript($user_id, $pageURI)) {
			if ($showMenuIfFailed) {
				include dirname(dirname(__FILE__)).'/include/header.include.php';
			} else {
				echo '<html><head></head><body>';
			}
			echo '<h3>You need the following permissions to use this page:</h3>';
			echo '<ul>';

			$fileCache = self::createFileCache();
			$db = null;
			$ps = new PreparedStatement('select description from appperm where perm_name = ?');
			foreach (Permissions::getRequiredPermissionsForScript($pageURI) as $p) {
				$cacheKey = sprintf('apppermDesc%s', $p);
				if (($desc = $fileCache->get($cacheKey)) === false) {
					if ($db === null) $db = ConnectionFactory::getConnection();
					$ps->clearParams();
					$ps->setString($p);
					if (($row = $db->fetchObject($db->executeQuery($ps), true)) !== false) {
						$desc = $row->description;
					} else {
						$desc = $p;
					}
					$fileCache->set($cacheKey, $desc);
				}
				echo '<li>';
				echo htmlspecialchars($desc);
				echo '</li>';
			}
			if ($db !== null) $db->close();

			echo '</ul>';

			if ($showMenuIfFailed) {
				include dirname(dirname(__FILE__)).'/include/footer.include.php';
			} else {
				echo '</body></html>';
			}
			exit();
		}
	}

	protected static function createFileCache() {
		return new FileCache(
			FileCache::getApplicationPrivateCacheDir().'/permissions',
			30
		);
	}
}

}	// if (!isset($__Permissions_class_php_included))
