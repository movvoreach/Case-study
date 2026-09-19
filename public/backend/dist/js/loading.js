/*
 * Global loading system. Plain JavaScript (works on pages without jQuery, e.g. login).
 *
 * Automatic:
 *   - Internal link clicks and form submits  -> top progress bar
 *   - Form submits that go through           -> page overlay (after a short delay) + spinner on the button
 *       GET forms (search / filter) say "Searching..."; add data-loading-target="#results" to mask just
 *       that element instead of the whole page
 *   - Links that reload the same page with a different query string (Reset, filters) -> same "searching" state
 *   - DataTables search / sort / paging      -> mask over the table area only (the search box stays usable)
 *   - jQuery AJAX and fetch()                -> top progress bar
 *   - Double submits of the same form are blocked while it is in flight
 *
 * Opt out / customise with attributes:
 *   data-loading="off"          on a link, form or button
 *   data-loading-text="..."     on a form, link or submit button (message)
 *   data-loading-target="#id"   on a form, link or submit button (mask this element instead of the page)
 *
 * Manual API (window.AppLoading):
 *   start() / done()            top bar (reference counted)
 *   overlay(text, {immediate}) / hideOverlay()
 *   button(el, on = true)       spinner on any button
 *   block(el, on = true, text)  white panel with dots over a card/table (text is for screen readers)
 *   wrap(promise, {overlay})    show loading until the promise settles
 *   reset()                     clear everything (also runs on the fail-safe and back/forward cache restore)
 * fetch(): pass { loading: false } in the init object to skip the bar (e.g. polling).
 */
