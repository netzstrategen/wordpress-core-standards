"use strict";

function gaOptout() {
    for (var i, t = 0; t < window.gaIdList.length; t++) i = "ga-disable-" + window.gaIdList[t], 
    document.cookie = i + "=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/", window[i] = !0;
}

for (var i = 0; i < window.gaIdList.length; i++) {
    var disableStr = "ga-disable-" + window.gaIdList[i];
    document.cookie.indexOf(disableStr + "=true") > -1 && (window[disableStr] = !0);
}
