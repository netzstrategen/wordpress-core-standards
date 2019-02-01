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

    // Ensures arbitrary script execution protection and fast 404 responses for
    // missing files in uploads folder.
    static::ensureUploadsHtaccess();
  }

  /**
   * register_deactivation_hook() callback.
   */
  public static function deactivate() {
    // Remove scheduled revision cleanup cron event.
    wp_clear_scheduled_hook(Admin::CRON_EVENT_REVISION_CLEANUP);
  }

  /**
   * register_uninstall_hook() callback.
   */
  public static function uninstall() {
    Admin::removeAccessCapability();
  }

  /**
   * Ensures arbitrary script execution protection and fast 404 responses for missing files in uploads folder.
   */
  public static function ensureUploadsHtaccess() {
    $uploads_dir = wp_upload_dir(NULL, FALSE)['basedir'];

    // Changes to .htaccess need to be performed in separate steps for each
    // chunk of content that needs to be ensured. Otherwise the existing chunks
    // would be duplicated.
    $pathname = $uploads_dir . '/.htaccess';
    $template = file_get_contents(Plugin::getBasePath() . '/conf/.htaccess.uploads.fast404');
    static::createOrPrependFile($pathname, $template, "\n");
    $template = file_get_contents(Plugin::getBasePath() . '/conf/.htaccess.uploads.noscript');
    static::createOrPrependFile($pathname, $template, "\n");

    // Ensure that .gitignore in the document root tracks the .htaccess file.
    $uploads_dir_relative = substr($uploads_dir, strlen(ABSPATH));
    $pathname = ABSPATH . '.gitignore';
    $template = "/$uploads_dir_relative/*
!/$uploads_dir_relative/.htaccess
";
    static::createOrPrependFile($pathname, $template);
  }

  /**
   * Creates or prepends a file with new content.
   *
   * @param string $pathname
   *   The pathname of the file to create or update.
   * @param string $template
   *   The new content to create or prepend.
   * @param string $separator
   *   (optional) Content to add between the new and original content. Defaults
   *   to an empty string.
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
        $content = file_get_contents($pathname);
        if (FALSE !== strpos($content, $template)) {
          \WP_CLI::warning(sprintf('Security has been hardened in %s. Review this before committing!', $pathname));
        }
        else {
          \WP_CLI::error(sprintf('Failed to harden security in %s. Fix this before proceeding!', $pathname));
        }
      }
    }
  }

  /**
   * Cron event callback to ensure proper database indexes.
   */
  public static function cron_ensure_indexes() {
    self::ensureIndex('options', 'autoload', 'autoload');
    self::ensureIndex('posts', 'type_status_date_gmt', 'post_type, post_status, post_date_gmt');
    self::ensureIndex('posts', 'type_status_comment_count', 'post_type, post_status, comment_count');
    self::ensureIndex('wp_usermeta', 'user_id_meta_key', 'user_id, meta_key');
    self::ensureIndex('posts', 'guid', 'guid');
  }

  /**
   * Helper function to check for index names and create them if needed.
   */
  public static function ensureIndex($table, $index_name, $index_definition) {
    global $wpdb;
    $table = $wpdb->{$table};
    if (!$wpdb->get_var("SHOW INDEX FROM $table WHERE Key_name = '$index_name'")) {
      self::createIndex($table, $index_name, $index_definition);
    }
  }

  /**
   * Helper function to create indexes.
   */
  public static function createIndex($table, $index_name, $index_definition) {
    global $wpdb;
    $wpdb->query("ALTER TABLE $table ADD INDEX $index_name ($index_definition)");
  }

}
