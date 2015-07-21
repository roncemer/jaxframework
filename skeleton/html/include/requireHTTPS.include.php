<?php
if ((!((isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')))) &&
	(!((isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') == 0))))) {
	if (!preg_match('/^dev\./', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ''))) {
		header('HTTP/1.1 302 Found');
		header(
			'Location: '.
			'https://'.
			(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '')).
			(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')
		);
		exit;
	}
}
