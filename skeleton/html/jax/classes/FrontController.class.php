<?php
if ((!defined('APP_ROOT_DIR')) || (!defined('APP_ROOT_URL')) || (!defined('APP_ROOT_URI'))) include dirname(dirname(__FILE__)).'/include/appRoot.include.php';
class FrontController {
	/**
	 * Array of preg_match()-compatible patterns which are matched against a
	 * candidate URI for a PHP page.  Any candidate PHP page which matches any
	 * of these patterns will be disallowed from being served.
	 */
	public $disallowedPHPFileURIPatterns = array(
		'/\\.include\\.php$/i',
		'/\\.class\\.php$/i',
		'/\\.interface\\.php$/i',
	);

	/**
	 * Get the current request URI, with query string and anchor stripped.
	 * @return string The bare request URI.
	 */
	public function getBareRequestURI() {
		$uri = $_SERVER['REQUEST_URI'];
		if (($qidx = strpos($uri, '?')) !== false) $uri = substr($uri, 0, $qidx);
		if (($pidx = strpos($uri, '#')) !== false) $uri = substr($uri, 0, $pidx);
		return $uri;
	}

	/**
	 * Given a bare request URI, such as returned from getBareRequestURI(), find any matching PHP
	 * script.
	 * @param string $bareRequestURI The bare request URI, with query string and anchor stripped.
	 * @return object An object representing the found PHP script, or false if none was found.
	 * The object contains an 'fn' property containing the filename of the PHP script, a 'uri'
	 * property containing the URI of the PHP script, and a 'pathinfo' property containing the
	 * pathinfo (extra path portion following the portion of the URI which matched the PHP script).
	 */
	public function findPHPScript($bareRequestURI) {
		// If the app root URI actually has path components, and the bare request URI begins with
		// the app root URI, remove the app root URI from the beginning of the bare request URI
		// before searching for PHP scripts.
		if ((APP_ROOT_URI != '') && (APP_ROOT_URI != '/') && (strlen($bareRequestURI) >= strlen(APP_ROOT_URI)) && (strncmp($bareRequestURI, APP_ROOT_URI, strlen(APP_ROOT_URI)) == 0)) {
			$bareRequestURI = substr($bareRequestURI, strlen(APP_ROOT_URI));
			if (($bareRequestURI == '') || ($bareRequestURI[0] != '/')) {
				$bareRequestURI = '/'.$bareRequestURI;
			}
		}

		$res = (object)array(
			'fn'=>'',
			'uri'=>'',
			'pathinfo'=>'',
		);

		// First look for an index.php in a directory where the directory is specified in the URI.
		// If we find that, return it.
		$indexuri = '/'.trim($bareRequestURI, '/').'/index.php';
		$indexfn = rtrim(APP_ROOT_DIR, '/').$indexuri;
		if (file_exists($indexfn)) {
			$res->fn = $indexfn;
			$res->uri = $indexuri;
			return $res;
		}
		unset($indexuri);
		unset($indexfn);

		// It's not a directory name where an index.php exists.
		$pieces = explode('/', $bareRequestURI);
		if ((!empty($pieces)) && ($pieces[0] == '')) array_shift($pieces);
		$fn = APP_ROOT_DIR;
		$uri = '';
		$found = false;
		foreach ($pieces as $piece) {
			$isphp = (preg_match('/\.php$/', $piece) != 0);
			$spiece = '/'.$piece;
			if (!$found) {
				$fn .= $spiece;
				$uri .= $spiece;
				if ($isphp) {
					if ((file_exists($fn)) && (!$this->isDisallowedPHPFile($uri))) {
						$res->fn = $fn;
						$res->uri = $uri;
						$found = true;
					}
				} else {
					$testfn = $fn.'.php';
					$testuri = $uri.'.php';
					if ((file_exists($testfn)) && (!$this->isDisallowedPHPFile($testuri))) {
						$res->fn = $testfn;
						$res->uri = $testuri;
						$found = true;
					}
					unset($testfn);
					unset($testuri);
				}
			} else {
				$res->pathinfo .= $spiece;
			}
		}
		if ($found) return $res;
		return false;
	}

	/**
	 * Determine whether a URI refers to a disallowed PHP file.
	 * @param string $uri The URI for the candidate PHP file.
	 * @return boolean true if the URI is a disallowed PHP file, and therefore cannot be served;
	 * false if it is not a disallowed PHP file and therefore can be served.
	 */
	public function isDisallowedPHPFile($uri) {
		foreach ($this->disallowedPHPFileURIPatterns as $pat) {
			if (preg_match($pat, $uri)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Override server variables before running a PHP script from the front controller.
	 * The following $_SERVER keys are affected: 'SCRIPT_FILENAME', 'SCRIPT_NAME', 'PATH_INFO',
	 * 'PHP_SELF', 'REQUEST_URI'.
	 * @param string $fn The filename of the PHP script we're about to run.  This value
	 * gets put into $_SERVER['SCRIPT_FILENAME'].
	 * @param string $uri The URI of the PHP script we're about to run.  This value gets
	 * put into $_SERVER['SCRIPT_NAME'].
	 * @param string $pathinfo The (extra) path portion following the portion of the URI
	 * which refers to the actual PHP script.  This value gets put into $_SERVER['PATH_INFO'].
	 */
	public function overrideServerVars($fn, $uri, $pathinfo, $querystring) {
		$_SERVER['SCRIPT_FILENAME'] = $fn;
		$_SERVER['SCRIPT_NAME'] = $uri;
		$_SERVER['PATH_INFO'] = $pathinfo;

		$_SERVER['PHP_SELF'] = $uri.$pathinfo;
		$_SERVER['REQUEST_URI'] = $uri.$pathinfo.(($querystring != '') ? '?'.$querystring : '');
	}

	public function showServerVars() {
		$vns = array('SCRIPT_FILENAME', 'SCRIPT_NAME', 'PATH_INFO', 'PHP_SELF', 'REQUEST_URI');
		foreach ($vns as $vn) {
			echo "$vn=".(isset($_SERVER[$vn]) ? $_SERVER[$vn] : '')."<br/>";
		}
	}

	// Get request parameters as an associative array.
	// If $parsePathInfo is true (the default if it is omitted), allow pathinfo to be used
	// instead of query string parameters, like this:
	//     /script-name/paramname1/paramval1/paramname2/paramval2
	// POST values have highest precedence, followed by query string parameters, then
	// (optional) pathinfo.
	public static function getRequestParams($parsePathinfo = true) {
		$params = array_merge($_GET, $_POST);
		if ($parsePathinfo) {
			$pipieces = (isset($_SERVER['PATH_INFO']) && ($_SERVER['PATH_INFO'] != '')) ?
				explode('/', trim($_SERVER['PATH_INFO'], '/')) : array();
			$npipieces = count($pipieces);
			for ($pii = 0; ($pii+1) < $npipieces; $pii += 2) {
				$k = urldecode($pipieces[$pii]);
				if (!isset($params[$k])) $params[$k] = urldecode($pipieces[$pii+1]);
			}
		}
		return $params;
	}
}
