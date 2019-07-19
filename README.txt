=== Core Standards ===
Contributors: netzstrategen, tha_sun, fabianmarz, juanlopez4691, lucapipolo
Tags: core, standards, defaults, enhancements, security
Requires at least: 4.5
Tested up to: 4.9.8
Stable tag: 1.23.2
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


== Installation ==

1. Extract the archive into the plugins directory as usual.

2. Activate the plugin as usual.


= Requirements =

- PHP 7.1 or later.
