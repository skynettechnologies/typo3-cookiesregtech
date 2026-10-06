/**
 * Open dashboard / Upgrade plan buttons.
 *
 * The module runs inside TYPO3's backend iframe. A form posted with
 * target="_blank" opens a top-level tab, where TYPO3 re-routes the module
 * URL into the backend shell as a GET and the action is lost. So instead
 * the button fetches the one-time sign-in link from the module endpoint
 * and opens it in a new tab directly.
 */
(function () {
  'use strict';

  // Confirmation prompts (inline onsubmit handlers are blocked by the
  // backend's Content-Security-Policy).
  document.addEventListener('submit', function (event) {
    var form = event.target.closest('form[data-crt-confirm]');
    if (form && !window.confirm(form.getAttribute('data-crt-confirm'))) {
      event.preventDefault();
    }
  });

  document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-crt-open]');
    if (!button) {
      return;
    }
    event.preventDefault();

    var endpoint = button.getAttribute('data-crt-endpoint');
    var body = new URLSearchParams();
    body.set('crt_action', 'link');
    if (button.getAttribute('data-crt-open') === 'upgrade') {
      body.set('intent', 'upgrade');
    }

    button.disabled = true;
    fetch(endpoint, { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (payload) {
        if (!payload.url) {
          throw new Error(payload.error || 'Could not create a sign-in link. Try again.');
        }
        var tab = window.open(payload.url, '_blank', 'noopener');
        if (!tab) {
          // Pop-up blocked: navigate here instead so the click still works.
          window.location.href = payload.url;
        }
      })
      .catch(function (error) {
        window.alert(error.message || 'Could not create a sign-in link. Try again.');
      })
      .then(function () { button.disabled = false; });
  });
})();
