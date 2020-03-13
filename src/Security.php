<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Security.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Protects the site against DoS and brute-force attacks.
 *
 * By default, /wp-login.php is replaced with /login.php. You can use a custom path
 * by setting the constant CORE_STANDARDS_LOGIN_PATH in wp-config.php:
 * ```
 * const CORE_STANDARDS_LOGIN_PATH = '/user/login';
 * ```
 * and routing inbound requests on that path into the original /wp-login.php file
 * by adding the following lines to the top of .htaccess:
 * ```
 * # Route /user/login into /wp-login.php.
 * RewriteEngine On
 * RewriteRule ^/?user/login$ /wp-login.php [QSA,END]
 * ```
 */
class Security {

  /**
   * @implements network_site_url
   * @implements site_url
   */
  public static function site_url($url, $path, $scheme) {
    if ($scheme === 'login' || $scheme === 'login_post') {
      $custom_path = defined('CORE_STANDARDS_LOGIN_PATH') ? CORE_STANDARDS_LOGIN_PATH : '/login.php';
      $url = strtr($url, ['/wp-login.php' => $custom_path]);
    }
    return $url;
  }

  /**
   * @implements wp_redirect
   */
  public static function wp_redirect($url) {
    $custom_path = defined('CORE_STANDARDS_LOGIN_PATH') ? CORE_STANDARDS_LOGIN_PATH : 'login.php';

    // wp-login.php calls wp_safe_redirect() with a relative path, which causes
    // wp_validate_redirect() to automatically prepend the current folder name
    // of the request URI to it. Prevent the path from being duplicated.
    if (strpos($url, dirname($custom_path)) === FALSE) {
      $url = strtr($url, ['wp-login.php' => $custom_path]);
    }
    else {
      $url = strtr($url, ['wp-login.php' => basename($custom_path)]);
    }
    return $url;
  }

}
