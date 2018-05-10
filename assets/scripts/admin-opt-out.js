'use strict';

(function ($) {
  var $dest = $('#core-standards-optout-script');
  // Restores the default opt-out script if textarea is empty.
  if (!$dest.val().trim().length) {
    load_default_script($dest);
  }

  $('#core-standards-optout-script-restore').on('click', function(e) {
    load_default_script($dest);
  });

  /**
   * Restores the default opt-out script as the
   * given element text content.
   *
   * @param $dest jQuery DOM element.
   */
  function load_default_script($dest) {
    $dest.val(core_standards.admin_optout_script);
  }
})(jQuery)
