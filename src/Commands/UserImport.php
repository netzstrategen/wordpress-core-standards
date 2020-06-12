<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Commands\UserImport.
 */

namespace Netzstrategen\CoreStandards\Commands;

class UserImport extends \WP_CLI_Command {

  /**
   * Import users from a CSV file with no password hashing.
   *
   * Note: You can implement the filter hook 'user_contactmethods' to control
   * which user meta fields are imported.
   *
   * WARNING: Even though the `wp user import-csv` command attempts to disable
   * email notifications for password and email changes by default, your site
   * may still send email notifications during import. Install the plugin
   * disable-emails to ensure no emails are sent out.
   *
   * @subcommand import-csv-raw
   * @synopsis <file>
   */
  public function __invoke(array $args, array $options) {
    $filename = $args[0];

    if (isset($options['send-email'])) {
      \WP_CLI::error('The option --send-email is not supported as it would send hashed passwords in emails to users.', TRUE);
      exit;
    }

    // Inject a stub for the password hashing service of WordPress Core
    // to not re-hash the password on saving.
    $GLOBALS['wp_hasher'] = new class {
      function HashPassword($password) {
        return $password;
      }
    };

    // add_filter('user_contactmethods', function ($user) {
    //   static $keys;
    //   global $wpdb;
    //   if (!isset($keys)) {
    //     $keys = $wpdb->query("");
    //   }
    // });

    \WP_CLI::run_command(array_merge(['user', 'import-csv'], $args), $options);
  }

}
