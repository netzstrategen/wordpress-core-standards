'use strict';

(function ($) {
  // Triggers out-out cookie creation when click on link.
  $('.core-standards-opt-out-link').on('click', function (e) {
    e.preventDefault();
    gaOptout();
    alert(core_standards_opt_out_confirmation_message);
  });
})(jQuery);
