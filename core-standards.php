<?php

/*
  Plugin Name: Core Standards
  Version: 2.1.0
  Text Domain: core-standards
  Description: Standard refinements.
  Author: netzstrategen
  Author URI: https://www.netzstrategen.com
  License: GPL-2.0+
  License URI: https://www.gnu.org/licenses/gpl-2.0
*/

namespace Netzstrategen\CoreStandards;

if (!defined('ABSPATH')) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
  exit;
}

/**
 * Loads PSR-4-style plugin classes.
 */
function classloader($class) {
  static $ns_offset;
  if (strpos($class, __NAMESPACE__ . '\\') === 0) {
    if ($ns_offset === NULL) {
      $ns_offset = strlen(__NAMESPACE__) + 1;
    }
    include __DIR__ . '/src/' . strtr(substr($class, $ns_offset), '\\', '/') . '.php';
  }
}
spl_autoload_register(__NAMESPACE__ . '\classloader');

register_activation_hook(__FILE__, __NAMESPACE__ . '\Schema::activate');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\Schema::deactivate');
register_uninstall_hook(__FILE__, __NAMESPACE__ . '\Schema::uninstall');
add_action('wp_upgrade', __NAMESPACE__ . '\Schema::ensureUploadsHtaccess');

add_action('widgets_init', __NAMESPACE__ . '\Widgets\UserLoginFormWidget::init');
add_action('plugins_loaded', __NAMESPACE__ . '\Plugin::plugins_loaded');
add_action('init', __NAMESPACE__ . '\Plugin::init', 20);
add_action('admin_init', __NAMESPACE__ . '\Admin::init');
// Adds settings fields to 'Reading' settings page, after 'Search Engine visibility'.
add_action('admin_init', __NAMESPACE__ . '\TrackingOptOut::admin_init', 9);
