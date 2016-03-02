<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\UserFrontend.
 */

namespace Netzstrategen\CoreStandards;

class UserFrontend {

  public static function init() {
    // Enable Remember Me by default.
    add_action('login_head', __CLASS__ . '::login_head');
    add_filter('login_form_defaults', __CLASS__ . '::login_form_defaults');

    // Remove WordPress promotion on /wp-login.php.
    add_action('login_enqueue_scripts', __CLASS__ . '::login_enqueue_scripts');
    add_filter('login_headerurl', 'site_url', 10, 0);
    add_filter('login_headertitle', __CLASS__ . '::login_headertitle');

    // Beautify intro text on wp-login.php?action=lostpassword
    add_filter('login_message', __CLASS__ . '::login_message');

    // Append "Register" link to wp_login_form() when used as widget,
    // NOT affecting wp-login.php.
    add_filter('login_form_bottom', __CLASS__ . '::login_form_bottom');

    // Append "Register For This Site" link to login notice on comment_form().
    add_filter('gettext', __CLASS__ . '::gettext', 100, 3);

    // Set proper From header for all emails.
    // Remove "[$blogname]" prefix in email subjects of user account mails.
    add_filter('wp_mail', __CLASS__ . '::wp_mail');
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
    $args += ['value_remember' => TRUE];
    return $args;
  }

  /**
   * @implements login_enqueue_scripts
   */
  public static function login_enqueue_scripts() {
    if (file_exists(get_stylesheet_directory() . '/dist/styles/login.css')) {
      wp_enqueue_style('theme/login', get_stylesheet_directory_uri() . '/dist/styles/login.css');
    }
    else {
      $url = get_stylesheet_directory_uri() . '/dist/images/logo.svg';
      echo <<<EOD
<style>
body, html {
  background-color: #fff;
}
#login {
  padding-top: 5%;
}
.login h1 a {
  background-image: url("$url");
  display: block;
  background-size: auto;
  width: auto;
  height: 50px;
}
</style>

EOD;
    }
  }

  /**
   * @implements login_headertitle
   */
  public static function login_headertitle($login_header_title) {
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
      $output .= '<a href="' . esc_url(wp_registration_url()) . '" class="register btn btn--no-border">' . __('Register') . '</a>';
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
   * @implements wp_mail
   */
  public static function wp_mail(array $message) {
    // Set a proper From header for all emails.
    $from_name = get_bloginfo('name');
    $from_email = get_option('admin_email');
    // Ensure that emails from staging site instances can still be identified.
    if (isset($_SERVER['SERVER_NAME'])) {
      $from_email = strtok($from_email, '@') . '@' . $_SERVER['SERVER_NAME'];
    }
    $from_header = "From: $from_name <$from_email>";
    if (empty($message['headers'])) {
      $message['headers'] = [$from_header];
    }
    elseif (is_string($message['headers'])) {
      if (FALSE === strpos($message['headers'], 'From: ')) {
        $message['headers'] .= "\r\n" . $from_header;
      }
    }
    else {
      if (FALSE === strpos(implode("\n", $message['headers']), 'From: ')) {
        $message['headers'][] = $from_header;
      }
    }

    // Remove the "[$blogname] " prefix in all email subjects (including translations).
    if (isset($message['subject'])) {
      // The blogname option is escaped with esc_html() prior saving, but the subject is plain-text.
      // @see wp_new_user_notification(), pluggable.php
      $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
      $message['subject'] = preg_replace('@^' . preg_quote("[$blogname] ", '@') . '@', '', $message['subject']);
    }
    return $message;
  }

}
