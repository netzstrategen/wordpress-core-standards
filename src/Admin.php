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

  private static $skip_sample_permalink = [];

  /**
   * @implements admin_init
   */
  public static function init() {
    static::preventBackendAccess();
    static::removeUselessCoreUpdateNagMessages();

    // Removes administrative dashboard widgets hidden by current user.
    add_action('hidden_meta_boxes', __CLASS__ . '::hidden_meta_boxes', 10, 3);

    add_filter('get_user_option_user-settings', __CLASS__ . '::get_user_option_user_settings', 10, 3);

    add_filter('get_sample_permalink', __CLASS__ . '::get_sample_permalink', 10, 5);
    add_filter('get_sample_permalink_html', __CLASS__ . '::get_sample_permalink_html', 10, 5);
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
   * Removes administrative dashboard widgets hidden by current user.
   *
   * When logging in to the administrative area the dashboard page can take a
   * very long time to load, even if the user disabled all widgets.
   *
   * @implements hidden_meta_boxes
   */
  public static function hidden_meta_boxes($hidden, $screen, $use_defaults) {
    global $wp_meta_boxes;
    static $has_run = FALSE;

    if ($screen->id !== 'dashboard' || $has_run) {
      return $hidden;
    }
    $has_run = TRUE;
    $hidden_ids = array_combine($hidden, $hidden);

    foreach ($wp_meta_boxes[$screen->id] as $context => $priorities) {
      foreach ($wp_meta_boxes[$screen->id][$context] as $priority => $boxes) {
        foreach ($wp_meta_boxes[$screen->id][$context][$priority] as $id => $box) {
          if (isset($hidden_ids[$id]) && isset($box['callback'])) {
            $wp_meta_boxes[$screen->id][$context][$priority][$id]['callback'] = '__return_null';
          }
        }
      }
    }
    return $hidden;
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

  /**
   * Prevents post ID to be used as permalink.
   *
   * When a new post is auto-saved without a title then a permalink is generated
   * from the post ID. This solution forces the permalink to stay blank until a
   * title is inserted.
   *
   * @see https://core.trac.wordpress.org/ticket/36157
   * @implements get_sample_permalink
   */
  public static function get_sample_permalink($permalink, $post_id, $title, $name, $post) {
    // $title is only passed from the autosave/post JavaScript, but not when the
    // permalink UI is generated for the initial page output on the server-side.
    if ($post_id === (int) $permalink[1] && empty($post->post_title)) {
      static::$skip_sample_permalink[$post_id] = TRUE;
      return array('', '');
    }
    static::$skip_sample_permalink[$post_id] = FALSE;
    return $permalink;
  }

  /**
   * Prevents unpretty preview link to be displayed until a post title is set.
   *
   * When get_sample_permalink() returns a blank permalink then
   * get_sample_permalink_html() still creates the permalink UI widget that
   * contains an unpretty preview link, which should not appear. This solution
   * uses a static flag to determine the situation, because there is no earlier
   * filter that allows to prevent the output.
   *
   * @implements get_sample_permalink_html
   */
  public static function get_sample_permalink_html($html, $post_id, $new_title, $new_slug, $post) {
    if (!empty(static::$skip_sample_permalink[$post_id])) {
      return '';
    }
    return $html;
  }

}
