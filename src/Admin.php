<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Admin.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Administrative back-end functionality.
 */
class Admin {

  /**
   * @implements admin_init
   */
  public static function init() {
    static::preventBackendAccess();
    static::removeUselessCoreUpdateNagMessages();

    add_filter('get_user_option_user-settings', __CLASS__ . '::get_user_option_user_settings', 10, 3);
  }

  /**
   * Removes impractical WordPress core update nag messages.
   *
   * @see http://www.paulund.co.uk/hide-wordpress-update-notice
   * @see http://premium.wpmudev.org/blog/hide-the-wordpress-update-notification/
   */
  public static function removeUselessCoreUpdateNagMessages() {
    if (!current_user_can('update_core')) {
      // @see wp-admin/includes/admin-filters.php
      remove_action('admin_notices', 'update_nag', 3);
      remove_action('admin_notices', 'maintenance_nag', 10);
    }
  }

  /**
   * Prevent users from accessing the backend.
   *
   * @param string $frontend_redirect_uri
   *   The uri to redirect users with no backend access to.
   */
  public static function preventBackendAccess($frontend_redirect_uri = '') {
    if (!defined('DOING_AJAX') && !defined('WP_IMPORTING') && !static::currentUserHasAccess()) {
      wp_redirect(home_url(apply_filters('core_standards/admin/frontend_redirect_uri', $frontend_redirect_uri)));
      exit;
    }
  }

  /**
   * Check if current user has backend access.
   */
  public static function currentUserHasAccess() {
    return current_user_can('admin_access');
  }

  /**
   * Returns the accessible user roles.
   */
  public static function getAccessibleRoles() {
    $roles = [
      'super_admin',
      'administrator',
      'editor',
      'author',
      'contributor',
    ];
    return apply_filters('core_standards/admin/accessible_roles', $roles);
  }

  /**
   * Adds admin access capability for the accessible user roles.
   */
  public static function addAccessCapability() {
    foreach (wp_roles()->role_objects as $name => $role) {
      if (in_array($name, static::getAccessibleRoles())) {
        $role->add_cap('admin_access');
      }
    }
  }

  /**
   * Removes admin access capability for the accessible user roles.
   */
  public static function removeAccessCapability() {
    foreach (wp_roles()->role_objects as $name => $role) {
      if (in_array($name, static::getAccessibleRoles())) {
        $role->remove_cap('admin_access');
      }
    }
  }

  /**
   * @implements get_user_option_{user_settings}
   *
   * Sets default settings for the attachment media popup.
   *
   * @see wp_user_settings();
   *
   * @param string $value
   *   The received option value.
   * @param string $option
   *   The current option name.
   * @param object $user
   *   The current WP_User object.
   *
   * @return string
   */
  public static function get_user_option_user_settings($value, $option, \WP_User $user) {
    $overrides = apply_filters('core-standards/user/settings/overrides', [
      'imgsize' => 'medium',
      'align' => 'center',
      'urlbutton' => 'none',
    ]);
    parse_str($value, $settings);
    $settings = http_build_query(array_merge($settings, $overrides));

    if (isset($_COOKIE['wp-settings-' . $user->ID])) {
      parse_str($_COOKIE['wp-settings-' . $user->ID], $cookie_settings);
      $cookie_settings = http_build_query(array_merge($cookie_settings, $overrides));
      $secure = ('https' === parse_url(admin_url(), PHP_URL_SCHEME));
      setcookie('wp-settings-' . $user->ID, $cookie_settings, time() + YEAR_IN_SECONDS, SITECOOKIEPATH, null, $secure);
      $_COOKIE['wp-settings-' . $user->ID] = $cookie_settings;
    }
    return $settings;
  }

}
