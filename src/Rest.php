<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Rest.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Protects site against admin user data enumeration.
 *
 * @see https://gosecure.ai/blog/2021/03/02/emails-disclosure-on-wordpress/
 */
class Rest {

  /**
   * @implements rest_endpoints
   */
  public static function rest_endpoints(array $endpoints): array {
    // Prevents user enumeration for unauthenticated requests.
    // @see https://wordpress.stackexchange.com/a/378142/80825
    $enumerable_endpoints = [
      '/wp/v2/users',
      '/wp/v2/users/(?P<id>[\d]+)',
    ];
    foreach ($enumerable_endpoints as $route) {
      // WooCommerce invokes REST API endpoints when registering routes.
      if (!isset($endpoints[$route])) {
        continue;
      }
      $wrapped_permission_callback = $endpoints[$route][0]['permission_callback'];
      $endpoints[$route][0]['permission_callback'] = fn($request) =>
        current_user_can('list_users')
          ? $wrapped_permission_callback($request)
          : new \WP_Error('rest_user_cannot_view', 'Insufficient permissions', ['status' => \rest_authorization_required_code()]);
    }
    return $endpoints;
  }

}
