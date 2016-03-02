<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Admin.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Administrative back-end functionality.
 */
class Admin {

  /**
   * @implements admin_init
   */
  public static function init() {
    static::removeUselessCoreUpdateNagMessages();
  }

  /**
   * Removes impractical WordPress core update nag messages.
   *
   * @see http://www.paulund.co.uk/hide-wordpress-update-notice
   * @see http://premium.wpmudev.org/blog/hide-the-wordpress-update-notification/
   */
  public static function removeUselessCoreUpdateNagMessages() {
    if (!current_user_can('update_core')) {
      // @see wp-admin/includes/admin-filters.php
      remove_action('admin_notices', 'update_nag', 3);
      remove_action('admin_notices', 'maintenance_nag', 10);
    }
  }

}
