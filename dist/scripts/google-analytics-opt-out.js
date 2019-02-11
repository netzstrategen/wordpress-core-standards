"use strict";

function gaOptout() {
    for (var o, i = "undefined" != typeof core_standards_opt_out && void 0 !== core_standards_opt_out.cookie_domain ? core_standards_opt_out.cookie_domain : "." + document.domain, d = 0; d < window.gaIdList.length; d++) o = "ga-disable-" + window.gaIdList[d], 
    document.cookie = o + "=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/; domain=" + i, 
    window[o] = !0;
}

window.gaIdList = window.gaIdList || [];

for (var i = 0; i < window.gaIdList.length; i++) {
    var disableStr = "ga-disable-" + window.gaIdList[i];
    document.cookie.indexOf(disableStr + "=true") > -1 && (window[disableStr] = !0);
}
