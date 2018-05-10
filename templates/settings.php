<?php

namespace Netzstrategen\CoreStandards;

$core_standards_optout_ids = esc_attr(get_option('core_standards_optout_ids'));
$core_standards_optout_script = esc_attr(get_option('core_standards_optout_script'));
$core_standards_optout_active = checked(1, get_option('core_standards_optout_active'), FALSE );
?>
<div class="wrap">
  <h2><?= __('WordPress Core Standards Settings', Plugin::L10N) ?></h2>
  <h3><?= __('Google Analytics Opt-Out', Plugin::L10N) ?></h3>
  <form method="post" action="options.php" class="<?= Plugin::PREFIX ?>-form">
    <?php settings_fields(Plugin::PREFIX . '-settings'); ?>
    <?php do_settings_sections(Plugin::PREFIX . '-settings'); ?>
    <table class="form-table">
      <tr class="form-field">
        <th scope="row"><label for="core-standards-optout-ids"><?= __('Google Analytics IDs', Plugin::L10N) ?></label></th>
        <td>
          <textarea id="core-standards-optout-ids" name="core_standards_optout_ids"><?= $core_standards_optout_ids ?></textarea>
        </td>
      </tr>
      <tr class="form-field">
        <th scope="row"><label for="core-standards-optout-script"><?= __('Google Analytics Opt-Out script', Plugin::L10N) ?></label></th>
        <td>
          <textarea rows="24" id="core-standards-optout-script" name="core_standards_optout_script"><?= $core_standards_optout_script ?></textarea>
          <p>
            <span id="core-standards-optout-script-restore" class="button button-secondary" /><?= __('Restore default script', Plugin::L10N) ?></span>
          </p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="core-standards-optout-active"><?= __('Activate Opt-Out', Plugin::L10N) ?></label></th>
        <td>
        <input type="checkbox" id="core-standards-optout-active" name="core_standards_optout_active" value="1" <?= $core_standards_optout_active ?> />
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
