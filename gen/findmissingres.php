<?php
function usage() {
	global $argv, $usageDescription, $docroot, $processAllTablesByDefault;

	fputs(
		STDERR,
		"Usage: php ".basename($argv[0])." <resource-filename> <src-filename> [<src-filename> ...]\n".
		"    resource-filename - A resource (*.strings) file.\n".
		"    src-filename - PHP or JavaScript source file(s) to process.\n".
		"Finds resource string identifiers within source files which are not defined in a resource file.\n"
	);
	exit(1);
}

if ($argc <= 2) usage();
$result = 0;
if (($resourceStrings = loadResourceFile($argv[1])) === false) {
	exit(2);
}
$missingKeys = array();
for ($ai = 2; $ai < $argc; $ai++) {
	if (processSourceFile($argv[$ai], $resourceStrings, $missingKeys) === false) {
		if ($result === 0) $result = 3;
	}
	natcasesort($missingKeys);
}
foreach ($missingKeys as $key) echo "$key\n";
return $result;

function loadResourceFile($filename) {
	$resourceStrings = array();
	if (($fp = @fopen($filename, 'r')) === false) {
		fprintf(STDERR, "Could not open resource filename: %s\n", $filename);
		return false;
	}
	$lineno = 0;
	while (($line = @fgets($fp)) !== false) {
		$lineno++;
		$line = trim($line);
		if (($line == '') || ($line[0] == '#') || ($line[0] == ';')) continue;
		if (($equalidx = strpos($line, '=')) === false) {
			error_log("Missing equal sign on line $lineno in resource bundle $filename.\n");
			continue;
		}

		$key = trim(substr($line, 0, $equalidx));
		if ($key == '') {
			error_log("Missing property name on line $lineno in resource bundle $filename.\n");
			continue;
		}

		$val = trim(substr($line, $equalidx+1));
		$heredocDelim = ((strlen($val) > 3) && (strncmp($val, '<<<', 3) == 0)) ?
			trim(substr($val, 3)) : '';
		if ($heredocDelim != '') {
			$val = '';
			while (true) {
				if (($moreval = @fgets($fp)) === false) break;
				if ((strlen($moreval) >= 2) &&
					(substr($moreval, strlen($moreval)-2) == "\r\n")) {
					$moreval = substr($moreval, 0, strlen($moreval)-2);
				} else if ((strlen($moreval) >= 1) &&
							(($moreval[strlen($moreval)-1] == "\r") ||
							 ($moreval[strlen($moreval)-1] == "\n"))) {
					$moreval = substr($moreval, 0, strlen($moreval)-1);
				}
				$lineno++;
				if (trim($moreval) === $heredocDelim) break;
				if ($val == '') $val = $moreval; else $val .= "\n".$moreval;
			}
		} else {
			while (substr($val, -1) == '\\') {
				if (($moreval = @fgets($fp)) === false) {
					$val = trim(substr($val, 0, strlen($val)-1));
					break;
				}
				$lineno++;
				$val = trim(substr($val, 0, strlen($val)-1))."\n".rtrim($moreval);
			}
		}

		$resourceStrings[$key] = $val;
	}
	@fclose($fp);

	return $resourceStrings;
}

function processSourceFile($filename, &$resourceStrings, &$missingKeys) {
	if (!file_exists($filename)) {
		fprintf(STDERR, "File not found: %s\n", $filename);
		return false;
	}

	unset($matches);
	if (preg_match_all('/((^_[te]\\()|([^a-zA-Z0-9]_[te]\\())(((\'[^\']+\')|(\"[^\"]+\"))+)(\\s*)\\)/', file_get_contents($filename), $matches) !== false) {
		foreach ($matches[4] as $key) {
			if ((($key[0] == '\'') && ($key[strlen($key)-1] == '\'')) ||
				(($key[0] == '\"') && ($key[strlen($key)-1] == '\"'))) {
				$key = substr($key, 1, strlen($key)-2);
			}
			if ((!isset($resourceStrings[$key])) && (!in_array($key, $missingKeys))) {
				$missingKeys[] = $key;
			}
		}
	}
	return true;
}
