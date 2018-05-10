'use strict';

// Disable tracking if the opt-out cookie exists.
for (var i = 0; i < window.gaIdList.length; i++) {
  var disableStr = 'ga-disable-' + window.gaIdList[i];
  if (document.cookie.indexOf(disableStr + '=true') > -1) {
    window[disableStr] = true;
  }
}
/**
 * Sets cookies to indicate Google Analytics tracking should be disabled.
 */
function gaOptout() {
  var disableStr;
  for (var i = 0; i < window.gaIdList.length; i++) {
    disableStr = 'ga-disable-' + window.gaIdList[i];
    document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
    window[disableStr] = true;
  }
}