<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\UserLoginForm.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Generic User Login Form functionality.
 */
class UserLoginForm {

  /**
   * Returns the login form module.
   *
   * @param array $args
   *
   * @return string
   */
  public static function getOutput($args = []) {
    $redirect = !empty($args['redirect']) ? $args['redirect'] : '';

    $output = '';
    if (!static::isLoggedIn()) {
      $output .= static::getForm($args);
    }

    $links = '';
    if (!empty($args['show_reset_link']) && $link = static::getResetPasswordLink()) {
      $links .= '<div class="user-login-form-reset-password-link-wrapper">' . $link . '</div>';
    }
    if (!empty($args['show_profile_link']) && $link = static::getProfileLink(!empty($args['profile_path']) ? $args['profile_path'] : '')) {
      $links .= '<div class="user-login-form-account-link-wrapper">' . $link . '</div>';
    }
    if (!empty($args['show_logout_link']) && $link = static::getLogoutLink($redirect)) {
      $links .= '<div class="user-login-form-logout-link-wrapper">' . $link . '</div>';
    }
    if ($links) {
      $output .= '<div class="user-login-form-links">' . $links . '</div>';
    }
    return $output;
  }

  /**
   * Returns the native wordpress login form.
   *
   * @param array $args
   *
   * @return string
   */
  public static function getForm($args = []) {
    $args = (array) $args + [
      'echo' => FALSE,
    ];
    if (isset($args['redirect'])) {
      $args['redirect'] = home_url($args['redirect']);
    }
    return wp_login_form($args);
  }

  /**
   * Returns the login link.
   *
   * @param array $args
   *
   * @return string
   */
  public static function getLoginLink($redirect = '') {
    if (static::isLoggedIn()) {
      return static::getProfileLink($redirect);
    }
    $redirect = $redirect ?: home_url(add_query_arg());
    $link_text = apply_filters('core_standards/user_login_form/link_text_login', __('Log in'));
    return '<a href="' . wp_login_url($redirect) . '" class="user-login-form-login-link">' . $link_text . '</a>';
  }

  /**
   * Returns the profile link.
   *
   * @param array $args
   *
   * @return string
   */
  public static function getProfileLink($redirect = '') {
    $redirect = $redirect ?: 'user';
    if (static::isLoggedIn()) {
      $link_text = apply_filters('core_standards/user_login_form/link_text_user', $GLOBALS['current_user']->display_name);
      return '<a href="' . home_url($redirect) . '" class="user-login-form-account-link">' . $link_text . '</a>';
    }
  }

  /**
   * Returns the logout link.
   *
   * @param array $args
   *
   * @return string
   */
  public static function getLogoutLink($redirect = '') {
    $redirect = $redirect ?: home_url(add_query_arg(NULL, NULL));
    if (static::isLoggedIn()) {
      $link_text = apply_filters('core_standards/user_login_form/link_text_logout', __('Log out'));
      return '<a href="' . wp_logout_url($redirect) . '" class="user-login-form-logout-link">' . $link_text . '</a>';
    }
  }

  /**
   * Returns the reset password link.
   *
   * @return string
   */
  public static function getResetPasswordLink() {
    if (!static::isLoggedIn()) {
      $link_text = apply_filters('core_standards/user_login_form/link_text_reset_password', __('Reset Password'));
      return '<a href="/wp-login.php?action=lostpassword" class="user-login-form-reset-password-link">' . $link_text . '</a>';
    }
  }

  /**
   * Returns whether the current user is logged in.
   *
   * @return bool
   */
  private static function isLoggedIn() {
    return !empty($GLOBALS['current_user']->ID);
  }

}
