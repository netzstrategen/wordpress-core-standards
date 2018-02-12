<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Schema.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Generic plugin lifetime and maintenance functionality.
 */
class Schema {

  /**
   * register_activation_hook() callback.
   */
  public static function activate() {
    Admin::addAccessCapability();

    // Ensures fast 404 responses for missing files in uploads folder.
    static::ensureUploadsFiles();
  }

  /**
   * register_deactivation_hook() callback.
   */
  public static function deactivate() {
  }

  /**
   * register_uninstall_hook() callback.
   */
  public static function uninstall() {
    Admin::removeAccessCapability();
  }

  /**
   * Ensures fast 404 responses for missing files in uploads folder.
   */
  public static function ensureUploadsFiles() {
    $uploads_dir = wp_upload_dir(NULL, FALSE)['basedir'];
    // For multisite setups, only process the master folder.
    $uploads_dir = explode('/sites/', $uploads_dir)[0];

    $pathname = $uploads_dir . '/.htaccess';
    $template = file_get_contents(Plugin::getBasePath() . '/conf/.htaccess.uploads');
    static::createOrPrependFile($pathname, $template, "\n");

    if (is_dir(ABSPATH . '.git')) {
      $uploads_dir_relative = substr($uploads_dir, strlen(ABSPATH));

      $pathname = ABSPATH . '.gitignore';
      $template = "/$uploads_dir_relative/*
!/$uploads_dir_relative/.htaccess
";
      static::createOrPrependFile($pathname, $template, FALSE);
    }
  }

  /**
   * Creates or prepends a file with new content.
   *
   * @param string $pathname
   *   The pathname of the file to create or update.
   * @param string $template
   *   The new content to create or prepend.
   * @param string $separator
   *   (optional) A separator to add between the new and original content.
   *   Defaults to an empty string.
   */
  private static function createOrPrependFile($pathname, $template, $separator = '') {
    $write_content = FALSE;
    if (!file_exists($pathname)) {
      $content = $template;
      $write_content = TRUE;
    }
    else {
      $content = file_get_contents($pathname);
      if (FALSE === strpos($content, $template)) {
        $content = $template . $separator . $content;
        $write_content = TRUE;
      }
    }
    if ($write_content) {
      file_put_contents($pathname, $content);
      if (defined('WP_CLI')) {
        \WP_CLI::success(sprintf(__('Security has been hardened in %s.', Plugin::L10N), $pathname));
      }
    }
  }

}
