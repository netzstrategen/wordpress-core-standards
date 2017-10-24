<?php

/*
  Plugin Name: Core Standards
  Version: 1.13.1
  Text Domain: core-standards
  Description: Standard refinements.
  Author: netzstrategen
  Author URI: http://www.netzstrategen.com
  License: GPL-2.0+
  License URI: http://www.gnu.org/licenses/gpl-2.0
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

add_action('widgets_init', __NAMESPACE__ . '\Widgets\UserLoginFormWidget::init');
add_action('init', __NAMESPACE__ . '\Plugin::init', 20);
add_action('admin_init', __NAMESPACE__ . '\Admin::init');

if (defined('WP_CLI') && WP_CLI) {
  // Subcommands that extend an existing namespace wrongly replace the whole
  // namespace, so each subcommand needs to be registered separately.
  // @see https://make.wordpress.org/cli/handbook/commands-cookbook/#required-registration-arguments
  // @see https://github.com/wp-cli/wp-cli/issues/1767
  // @see https://github.com/wp-cli/wp-cli/issues/1795
  // @see https://github.com/wp-cli/wp-cli/pull/1877
  \WP_CLI::add_command('user export-csv', __NAMESPACE__ . '\Commands\UserExport');
  \WP_CLI::add_command('user import-csv-raw', __NAMESPACE__ . '\Commands\UserImport');
}
