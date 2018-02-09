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

    // Ensures uploads folder .htaccess and .gitignore files exists and include the expected content.
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
   * Ensures uploads folder .htaccess and .gitignore files exists and include the expected content.
   */
  public static function ensureUploadsFiles() {
    $upload_dir = explode('sites/', wp_upload_dir(NULL, FALSE)['basedir'])[0];
    $htaccess_dest = $upload_dir . '/.htaccess';

    $uploadsHtaccessRules = [
      "# Turn off all options we don't need.\n" .
      "Options None\n" .
      "Options +FollowSymLinks\n",
      "# Set the catch-all handler to prevent scripts from being executed.\n" .
      "SetHandler Drupal_Security_Do_Not_Remove_See_SA_2006_006\n" .
      "<Files *>\n" .
      "  # Override the handler again if we're run later in the evaluation list.\n" .
      "  SetHandler Drupal_Security_Do_Not_Remove_See_SA_2013_003\n" .
      "</Files>\n",
      "# If we know how to do it safely, disable the PHP engine entirely.\n" .
      "<IfModule mod_php5.c>\n" .
      "  php_flag engine off\n" .
      "</IfModule>\n",
      "# Fast 404 error responses for missing uploaded files, skipping\n" .
      "# WordPress \"File not found\" page rendering.\n" .
      "<IfModule mod_rewrite.c>\n" .
      "  RewriteEngine On\n" .
      "  RewriteCond %{REQUEST_FILENAME} !-f\n" .
      "  RewriteCond %{REQUEST_FILENAME} !-d\n" .
      "  RewriteCond %{REQUEST_FILENAME} !-l\n" .
      "  RewriteRule ^.*$ - [nocase,R=404,L]\n" .
      "</IfModule>\n",
    ];

    $uploadsGitignoreRules = [
      ".htaccess\n",
    ];

    // Tries to update uploads folder .htacces file with the new rules.
    if (FALSE !== static::updateFile($upload_dir . '/.htaccess', $uploadsHtaccessRules)) {
      set_transient('plugin_core-standards_uploads_htacces_updated', 'success', 60);
    }
    else {
      set_transient('plugin_core-standards_uploads_htacces_updated', 'fail', 60);
      if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::error(static::$activationMessages['plugin_core-standards_uploads_htacces_updated']['wpcli_error']);
      }
    }

    // Tries to update uploads folder .gitignore file.
    if (FALSE !== static::updateFile($upload_dir . '/.gitignore', $uploadsGitignoreRules)) {
      set_transient('plugin_core-standards_uploads_gitignore_updated', 'success', 60);
    }
    else {
      set_transient('plugin_core-standards_uploads_gitignore_updated', 'fail', 60);
      if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::error(static::$activationMessages['plugin_core-standards_uploads_gitignore_updated']['wpcli_error']);
      }
    }
  }

  /**
   * Creates a text file with given contents blocks or appends them if the file exists.
   *
   * @param string $file
   *   Full path to the file to create or update.
   * @param array $new_contents
   *   Contents to put into or append to the file divided in blocks.
   *
   * @return mixed
   *   Bytes written to file or FALSE if failed.
   */
  public static function updateFile($file, array $new_contents) {
    if (!file_exists($file)) {
      return file_put_contents($file, $new_contents);
    }

    if (!is_writable($file)) {
      return FALSE;
    }

    // Check if file includes each of the content segments, otherwise append them.
    $file_contents = file_get_contents($file);
    foreach ($new_contents as $content) {
      if (FALSE === strpos($file_contents, $content)) {
        $file_contents .= "\n" . $content;
      }
    }
    return file_put_contents($file, ltrim($file_contents));
  }

  /**
   * @implements admin_notices
   */
  public static function admin_notices() {
    foreach (static::getAdminMessages() as $key => $message) {
      if ('fail' === get_transient($key)) {
        echo static::getAdminMessage($message['admin_error'], 'error');
      }
      delete_transient($key);
    }
  }

  /**
   * Returns a message formatted to be displayed in the admin backend.
   *
   * @param string $text
   *   The contents of the message.
   * @param string $type
   *   Type of notice to display.
   */
  public static function getAdminMessage($text, $type = 'info') {
    return '<div class="notice notice-' . $type . ' is-dismissible"><p>' . $text . '</p></div>';
  }

  /**
   * Returns messages to display in the admin backend.
   */
  public static function getAdminMessages() {
    return [
      'plugin_core-standards_uploads_htacces_updated' => [
        'admin_error' => __('<strong>Core Standards plugin:</strong> could not create or update the uploads .htaccess file.', Plugin::L10N),
        'wpcli_error' => __('Could not create or update the uploads .htaccess file.', Plugin::L10N),
      ],
      'plugin_core-standards_uploads_gitignore_updated' => [
        'admin_error' => __('<strong>Core Standards plugin:</strong> could not create or update the uploads .gitignore file.', Plugin::L10N),
        'wpcli_error' => __('Could not create or update the uploads .gitignore file.', Plugin::L10N),
      ],
    ];
  }

}
