=== Core Standards ===
Contributors: netzstrategen, tha_sun, fabianmarz, juanlopez4691, lucapipolo, colourgarden
Tags: core, standards, defaults, enhancements, security
Requires at least: 4.5
Tested up to: 5.3.2
Stable tag: 2.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Various features and adjustments for WordPress Core that do not need configuration.

== Description ==

Performs several adjustments to native WordPress functionality that should be in
Core already but are not for different reasons (as the name implies).


= Features =

- Replaces the front controller `wp-login.php` with `login.php` and blocks
  access to `wp-login.php` and `xmlrpc.php` to prevent Denial-of-Service (DoS)
  and brute-force attacks.

  For Apache, this requires `AllowOverride all` to be set for the directory of the
  virtual host or the whole server (the latter is not recommended for production
  servers).

= Customization =

By default, /wp-login.php is replaced with /login.php. You can use a custom path
by setting the constant CORE_STANDARDS_LOGIN_PATH in wp-config.php:
```
const CORE_STANDARDS_LOGIN_PATH = '/user/login';
```
and routing inbound requests on that path into the original /wp-login.php file
by adding the following lines to the top of .htaccess:
```
# Route /user/login into /wp-login.php.
RewriteEngine On
RewriteRule ^/?user/login$ /wp-login.php [QSA,END]
```


== Installation ==

1. Extract the archive into the plugins directory as usual.

2. Activate the plugin as usual.


= Requirements =

- PHP 7.1 or later.
