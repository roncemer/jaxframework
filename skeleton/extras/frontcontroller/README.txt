The default .htaccess file works without a front controller.  Basically, it
just implements a 404 handler document, hides the .php extensions by serving
.php files which exist when they are requested without their .php extensions,
and issues error 404 if someone tries to access a .php file directly (with the
.php extension).

This functionality allows your web application to be relocated into sub-URIs
by use of aliases in Apache, as long as you take care to always use
directory-relative URIs for navigation within the site (no http://hostname/,
and no leading slash in any URI).

In some cases, you may need a front controller to accomplish a specific task.
Included here is a frontcontroller, and a corresponding replacement .htaccess
file.  Simply copy frontcontroller.php to your html directory, and copy the
frontcontroller_htaccess file over the existing html/.htaccess file.  Then
restart apache.

If you use a front controller, your web application MUST be located at the
root of the domain, and URI aliases in your web appliction's apache config
file will NOT work.
