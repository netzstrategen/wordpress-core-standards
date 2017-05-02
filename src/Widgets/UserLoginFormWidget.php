<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Widgets\UserLoginFormWidget.
 */

namespace Netzstrategen\CoreStandards\Widgets;

use Netzstrategen\CoreStandards\Plugin;
use Netzstrategen\CoreStandards\UserLoginForm;

/**
 * User Login Form widget.
 */
class UserLoginFormWidget extends \WP_Widget {

  public function __construct() {
    parent::__construct(Plugin::PREFIX . '_user-login-form', __('User Login Form', Plugin::L10N));
  }

  /**
   * @implements widgets_init
   *
   * register_widget() directly instantiates the given class, so this file will
   * be loaded on all pages anyway.
   */
  public static function init() {
    register_widget(__CLASS__);
  }

  /**
   * Front-end display of widget.
   */
  public function widget($args, $instance) {
    echo UserLoginForm::getOutput($instance);
  }

  /**
   * Outputs the administrative widget form.
   */
  function form($instance) {
    $instance += [
      'redirect' => NULL,
      'profile_path' => NULL,
      'show_login_link' => 0,
      'show_reset_link' => 0,
      'show_profile_link' => 0,
      'show_logout_link' => 0,
    ];
  ?>
<p>
  <label for="<?= $this->get_field_id('redirect') ?>"><?= __('Login redirect', Plugin::L10N) ?>:</label>
  <input type="text" name="<?= $this->get_field_name('redirect') ?>" value="<?= $instance['redirect'] ?>" class="widefat" id="<?= $this->get_field_id('redirect') ?>">
  <span class="description"><?= __('Leave empty to redirect to current page.', Plugin::L10N) ?></span>
</p>
<p>
  <label for="<?= $this->get_field_id('profile_path') ?>"><?= __('User profile path', Plugin::L10N) ?>:</label>
  <input type="text" name="<?= $this->get_field_name('profile_path') ?>" value="<?= $instance['profile_path'] ?>" class="widefat" id="<?= $this->get_field_id('profile_path') ?>">
  <span class="description"><?= __('Defaults to "user".', Plugin::L10N) ?></span>
</p>
<p>
  <label for="<?= $this->get_field_id('show_login_link') ?>">
    <input type="hidden" name="<?= $this->get_field_name('show_login_link') ?>" value="0">
    <input type="checkbox" name="<?= $this->get_field_name('show_login_link') ?>" value="1" <?= checked($instance['show_login_link']) ?> class="checkbox" id="<?= $this->get_field_id('show_login_link') ?>"> <?= __('Show login link', Plugin::L10N) ?>
  </label>
</p>
<p>
  <label for="<?= $this->get_field_id('show_reset_link') ?>">
    <input type="hidden" name="<?= $this->get_field_name('show_reset_link') ?>" value="0">
    <input type="checkbox" name="<?= $this->get_field_name('show_reset_link') ?>" value="1" <?= checked($instance['show_reset_link']) ?> class="checkbox" id="<?= $this->get_field_id('show_reset_link') ?>"> <?= __('Show reset link', Plugin::L10N) ?>
  </label>
</p>
<p>
  <label for="<?= $this->get_field_id('show_profile_link') ?>">
    <input type="hidden" name="<?= $this->get_field_name('show_profile_link') ?>" value="0">
    <input type="checkbox" name="<?= $this->get_field_name('show_profile_link') ?>" value="1" <?= checked($instance['show_profile_link']) ?> class="checkbox" id="<?= $this->get_field_id('show_profile_link') ?>"> <?= __('Show profile link', Plugin::L10N) ?>
  </label>
</p>
<p>
  <label for="<?= $this->get_field_id('show_logout_link') ?>">
    <input type="hidden" name="<?= $this->get_field_name('show_logout_link') ?>" value="0">
    <input type="checkbox" name="<?= $this->get_field_name('show_logout_link') ?>" value="1" <?= checked($instance['show_logout_link']) ?> class="checkbox" id="<?= $this->get_field_id('show_logout_link') ?>"> <?= __('Show logout link', Plugin::L10N) ?>
  </label>
</p>
  <?php
  }

  /**
   * Sanitizes widget form values as they are saved.
   */
  function update($new_instance, $old_instance) {
    $instance = [];
    $instance['redirect'] = $new_instance['redirect'];
    $instance['profile_path'] = $new_instance['profile_path'];
    $instance['show_login_link'] = $new_instance['show_login_link'];
    $instance['show_reset_link'] = $new_instance['show_reset_link'];
    $instance['show_profile_link'] = $new_instance['show_profile_link'];
    $instance['show_logout_link'] = $new_instance['show_logout_link'];
    return $instance;
  }

}
