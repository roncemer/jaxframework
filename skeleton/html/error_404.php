<?php
// WARNING: This is both an actual page, and an include file which can be included anywhere you
// need to issue a 404 error.  Thereore, it MUST NOT be named as an include file.
header('HTTP/1.1 404 Not Found');
include dirname(__FILE__).'/include/header.include.php';
?>
<h1>404 - Not Found</h1>

<p>
The page you requested was not found.
</p>
<?php
include dirname(__FILE__).'/include/footer.include.php';
exit;
