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
    Admin::createUploadsHtaccessFile();
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

}