(function (win, doc) {
    'use strict';
    if (win.AppLoading) return;

    var isKm = doc.documentElement.lang === 'km';
    var CFG = {
        overlayDelay: 150,      // ms before the overlay appears, so quick actions never flash
        failSafeMs: 20000,      // reset everything if the page never navigates (e.g. a file download)
        tableHold: 300,         // ms the table mask stays after the last DataTables draw (no flicker while typing)
        text: isKm ? 'កំពុងដំណើរការ...' : 'Processing...',
        searchText: isKm ? 'កំពុងស្វែងរក...' : 'Searching...'
    };

    /* ------------------------------------------------------------ top progress bar */
    var barEl, fillEl, barValue = 0, barActive = 0, trickleTimer = null, hideTimer = null;

    function ensureBar() {
        if (barEl || !doc.body) return !!barEl;
        barEl = doc.createElement('div');
        barEl.id = 'app-loading-bar';
        barEl.setAttribute('aria-hidden', 'true');
        fillEl = doc.createElement('div');
        fillEl.className = 'app-loading-bar-fill';
        barEl.appendChild(fillEl);
        doc.body.appendChild(barEl);
        return true;
    }
    function setBar(value, animate) {
        barValue = value;
        if (!animate) fillEl.style.transition = 'none';
        fillEl.style.transform = 'scaleX(' + value + ')';
        if (!animate) { void fillEl.offsetWidth; fillEl.style.transition = ''; }
    }
    function start() {
        if (!ensureBar()) return;
        barActive++;
        if (barActive > 1) return;
        clearTimeout(hideTimer);
        clearInterval(trickleTimer);
        barEl.classList.add('is-active');
        setBar(0, false);
        setBar(0.08, true);
        trickleTimer = setInterval(function () {
            if (barValue >= 0.95) return;
            var step = barValue < 0.5 ? 0.06 : barValue < 0.8 ? 0.03 : 0.01;
            setBar(Math.min(0.95, barValue + step * (0.5 + Math.random())), true);
        }, 350);
    }
    function done(force) {
        if (!barEl) return;
        barActive = force ? 0 : Math.max(0, barActive - 1);
        if (barActive > 0) return;
        clearInterval(trickleTimer);
        setBar(1, true);
        hideTimer = setTimeout(function () {
            barEl.classList.remove('is-active');
            hideTimer = setTimeout(function () { setBar(0, false); }, 300);
        }, 220);
    }

    /* ------------------------------------------------------------ page overlay */
    var overlayEl, overlayLabel, overlayTimer = null, overlayFailSafe = null;

    function ensureOverlay() {
        if (overlayEl || !doc.body) return !!overlayEl;
        overlayEl = doc.createElement('div');
        overlayEl.className = 'app-loading-overlay';
        overlayEl.setAttribute('role', 'status');
        overlayEl.setAttribute('aria-live', 'polite');
        overlayEl.innerHTML = '<div class="app-loading-card"><span class="app-dots" aria-hidden="true"><span></span><span></span><span></span><span></span></span><span class="app-loading-text"></span></div>';
        overlayLabel = overlayEl.querySelector('.app-loading-text');
        doc.body.appendChild(overlayEl);
        return true;
    }
    function overlay(text, opts) {
        opts = opts || {};
        if (!ensureOverlay()) return;
        overlayLabel.textContent = text || CFG.text;
        clearTimeout(overlayTimer);
        overlayTimer = setTimeout(function () {
            overlayEl.classList.add('is-visible');
            doc.body.setAttribute('aria-busy', 'true');
        }, opts.immediate ? 0 : CFG.overlayDelay);
        clearTimeout(overlayFailSafe);
        if (!opts.persist) overlayFailSafe = setTimeout(hideOverlay, CFG.failSafeMs);
    }
    function hideOverlay() {
        clearTimeout(overlayTimer);
        clearTimeout(overlayFailSafe);
        if (!overlayEl) return;
        overlayEl.classList.remove('is-visible');
        doc.body.removeAttribute('aria-busy');
    }

    /* ------------------------------------------------------------ button + block spinners */
    function button(el, on) {
        if (!el) return;
        if (on === false) {
            el.classList.remove('app-busy');
            el.removeAttribute('aria-busy');
            el.removeAttribute('aria-disabled');
            var old = el.querySelector(':scope > .app-btn-dots');
            if (old) old.remove();
            return;
        }
        // Pages that already show their own spinner on the button (e.g. lesson studio) keep it.
        if (el.classList.contains('app-busy') || el.querySelector('.app-btn-dots, .ls-spin, .fa-spin')) return;
        el.classList.add('app-busy');
        el.setAttribute('aria-busy', 'true');
        el.setAttribute('aria-disabled', 'true'); // not `disabled`: that would drop the button's own name/value from the POST
        var dots = doc.createElement('span');
        dots.className = 'app-btn-dots';
        dots.setAttribute('aria-hidden', 'true');
        dots.innerHTML = '<span></span><span></span><span></span>';
        el.insertBefore(dots, el.firstChild);
    }

    function block(el, on, text) {
        if (!el) return;
        var mask = el.querySelector(':scope > .app-block-mask');
        if (on === false) {
            if (mask) mask.remove();
            el.classList.remove('app-block');
            return;
        }
        if (mask) return;
        el.classList.add('app-block');
        mask = doc.createElement('div');
        mask.className = 'app-block-mask';
        mask.setAttribute('role', 'status');
        mask.setAttribute('aria-live', 'polite');
        var card = doc.createElement('div');
        card.className = 'app-block-card';
        card.innerHTML = '<span class="app-dots" aria-hidden="true"><span></span><span></span><span></span><span></span></span><span class="app-sr app-block-text"></span>'; // label is read by screen readers only, like DataTables
        card.lastChild.textContent = text || '';
        mask.appendChild(card);
        el.appendChild(mask);
        // Taller than most of the screen: centring would push the panel out of view, so anchor it near the top.
        if (el.offsetHeight > win.innerHeight * 0.7) mask.classList.add('is-tall');
    }

    function wrap(promise, opts) {
        opts = opts || {};
        start();
        if (opts.overlay) overlay(typeof opts.overlay === 'string' ? opts.overlay : undefined);
        var finish = function () { done(); if (opts.overlay) hideOverlay(); };
        return Promise.resolve(promise).then(function (v) { finish(); return v; }, function (e) { finish(); throw e; });
    }

    function reset() {
        hideOverlay();
        done(true);
        [].forEach.call(doc.querySelectorAll('.app-busy'), function (el) { button(el, false); });
        [].forEach.call(doc.querySelectorAll('.app-block'), function (el) { block(el, false); });
        [].forEach.call(doc.querySelectorAll('[data-app-submitting]'), function (f) { f.removeAttribute('data-app-submitting'); });
    }

    /* ------------------------------------------------------------ automatic hooks */
    var failSafeTimer = null;
    function armFailSafe() {
        clearTimeout(failSafeTimer);
        failSafeTimer = setTimeout(reset, CFG.failSafeMs);
    }

    // Mask the element the trigger names (data-loading-target), otherwise dim the whole page.
    function busy(text, targetSelector, persist) {
        var target = targetSelector ? doc.querySelector(targetSelector) : null;
        if (target) block(target, true, text);
        else overlay(text, { persist: persist });
    }

    function skipLink(a) {
        var href = a.getAttribute('href') || '';
        if (!href || href.charAt(0) === '#' || /^(javascript:|mailto:|tel:|blob:|data:)/i.test(href)) return true;
        if (a.hasAttribute('download') || (a.target && a.target !== '_self')) return true;
        if (a.closest('[data-loading="off"]')) return true;
        if (a.closest('[data-toggle], [data-widget], [data-dismiss], [data-bs-toggle], [data-target]')) return true;
        if (a.pathname === win.location.pathname && a.search === win.location.search && a.hash) return true; // in-page anchor
        return false;
    }

    // Links: after every other handler had its chance to cancel the click.
    win.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || skipLink(a)) return;
        start();
        // Same page, different query string = a search / filter / reset of the current list.
        var sameListQuery = a.pathname === win.location.pathname && a.search !== win.location.search;
        var text = a.getAttribute('data-loading-text') || (sameListQuery ? CFG.searchText : null);
        var target = a.getAttribute('data-loading-target');
        if (text || target) busy(text || CFG.searchText, target);
        armFailSafe();
    }, false);

    // Double-submit guard: runs first (capture) and blocks a form that is already in flight.
    win.addEventListener('submit', function (e) {
        var form = e.target;
        if (form && form.getAttribute && form.getAttribute('data-app-submitting') === '1') {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    // Submits: only when nobody cancelled it (e.g. a declined confirm() or failed client validation).
    win.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof win.HTMLFormElement) || e.defaultPrevented) return;
        var submitter = e.submitter || (form.contains(doc.activeElement) && doc.activeElement.matches('button, input[type="submit"]') ? doc.activeElement : null);
        if (form.getAttribute('data-loading') === 'off' || (submitter && submitter.getAttribute('data-loading') === 'off')) return;
        var target = (submitter && submitter.formTarget) || form.target;
        if (target && target !== '_self') return;
        var method = (form.getAttribute('method') || 'get').toLowerCase();
        if (method === 'dialog') return;

        start();
        var isGet = method === 'get';
        var hasFiles = [].some.call(form.querySelectorAll('input[type="file"]'), function (i) { return i.files && i.files.length; });
        var attr = function (name) { return (submitter && submitter.getAttribute(name)) || form.getAttribute(name); };

        form.setAttribute('data-app-submitting', '1');
        busy(attr('data-loading-text') || (isGet ? CFG.searchText : CFG.text), attr('data-loading-target'), hasFiles);
        if (submitter) button(submitter, true);
        if (!hasFiles) armFailSafe(); // big uploads may legitimately take longer than the fail-safe
    }, false);

    // Back/forward cache restores the page exactly as it was left: unlock everything.
    win.addEventListener('pageshow', function (e) { if (e.persisted) reset(); });

    if (win.jQuery) {
        var $doc = win.jQuery(doc);

        // jQuery AJAX
        $doc.on('ajaxSend', function () { start(); }).on('ajaxComplete', function () { done(); });

        // DataTables: mask the table area (not the search box) on every search / sort / page draw.
        // The initial draw is ignored because 'init.dt' fires after it.
        $doc.on('init.dt', function (e) { win.jQuery(e.target).data('appLoadingReady', true); });
        $doc.on('preDraw.dt', function (e) {
            var $table = win.jQuery(e.target);
            if (!$table.data('appLoadingReady') || !e.target.parentNode) return;
            clearTimeout($table.data('appLoadingTimer'));
            block(e.target.parentNode, true, CFG.searchText);
        });
        $doc.on('draw.dt', function (e) {
            var $table = win.jQuery(e.target);
            if (!$table.data('appLoadingReady') || !e.target.parentNode) return;
            clearTimeout($table.data('appLoadingTimer'));
            $table.data('appLoadingTimer', setTimeout(function () { block(e.target.parentNode, false); }, CFG.tableHold));
        });
    }

    // fetch()
    if (typeof win.fetch === 'function' && !win.fetch.__appLoading) {
        var nativeFetch = win.fetch;
        win.fetch = function (input, init) {
            if (init && init.loading === false) return nativeFetch.apply(this, arguments);
            start();
            var request = nativeFetch.apply(this, arguments);
            request.then(function () { done(); }, function () { done(); });
            return request;
        };
        win.fetch.__appLoading = true;
    }

    win.AppLoading = {
        start: start, done: done, overlay: overlay, hideOverlay: hideOverlay,
        button: button, block: block, wrap: wrap, reset: reset
    };
})(window, document);
