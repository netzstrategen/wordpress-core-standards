"use strict";

function gaOptout() {
    for (var i, o = 0; o < window.gaIdList.length; o++) i = "ga-disable-" + window.gaIdList[o],
    document.cookie = i + "=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/; domain=" + core_standards_opt_out.cookie_domain,
    window[i] = !0;
}

for (var i = 0; i < window.gaIdList.length; i++) {
    var disableStr = "ga-disable-" + window.gaIdList[i];
    document.cookie.indexOf(disableStr + "=true") > -1 && (window[disableStr] = !0);
}
