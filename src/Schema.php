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
   * Default http headers useful to prevent clickjacking issues.
   * Please note how the value of each header must include the quotes when needed.
   * In case any specific project wants to extend/override these from the config/env
   * Using the constant called CORE_STANDARDS_HTTP_HEADERS.
   */
  const HTTP_RESPONSE_HEADERS = [
    'Content-Security-Policy' => '"frame-ancestors"',
    'Referrer-Policy' => '"no-referrer-when-downgrade"',
    'Strict-Transport-Security' => '"Strict-Transport-Security: max-age=31536000; includeSubDomains"',
    'X-Content-Type-Options' => '"nosniff"',
    'X-XSS-Protection' => '"1; mode=block"',
  ];

  /**
   * register_activation_hook() callback.
   */
  public static function activate() {
    Admin::addAccessCapability();

    // Ensures arbitrary script execution protection.
    static::ensureUploadsHtaccess();

    // Fast 404 responses for missing files in uploads folder.
    static::ensureFast404Response();

    // Rename /wp-login.php into /login.php and deny access to XML-RPC API.
    static::ensureFrontControllerAccess();

    // Force a long client-side caching time for assets with "ver" query string.
    static::ensureAssetsCacheControl();

    // Includes the necessary response headers for all requests in htaccess file.
    static::ensureResponseHeadersHtaccess();
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
   * Ensures dynamic response headers for all requests in htaccess.
   */
  public static function ensureResponseHeadersHtaccess() {
    $headers = self::HTTP_RESPONSE_HEADERS;
    if (defined('CORE_STANDARDS_HTTP_HEADERS') && is_array(CORE_STANDARDS_HTTP_HEADERS)) {
      $headers = array_merge($headers, CORE_STANDARDS_HTTP_HEADERS);
    }
    $template = '# BEGIN core-standards:headers' . PHP_EOL;
    $template .= '<IfModule mod_headers.c>' . PHP_EOL;
    foreach ($headers as $header => $value) {
      $template .= "\tHeader set $header $value" . PHP_EOL;
    }
    $template .= '</IfModule>' . PHP_EOL;
    $template .= '# END core-standards:headers' . PHP_EOL;
    static::createOrPrependFile(ABSPATH . '.htaccess', $template, "\n");
  }

  /**
   * Ensures arbitrary script execution protection.
   */
  public static function ensureUploadsHtaccess() {
    $uploads_dir = wp_upload_dir(NULL, FALSE)['basedir'];

    // Changes to .htaccess need to be performed in separate steps for each
    // chunk of content that needs to be ensured. Otherwise the existing chunks
    // would be duplicated.
    $pathname = $uploads_dir . '/.htaccess';
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
   * Ensures fast 404 responses for missing files in uploads folder.
   */
  public static function ensureFast404Response() {
    $uploads_dir = wp_get_upload_dir()['basedir'];
    $uploads_dir_relative = substr($uploads_dir, strlen(ABSPATH));

    // Changes to .htaccess need to be performed in separate steps for each
    // chunk of content that needs to be ensured. Otherwise the existing chunks
    // would be duplicated.
    $template = file_get_contents(Plugin::getBasePath() . '/conf/.htaccess.fast404');
    $template = str_replace('UPLOAD_DIR_REPLACE', $uploads_dir_relative, $template);
    static::createOrPrependFile(ABSPATH . '.htaccess', $template, "\n");
    $template = file_get_contents(Plugin::getBasePath() . '/conf/remove/.htaccess.uploads.fast404');
    static::removeFromFile($uploads_dir . '/.htaccess', $template);
  }

  /**
   * Ensures no access to slow front controllers and creates route for login.php.
   */
  public static function ensureFrontControllerAccess() {
    $template = file_get_contents(Plugin::getBasePath() . '/conf/.htaccess.security');
    static::createOrPrependFile(ABSPATH . '.htaccess', $template, "\n");
  }

  /**
   * Force a long client-side caching time for assets with "ver" query string.
   */
  public static function ensureAssetsCacheControl() {
    $template = file_get_contents(Plugin::getBasePath() . '/conf/.htaccess.assets.cache');
    static::createOrPrependFile(ABSPATH . '.htaccess', $template, "\n");
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
        // Remove old content if present, looking for the template block's
        // BEGIN and END lines.
        $template_lines = explode("\n", rtrim($template, "\n"));
        $begin = strpos($content, $template_lines[0]);
        $lastLine = end($template_lines);
        $end = strpos($content, $lastLine) + strlen($lastLine);
        if ($begin !== FALSE && $end !== FALSE) {
          $content = substr_replace($content, $template, $begin, $end - $begin + 1);
        }
        else {
          $content = $template . $separator . $content;
        }
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
   * Removes content from a file.
   *
   * @param string $pathname
   *   The pathname of the file to remove from.
   * @param string $template
   *   The content to be removed.
   */
  private static function removeFromFile($pathname, $template) {
    if (!file_exists($pathname)) {
      return;
    }

    $content = file_get_contents($pathname);
    // There is nothing to be removed.
    if (FALSE === strpos($content, $template)) {
      return;
    }

    $content = str_replace($template, '', $content);
    file_put_contents($pathname, $content);
  }

  /**
   * Cron event callback to ensure proper database indexes.
   */
  public static function cron_ensure_indexes() {
    // Core queries all options with autoload=yes on every request, which becomes
    // slower when there are plenty of rows (e.g., due to options that ought to
    // be transients but are not).
    self::ensureIndex('options', 'autoload', 'autoload');

    // Core as well as various plugins attempt to find posts having certain meta
    // values, but there is no index on the values by default as it is a longtext
    // column.
    self::ensureIndex('postmeta', 'meta_value', 'meta_value(60)');

    // Data migration and import scripts and plugins are trying to locate
    // previously migrated content by its UUID, but the GUID column does not have
    // an index.
    self::ensureIndex('posts', 'guid', 'guid');

    // Some code (probably the Dashboard) attempts to retrieve the most commented
    // posts on the site for statistics, but there is no index for this query.
    self::ensureIndex('posts', 'type_status_comment_count', 'post_type, post_status, comment_count');

    // Some code (probably the Dashboard) retrieves the GMT date (only the
    // timestamp without post ID) of the most recent published post of all posts,
    // which takes long with a lot of rows.
    self::ensureIndex('posts', 'type_status_date_gmt', 'post_type, post_status, post_date_gmt');

    // Administrative user listing attempts to filter users by meta fields.
    self::ensureIndex('usermeta', 'user_id_meta_key', 'user_id, meta_key');
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
