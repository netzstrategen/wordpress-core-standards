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
    // Copies .htaccess template file into uploads directory.
    static::createUploadsHtaccessFile();
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
   * Ensures .htaccess exists in uploads directory and includes the template file content.
   */
  public static function ensureUploadsHtaccessFile() {
    $upload_dir = explode('sites/', wp_upload_dir(NULL, FALSE)['basedir'])[0];
    copy(dirname(__DIR__) . '/conf/.htaccess.uploads', $upload_dir . '/.htaccess');
  }

}
