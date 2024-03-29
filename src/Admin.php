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
   * Cron event name for revision cleanup.
   *
   * @var string
   */
  const CRON_EVENT_REVISION_CLEANUP = Plugin::PREFIX . '/cron/revision-cleanup';

  /**
   * Time-frame in days for which to retain all revisions (for post type 'posts).
   *
   * @var int
   */
  const CRON_EVENT_REVISION_CLEANUP_RETAIN_DAYS = 90;

  /**
   * Cron event name for delete orphan postmeta.
   *
   * @var string
   */
  const CRON_EVENT_POSTMETA_CLEANUP = Plugin::PREFIX . '/cron/postmeta-cleanup';

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

    // Prevents TinyMCE editor setting unwanted rel attribute values for links opening in new window.
    add_filter('tiny_mce_before_init', __CLASS__ . '::tiny_mce_before_init');

    // Exposes SVG images in media library.
    add_filter('wp_prepare_attachment_for_js', __CLASS__ . '::wp_prepare_attachment_for_js');
    add_action('admin_head', __CLASS__ . '::admin_head');

    // Cache available months for filtering posts and attachments.
    add_filter('media_library_months_with_files', __NAMESPACE__ . '\AdminDateFilterCache::media_library_months_with_files');
    add_filter('pre_months_dropdown_query', __NAMESPACE__ . '\AdminDateFilterCache::pre_months_dropdown_query', 10, 2);

    // Exclude subscribers from post author select options to prevent a performance
    // slowdown on sites with large amounts of non-administrative registered users.
    add_filter('wp_dropdown_users_args', __CLASS__ . '::wp_dropdown_users_args');

    // Limit revisions per post type and age (publishing date).
    add_filter('wp_revisions_to_keep', __CLASS__ . '::wp_revisions_to_keep', 10, 2);
    if (!wp_next_scheduled(static::CRON_EVENT_REVISION_CLEANUP)) {
      wp_schedule_event(time(), 'twicedaily', static::CRON_EVENT_REVISION_CLEANUP);
    }
    add_action(static::CRON_EVENT_REVISION_CLEANUP, __CLASS__ . '::cron_revision_cleanup');

    // Schedules delete orphan postmeta cron.
    if (!wp_next_scheduled(static::CRON_EVENT_POSTMETA_CLEANUP)) {
      wp_schedule_event(time(), 'weekly', static::CRON_EVENT_POSTMETA_CLEANUP);
    }
    add_action(static::CRON_EVENT_POSTMETA_CLEANUP, __CLASS__ . '::cron_postmeta_cleanup');

    // Removes Site Health from the dashboard.
    add_action('wp_dashboard_setup', __CLASS__ . '::removeSiteHealthDashboardWidget');
  }

  /**
   * Removes impractical WordPress core update nag messages.
   *
   * @see http://www.paulund.co.uk/hide-wordpress-update-notice
   * @see http://premium.wpmudev.org/blog/hide-the-wordpress-update-notification/
   */
  public static function removeUselessCoreUpdateNagMessages() {
    // @see wp-admin/includes/admin-filters.php
    remove_action('admin_notices', 'update_nag', 3);
    remove_action('admin_notices', 'maintenance_nag', 10);
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
      'shop_manager',
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
      setcookie('wp-settings-' . $user->ID, $cookie_settings, time() + YEAR_IN_SECONDS, SITECOOKIEPATH, '', $secure);
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

  /**
   * Prevents TinyMCE editor setting unwanted rel attribute values for links.
   *
   * TinyMCE sets attribute rel="noopener noreferrer" for links opening in new
   * window (target="_blank"). This can prevent affiliate marketing tools from
   * correctly tracking such links (e.g. in sponsored posts), potentially
   * causing less revenue, so we disable the behavior by default.
   *
   * @see https://www.tinymce.com/docs/configure/content-filtering/#allow_unsafe_link_target
   * @implements tiny_mce_before_init
   */
  public static function tiny_mce_before_init(array $mceInit) {
    $mceInit['allow_unsafe_link_target'] = TRUE;
    return $mceInit;
  }

  /**
   * Exposes SVG images in media grid views.
   *
   * @implements wp_prepare_attachment_for_js
   */
  public static function wp_prepare_attachment_for_js($post) {
    if ($post['mime'] !== 'image/svg+xml') {
      return $post;
    }
    $post['icon'] = $post['url'];
    return $post;
  }

  /**
   * Adds styles for showing SVG icons.
   *
   * @implements admin_head
   */
  public static function admin_head() {
    echo <<<EOD
<style>
  .media-icon {
    background: #eee;
    box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.1), inset 0 0 0 1px rgba(0, 0, 0, 0.05);
  }

  .media-icon img[src$=".svg"],
  img[src$=".svg"].attachment-post-thumbnail {
    width: 100% !important;
    height: auto !important;
  }
</style>
EOD;
  }

  /**
   * @implements wp_dropdown_users_args
   */
  public static function wp_dropdown_users_args(array $query_args) {
    if (isset($query_args['who']) && $query_args['who'] === 'authors') {
      $query_args['role__in'] = array_diff($query_args['role__in'] ?? [], ['subscriber']);
      $query_args['role__not_in'] = array_unique(array_merge($query_args['role__not_in'] ?? [], ['subscriber']));
    }
    return $query_args;
  }

  /**
   * @implements wp_revisions_to_keep
   *
   * Revisions of other post types are not limited and only cleaned up by a cron
   * job.
   *
   * @see Admin::cron_revision_cleanup()
   */
  public static function wp_revisions_to_keep($num, \WP_Post $post) {
    // Revisions for static pages (such as about or imprint pages) should be
    // retained infinitely.
    if ($post->post_type === 'page') {
      $num = -1;
    }
    return $num;
  }

  /**
   * Cron event callback to clean up obsolete revisions.
   *
   * @param int $limit
   *   (optional) The maximum amount of revisions to delete. Defaults to 100 in
   *   order to not slow down and exceed cron execution time.
   */
  public static function cron_revision_cleanup($limit = 100) {
    global $wpdb;

    $post_types = 'post, product, product_variation';

    // Revisions of posts that were last modified a long time ago (3 months) are
    // no longer necessary and can be cleaned up.
    $revision_ids = $wpdb->get_col($wpdb->prepare("SELECT revision.ID
FROM $wpdb->posts revision
INNER JOIN $wpdb->posts parent ON parent.ID = revision.post_parent AND parent.post_type IN ($post_types)
WHERE revision.post_type = 'revision' AND revision.post_modified_gmt < %s AND revision.post_name NOT LIKE '%autosave%'
LIMIT 0,%d
", date('Y-m-d', strtotime('today - ' . static::CRON_EVENT_REVISION_CLEANUP_RETAIN_DAYS . ' days')), $limit));
    foreach ($revision_ids as $revision_id) {
      wp_delete_post_revision($revision_id);
    }
  }

  /**
   * Cron event callback to delete orphan postmeta.
   */
  public static function cron_postmeta_cleanup() {
    global $wpdb;
    $wpdb->query("DELETE pm FROM {$wpdb->prefix}postmeta pm LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id WHERE p.ID IS NULL");
  }

  /**
   * Removes Site Health from the dashboard.
   *
   * @implements wp_dashboard_setup
   */
  public static function removeSiteHealthDashboardWidget() {
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
  }

}
