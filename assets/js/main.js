/* Uptime Fixer — Main JS */
(function () {
  'use strict';

  /* Privacy-safe GTM dataLayer events. Never include tool input or file data. */
  window.ufxTrack = function (eventName, details) {
    if (!window.UFX_TRACKING || !UFX_TRACKING.enabled || !eventName) return;
    var payload = { event: eventName };
    var allowed = ['tool_slug', 'tool_category', 'action', 'result_state', 'search_length'];
    Object.keys(details || {}).forEach(function (key) {
      if (allowed.indexOf(key) !== -1) payload[key] = details[key];
    });
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(payload);
  };

  /* ── Dark mode toggle ────────────────────────────────── */
  var darkBtn = document.getElementById('at-dark-toggle');
  if (darkBtn) {
    var moonSVG = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
    var sunSVG  = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';

    var iconWrap = darkBtn.querySelector('.at-dark-icon');
    function setDarkIcon() {
      var isDark = document.body.classList.contains('at-dark');
      if (iconWrap) iconWrap.innerHTML = isDark ? sunSVG : moonSVG;
    }
    setDarkIcon();
    darkBtn.addEventListener('click', function () {
      var isDark = document.body.classList.toggle('at-dark');
      document.body.classList.toggle('at-light', !isDark);
      document.cookie = 'alltools_dark=' + (isDark ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax';
      setDarkIcon();
    });
  }

  /* ── Mobile menu ─────────────────────────────────────── */
  var menuBtn = document.getElementById('at-menu-toggle');
  var mobileNav = document.getElementById('at-mobile-nav');
  var mobileOverlay = document.getElementById('at-mobile-overlay');
  var menuClose = document.getElementById('at-menu-close');
  if (menuBtn && mobileNav) {
    function setMenu(open) {
      mobileNav.classList.toggle('is-open', open);
      if (mobileOverlay) mobileOverlay.classList.toggle('is-open', open);
      document.body.classList.toggle('at-menu-open', open);
      mobileNav.setAttribute('aria-hidden', open ? 'false' : 'true');
      mobileNav.inert = !open;
      if (open) { var first = mobileNav.querySelector('button, a'); if(first) first.focus(); }
      else if(mobileNav.contains(document.activeElement)) menuBtn.focus();
      if (mobileOverlay) mobileOverlay.setAttribute('aria-hidden', open ? 'false' : 'true');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }
    setMenu(false);
    menuBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      setMenu(!mobileNav.classList.contains('is-open'));
    });
    if (menuClose) menuClose.addEventListener('click', function () { setMenu(false); });
    if (mobileOverlay) mobileOverlay.addEventListener('click', function () { setMenu(false); });
    mobileNav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () { setMenu(false); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') setMenu(false);
      if(e.key==='Tab' && mobileNav.classList.contains('is-open')) { var items=mobileNav.querySelectorAll('a[href],button:not([disabled])'),first=items[0],last=items[items.length-1]; if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();} }
    });
  }

  /* ── FAQ accordion ───────────────────────────────────── */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.at-faq-q');
    if (!btn) return;
    e.preventDefault();
    var item = btn.parentElement;
    var list = btn.closest('.at-faq-list');
    var isOpen = btn.getAttribute('aria-expanded') === 'true';

    if (list) {
      list.querySelectorAll('.at-faq-q').forEach(function (b) {
        b.setAttribute('aria-expanded', 'false');
      });
      list.querySelectorAll('.at-faq-a').forEach(function (a) {
        a.classList.remove('is-open');
      });
    }
    if (!isOpen) {
      btn.setAttribute('aria-expanded', 'true');
      var ans = btn.nextElementSibling;
      if (ans) ans.classList.add('is-open');
    }
  });

  /* ── Live tool search (homepage) ─────────────────────── */
  var homeSearchForm = document.querySelector('[data-home-tool-search]');
  if (homeSearchForm) {
    var homeSearchInput = homeSearchForm.querySelector('input[type="search"]');
    var homeSearchResults = homeSearchForm.querySelector('.ufx-home-search-results');
    var homeSearchEmpty = homeSearchForm.querySelector('[data-search-empty]');
    var homeSearchIndexNode = homeSearchForm.querySelector('[data-home-tool-index]');
    var homeSearchIndex = [];
    try {
      homeSearchIndex = JSON.parse(homeSearchIndexNode ? homeSearchIndexNode.textContent : '[]');
    } catch (error) {
      homeSearchIndex = [];
    }

    function renderHomeMatches(matches) {
      if (!homeSearchResults) return;
      homeSearchResults.querySelectorAll('[data-tool-search-item]').forEach(function (item) { item.remove(); });
      matches.forEach(function (tool) {
        var link = document.createElement('a');
        var icon = document.createElement('span');
        var title = document.createElement('strong');
        var category = document.createElement('small');
        link.href = tool.url;
        link.setAttribute('data-tool-search-item', '');
        link.className = 'is-match';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = '↗';
        title.textContent = tool.title;
        category.textContent = tool.category;
        link.appendChild(icon);
        link.appendChild(title);
        link.appendChild(category);
        homeSearchResults.insertBefore(link, homeSearchEmpty);
      });
    }

    function closeHomeSearch() {
      if (homeSearchResults) homeSearchResults.classList.remove('is-open');
      if (homeSearchInput) homeSearchInput.setAttribute('aria-expanded', 'false');
    }

    function updateHomeSearch() {
      if (!homeSearchInput || !homeSearchResults) return;
      var query = homeSearchInput.value.toLowerCase().trim();
      var matches = [];
      if (query.length > 0) {
        matches = homeSearchIndex.map(function (tool, order) {
          var title = String(tool.title || '').toLowerCase();
          var category = String(tool.category || '').toLowerCase();
          var summary = String(tool.summary || '').toLowerCase();
          var score = title.indexOf(query) === 0 ? 0 : title.indexOf(query) !== -1 ? 1 : category.indexOf(query) !== -1 ? 2 : summary.indexOf(query) !== -1 ? 3 : 99;
          return { tool: tool, order: order, score: score };
        }).filter(function (match) { return match.score < 99; }).sort(function (a, b) {
          return a.score - b.score || a.order - b.order;
        }).slice(0, 8).map(function (match) { return match.tool; });
      }
      renderHomeMatches(matches);
      if (homeSearchEmpty) homeSearchEmpty.classList.toggle('is-show', query.length > 0 && matches.length === 0);
      homeSearchResults.classList.toggle('is-open', query.length > 0);
      homeSearchInput.setAttribute('aria-expanded', query.length > 0 ? 'true' : 'false');
    }

    if (homeSearchInput) {
      homeSearchInput.addEventListener('input', updateHomeSearch);
      homeSearchInput.addEventListener('focus', updateHomeSearch);
      homeSearchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeHomeSearch();
        if (event.key === 'ArrowDown' && homeSearchResults && homeSearchResults.classList.contains('is-open')) {
          var firstMatch = homeSearchResults.querySelector('a.is-match');
          if (firstMatch) {
            event.preventDefault();
            firstMatch.focus();
          }
        }
      });
    }
    homeSearchForm.addEventListener('submit', function (event) {
      if (!homeSearchInput || !homeSearchInput.value.trim()) {
        event.preventDefault();
        return;
      }
      window.ufxTrack('tool_search', { search_length: homeSearchInput.value.trim().length });
    });
    if (homeSearchResults) {
      homeSearchResults.addEventListener('click', function (event) {
        if (event.target.closest('[data-tool-search-item]')) window.ufxTrack('tool_search_result_click', {});
      });
    }
    document.addEventListener('click', function (event) {
      if (!homeSearchForm.contains(event.target)) closeHomeSearch();
    });
  }

  document.addEventListener('click', function (event) {
    var categoryLink = event.target.closest('[data-tool-category]');
    if (categoryLink) window.ufxTrack('tool_category_click', { tool_category: categoryLink.getAttribute('data-tool-category') || '' });

    var action = event.target.closest('.at-tool-section button, .at-tool-section a.at-btn');
    if (!action || action.closest('[data-ufxots-tool], [data-ufxie-extractor]')) return;
    var label = String(action.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 50);
    var marker = ((action.id || '') + ' ' + (action.className || '') + ' ' + label).toLowerCase();
    var eventName = marker.indexOf('download') !== -1 ? 'tool_download' : marker.indexOf('copy') !== -1 ? 'tool_copy' : 'tool_action';
    window.ufxTrack(eventName, { tool_slug: UFX_TRACKING.toolSlug || '', action: label });
  });

  var searchInput = document.getElementById('at-tool-search');
  var toolCards = document.querySelectorAll('.at-tool-card[data-cat]');
  if (searchInput && toolCards.length) {
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.toLowerCase().trim();
      toolCards.forEach(function (card) {
        var h = card.querySelector('h3');
        var p = card.querySelector('p');
        var txt = ((h ? h.textContent : '') + ' ' + (p ? p.textContent : '')).toLowerCase();
        card.style.display = !q || txt.indexOf(q) !== -1 ? '' : 'none';
      });
    });
    // Prevent submit (don't go to search page if input is empty)
    var form = searchInput.closest('form');
    if (form) {
      form.addEventListener('submit', function (e) {
        if (!searchInput.value.trim()) e.preventDefault();
      });
    }
  }

  /* ── Toast helper ────────────────────────────────────── */
  window.atToast = function (msg, duration) {
    duration = duration || 2500;
    var t = document.getElementById('at-toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('is-show');
    clearTimeout(t._timer);
    t._timer = setTimeout(function () {
      t.classList.remove('is-show');
    }, duration);
  };

  /* ── Smooth scroll for in-page links ─────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href').slice(1);
      if (!id) return;
      var el = document.getElementById(id);
      if (el) {
        e.preventDefault();
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* ── Copy helper for any [data-copy] element ─────────── */
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-copy]');
    if (!el) return;
    var text = el.getAttribute('data-copy');
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () {
        window.atToast && window.atToast('Copied!');
      });
    }
  });

})();
