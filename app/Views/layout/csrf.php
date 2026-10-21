<?php
/**
 * CSRF helper – include once in the <head> of every page, before other scripts.
 *
 * The token is attached automatically to:
 *   - every same-origin XMLHttpRequest / jQuery AJAX call that changes data
 *   - every same-origin fetch() that changes data
 *   - every native <form method="post"> (including form.submit() calls)
 * It deliberately does not rely on $.ajaxSetup(), because jQuery is loaded twice on
 * most pages and a second load discards that configuration.
 */
$csrfHeader = config('Security')->headerName;
?>
<meta name="csrf-token" content="<?= esc(csrf_hash(), 'attr') ?>">
<script>
(function () {
    var TOKEN = <?= json_encode(csrf_hash()) ?>;
    var FIELD = <?= json_encode(csrf_token()) ?>;
    var HEADER = <?= json_encode($csrfHeader) ?>;
    var SAFE = /^(GET|HEAD|OPTIONS)$/i;

    function sameOrigin(url) {
        try { return new URL(url, window.location.href).origin === window.location.origin; }
        catch (e) { return true; }
    }

    // XMLHttpRequest (covers jQuery.ajax). Some libraries replace XMLHttpRequest.prototype.open
    // after this script ran, so the wrapper is (re)installed whenever it is found missing.
    var xhrSend = XMLHttpRequest.prototype.send;
    function installXhr() {
        var current = XMLHttpRequest.prototype.open;
        if (current && current.__oxCsrf) { return; }
        var wrapped = function (method, url) {
            this.__csrf = { method: method, url: url };
            return current.apply(this, arguments);
        };
        wrapped.__oxCsrf = true;
        XMLHttpRequest.prototype.open = wrapped;
    }
    XMLHttpRequest.prototype.send = function () {
        var meta = this.__csrf;
        if (meta && !SAFE.test(meta.method) && sameOrigin(meta.url)) {
            try { this.setRequestHeader(HEADER, TOKEN); } catch (e) { /* already sent */ }
        }
        return xhrSend.apply(this, arguments);
    };
    installXhr();
    document.addEventListener('DOMContentLoaded', installXhr);
    window.addEventListener('load', installXhr);

    // fetch()
    if (window.fetch) {
        var nativeFetch = window.fetch;
        window.fetch = function (input, init) {
            var method = (init && init.method) || (input && input.method) || 'GET';
            var url = typeof input === 'string' ? input : (input && input.url) || '';
            if (!SAFE.test(method) && sameOrigin(url)) {
                init = init || {};
                var headers = new Headers(init.headers || (input && input.headers) || {});
                if (!headers.has(HEADER)) { headers.set(HEADER, TOKEN); }
                init.headers = headers;
            }
            return nativeFetch.call(this, input, init);
        };
    }

    // Native forms
    function addToken(form) {
        if (!form || !form.method || form.method.toLowerCase() !== 'post') { return; }
        if (form.action && !sameOrigin(form.action)) { return; }
        if (form.querySelector('input[name="' + FIELD + '"]')) { return; }
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = FIELD;
        input.value = TOKEN;
        form.appendChild(input);
    }
    document.addEventListener('submit', function (e) { addToken(e.target); }, true);
    var nativeSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () { addToken(this); return nativeSubmit.call(this); };
})();
</script>
