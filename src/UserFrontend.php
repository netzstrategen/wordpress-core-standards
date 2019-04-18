<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\UserFrontend.
 */

namespace Netzstrategen\CoreStandards;

class UserFrontend {

  /**
   * Admin bar (front-end) should be shown at bottom of viewport.
   *
   * @var boolean
   */
  const SHOW_ADMIN_BAR_AT_BOTTOM = FALSE;

  public static function init() {
    // Injects actual nonce values for each menu link that contains the query
    // string '_wpnonce=[action]'; e.g., '_wpnonce=customer-logout' for the
    // WooCommerce logout link.
    add_filter('wp_nav_menu_objects', __CLASS__ . '::wp_nav_menu_objects', 20);

    // Increase session cookie lifetime to prevent unnecessary logouts.
    add_filter('auth_cookie_expiration', __CLASS__ . '::auth_cookie_expiration', 10, 3);

    // Enable Remember Me by default.
    add_action('login_head', __CLASS__ . '::login_head');
    add_filter('login_form_defaults', __CLASS__ . '::login_form_defaults');

    // Remove WordPress promotion on /wp-login.php.
    add_action('login_enqueue_scripts', __CLASS__ . '::login_enqueue_scripts');
    add_filter('login_headerurl', 'site_url', 10, 0);
    add_filter('login_headertext', __CLASS__ . '::login_headertext');

    // Beautify intro text on wp-login.php?action=lostpassword
    add_filter('login_message', __CLASS__ . '::login_message');

    // Append "Register" link to wp_login_form() when used as widget,
    // NOT affecting wp-login.php.
    add_filter('login_form_bottom', __CLASS__ . '::login_form_bottom');

    // Append "Register For This Site" link to login notice on comment_form().
    add_filter('gettext', __CLASS__ . '::gettext', 100, 3);

    // Remove admin bar for users with no backend access.
    if (!Admin::currentUserHasAccess()) {
      add_filter('show_admin_bar', '__return_false');
    }

    // Moves WP admin bar to bottom of page.
    if (apply_filters(Plugin::PREFIX . '-admin-bar-bottom', static::SHOW_ADMIN_BAR_AT_BOTTOM)) {
      add_action('admin_bar_init', __CLASS__ . '::move_admin_bar_to_bottom');
    }
  }

  /**
   * @implements wp_nav_menu_objects
   */
  public static function wp_nav_menu_objects(array $items) {
    foreach ($items as $item) {
      if (strpos($item->url, '_wpnonce=')) {
        $query = parse_url($item->url, PHP_URL_QUERY);
        parse_str($query, $args);
        $args['_wpnonce'] = wp_create_nonce($args['_wpnonce']);
        $item->url = strtr($item->url, [$query => http_build_query($args)]);
      }
    }
    return $items;
  }

  /**
   * @implements auth_cookie_expiration
   */
  public static function auth_cookie_expiration($duration, $user_id, $remember) {
    // Default duration is 2 days, with remember-me 14 days.
    // Increase the first to 1 month and the second to 7 months.
    return $duration * 15;
  }

  /**
   * @implements login_head
   */
  public static function login_head() {
    if (empty($_POST)) {
      $_POST['rememberme'] = 1;
    }
  }

  /**
   * @implements login_form_defaults
   */
  public static function login_form_defaults(array $args) {
    wp_enqueue_style('core-standards/login', Plugin::getBaseUrl() . '/assets/user-login-form.css', FALSE);
    $args['value_remember'] = TRUE;
    return $args;
  }

  /**
   * @implements login_enqueue_scripts
   */
  public static function login_enqueue_scripts() {
    if (file_exists(get_stylesheet_directory() . '/dist/styles/login.css')) {
      wp_enqueue_style('theme/login', get_stylesheet_directory_uri() . '/dist/styles/login.css', FALSE);
    }
    else {
      $url = apply_filters(Plugin::PREFIX . '-login-logo-url', get_stylesheet_directory_uri() . '/dist/images/logo.svg');
      echo <<<EOD
<style>
body, html {
  background-color: #fff;
}
#login {
  padding-top: 5%;
}
#login h1 a {
  background-image: url("$url");
  display: block;
  background-size: auto;
  width: auto;
  height: 50px;
}
#loginform .forgetmenot {
  display: none;
}
</style>

EOD;
    }
  }

  /**
   * @implements login_headertext
   */
  public static function login_headertext($login_header_text) {
    return get_bloginfo('name');
  }

  /**
   * @implements login_message
   */
  public static function login_message($message) {
    // Inject a line feed between the two sentences.
    // This will work as long as there is this lengthy message.
    if (isset($_GET['action']) && $_GET['action'] === 'lostpassword') {
      $message = preg_replace('@(\w+\.)\ (\w+)@', '$1<br><br>$2', $message);
    }
    return $message;
  }

  /**
   * @implements login_form_bottom
   */
  public static function login_form_bottom($output) {
    if (get_option('users_can_register')) {
      $output .= '<a href="' . esc_url(wp_registration_url()) . '" class="register">' . __('Register') . '</a>';
    }
    return $output;
  }

  /**
   * @implements gettext
   */
  public static function gettext($translation, $text, $domain) {
    if ($domain === 'default' && $text === 'You must be <a href="%s">logged in</a> to post a comment.') {
      if (get_option('users_can_register')) {
        $translation .= ' ' . '<a href="' . esc_url(wp_registration_url()) . '" class="register">' . __('Register For This Site') . '</a>';
      }
    }
    return $translation;
  }

  /**
   * @implements move_admin_bar_to_bottom
   */
  public static function move_admin_bar_to_bottom() {
    remove_action('wp_head', '_admin_bar_bump_cb');
    if (is_user_logged_in() && current_user_can('admin_access')) {
      wp_enqueue_style('core-standards/admin-bar', Plugin::getBaseUrl() . '/dist/styles/admin-bar.css');
    }
  }

}
