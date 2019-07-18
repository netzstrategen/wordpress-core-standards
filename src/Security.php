<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Security.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Protects the site against DoS and brute-force attacks.
 */
class Security {

  /**
   * @implements network_site_url
   * @implements site_url
   */
  public static function site_url($url, $path, $scheme) {
    if ($scheme === 'login' || $scheme === 'login_post') {
      $url = strtr($url, ['/wp-login.php' => '/login.php']);
    }
    return $url;
  }

  /**
   * @implements wp_redirect
   */
  public static function wp_redirect($url) {
    $url = strtr($url, ['wp-login.php' => 'login.php']);
    return $url;
  }

}
