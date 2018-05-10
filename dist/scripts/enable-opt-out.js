"use strict";

!function(t) {
    t(".core-standards-opt-out-link").on("click", function(t) {
        t.preventDefault(), gaOptout(), alert(core_standards.opt_out_confirmation_message);
    });
}(jQuery);
