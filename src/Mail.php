<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Mail.
 */

namespace Netzstrategen\CoreStandards;

class Mail {

  /**
   * Replaces From name and email with site name and admin_email.
   *
   * @implements wp_mail
   */
  public static function wp_mail(array $message) {
    // Set a proper From header for all emails.
    $from_name = get_bloginfo('name');
    $from_email = get_option('admin_email');
    // Ensure that emails from staging site instances can still be identified.
    $from_email = strtok($from_email, '@') . '@' . str_replace('www.', '', parse_url(network_home_url(), PHP_URL_HOST));
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
