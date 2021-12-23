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
    $custom_path = defined('CORE_STANDARDS_LOGIN_PATH') ? CORE_STANDARDS_LOGIN_PATH : '/login.php';

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

  /**
   * @implemements wp_headers
   *
   * @return array
   */
  public static function additional_securityheaders(array $headers):array {
    if (!is_admin()) {
      $headers['Referrer-Policy']           ??= defined('CORE_STANDARDS_Referrer_Policy') ? CORE_STANDARDS_Referrer_Policy : 'no-referrer-when-downgrade';
      $headers['X-Content-Type-Options']    ??= defined('CORE_STANDARDS_X_Content_Type_Options') ? CORE_STANDARDS_X_Content_Type_Options : 'nosniff';
      $headers['X-XSS-Protection']          ??= defined('CORE_STANDARDS_X_XSS_Protection') ? CORE_STANDARDS_X_XSS_Protection : '1; mode=block';
      $headers['Permissions-Policy']        ??= defined('CORE_STANDARDS_Permissions_Policy') ? CORE_STANDARDS_Permissions_Policy : 'geolocation=(self "https://example.com") microphone=() camera=()';
      $headers['Content-Security-Policy']   ??= defined('CORE_STANDARDS_Content_Security_Policy') ? CORE_STANDARDS_Content_Security_Policy : 'frame-ancestors';
      $headers['X-Frame-Options']           ??= defined('CORE_STANDARDS_X_Frame_Options') ? CORE_STANDARDS_X_Frame_Options : 'SAMEORIGIN';
      $headers['Strict-Transport-Security'] ??= defined('CORE_STANDARDS_Strict_Transport_Security') ? CORE_STANDARDS_Strict_Transport_Security : 'Strict-Transport-Security: max-age=31536000; includeSubDomains';
    }
    return $headers;
  }
}
