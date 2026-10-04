/* Uptime Fixer — Tool implementations */
(function () {
  'use strict';

  var T = function (msg) { if (window.atToast) window.atToast(msg); };
  var $ = function (sel) { return document.querySelector(sel); };
  var $$ = function (sel) { return document.querySelectorAll(sel); };
  var fmt = function (n) { return n.toLocaleString(); };

  /* ════════════════════════════════════════════
     AGE CALCULATOR
  ════════════════════════════════════════════ */
  (function ageCalc() {
    var dob = $('#age-dob');
    if (!dob) return;
    // The current template is handled by ageCalcV2 below; retain this block only
    // for older saved shortcode markup to avoid duplicate click handlers.
    if ($('#age-info-strip')) return;
    var asof = $('#age-asof');
    var btn  = $('#age-calc-btn');
    var friendName = $('#age-friend-name');
    var copyWishBtn = $('#age-copy-wish');
    var shareWishBtn = $('#age-share-wish');
    var calBtn = $('#age-calendar');
    var friendCta = $('#age-friend-cta');
    var friendToggle = $('#age-friend-toggle');
    var birthdayPanel = $('#age-birthday-panel');
    var lastBirthday = null;
    var friendPanelOpen = false;

    function pad2(n) { return n < 10 ? '0' + n : '' + n; }
    function localDateInputValue(d) {
      return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }
    function parseDateInput(v) {
      var parts = (v || '').split('-').map(function (x) { return parseInt(x, 10); });
      if (parts.length !== 3 || parts.some(isNaN)) return null;
      return new Date(parts[0], parts[1] - 1, parts[2]);
    }
    function startOfDay(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
    function daysBetween(a, b) { return Math.max(0, Math.ceil((startOfDay(b) - startOfDay(a)) / 86400000)); }
    function birthdayDateForYear(birth, year) {
      var d = new Date(year, birth.getMonth(), birth.getDate());
      // For Feb 29 birthdays in non-leap years, use Feb 28 instead of rolling to Mar 1.
      if (birth.getMonth() === 1 && birth.getDate() === 29 && d.getMonth() !== 1) {
        d = new Date(year, 1, 28);
      }
      return d;
    }
    function nextBirthdayDate(birth, ref) {
      var next = birthdayDateForYear(birth, ref.getFullYear());
      if (startOfDay(next) < startOfDay(ref)) {
        next = birthdayDateForYear(birth, ref.getFullYear() + 1);
      }
      return next;
    }
    function friendlyDate(d) {
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    function possessiveName() {
      var n = friendName ? friendName.value.trim() : '';
      if (!n) return "Your friend's";
      return /s$/i.test(n) ? n + "'" : n + "'s";
    }
    function buildWishText() {
      if (!lastBirthday) return '';
      var who = possessiveName();
      if (lastBirthday.daysLeft === 0) {
        return who + ' birthday is today! 🎉 Send a warm wish and make their day special.';
      }
      return who + ' birthday is in ' + lastBirthday.daysLeft + ' days — on ' + friendlyDate(lastBirthday.date) + '. Mark the date and get ready to send wishes.';
    }
    function updateBirthdayPreview() {
      if (!lastBirthday) return;
      var preview = $('#age-wish-preview');
      if (preview) preview.textContent = buildWishText();
    }
    function copyText(text) {
      if (!text) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () { T('Copied!'); });
        return;
      }
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.left = '-9999px';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      try { document.execCommand('copy'); T('Copied!'); } catch (e) { T('Copy is not available'); }
      document.body.removeChild(ta);
    }
    function icsDate(d) {
      return d.getFullYear() + pad2(d.getMonth() + 1) + pad2(d.getDate());
    }
    function escapeIcs(v) {
      return String(v || '').replace(/\\/g, '\\\\').replace(/;/g, '\\;').replace(/,/g, '\\,').replace(/\n/g, '\\n');
    }
    function openFriendPanel() {
      if (!lastBirthday) { T('Please calculate the age first'); return; }
      friendPanelOpen = true;
      if (birthdayPanel) birthdayPanel.style.display = 'block';
      if (friendToggle) friendToggle.innerHTML = 'Friend Options Open';
      updateBirthdayPreview();
      if (friendName) friendName.focus();
    }

    function downloadCalendarEvent() {
      if (!lastBirthday) return;
      var title = possessiveName() + ' birthday';
      var start = lastBirthday.date;
      var end = new Date(start.getFullYear(), start.getMonth(), start.getDate() + 1);
      var now = new Date();
      var stamp = now.getUTCFullYear() + pad2(now.getUTCMonth() + 1) + pad2(now.getUTCDate()) + 'T' + pad2(now.getUTCHours()) + pad2(now.getUTCMinutes()) + pad2(now.getUTCSeconds()) + 'Z';
      var body = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Uptime Fixer//Birthday Reminder//EN',
        'BEGIN:VEVENT',
        'UID:' + Date.now() + '@uptimefixer.local',
        'DTSTAMP:' + stamp,
        'DTSTART;VALUE=DATE:' + icsDate(start),
        'DTEND;VALUE=DATE:' + icsDate(end),
        'SUMMARY:' + escapeIcs(title),
        'DESCRIPTION:' + escapeIcs(buildWishText()),
        'END:VEVENT',
        'END:VCALENDAR'
      ].join('\r\n');
      var blob = new Blob([body], { type: 'text/calendar;charset=utf-8' });
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'birthday-reminder.ics';
      document.body.appendChild(a);
      a.click();
      URL.revokeObjectURL(a.href);
      document.body.removeChild(a);
      T('Calendar file created!');
    }

    if (asof) asof.value = localDateInputValue(new Date());

    function calc() {
      if (!dob.value) { T('Please select a date of birth'); return; }
      var birth = parseDateInput(dob.value);
      var ref = asof && asof.value ? parseDateInput(asof.value) : new Date();
      if (!birth || !ref) { T('Please select a valid date'); return; }
      if (startOfDay(birth) > startOfDay(ref)) { T('Date of birth cannot be in the future'); return; }

      var years = ref.getFullYear() - birth.getFullYear();
      var months = ref.getMonth() - birth.getMonth();
      var days = ref.getDate() - birth.getDate();
      if (days < 0) {
        months--;
        var prevMonth = new Date(ref.getFullYear(), ref.getMonth(), 0);
        days += prevMonth.getDate();
      }
      if (months < 0) { years--; months += 12; }

      var tdays = Math.floor((startOfDay(ref) - startOfDay(birth)) / 86400000);
      var tweeks = Math.floor(tdays / 7);
      var tmonths = years * 12 + months;
      var nextBday = nextBirthdayDate(birth, ref);
      var bdayDaysLeft = daysBetween(ref, nextBday);
      lastBirthday = { date: nextBday, daysLeft: bdayDaysLeft };

      $('#age-years').textContent = years;
      $('#age-months').textContent = months;
      $('#age-days').textContent = days;
      $('#age-tmonths').textContent = fmt(tmonths);
      $('#age-tweeks').textContent = fmt(tweeks);
      $('#age-tdays').textContent = fmt(tdays);
      var banner = $('#age-result-banner');
      var alive = $('#age-alive');
      if (banner) banner.style.display = 'flex';
      if (alive) alive.textContent = fmt(tdays);

      if (friendCta) friendCta.style.display = 'flex';
      if (birthdayPanel) birthdayPanel.style.display = friendPanelOpen ? 'block' : 'none';
      if (friendToggle && !friendPanelOpen) {
        friendToggle.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Check Friend Age';
      }
      var daysOut = $('#age-next-bday');
      var dateOut = $('#age-next-bday-date');
      if (daysOut) daysOut.textContent = fmt(bdayDaysLeft);
      if (dateOut) {
        dateOut.textContent = bdayDaysLeft === 0 ? 'Today' : friendlyDate(nextBday);
      }
      if (friendPanelOpen) updateBirthdayPreview();

      // Update "As of" label
      var asofLabel = $('#age-asof-label');
      if (asofLabel) {
        asofLabel.textContent = 'As of ' + friendlyDate(ref);
      }
    }
    if (btn) btn.addEventListener('click', calc);
    if (friendToggle) friendToggle.addEventListener('click', openFriendPanel);
    if (friendName) friendName.addEventListener('input', updateBirthdayPreview);
    if (copyWishBtn) copyWishBtn.addEventListener('click', function () { copyText(buildWishText()); });
    if (shareWishBtn) {
      shareWishBtn.addEventListener('click', function () {
        var text = buildWishText();
        if (!text) return;
        if (navigator.share) {
          navigator.share({ title: 'Birthday Reminder', text: text }).catch(function () {});
        } else {
          copyText(text);
        }
      });
    }
    if (calBtn) calBtn.addEventListener('click', downloadCalendarEvent);
  })();

  /* ════════════════════════════════════════════
     PERCENTAGE CALCULATOR (5 modes)
  ════════════════════════════════════════════ */
  (function pctCalc() {
    var tabs = $('#pct-tabs');
    if (!tabs) return;
    var result = $('#pct-result');
    var copyBtn = $('#pct-copy');
    var currentMode = 'basic';

    tabs.querySelectorAll('.at-tab').forEach(function (t) {
      t.addEventListener('click', function () {
        tabs.querySelectorAll('.at-tab').forEach(function (x) { x.classList.remove('is-active'); });
        t.classList.add('is-active');
        currentMode = t.getAttribute('data-mode');
        $$('.at-pct-mode').forEach(function (m) {
          m.style.display = m.getAttribute('data-mode') === currentMode ? '' : 'none';
        });
        update();
      });
    });

    function update() {
      var x, y, out;
      switch (currentMode) {
        case 'basic':
          x = parseFloat($('#pct-basic-x').value);
          y = parseFloat($('#pct-basic-y').value);
          if (!isNaN(x) && !isNaN(y)) out = (x / 100 * y).toFixed(2);
          break;
        case 'change':
          x = parseFloat($('#pct-change-x').value);
          y = parseFloat($('#pct-change-y').value);
          if (!isNaN(x) && !isNaN(y) && x !== 0) out = ((y - x) / x * 100).toFixed(2) + '%';
          break;
        case 'isof':
          x = parseFloat($('#pct-isof-x').value);
          y = parseFloat($('#pct-isof-y').value);
          if (!isNaN(x) && !isNaN(y) && y !== 0) out = (x / y * 100).toFixed(2) + '%';
          break;
        case 'add':
          x = parseFloat($('#pct-add-x').value);
          y = parseFloat($('#pct-add-y').value);
          if (!isNaN(x) && !isNaN(y)) out = (y + x / 100 * y).toFixed(2);
          break;
        case 'sub':
          x = parseFloat($('#pct-sub-x').value);
          y = parseFloat($('#pct-sub-y').value);
          if (!isNaN(x) && !isNaN(y)) out = (y - x / 100 * y).toFixed(2);
          break;
      }
      result.textContent = out !== undefined ? out : '—';
    }

    $$('.pct-input').forEach(function (i) { i.addEventListener('input', update); });
    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        if (result.textContent && result.textContent !== '—' && navigator.clipboard) {
          navigator.clipboard.writeText(result.textContent).then(function () { T('Copied!'); });
        }
      });
    }
  })();

  /* ════════════════════════════════════════════
     EMI CALCULATOR
  ════════════════════════════════════════════ */
  (function emiCalc() {
    var btn = $('#emi-calc-btn');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var P = parseFloat($('#emi-principal').value);
      var R = parseFloat($('#emi-rate').value);
      var Y = parseFloat($('#emi-years').value);
      if (!P || !R || !Y || P <= 0 || R <= 0 || Y <= 0) {
        T('Please enter valid loan details'); return;
      }
      var r = R / 12 / 100;
      var n = Y * 12;
      var emi = P * r * Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1);
      var total = emi * n;
      var interest = total - P;

      $('#emi-monthly').textContent = '₹' + fmt(Math.round(emi));
      $('#emi-interest').textContent = '₹' + fmt(Math.round(interest));
      $('#emi-total').textContent = '₹' + fmt(Math.round(total));

      // Amortization table
      var tbody = $('#emi-table tbody');
      tbody.innerHTML = '';
      var balance = P;
      for (var m = 1; m <= n; m++) {
        var iThis = balance * r;
        var pThis = emi - iThis;
        balance -= pThis;
        var tr = document.createElement('tr');
        tr.innerHTML = '<td>' + m + '</td><td>₹' + fmt(Math.round(emi)) + '</td><td>₹' + fmt(Math.round(pThis)) + '</td><td>₹' + fmt(Math.round(iThis)) + '</td><td>₹' + fmt(Math.max(0, Math.round(balance))) + '</td>';
        tbody.appendChild(tr);
      }
      T('EMI calculated');
    });
  })();

  /* ════════════════════════════════════════════
     IMAGE CONVERTER
  ════════════════════════════════════════════ */
  (function imageConverter() {
    var dz = $('#ic-dropzone');
    if (!dz) return;
    var fileInput = $('#ic-file');
    var pickBtn = $('#ic-pick-btn');
    var fromLabel = $('#ic-from-label');
    var target = $('#ic-target');
    var convBtn = $('#ic-convert-btn');
    var preview = $('#ic-preview');
    var resultArea = $('#ic-result');
    var dlBtn = $('#ic-download');
    var sourceImg = null;
    var sourceName = '';
    var sourceType = '';
    var outputUrl = '';

    function revokeOutput() {
      if (outputUrl) URL.revokeObjectURL(outputUrl);
      outputUrl = '';
      if (resultArea) resultArea.style.display = 'none';
      if (preview) preview.removeAttribute('src');
    }

    function fail(message) {
      convBtn.disabled = !sourceImg;
      convBtn.textContent = 'Convert Image';
      T(message);
    }

    function pick() { fileInput.click(); }
    if (pickBtn) pickBtn.addEventListener('click', pick);
    dz.addEventListener('click', function (e) { if (e.target === dz || e.target.closest('h3,p:not(.at-dropzone-hint)')) pick(); });

    ['dragover','dragenter'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('is-drag'); });
    });
    ['dragleave','drop'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('is-drag'); });
    });
    dz.addEventListener('drop', function (e) {
      var f = e.dataTransfer.files[0]; if (f) handle(f);
    });
    fileInput.addEventListener('change', function () {
      if (fileInput.files[0]) handle(fileInput.files[0]);
    });

    function handle(file) {
      var normalizedType = file.type === 'image/jpg' ? 'image/jpeg' : file.type;
      if (!normalizedType) {
        var extension = (file.name.match(/\.([^.]+)$/) || [,''])[1].toLowerCase();
        normalizedType = extension === 'jpg' || extension === 'jpeg' ? 'image/jpeg' : extension === 'png' ? 'image/png' : extension === 'webp' ? 'image/webp' : '';
      }
      if (!/^image\/(?:jpeg|png|webp)$/.test(normalizedType) || file.size > 10 * 1024 * 1024) {
        revokeOutput();
        sourceImg = null;
        sourceName = '';
        sourceType = '';
        convBtn.disabled = true;
        T(!/^image\/(?:jpeg|png|webp)$/.test(normalizedType) ? 'Please choose a JPG, PNG, or WebP image' : 'Please choose an image smaller than 10 MB');
        return;
      }
      revokeOutput();
      sourceImg = null;
      convBtn.disabled = true;
      sourceName = file.name;
      sourceType = normalizedType;
      var typeLabel = normalizedType.split('/')[1].toUpperCase().replace('JPEG','JPG');
      if (fromLabel) {
        var span = fromLabel.querySelector('span');
        if (span) span.textContent = typeLabel;
      }
      if (!target.value || target.value === sourceType) {
        target.value = sourceType === 'image/png' ? 'image/webp' : 'image/png';
      }
      var reader = new FileReader();
      reader.onload = function (e) {
        sourceImg = new Image();
        sourceImg.onload = function () {
          var width = sourceImg.naturalWidth || sourceImg.width;
          var height = sourceImg.naturalHeight || sourceImg.height;
          if (!width || !height || width * height > 40000000) {
            sourceImg = null;
            fail('This image is too large to convert safely in the browser');
            return;
          }
          convBtn.disabled = false;
          T('Image loaded — ready to convert to ' + target.options[target.selectedIndex].text);
        };
        sourceImg.onerror = function () { sourceImg = null; fail('The selected image could not be decoded'); };
        sourceImg.src = e.target.result;
      };
      reader.onerror = function () { fail('The selected image could not be read'); };
      reader.readAsDataURL(file);
    }

    convBtn.addEventListener('click', function () {
      if (!sourceImg) { T('Upload an image first'); return; }
      if (!target.value) { T('Choose a target format'); target.focus(); return; }
      revokeOutput();
      convBtn.disabled = true;
      convBtn.textContent = 'Converting…';
      var canvas = document.createElement('canvas');
      canvas.width = sourceImg.naturalWidth || sourceImg.width;
      canvas.height = sourceImg.naturalHeight || sourceImg.height;
      var ctx = canvas.getContext('2d');
      if (!ctx) { fail('Image conversion is not supported in this browser'); return; }
      if (target.value === 'image/jpeg') { ctx.fillStyle = '#fff'; ctx.fillRect(0,0,canvas.width,canvas.height); }
      ctx.drawImage(sourceImg, 0, 0);
      canvas.toBlob(function (blob) {
        if (!blob) { fail('This browser could not export the selected format'); return; }
        outputUrl = URL.createObjectURL(blob);
        preview.src = outputUrl;
        resultArea.style.display = 'block';
        dlBtn.onclick = function () {
          var a = document.createElement('a');
          a.href = outputUrl;
          var ext = target.value.split('/')[1].replace('jpeg','jpg');
          a.download = (sourceName.replace(/\.[^.]+$/,'') || 'converted') + '.' + ext;
          document.body.appendChild(a);
          a.click();
          a.remove();
        };
        convBtn.disabled = false;
        convBtn.textContent = 'Convert Image';
        T('Converted!');
      }, target.value, 0.92);
    });

    target.addEventListener('change', function () {
      if (sourceImg && target.value) T('Target format set to ' + target.options[target.selectedIndex].text);
    });

    window.addEventListener('pagehide', revokeOutput);
  })();

  /* ════════════════════════════════════════════
     IMAGE RESIZER
  ════════════════════════════════════════════ */
  (function imageResizer() {
    var dz = $('#ir-dropzone');
    if (!dz) return;
    var fi = $('#ir-file'), w = $('#ir-w'), h = $('#ir-h'), lock = $('#ir-lock');
    var fmt = $('#ir-format'), q = $('#ir-q'), qv = $('#ir-q-val');
    var btn = $('#ir-resize-btn'), dlBtn = $('#ir-download-btn');
    var preview = $('#ir-preview'), placeholder = $('#ir-placeholder');
    var info = $('#ir-info'), nameEl = $('#ir-name'), sizeEl = $('#ir-size'), origDim = $('#ir-orig-dim');
    var outDim = $('#ir-out-dim'), outFmt = $('#ir-out-fmt'), outSize = $('#ir-out-size'), outQ = $('#ir-out-q');
    var readyBadge = $('#ir-ready-badge'), successBanner = $('#ir-success'), successDim = $('#ir-success-dim');
    var sourceImg = null, sourceName = '', aspect = 0, outputBlob = null;

    dz.addEventListener('click', function () { fi.click(); });
    ['dragover','dragenter'].forEach(function (e){ dz.addEventListener(e, function(ev){ ev.preventDefault(); dz.classList.add('is-drag');}); });
    ['dragleave','drop'].forEach(function (e){ dz.addEventListener(e, function(ev){ ev.preventDefault(); dz.classList.remove('is-drag');}); });
    dz.addEventListener('drop', function(e){ if (e.dataTransfer.files[0]) load(e.dataTransfer.files[0]); });
    fi.addEventListener('change', function(){ if (fi.files[0]) load(fi.files[0]); });

    function load(file) {
      if (!/^image\//.test(file.type)) { T('Please choose an image'); return; }
      sourceName = file.name;
      var reader = new FileReader();
      reader.onload = function(e){
        sourceImg = new Image();
        sourceImg.onload = function(){
          preview.src = sourceImg.src;
          preview.style.display = 'block';
          if (placeholder) placeholder.style.display = 'none';
          if (info) info.style.display = '';
          nameEl.textContent = sourceName;
          sizeEl.textContent = (file.size/1024/1024).toFixed(2) + ' MB';
          origDim.textContent = sourceImg.width + ' × ' + sourceImg.height + ' px';
          aspect = sourceImg.width / sourceImg.height;
          if (!w.value) w.value = sourceImg.width;
          if (!h.value) h.value = sourceImg.height;
          T('Image loaded');
        };
        sourceImg.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    w.addEventListener('input', function(){ if (lock.checked && aspect) h.value = Math.round(parseFloat(w.value)/aspect); });
    h.addEventListener('input', function(){ if (lock.checked && aspect) w.value = Math.round(parseFloat(h.value)*aspect); });
    q.addEventListener('input', function(){ qv.textContent = q.value + '%'; });

    $$('.at-preset').forEach(function(p){
      p.addEventListener('click', function(e){
        e.preventDefault();
        var pw = p.getAttribute('data-w'), ph = p.getAttribute('data-h');
        if (pw && ph) { w.value = pw; h.value = ph; aspect = pw/ph; }
      });
    });

    btn.addEventListener('click', function(){
      if (!sourceImg) { T('Upload an image first'); return; }
      var tw = parseInt(w.value, 10) || sourceImg.width;
      var th = parseInt(h.value, 10) || sourceImg.height;
      var canvas = document.createElement('canvas');
      canvas.width = tw; canvas.height = th;
      var ctx = canvas.getContext('2d');
      if (fmt.value === 'image/jpeg') { ctx.fillStyle = '#fff'; ctx.fillRect(0,0,tw,th); }
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(sourceImg, 0, 0, tw, th);
      canvas.toBlob(function(blob){
        outputBlob = blob;
        preview.src = URL.createObjectURL(blob);
        outDim.textContent = tw + ' × ' + th + ' px';
        outFmt.textContent = fmt.options[fmt.selectedIndex].text;
        outSize.textContent = '~ ' + Math.round(blob.size/1024) + ' KB';
        outQ.textContent = q.value + '%';
        readyBadge.style.display = '';
        successBanner.style.display = 'flex';
        successDim.textContent = tw + ' × ' + th + ' px';
        dlBtn.disabled = false;
        T('Image resized');
      }, fmt.value, parseInt(q.value,10)/100);
    });

    dlBtn.addEventListener('click', function(){
      if (!outputBlob) return;
      var a = document.createElement('a');
      a.href = URL.createObjectURL(outputBlob);
      var ext = fmt.value.split('/')[1].replace('jpeg','jpg');
      a.download = (sourceName.replace(/\.[^.]+$/,'') || 'resized') + '-' + w.value + 'x' + h.value + '.' + ext;
      a.click();
    });
  })();

  /* ════════════════════════════════════════════
     FAVICON GENERATOR
  ════════════════════════════════════════════ */
  (function faviconGen() {
    var dz = $('#fg-dropzone');
    if (!dz) return;
    var fi = $('#fg-file'), pickBtn = $('#fg-pick'), genBtn = $('#fg-generate-btn');
    var dlAll = $('#fg-download-all'), dlEach = $('#fg-download-each'), readyEl = $('#fg-ready');
    var bgColor = $('#fg-bg-color');
    var SIZES = [16,32,48,64,96,128,180,192];
    var sourceImg = null, currentShape = 'square', currentBg = 'transparent';
    var blobs = {};

    pickBtn.addEventListener('click', function(){ fi.click(); });
    dz.addEventListener('click', function(){ fi.click(); });
    ['dragover','dragenter'].forEach(function (e){ dz.addEventListener(e, function(ev){ ev.preventDefault(); dz.classList.add('is-drag');}); });
    ['dragleave','drop'].forEach(function (e){ dz.addEventListener(e, function(ev){ ev.preventDefault(); dz.classList.remove('is-drag');}); });
    dz.addEventListener('drop', function(e){ if (e.dataTransfer.files[0]) load(e.dataTransfer.files[0]); });
    fi.addEventListener('change', function(){ if (fi.files[0]) load(fi.files[0]); });

    $$('input[name="fg-bg"]').forEach(function(r){
      r.addEventListener('change', function(){
        $$('.at-radio-pill').forEach(function(p){ p.classList.remove('is-active'); });
        r.closest('.at-radio-pill').classList.add('is-active');
        currentBg = r.value;
        bgColor.style.display = r.value === 'custom' ? '' : 'none';
        render();
      });
    });
    bgColor.addEventListener('input', render);
    $$('.at-shape').forEach(function(s){
      s.addEventListener('click', function(){
        $$('.at-shape').forEach(function(x){ x.classList.remove('is-active'); });
        s.classList.add('is-active');
        currentShape = s.getAttribute('data-shape');
        render();
      });
    });

    function load(file) {
      var r = new FileReader();
      r.onload = function(e){
        sourceImg = new Image();
        sourceImg.onload = function(){ genBtn.disabled = false; render(); T('Image loaded — click Generate'); };
        sourceImg.src = e.target.result;
      };
      r.readAsDataURL(file);
    }

    function clipShape(ctx, size, shape) {
      ctx.beginPath();
      if (shape === 'circle') {
        ctx.arc(size/2, size/2, size/2, 0, Math.PI*2);
      } else if (shape === 'rounded') {
        var r = size*0.18;
        ctx.moveTo(r,0); ctx.lineTo(size-r,0); ctx.quadraticCurveTo(size,0,size,r);
        ctx.lineTo(size,size-r); ctx.quadraticCurveTo(size,size,size-r,size);
        ctx.lineTo(r,size); ctx.quadraticCurveTo(0,size,0,size-r);
        ctx.lineTo(0,r); ctx.quadraticCurveTo(0,0,r,0);
      } else if (shape === 'squircle') {
        var r2 = size*0.32;
        ctx.moveTo(r2,0); ctx.lineTo(size-r2,0); ctx.quadraticCurveTo(size,0,size,r2);
        ctx.lineTo(size,size-r2); ctx.quadraticCurveTo(size,size,size-r2,size);
        ctx.lineTo(r2,size); ctx.quadraticCurveTo(0,size,0,size-r2);
        ctx.lineTo(0,r2); ctx.quadraticCurveTo(0,0,r2,0);
      } else {
        ctx.rect(0,0,size,size);
      }
      ctx.closePath();
      ctx.clip();
    }

    function render() {
      if (!sourceImg) return;
      blobs = {};
      SIZES.forEach(function(s){
        var canvas = $('#fg-c-' + s);
        canvas.width = s; canvas.height = s;
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0,0,s,s);
        ctx.save();
        clipShape(ctx, s, currentShape);
        if (currentBg !== 'transparent') {
          ctx.fillStyle = bgColor.value;
          ctx.fillRect(0,0,s,s);
        }
        // Fit image inside (contain)
        var ratio = Math.min(s/sourceImg.width, s/sourceImg.height);
        var iw = sourceImg.width * ratio;
        var ih = sourceImg.height * ratio;
        ctx.drawImage(sourceImg, (s-iw)/2, (s-ih)/2, iw, ih);
        ctx.restore();
        canvas.toBlob(function(b){ blobs[s] = b; }, 'image/png');
      });
      readyEl.style.display = 'flex';
      dlAll.disabled = false;
      dlEach.disabled = false;
    }

    genBtn.addEventListener('click', render);

    dlAll.addEventListener('click', function(){
      // No zip lib — download each individually
      SIZES.forEach(function(s){
        if (!blobs[s]) return;
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blobs[s]);
        a.download = 'favicon-' + s + 'x' + s + '.png';
        a.click();
      });
      T('All sizes downloaded');
    });
    dlEach.addEventListener('click', function(){ dlAll.click(); });

    var copyCode = $('#fg-copy-code');
    if (copyCode) {
      copyCode.addEventListener('click', function(){
        var pre = $('#fg-code');
        if (pre && navigator.clipboard) {
          navigator.clipboard.writeText(pre.innerText).then(function(){ T('Code copied!'); });
        }
      });
    }
  })();

  /* ════════════════════════════════════════════
     WORD COUNTER
  ════════════════════════════════════════════ */
  (function wordCounter() {
    var input = $('#wc-input');
    if (!input) return;
    var els = {
      cur: $('#wc-cur'), words: $('#wc-words'), chars: $('#wc-chars'),
      charsNs: $('#wc-chars-ns'), sentences: $('#wc-sentences'),
      paragraphs: $('#wc-paragraphs'), read: $('#wc-read'), speak: $('#wc-speak'),
      unique: $('#wc-unique'), ease: $('#wc-ease'), density: $('#wc-density'),
      wps: $('#wc-wps'), wpp: $('#wc-wpp')
    };

    function update() {
      var t = input.value;
      var chars = t.length;
      var charsNs = t.replace(/\s/g, '').length;
      var wordsArr = t.trim() ? t.trim().split(/\s+/) : [];
      var words = wordsArr.length;
      var sentences = (t.match(/[.!?]+(?:\s|$)/g) || []).length;
      var paragraphs = t.trim() ? t.trim().split(/\n\s*\n/).length : 0;
      var read = Math.max(1, Math.ceil(words / 200));
      var speak = Math.max(1, Math.ceil(words / 130));
      var uniqueArr = wordsArr.map(function(w){ return w.toLowerCase().replace(/[^a-z0-9]/g,''); }).filter(Boolean);
      var unique = (new Set(uniqueArr)).size;

      // Reading ease (rough Flesch-like)
      var avgSyl = wordsArr.length ? wordsArr.reduce(function(s,w){ return s + Math.max(1, (w.match(/[aeiouy]+/gi)||[]).length); },0) / wordsArr.length : 0;
      var avgWps = sentences ? words/sentences : 0;
      var ease = Math.max(0, Math.min(100, Math.round(206.835 - 1.015*avgWps - 84.6*avgSyl)));
      var density = words ? Math.round(unique/words*100) : 0;
      var wps = sentences ? (words/sentences).toFixed(1) : '0';
      var wpp = paragraphs ? (words/paragraphs).toFixed(1) : '0';

      if (els.cur) els.cur.textContent = fmt(chars);
      if (els.words) els.words.textContent = fmt(words);
      if (els.chars) els.chars.textContent = fmt(chars);
      if (els.charsNs) els.charsNs.textContent = fmt(charsNs);
      if (els.sentences) els.sentences.textContent = fmt(sentences);
      if (els.paragraphs) els.paragraphs.textContent = fmt(paragraphs);
      if (els.read) els.read.textContent = words ? read : 0;
      if (els.speak) els.speak.textContent = words ? speak : 0;
      if (els.unique) els.unique.textContent = fmt(unique);
      if (els.ease) els.ease.textContent = ease;
      if (els.density) els.density.textContent = density;
      if (els.wps) els.wps.textContent = wps;
      if (els.wpp) els.wpp.textContent = wpp;
    }
    input.addEventListener('input', update);
    var clear = $('#wc-clear'), copy = $('#wc-copy'), dl = $('#wc-download');
    if (clear) clear.addEventListener('click', function(){ input.value=''; update(); });
    if (copy) copy.addEventListener('click', function(){ if (navigator.clipboard && input.value) navigator.clipboard.writeText(input.value).then(function(){ T('Copied!'); }); });
    if (dl) dl.addEventListener('click', function(){
      var blob = new Blob([input.value], {type:'text/plain'});
      var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'text.txt'; a.click();
    });
    update();
  })();

  /* ════════════════════════════════════════════
     CHARACTER COUNTER
  ════════════════════════════════════════════ */
  (function charCounter() {
    var input = $('#cc-input');
    if (!input) return;
    var counter = $('#cc-counter');
    var limits = { tw: 280, ig: 2200, seo: 160, sms: 160 };

    function update() {
      var t = input.value;
      var total = t.length;
      var nospace = t.replace(/\s/g, '').length;
      var wordsArr = t.trim() ? t.trim().split(/\s+/) : [];
      var words = wordsArr.length;
      var sent = (t.match(/[.!?]+(?:\s|$)/g) || []).length;
      var para = t.trim() ? t.trim().split(/\n\s*\n/).length : 0;
      var read = Math.max(0, Math.ceil(words / 200));

      [['cc-total',total],['cc-nospace',nospace],['cc-words',words],['cc-sent',sent],['cc-para',para],['cc-read',read]].forEach(function(p){
        var el = $('#' + p[0]); if (el) el.textContent = fmt(p[1]);
      });
      if (counter) counter.textContent = total;

      Object.keys(limits).forEach(function(k){
        var lim = limits[k];
        var el = $('#cc-' + k);
        if (el) el.textContent = fmt(total);
        var fill = document.querySelector('.at-limit-fill[data-platform="' + k + '"]');
        var rem  = document.querySelector('.at-limit-rem[data-platform="' + k + '"]');
        if (fill) {
          var pct = Math.min(100, total / lim * 100);
          fill.style.width = pct + '%';
          fill.style.background = total > lim ? '#EF4444' : (total > lim*0.85 ? '#F59E0B' : (k==='tw'?'#10B981':k==='ig'?'#EC4899':k==='seo'?'#2563EB':'#10B981'));
        }
        if (rem) {
          var d = lim - total;
          rem.textContent = (d >= 0 ? fmt(d) + ' characters remaining' : fmt(-d) + ' characters over');
          if (d < 0) rem.classList.add('is-over'); else rem.classList.remove('is-over');
        }
      });
    }
    input.addEventListener('input', update);
    var clear = $('#cc-clear'), pasteBtn = $('#cc-paste'), copy = $('#cc-copy');
    if (clear) clear.addEventListener('click', function(){ input.value=''; update(); });
    if (pasteBtn) pasteBtn.addEventListener('click', function(){
      if (!navigator.clipboard || !navigator.clipboard.readText) { T('Clipboard not available'); return; }
      navigator.clipboard.readText().then(function(t){ input.value=t; update(); T('Pasted'); });
    });
    if (copy) copy.addEventListener('click', function(){ if (input.value && navigator.clipboard) navigator.clipboard.writeText(input.value).then(function(){ T('Copied!'); }); });
    update();
  })();

  /* ════════════════════════════════════════════
     CASE CONVERTER
  ════════════════════════════════════════════ */
  (function caseConverter() {
    var input = $('#case-input');
    if (!input) return;
    var output = $('#case-output');
    var counter = $('#case-ctr');
    var current = 'upper';

    function convert(t, mode) {
      switch (mode) {
        case 'upper': return t.toUpperCase();
        case 'lower': return t.toLowerCase();
        case 'sentence': return t.toLowerCase().replace(/(^\s*\w|[.!?]\s+\w)/g, function(c){ return c.toUpperCase(); });
        case 'title': return t.toLowerCase().replace(/\b\w/g, function(c){ return c.toUpperCase(); });
        case 'capitalize': return t.toLowerCase().replace(/\b\w/g, function(c){ return c.toUpperCase(); });
        case 'toggle': return t.split('').map(function(c){ return c===c.toUpperCase() ? c.toLowerCase() : c.toUpperCase(); }).join('');
        case 'camel': return t.toLowerCase().replace(/[^a-z0-9]+(.)/g, function(_,c){ return c.toUpperCase(); }).replace(/^./, function(c){ return c.toLowerCase(); });
        case 'pascal': return t.toLowerCase().replace(/(?:^|[^a-z0-9]+)([a-z0-9])/g, function(_,c){ return c.toUpperCase(); });
        case 'snake': return t.toLowerCase().trim().replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
        case 'kebab': return t.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
      }
      return t;
    }

    function update() {
      if (counter) counter.textContent = input.value.length;
      output.textContent = input.value ? convert(input.value, current) : 'Your converted text will appear here...';
    }

    $$('.at-case-btn').forEach(function(b){
      b.addEventListener('click', function(){
        $$('.at-case-btn').forEach(function(x){ x.classList.remove('is-active'); });
        b.classList.add('is-active');
        current = b.getAttribute('data-mode');
        update();
      });
    });
    input.addEventListener('input', update);
    var clear = $('#case-clear'), copy = $('#case-copy');
    if (clear) clear.addEventListener('click', function(){ input.value=''; update(); });
    if (copy) copy.addEventListener('click', function(){ if (output.textContent && navigator.clipboard) navigator.clipboard.writeText(output.textContent).then(function(){ T('Copied!'); }); });
  })();

  /* ════════════════════════════════════════════
     REMOVE DUPLICATE LINES
  ════════════════════════════════════════════ */
  (function removeDup() {
    var input = $('#rdl-input');
    if (!input) return;
    var output = $('#rdl-output');
    var caseCk = $('#rdl-case'), trimCk = $('#rdl-trim');

    function stats(el, target) {
      var t = el.value;
      var lines = t ? t.split('\n').length : 0;
      $('#' + target).textContent = fmt(t.length) + ' Characters · ' + fmt(lines) + ' Lines';
    }

    function run() {
      var lines = input.value.split('\n');
      var seen = {}, out = [], dups = 0;
      lines.forEach(function(line){
        var key = trimCk.checked ? line.trim() : line;
        if (!caseCk.checked) key = key.toLowerCase();
        if (seen[key]) { dups++; return; }
        seen[key] = true;
        out.push(trimCk.checked ? line.trim() : line);
      });
      output.value = out.join('\n');
      stats(input, 'rdl-stats');
      stats(output, 'rdl-out-stats');
      $('#rdl-totals').textContent = out.length;
      $('#rdl-dups').textContent = dups;
      $('#rdl-unique').textContent = out.length;
      $('#rdl-success').style.display = 'flex';
      T(dups + ' duplicate line(s) removed');
    }

    input.addEventListener('input', function(){ stats(input, 'rdl-stats'); });
    $('#rdl-run').addEventListener('click', run);
    $('#rdl-copy').addEventListener('click', function(){ if (output.value && navigator.clipboard) navigator.clipboard.writeText(output.value).then(function(){ T('Copied!'); }); });
    $('#rdl-download').addEventListener('click', function(){
      var blob = new Blob([output.value], {type:'text/plain'});
      var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'cleaned.txt'; a.click();
    });
    $('#rdl-clear').addEventListener('click', function(){ input.value=''; output.value=''; stats(input,'rdl-stats'); stats(output,'rdl-out-stats'); $('#rdl-success').style.display='none'; });
    stats(input,'rdl-stats'); stats(output,'rdl-out-stats');
  })();

  /* ════════════════════════════════════════════
     REMOVE EXTRA SPACES
  ════════════════════════════════════════════ */
  (function removeSpaces() {
    var input = $('#res-input');
    if (!input) return;
    var output = $('#res-output');
    var multiCk = $('#res-multi'), trimCk = $('#res-trim'), blankCk = $('#res-blank');

    function stats(el, target) {
      var t = el.value;
      var lines = t ? t.split('\n').length : 0;
      $('#' + target).textContent = fmt(t.length) + ' Characters · ' + fmt(lines) + ' Lines';
    }

    function run() {
      var lines = input.value.split('\n');
      if (multiCk.checked) lines = lines.map(function(l){ return l.replace(/ +/g,' ').replace(/\t+/g,' '); });
      if (trimCk.checked) lines = lines.map(function(l){ return l.trim(); });
      if (blankCk.checked) lines = lines.filter(function(l){ return l !== ''; });
      var result = lines.join('\n');
      var removed = input.value.length - result.length;
      output.value = result;
      stats(input,'res-stats'); stats(output,'res-out-stats');
      $('#res-totals').textContent = lines.length;
      $('#res-removed').textContent = Math.max(0, removed);
      $('#res-clean').textContent = Math.max(0, removed);
      $('#res-success').style.display = 'flex';
      T('Extra spaces removed');
    }

    input.addEventListener('input', function(){ stats(input,'res-stats'); });
    $('#res-run').addEventListener('click', run);
    $('#res-copy').addEventListener('click', function(){ if (output.value && navigator.clipboard) navigator.clipboard.writeText(output.value).then(function(){ T('Copied!'); }); });
    $('#res-download').addEventListener('click', function(){
      var blob = new Blob([output.value], {type:'text/plain'});
      var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'cleaned.txt'; a.click();
    });
    $('#res-clear').addEventListener('click', function(){ input.value=''; output.value=''; stats(input,'res-stats'); stats(output,'res-out-stats'); $('#res-success').style.display='none'; });
    stats(input,'res-stats'); stats(output,'res-out-stats');
  })();

  /* ════════════════════════════════════════════
     QR CODE GENERATOR
  ════════════════════════════════════════════ */
  (function qrGen() {
    var canvas = $('#qr-canvas');
    if (!canvas) return;
    var fg = $('#qr-fg'), bg = $('#qr-bg'), fgHex = $('#qr-fg-hex'), bgHex = $('#qr-bg-hex');
    var fields = $('#qr-fields');
    var step2Label = $('#qr-step2-label');
    var sumType = $('#qr-sum-type'), sumContent = $('#qr-sum-content');
    var logoInput = $('#qr-logo'), logoZone = $('#qr-logo-zone'), logoSize = $('#qr-logo-size'), logoSizeVal = $('#qr-logo-size-val');
    var currentType = 'url';
    var currentStyle = 'square';
    var logoImg = null;

    var fieldDefs = {
      url: [{label:'URL', name:'url', type:'url', placeholder:'https://www.example.com'}],
      text: [{label:'Text', name:'text', type:'textarea', placeholder:'Enter your text...'}],
      email: [
        {label:'Email Address', name:'email', type:'email', placeholder:'name@example.com'},
        {label:'Subject (optional)', name:'subject', type:'text'},
        {label:'Message (optional)', name:'body', type:'textarea'}
      ],
      phone: [{label:'Phone Number', name:'phone', type:'tel', placeholder:'+1234567890'}],
      sms: [
        {label:'Phone Number', name:'phone', type:'tel', placeholder:'+1234567890'},
        {label:'Message', name:'body', type:'textarea'}
      ],
      wifi: [
        {label:'Network Name (SSID)', name:'ssid', type:'text'},
        {label:'Password', name:'password', type:'text'},
        {label:'Encryption', name:'encryption', type:'select', options:['WPA','WEP','nopass']}
      ],
      vcard: [
        {label:'First Name', name:'fn', type:'text'},
        {label:'Last Name', name:'ln', type:'text'},
        {label:'Phone', name:'tel', type:'tel'},
        {label:'Email', name:'email', type:'email'},
        {label:'Organization', name:'org', type:'text'}
      ]
    };

    function buildFields() {
      var defs = fieldDefs[currentType] || [];
      step2Label.textContent = currentType === 'url' ? 'URL' : currentType.charAt(0).toUpperCase() + currentType.slice(1);
      var html = '';
      defs.forEach(function(f){
        html += '<div class="at-field"><label>' + f.label + '</label>';
        if (f.type === 'textarea') {
          html += '<textarea class="at-textarea qr-field" data-name="' + f.name + '" placeholder="' + (f.placeholder||'') + '" rows="3"></textarea>';
        } else if (f.type === 'select') {
          html += '<select class="at-select qr-field" data-name="' + f.name + '">';
          f.options.forEach(function(o){ html += '<option value="' + o + '">' + o + '</option>'; });
          html += '</select>';
        } else {
          html += '<input type="' + f.type + '" class="at-input qr-field" data-name="' + f.name + '" placeholder="' + (f.placeholder||'') + '"' + (currentType==='url' ? ' value="https://www.example.com"' : '') + '>';
        }
        html += '<small class="at-help">' + (currentType==='url' && f.name==='url' ? 'Enter a valid URL including https:// or http://' : '') + '</small></div>';
      });
      fields.innerHTML = html;
      fields.querySelectorAll('.qr-field').forEach(function(el){
        el.addEventListener('input', render);
        el.addEventListener('change', render);
      });
    }

    function buildText() {
      var values = {};
      fields.querySelectorAll('.qr-field').forEach(function(el){ values[el.getAttribute('data-name')] = el.value; });
      switch (currentType) {
        case 'url':
        case 'text': return values.url || values.text || '';
        case 'email':
          var s = values.subject ? 'subject=' + encodeURIComponent(values.subject) : '';
          var b = values.body ? 'body=' + encodeURIComponent(values.body) : '';
          var q = [s,b].filter(Boolean).join('&');
          return 'mailto:' + (values.email || '') + (q ? '?' + q : '');
        case 'phone': return 'tel:' + (values.phone || '');
        case 'sms': return 'smsto:' + (values.phone || '') + ':' + (values.body || '');
        case 'wifi': return 'WIFI:T:' + (values.encryption||'WPA') + ';S:' + (values.ssid||'') + ';P:' + (values.password||'') + ';;';
        case 'vcard':
          return 'BEGIN:VCARD\nVERSION:3.0\nFN:' + (values.fn||'') + ' ' + (values.ln||'') + '\nTEL:' + (values.tel||'') + '\nEMAIL:' + (values.email||'') + '\nORG:' + (values.org||'') + '\nEND:VCARD';
      }
      return '';
    }

    function render() {
      var text = buildText();
      if (!text) {
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0,0,canvas.width,canvas.height);
        return;
      }
      try {
        var qr = qrcode(0, $('#qr-correction') ? $('#qr-correction').value : 'M');
        qr.addData(text);
        qr.make();
        var count = qr.getModuleCount();
        canvas.width = canvas.height = parseInt($('#qr-size') ? $('#qr-size').value : '1024',10);
        var size = canvas.width;
        var cell = size / (count + 8);
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = bg.value;
        ctx.fillRect(0,0,size,size);
        ctx.fillStyle = fg.value;
        for (var r=0; r<count; r++) {
          for (var c=0; c<count; c++) {
            if (qr.isDark(r,c)) {
              if (currentStyle === 'dots' || currentStyle === 'dotsbig' || currentStyle === 'round') {
                ctx.beginPath();
                ctx.arc((c+4)*cell + cell/2, (r+4)*cell + cell/2, cell/2 * (currentStyle==='dotsbig'?0.92:0.85), 0, Math.PI*2);
                ctx.fill();
              } else {
                ctx.fillRect((c+4)*cell, (r+4)*cell, Math.ceil(cell), Math.ceil(cell));
              }
            }
          }
        }
        // Logo
        if (logoImg) {
          var lp = parseInt(logoSize.value,10)/100;
          var ls = size * lp;
          var lx = (size - ls)/2, ly = (size - ls)/2;
          ctx.fillStyle = bg.value;
          ctx.fillRect(lx-4, ly-4, ls+8, ls+8);
          ctx.drawImage(logoImg, lx, ly, ls, ls);
        }
        // Summary
        sumType.textContent = currentType.toUpperCase();
        sumContent.textContent = text.length > 60 ? text.slice(0, 60) + '...' : text;
      } catch (e) { console.warn(e); }
    }

    $$('.at-qr-type').forEach(function(b){
      b.addEventListener('click', function(){
        $$('.at-qr-type').forEach(function(x){ x.classList.remove('is-active'); });
        b.classList.add('is-active');
        currentType = b.getAttribute('data-type');
        buildFields(); render();
      });
    });
    $$('.at-style').forEach(function(s){
      s.addEventListener('click', function(){
        $$('.at-style').forEach(function(x){ x.classList.remove('is-active'); });
        s.classList.add('is-active');
        currentStyle = s.getAttribute('data-style');
        render();
      });
    });
    fg.addEventListener('input', function(){ fgHex.value = fg.value; render(); });
    bg.addEventListener('input', function(){ bgHex.value = bg.value; render(); });
    fgHex.addEventListener('change', function(){ fg.value = fgHex.value; render(); });
    bgHex.addEventListener('change', function(){ bg.value = bgHex.value; render(); });

    logoZone.addEventListener('click', function(){ logoInput.click(); });
    logoInput.addEventListener('change', function(){
      var f = logoInput.files[0]; if (!f) return;
      var r = new FileReader();
      r.onload = function(e){
        logoImg = new Image();
        logoImg.onload = function(){ render(); T('Logo added'); };
        logoImg.src = e.target.result;
      };
      r.readAsDataURL(f);
    });
    logoSize.addEventListener('input', function(){ logoSizeVal.textContent = logoSize.value + '%'; render(); });

    $('#qr-download-png').addEventListener('click', function(){
      if(!buildText()){T('Enter QR content first.');return;}
      var a = document.createElement('a'); a.href = canvas.toDataURL('image/png'); a.download = 'qrcode.png'; a.click();
    });
    $('#qr-download-svg').addEventListener('click', function(){
      var text = buildText(); if (!text) return;
      var qr = qrcode(0, $('#qr-correction') ? $('#qr-correction').value : 'M'); qr.addData(text); qr.make();
      var count = qr.getModuleCount();
      count += 8;
      var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + count + ' ' + count + '"><rect width="' + count + '" height="' + count + '" fill="' + bg.value + '"/>';
      for (var r=0; r<count; r++) for (var c=0; c<count; c++) if (r < count-8 && c < count-8 && qr.isDark(r,c)) svg += '<rect x="' + (c+4) + '" y="' + (r+4) + '" width="1" height="1" fill="' + fg.value + '"/>';
      if(logoImg){var ls=count*(parseInt(logoSize.value,10)/100),lp=(count-ls)/2;svg+='<rect x="'+(lp-1)+'" y="'+(lp-1)+'" width="'+(ls+2)+'" height="'+(ls+2)+'" fill="'+bg.value+'"/><image href="'+logoImg.src+'" x="'+lp+'" y="'+lp+'" width="'+ls+'" height="'+ls+'"/>'; }
      svg += '</svg>';
      var blob = new Blob([svg], {type:'image/svg+xml'});
      var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'qrcode.svg'; a.click();
    });

    ['qr-size','qr-correction'].forEach(function(id){var e=$('#'+id);if(e)e.addEventListener('change',render);});
    buildFields();
    render();
  })();


  /* ════════════════════════════════════════════
     WEBSITE TOOLS — shared API helper
  ════════════════════════════════════════════ */
  function ufxTimedFetch(url, options, timeout) {
    var controller = window.AbortController ? new AbortController() : null;
    var timer = controller ? setTimeout(function(){ controller.abort(); }, timeout || 55000) : null;
    options = options || {};
    if (controller) options.signal = controller.signal;
    return fetch(url, options).then(function(response){
      if (timer) clearTimeout(timer);
      return response;
    }, function(error){
      if (timer) clearTimeout(timer);
      if (error && error.name === 'AbortError') throw new Error('The check took too long. Please try again.');
      throw error;
    });
  }
  function ufxApi(action, data, retried) {
    if (!window.UFX_API || !window.UFX_API.ajax || !window.UFX_API.nonce) {
      return Promise.reject(new Error('Tool API is not loaded. Please refresh the page once.'));
    }
    var body = new URLSearchParams();
    var requestTimeout = /^(?:growth_ai_image|growth_speech)$/.test(action) ? 110000 : 55000;
    body.append('action', 'ufx_' + action);
    body.append('nonce', window.UFX_API.nonce);
    Object.keys(data || {}).forEach(function(k){ body.append(k, data[k]); });
    return ufxTimedFetch(window.UFX_API.ajax, { method: 'POST', body: body, credentials: 'same-origin' }, requestTimeout)
      .then(function(r){
        return r.text().then(function(txt){
          var json;
          try { json = JSON.parse(txt); }
          catch (e) { throw new Error('Server returned an invalid response. Please check WordPress/PHP errors.'); }
          if (!r.ok || !json || !json.success) {
            var msg = (json && json.data && json.data.message) || 'Request failed';
            if (!retried && msg === 'Invalid request.') {
              var nonceBody = new URLSearchParams();
              nonceBody.append('action', 'ufx_nonce');
              return ufxTimedFetch(window.UFX_API.ajax, { method: 'POST', body: nonceBody, credentials: 'same-origin' }, 15000)
                .then(function(nr){ return nr.json(); })
                .then(function(njson){
                  if (njson && njson.success && njson.data && njson.data.nonce) {
                    window.UFX_API.nonce = njson.data.nonce;
                    return ufxApi(action, data, true);
                  }
                  throw new Error(msg);
                });
            }
            throw new Error(msg);
          }
          return json.data;
        });
      });
  }
  // Extra and growth bundles use the same timeout and expired-nonce recovery.
  window.UFX_REQUEST = ufxApi;
  function ufxBusy(btn, busy, label) {
    if (!btn) return;
    if (busy) { btn._html = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<span class="at-spinner"></span> ' + (label || 'Checking...'); }
    else { btn.disabled = false; if (btn._html) btn.innerHTML = btn._html; }
  }
  function ufxNow() {
    var d = new Date();
    return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) + ' ' + d.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
  }

  /* ── Uptime Checker ─────────────────────────────────── */
  (function uptime() {
    var btn = $('#up-check'); if (!btn) return;
    var url = $('#up-url');
    btn.addEventListener('click', function(){
      var u = url.value.trim();
      if (!u) { T('Please enter a URL'); return; }
      ufxBusy(btn, true, 'Checking...');
      ufxApi('uptime', { url: u }).then(function(d){
        ufxBusy(btn, false);
        $('#up-result').style.display = 'block';
        $('#up-stats').style.display = 'grid';
        var circle = $('#up-circle');
        if (d.online) {
          circle.className = 'at-uptime-circle at-uptime-circle-up';
          circle.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
          $('#up-label').textContent = 'Online'; $('#up-label').style.color = '#10B981';
          $('#up-desc').textContent = 'The website is up and running.';
          $('#up-cstatus').textContent = 'Online'; $('#up-cstatus').style.color = '#10B981';
        } else {
          circle.className = 'at-uptime-circle at-uptime-circle-down';
          circle.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
          $('#up-label').textContent = 'Offline'; $('#up-label').style.color = '#EF4444';
          $('#up-desc').textContent = d.message || 'The website is not responding.';
          $('#up-cstatus').textContent = 'Offline'; $('#up-cstatus').style.color = '#EF4444';
        }
        $('#up-rt').textContent = d.response_time + ' ms';
        $('#up-server').textContent = d.server || 'Unknown';
        $('#up-time').textContent = ufxNow();
        $('#up-ip').textContent = d.ip || '—';
        $('#up-avg').textContent = d.response_time + ' ms';
      }).catch(function(e){ ufxBusy(btn, false); T(e.message); });
    });
  })();

  /* ── Speed Test ─────────────────────────────────────── */
  (function speed() {
    var btn = $('#sp-run'); if (!btn) return;
    var url = $('#sp-url');
    function ratingForMs(ms, good, ok) { return ms <= good ? 'Good' : (ms <= ok ? 'Fair' : 'Poor'); }
    function run() {
      var u = url.value.trim();
      if (!u) { T('Please enter a URL'); return; }
      ufxBusy(btn, true, 'Testing...');
      ufxApi('speed', { url: u }).then(function(d){
        ufxBusy(btn, false);
        $('#sp-result').style.display = 'block';
        $('#sp-tested').textContent = 'Tested on ' + ufxNow();
        $('#sp-score').textContent = d.score;
        $('#sp-rating').textContent = d.rating;
        var ring = $('#sp-ring');
        ring.style.strokeDashoffset = (327 - (327 * d.score / 100));
        ring.style.stroke = d.score >= 90 ? '#10B981' : (d.score >= 75 ? '#3B82F6' : (d.score >= 50 ? '#F59E0B' : '#EF4444'));
        $('#sp-load').textContent = d.load_time;
        $('#sp-fcp').textContent = d.fcp;
        $('#sp-lcp').textContent = d.lcp;
        $('#sp-ttfb').textContent = d.ttfb;
        $('#sp-tbt').textContent = d.tbt;
        $('#sp-cls').textContent = d.cls;
        $('#sp-load-r').textContent = ratingForMs(d.load_ms || 0, 1000, 2500);
        $('#sp-ttfb-r').textContent = 'This request';
        $('#sp-fcp-r').textContent = $('#sp-lcp-r').textContent = $('#sp-tbt-r').textContent = $('#sp-cls-r').textContent = 'Browser test required';
        $('#sp-size').textContent = d.page_size;
        $('#sp-req').textContent = d.requests;
      }).catch(function(e){ ufxBusy(btn, false); T(e.message); });
    }
    btn.addEventListener('click', run);
    var again = $('#sp-again'); if (again) again.addEventListener('click', run);
  })();

  /* ── HTTP Status Checker ────────────────────────────── */
  (function http() {
    var btn = $('#hs-check'); if (!btn) return;
    var url = $('#hs-url');
    btn.addEventListener('click', function(){
      var u = url.value.trim();
      if (!u) { T('Please enter a URL'); return; }
      ufxBusy(btn, true);
      ufxApi('http_status', { url: u }).then(function(d){
        ufxBusy(btn, false);
        $('#hs-result').style.display = 'grid';
        var bigEl = $('#hs-status-big');
        bigEl.textContent = d.status + ' ' + (d.message || '');
        var colors = { 'success':'#10B981','redirect':'#3B82F6','client-error':'#F97316','server-error':'#EF4444' };
        bigEl.style.color = colors[d.category] || '#10B981';
        var msgs = {
          'success':'The request was successful.', 'redirect':'The resource has been redirected.',
          'client-error':'There was a problem with the request.', 'server-error':'The server encountered an error.'
        };
        $('#hs-status-msg').textContent = msgs[d.category] || '';
        $('#hs-url-out').textContent = d.url;
        $('#hs-ct').textContent = d.content_type || '—';
        $('#hs-srv').textContent = d.server || '—';
        $('#hs-time').textContent = d.response_time + ' ms';
        var pre = $('#hs-headers');
        var lines = ['HTTP/2 ' + d.status];
        Object.keys(d.headers || {}).forEach(function(k){ lines.push(k + ': ' + d.headers[k]); });
        pre.textContent = lines.join('\n');
      }).catch(function(e){ ufxBusy(btn, false); T(e.message); });
    });
  })();

  /* ── SSL Checker ────────────────────────────────────── */
  (function ssl() {
    var btn = $('#ssl-check'); if (!btn) return;
    var url = $('#ssl-url');
    btn.addEventListener('click', function(){
      var u = url.value.trim();
      if (!u) { T('Please enter a domain'); return; }
      ufxBusy(btn, true);
      ufxApi('ssl', { url: u }).then(function(d){
        ufxBusy(btn, false);
        $('#ssl-result').style.display = 'block';
        $('#ssl-status').textContent = d.valid ? 'Valid' : 'Invalid';
        $('#ssl-status').style.color = d.valid ? '#10B981' : '#EF4444';
        $('#ssl-status-desc').textContent = d.valid ? 'This certificate is valid and trusted.' : 'This certificate is invalid or expired.';
        $('#ssl-from').textContent = d.valid_from;
        $('#ssl-from-time').textContent = d.valid_from_time;
        $('#ssl-to').textContent = d.valid_to;
        $('#ssl-to-time').textContent = d.valid_to_time;
        $('#ssl-days').textContent = d.days_remaining + ' days';
        $('#ssl-host').textContent = d.hostname;
        $('#ssl-issuer').textContent = "Let's Encrypt" === d.issuer ? "Let's Encrypt" : d.issuer;
        $('#ssl-issuer-cn').textContent = d.issuer_cn;
        $('#ssl-sig').textContent = d.signature;
        var chain = $('#ssl-chain'); chain.innerHTML = '';
        if (d.chain && d.chain.length) {
          d.chain.forEach(function(c){
            var li = document.createElement('li');
            li.innerHTML = '<strong>' + (c.name || c.issuer || '—') + '</strong><br><small>Issued by: ' + (c.issuer || '—') + '</small>';
            chain.appendChild(li);
          });
        } else {
          chain.innerHTML = '<li>Chain not available</li>';
        }
      }).catch(function(e){ ufxBusy(btn, false); T(e.message); });
    });
  })();

  /* ── DNS Lookup ─────────────────────────────────────── */
  (function dns() {
    var btn = $('#dns-go'); if (!btn) return;
    var domain = $('#dns-domain');
    var lastData = null;
    var currentFilter = 'all';
    function renderTable() {
      var tbody = $('#dns-tbody'); tbody.innerHTML = '';
      var rows = lastData ? lastData.records.filter(function(r){ return currentFilter === 'all' || r.type === currentFilter; }) : [];
      if (!rows.length) { tbody.innerHTML = '<tr><td colspan="4" class="at-empty">No records found.</td></tr>'; return; }
      var pillColors = {'A':'#D1FAE5;color:#10B981','AAAA':'#EDE9FE;color:#8B5CF6','MX':'#FED7AA;color:#F97316','TXT':'#D1FAE5;color:#10B981','CNAME':'#FCE7F3;color:#EC4899','NS':'#DBEAFE;color:#2563EB'};
      rows.forEach(function(r){
        var tr = document.createElement('tr');
        tr.innerHTML = '<td><span class="at-dns-pill" style="background:' + (pillColors[r.type]||'#DBEAFE;color:#2563EB') + ';">' + r.type + '</span></td><td style="word-break:break-all;">' + r.value + '</td><td>' + r.ttl + '</td><td>' + r.priority + '</td>';
        tbody.appendChild(tr);
      });
    }
    btn.addEventListener('click', function(){
      var d = domain.value.trim();
      if (!d) { T('Please enter a domain'); return; }
      ufxBusy(btn, true);
      ufxApi('dns', { domain: d }).then(function(data){
        ufxBusy(btn, false);
        lastData = data;
        $('#dns-result').style.display = 'block';
        ['all','A','AAAA','MX','TXT','CNAME','NS'].forEach(function(k){
          var c = $('#dns-c-' + k); if (c) c.textContent = data.counts[k] || 0;
        });
        $('#dns-sum-domain').textContent = data.domain;
        $('#dns-sum-ns').textContent = data.nameservers || '—';
        $('#dns-sum-total').textContent = data.total;
        $('#dns-sum-time').textContent = ufxNow();
        currentFilter = 'all';
        $$('#dns-tabs .at-tab').forEach(function(b){ b.classList.toggle('is-active', b.getAttribute('data-dns') === 'all'); });
        renderTable();
      }).catch(function(e){ ufxBusy(btn, false); T(e.message); });
    });
    $$('#dns-tabs .at-tab').forEach(function(b){
      b.addEventListener('click', function(){
        $$('#dns-tabs .at-tab').forEach(function(x){ x.classList.remove('is-active'); });
        b.classList.add('is-active');
        currentFilter = b.getAttribute('data-dns');
        renderTable();
      });
    });
  })();

  /* ── Redirect Checker ───────────────────────────────── */
  (function redirect() {
    var btn = $('#rd-check'); if (!btn) return;
    var url = $('#rd-url');
    btn.addEventListener('click', function(){
      var u = url.value.trim();
      if (!u) { T('Please enter a URL'); return; }
      ufxBusy(btn, true);
      ufxApi('redirect', { url: u }).then(function(d){
        ufxBusy(btn, false);
        $('#rd-result').style.display = 'block';
        $('#rd-hops-badge').textContent = d.hops + ' hops';
        $('#rd-time').textContent = 'Checked on ' + ufxNow();
        var chain = $('#rd-chain'); chain.innerHTML = '';
        d.chain.forEach(function(h, i){
          var isFinal = i === d.chain.length - 1 && h.status < 300;
          var statusColor = h.status >= 200 && h.status < 300 ? '#10B981' : (h.status >= 300 && h.status < 400 ? '#F97316' : '#EF4444');
          var stepLabel = i === 0 ? 'Start' : (isFinal ? 'Final' : (i + 1));
          var stepClass = i === 0 ? 'at-step-start' : (isFinal ? 'at-step-final' : '');
          chain.innerHTML += '<div class="at-redirect-hop ' + stepClass + '"><div class="at-redirect-num">' + stepLabel + '</div><div class="at-redirect-info"><div class="at-redirect-url">' + h.url + '</div><div class="at-redirect-meta"><span>' + ufxNow() + '</span></div></div><div class="at-redirect-status" style="color:' + statusColor + ';">' + h.status + '</div><div class="at-redirect-type">' + h.type + '</div><div class="at-redirect-proto">' + h.protocol + '</div><div class="at-redirect-time">' + h.time + '</div></div>';
        });
        var finalHop = d.chain[d.chain.length - 1];
        $('#rd-final').textContent = finalHop.status + ' ' + (finalHop.type || 'OK');
        $('#rd-type').textContent = d.is_permanent ? 'Permanent' : 'Temporary';
        $('#rd-hops').textContent = d.redirects + ' redirects';
        $('#rd-total-time').textContent = d.total_time + ' ms';
      }).catch(function(e){ ufxBusy(btn, false); T(e.message); });
    });
  })();

  /* ════════════════════════════════════════════
     AGE CALCULATOR v2 — Enhanced
  ════════════════════════════════════════════ */
  (function ageCalcV2() {
    var dob = $('#age-dob');
    if (!dob) return;
    var asof    = $('#age-asof');
    var calcBtn = $('#age-calc-btn');
    var calCta  = $('#age-cal-cta');
    var lastResult = null;

    var WEEKDAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var ZODIAC = [
      {sign:'Capricorn',  dates:'Dec 22 \u2013 Jan 19'},
      {sign:'Aquarius',   dates:'Jan 20 \u2013 Feb 18'},
      {sign:'Pisces',     dates:'Feb 19 \u2013 Mar 20'},
      {sign:'Aries',      dates:'Mar 21 \u2013 Apr 19'},
      {sign:'Taurus',     dates:'Apr 20 \u2013 May 20'},
      {sign:'Gemini',     dates:'May 21 \u2013 Jun 20'},
      {sign:'Cancer',     dates:'Jun 21 \u2013 Jul 22'},
      {sign:'Leo',        dates:'Jul 23 \u2013 Aug 22'},
      {sign:'Virgo',      dates:'Aug 23 \u2013 Sep 22'},
      {sign:'Libra',      dates:'Sep 23 \u2013 Oct 22'},
      {sign:'Scorpio',    dates:'Oct 23 \u2013 Nov 21'},
      {sign:'Sagittarius',dates:'Nov 22 \u2013 Dec 21'}
    ];
    var CHINESE = ['Rat','Ox','Tiger','Rabbit','Dragon','Snake','Horse','Goat','Monkey','Rooster','Dog','Pig'];
    var GENS = [
      {name:'Silent Generation', start:1928,end:1945},
      {name:'Baby Boomers',      start:1946,end:1964},
      {name:'Generation X',      start:1965,end:1980},
      {name:'Millennials',       start:1981,end:1996},
      {name:'Generation Z',      start:1997,end:2012},
      {name:'Generation Alpha',  start:2013,end:2030}
    ];

    function pad2(n){return n<10?'0'+n:''+n;}
    function parseD(v){var p=(v||'').split('-').map(function(x){return parseInt(x,10);});if(p.length!==3||p.some(isNaN))return null;return new Date(p[0],p[1]-1,p[2]);}
    function startD(d){return new Date(d.getFullYear(),d.getMonth(),d.getDate());}
    function dBetween(a,b){return Math.max(0,Math.floor((startD(b)-startD(a))/86400000));}
    function friendlyD(d){return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});}
    function setTxt(id,v){var el=document.getElementById(id);if(el)el.textContent=v;}

    function getZodiac(d) {
      var m=d.getMonth()+1, day=d.getDate();
      if((m===12&&day>=22)||(m===1&&day<=19)) return ZODIAC[0];
      if((m===1&&day>=20)||(m===2&&day<=18)) return ZODIAC[1];
      if((m===2&&day>=19)||(m===3&&day<=20)) return ZODIAC[2];
      if((m===3&&day>=21)||(m===4&&day<=19)) return ZODIAC[3];
      if((m===4&&day>=20)||(m===5&&day<=20)) return ZODIAC[4];
      if((m===5&&day>=21)||(m===6&&day<=20)) return ZODIAC[5];
      if((m===6&&day>=21)||(m===7&&day<=22)) return ZODIAC[6];
      if((m===7&&day>=23)||(m===8&&day<=22)) return ZODIAC[7];
      if((m===8&&day>=23)||(m===9&&day<=22)) return ZODIAC[8];
      if((m===9&&day>=23)||(m===10&&day<=22)) return ZODIAC[9];
      if((m===10&&day>=23)||(m===11&&day<=21)) return ZODIAC[10];
      return ZODIAC[11];
    }
    function getChinese(y){return CHINESE[((y-4)%12+12)%12];}
    function getGen(y){for(var i=0;i<GENS.length;i++){if(y>=GENS[i].start&&y<=GENS[i].end)return GENS[i];}return null;}
    function nextBday(birth,ref){
      var n=new Date(ref.getFullYear(),birth.getMonth(),birth.getDate());
      if(birth.getMonth()===1&&birth.getDate()===29&&n.getMonth()!==1)n=new Date(ref.getFullYear(),1,28);
      if(startD(n)<startD(ref))n=new Date(ref.getFullYear()+1,birth.getMonth(),birth.getDate());
      return n;
    }

    if(asof) asof.value=(function(){var d=new Date();return d.getFullYear()+'-'+pad2(d.getMonth()+1)+'-'+pad2(d.getDate());})();

    function runCalc() {
      if(!dob.value){T('Please select a date of birth');return;}
      var birth=parseD(dob.value);
      var ref=asof&&asof.value?parseD(asof.value):new Date();
      if(!birth||!ref){T('Please select valid dates');return;}
      if(startD(birth)>startD(ref)){T('Date of birth cannot be in the future');return;}

      var yr=ref.getFullYear()-birth.getFullYear();
      var mo=ref.getMonth()-birth.getMonth();
      var da=ref.getDate()-birth.getDate();
      if(da<0){mo--;var pm=new Date(ref.getFullYear(),ref.getMonth(),0);da+=pm.getDate();}
      if(mo<0){yr--;mo+=12;}
      var tdays=Math.floor((startD(ref)-startD(birth))/86400000);
      var tweeks=Math.floor(tdays/7);
      var thours=tdays*24;
      var tmins=thours*60;

      setTxt('age-years', yr);
      setTxt('age-months', mo);
      setTxt('age-days', da);
      setTxt('age-tweeks', fmt(tweeks));
      setTxt('age-thours', fmt(thours));
      setTxt('age-tmins', fmt(tmins));
      setTxt('age-alive', fmt(tdays));

      var banner=document.getElementById('age-result-banner');
      if(banner) banner.style.display='flex';

      var asofLbl=document.getElementById('age-asof-label');
      if(asofLbl) asofLbl.textContent='as of '+friendlyD(ref);

      var strip=document.getElementById('age-info-strip');
      if(strip) strip.style.display='block';

      var nb=nextBday(birth,ref);
      var nbDays=dBetween(ref,nb);
      setTxt('age-bday-days', nbDays===0?'Today! \uD83C\uDF89':fmt(nbDays)+' days');
      setTxt('age-bday-date', nbDays===0?'Happy Birthday!':friendlyD(nb));

      setTxt('age-born-weekday', WEEKDAYS[birth.getDay()]);
      setTxt('age-next-weekday', 'Next birthday is a '+WEEKDAYS[nb.getDay()]);

      var z=getZodiac(birth);
      setTxt('age-zodiac', z.sign);
      setTxt('age-zodiac-dates', z.dates);

      var cz=getChinese(birth.getFullYear());
      setTxt('age-chinese-zodiac', cz);
      setTxt('age-chinese-year', 'Year of the '+cz);

      var gen=getGen(birth.getFullYear());
      if(gen){setTxt('age-generation',gen.name);setTxt('age-generation-range','('+gen.start+' \u2013 '+gen.end+')');}

      var lifePct=Math.min(100,(yr/90)*100);
      setTxt('age-life-pct', lifePct.toFixed(2)+'%');
      var fill=document.getElementById('age-life-fill');if(fill)fill.style.width=lifePct+'%';
      var dot=document.getElementById('age-life-dot');if(dot)dot.style.left=Math.min(99,lifePct)+'%';

      lastResult={birth:birth,nb:nb,nbDays:nbDays,yr:yr,mo:mo,da:da,tdays:tdays,tweeks:tweeks,thours:thours,tmins:tmins,zodiac:z.sign,lifePct:lifePct.toFixed(2)};

      try{sessionStorage.setItem('ufx_age_result',JSON.stringify(lastResult));}catch(e){}
    }

    if(calcBtn) calcBtn.addEventListener('click', runCalc);

    /* Age Difference Calculator */
    var diffFrom=document.getElementById('diff-from');
    var diffTo=document.getElementById('diff-to');
    var diffBtn=document.getElementById('diff-calc');
    if(diffBtn){
      if(diffFrom) diffFrom.value=(function(){var d=new Date(new Date().getFullYear()-35,0,1);return d.getFullYear()+'-'+pad2(d.getMonth()+1)+'-'+pad2(d.getDate());})();
      if(diffTo){var dd=new Date();diffTo.value=dd.getFullYear()+'-'+pad2(dd.getMonth()+1)+'-'+pad2(dd.getDate());}
      diffBtn.addEventListener('click', function(){
        var a=parseD(diffFrom&&diffFrom.value?diffFrom.value:'');
        var b=parseD(diffTo&&diffTo.value?diffTo.value:'');
        if(!a||!b){T('Please select both dates');return;}
        if(startD(a)>startD(b)){T('From date must be before To date');return;}
        var yr2=b.getFullYear()-a.getFullYear(),mo2=b.getMonth()-a.getMonth(),da2=b.getDate()-a.getDate();
        if(da2<0){mo2--;var pm=new Date(b.getFullYear(),b.getMonth(),0);da2+=pm.getDate();}
        if(mo2<0){yr2--;mo2+=12;}
        var td2=Math.floor((startD(b)-startD(a))/86400000);
        setTxt('diff-years',yr2);setTxt('diff-months',mo2);setTxt('diff-days',da2);setTxt('diff-tdays',fmt(td2));
        setTxt('diff-label','Difference between '+friendlyD(a)+' and '+friendlyD(b));
        var dr=document.getElementById('diff-result');if(dr)dr.style.display='block';
      });
    }

    /* Calendar CTA */
    if(calCta){
      calCta.addEventListener('click',function(){
        if(!lastResult){T('Please calculate your age first');return;}
        function icsD(d){return d.getFullYear()+pad2(d.getMonth()+1)+pad2(d.getDate());}
        var nb=lastResult.nb;
        var now=new Date();
        var stamp=now.getUTCFullYear()+pad2(now.getUTCMonth()+1)+pad2(now.getUTCDate())+'T000000Z';
        var body=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Uptime Fixer//Birthday//EN','BEGIN:VEVENT','UID:'+Date.now()+'@uptimefixer','DTSTAMP:'+stamp,'DTSTART;VALUE=DATE:'+icsD(nb),'DTEND;VALUE=DATE:'+icsD(new Date(nb.getFullYear(),nb.getMonth(),nb.getDate()+1)),'SUMMARY:My Birthday \uD83C\uDF82','RRULE:FREQ=YEARLY','END:VEVENT','END:VCALENDAR'].join('\r\n');
        var bl=new Blob([body],{type:'text/calendar;charset=utf-8'});
        var a=document.createElement('a');a.href=URL.createObjectURL(bl);a.download='my-birthday.ics';document.body.appendChild(a);a.click();URL.revokeObjectURL(a.href);document.body.removeChild(a);
        T('Calendar file created!');
      });
    }
  })();

  /* ════════════════════════════════════════════
     CHECK FRIEND'S AGE
  ════════════════════════════════════════════ */
  (function friendAge() {
    var btn=document.getElementById('fa-check-btn');if(!btn)return;
    var nameIn=document.getElementById('fa-name');
    var dobIn=document.getElementById('fa-dob');
    var asofIn=document.getElementById('fa-asof');
    var WDAYS=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var ZOD=[{sign:'Capricorn',dates:'Dec 22 \u2013 Jan 19'},{sign:'Aquarius',dates:'Jan 20 \u2013 Feb 18'},{sign:'Pisces',dates:'Feb 19 \u2013 Mar 20'},{sign:'Aries',dates:'Mar 21 \u2013 Apr 19'},{sign:'Taurus',dates:'Apr 20 \u2013 May 20'},{sign:'Gemini',dates:'May 21 \u2013 Jun 20'},{sign:'Cancer',dates:'Jun 21 \u2013 Jul 22'},{sign:'Leo',dates:'Jul 23 \u2013 Aug 22'},{sign:'Virgo',dates:'Aug 23 \u2013 Sep 22'},{sign:'Libra',dates:'Sep 23 \u2013 Oct 22'},{sign:'Scorpio',dates:'Oct 23 \u2013 Nov 21'},{sign:'Sagittarius',dates:'Nov 22 \u2013 Dec 21'}];
    var GENS=[{name:'Silent Generation',start:1928,end:1945},{name:'Baby Boomers',start:1946,end:1964},{name:'Generation X',start:1965,end:1980},{name:'Millennials',start:1981,end:1996},{name:'Generation Z',start:1997,end:2012},{name:'Generation Alpha',start:2013,end:2030}];

    function pad2(n){return n<10?'0'+n:''+n;}
    function parseD(v){var p=(v||'').split('-').map(function(x){return parseInt(x,10);});if(p.length!==3||p.some(isNaN))return null;return new Date(p[0],p[1]-1,p[2]);}
    function startD(d){return new Date(d.getFullYear(),d.getMonth(),d.getDate());}
    function dBetween(a,b){return Math.max(0,Math.floor((startD(b)-startD(a))/86400000));}
    function friendlyD(d){return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});}
    function setTxt(id,v){var el=document.getElementById(id);if(el)el.textContent=v;}
    function getZ(d){var m=d.getMonth()+1,day=d.getDate();if((m===12&&day>=22)||(m===1&&day<=19))return ZOD[0];if((m===1&&day>=20)||(m===2&&day<=18))return ZOD[1];if((m===2&&day>=19)||(m===3&&day<=20))return ZOD[2];if((m===3&&day>=21)||(m===4&&day<=19))return ZOD[3];if((m===4&&day>=20)||(m===5&&day<=20))return ZOD[4];if((m===5&&day>=21)||(m===6&&day<=20))return ZOD[5];if((m===6&&day>=21)||(m===7&&day<=22))return ZOD[6];if((m===7&&day>=23)||(m===8&&day<=22))return ZOD[7];if((m===8&&day>=23)||(m===9&&day<=22))return ZOD[8];if((m===9&&day>=23)||(m===10&&day<=22))return ZOD[9];if((m===10&&day>=23)||(m===11&&day<=21))return ZOD[10];return ZOD[11];}
    function getG(y){for(var i=0;i<GENS.length;i++){if(y>=GENS[i].start&&y<=GENS[i].end)return GENS[i];}return null;}
    function nextBday(birth,ref){var n=new Date(ref.getFullYear(),birth.getMonth(),birth.getDate());if(startD(n)<startD(ref))n=new Date(ref.getFullYear()+1,birth.getMonth(),birth.getDate());return n;}

    if(asofIn){var dd=new Date();asofIn.value=dd.getFullYear()+'-'+pad2(dd.getMonth()+1)+'-'+pad2(dd.getDate());}

    var lastRes=null;

    btn.addEventListener('click',function(){
      if(!dobIn||!dobIn.value){T('Please enter date of birth');return;}
      var birth=parseD(dobIn.value);
      var ref=asofIn&&asofIn.value?parseD(asofIn.value):new Date();
      if(!birth||!ref){T('Please enter valid dates');return;}
      if(startD(birth)>startD(ref)){T('Date of birth cannot be in the future');return;}

      var name=(nameIn&&nameIn.value.trim())||'Friend';
      var yr=ref.getFullYear()-birth.getFullYear(),mo=ref.getMonth()-birth.getMonth(),da=ref.getDate()-birth.getDate();
      if(da<0){mo--;var pm=new Date(ref.getFullYear(),ref.getMonth(),0);da+=pm.getDate();}
      if(mo<0){yr--;mo+=12;}
      var tdays=Math.floor((startD(ref)-startD(birth))/86400000);

      var empty=document.getElementById('fa-empty-state');if(empty)empty.style.display='none';
      var results=document.getElementById('fa-results');if(results)results.style.display='block';

      var av=document.getElementById('fa-avatar');if(av)av.textContent=name.charAt(0).toUpperCase();
      setTxt('fa-display-name',name);setTxt('fa-display-name2',name);
      setTxt('fa-born-on',friendlyD(birth));
      setTxt('fa-years',yr);setTxt('fa-months',mo);setTxt('fa-days',da);setTxt('fa-tdays',fmt(tdays));
      setTxt('fa-alive',fmt(tdays));
      setTxt('fa-today-label','Today: '+friendlyD(ref));

      var strip=document.getElementById('fa-info-strip');if(strip)strip.style.display='block';

      var nb=nextBday(birth,ref);
      var nbDays=dBetween(ref,nb);
      setTxt('fa-bday-who',name+"'s next birthday is in");
      setTxt('fa-bday-days',nbDays===0?'Today! \uD83C\uDF89':fmt(nbDays)+' days');
      setTxt('fa-bday-date',nbDays===0?'Happy Birthday!':friendlyD(nb));
      setTxt('fa-born-who',name+' was born on a');
      setTxt('fa-born-weekday',WDAYS[birth.getDay()]);
      setTxt('fa-next-weekday',"It's also a "+WDAYS[nb.getDay()]);
      var z=getZ(birth);
      setTxt('fa-zodiac-who',name+"'s zodiac sign is");
      setTxt('fa-zodiac',z.sign);setTxt('fa-zodiac-dates',z.dates);
      var gen=getG(birth.getFullYear());
      if(gen){setTxt('fa-gen-who',name+' belongs to');setTxt('fa-generation',gen.name);setTxt('fa-generation-range','('+gen.start+' \u2013 '+gen.end+')');}
      var lp=Math.min(100,(yr/90)*100);
      setTxt('fa-life-pct',lp.toFixed(2)+'%');
      setTxt('fa-life-sub',name+"'s journey so far");
      var fl=document.getElementById('fa-life-fill');if(fl)fl.style.width=lp+'%';
      var dl=document.getElementById('fa-life-dot');if(dl)dl.style.left=Math.min(99,lp)+'%';

      var cta=document.getElementById('fa-cta-bar');if(cta)cta.style.display='flex';

      lastRes={name:name,birth:birth,nb:nb,nbDays:nbDays,yr:yr,mo:mo,da:da,tdays:tdays};

      var copyBtn=document.getElementById('fa-copy-btn');
      if(copyBtn) copyBtn.onclick=function(){
        var txt=name+"'s Age: "+yr+' years, '+mo+' months, '+da+' days ('+fmt(tdays)+' total days). Next birthday in '+fmt(nbDays)+' days ('+friendlyD(nb)+').';
        if(navigator.clipboard){navigator.clipboard.writeText(txt).then(function(){T('Copied!');});}
      };
      var shareBtn=document.getElementById('fa-share-btn');
      if(shareBtn) shareBtn.onclick=function(){
        var txt=name+"'s Age:\n"+yr+' years, '+mo+' months, '+da+' days\nTotal days: '+fmt(tdays)+'\nNext birthday in '+fmt(nbDays)+' days';
        if(navigator.share){navigator.share({title:name+"'s Age",text:txt}).catch(function(){});}
        else if(navigator.clipboard){navigator.clipboard.writeText(txt).then(function(){T('Copied to clipboard!');});}
      };
      var calBtn=document.getElementById('fa-cal-btn');
      if(calBtn) calBtn.onclick=function(){
        function icsD(d){return d.getFullYear()+pad2(d.getMonth()+1)+pad2(d.getDate());}
        var now=new Date(),stamp=now.getUTCFullYear()+pad2(now.getUTCMonth()+1)+pad2(now.getUTCDate())+'T000000Z';
        var body=['BEGIN:VCALENDAR','VERSION:2.0','BEGIN:VEVENT','DTSTART;VALUE=DATE:'+icsD(nb),'DTEND;VALUE=DATE:'+icsD(new Date(nb.getFullYear(),nb.getMonth(),nb.getDate()+1)),'SUMMARY:'+name+"'s Birthday \uD83C\uDF82",'RRULE:FREQ=YEARLY','END:VEVENT','END:VCALENDAR'].join('\r\n');
        var bl=new Blob([body],{type:'text/calendar;charset=utf-8'});
        var a=document.createElement('a');a.href=URL.createObjectURL(bl);a.download=name.toLowerCase().replace(/\s+/g,'-')+'-birthday.ics';document.body.appendChild(a);a.click();URL.revokeObjectURL(a.href);document.body.removeChild(a);
        T('Calendar file created!');
      };
    });
  })();

  /* ════════════════════════════════════════════
     SHARE AGE RESULT
  ════════════════════════════════════════════ */
  (function shareAge() {
    var card=document.getElementById('share-card-preview');if(!card)return;
    function setTxt(id,v){var el=document.getElementById(id);if(el)el.textContent=v;}

    /* Load stored data */
    try{
      var stored=sessionStorage.getItem('ufx_age_result');
      if(stored){
        var d=JSON.parse(stored);
        setTxt('sc-years',d.years||'—');
        setTxt('sc-weeks',d.tweeks?fmt(d.tweeks):'—');
        setTxt('sc-hours',d.thours?fmt(d.thours):'—');
        setTxt('sc-minutes',d.tmins?fmt(d.tmins):'—');
        setTxt('sc-bday',(d.nbDays||0)+' days');
        setTxt('sc-zodiac',d.zodiac||'—');
        
        
      }
    }catch(e){}

    /* Theme picker */
    $$('.at-share-theme').forEach(function(b){
      b.addEventListener('click',function(){
        $$('.at-share-theme').forEach(function(x){x.classList.remove('is-active');});
        b.classList.add('is-active');
        card.setAttribute('data-theme',b.getAttribute('data-theme'));
      });
    });

    /* Name input */
    var nameIn=document.getElementById('sc-input-name');
    if(nameIn) nameIn.addEventListener('input',function(){
      var n=nameIn.value.trim()||'Your Name';
      setTxt('sc-name',n);
      var av=document.getElementById('sc-avatar');if(av)av.textContent=n.charAt(0).toUpperCase();
    });

    /* Caption input */
    var capIn=document.getElementById('sc-input-caption'),capCnt=document.getElementById('sc-cap-count');
    if(capIn) capIn.addEventListener('input',function(){
      setTxt('sc-caption',capIn.value||"Here's my age insight \uD83C\uDF89");
      if(capCnt)capCnt.textContent=capIn.value.length+'/120';
    });

    /* Caption use buttons */
    $$('.at-caption-use').forEach(function(b){
      b.addEventListener('click',function(){
        var cap=b.getAttribute('data-caption');
        if(capIn){capIn.value=cap;capIn.dispatchEvent(new Event('input'));}
      });
    });

    /* Include toggles */
    function addToggle(chkId,sectId){
      var chk=document.getElementById(chkId),sect=document.getElementById(sectId);
      if(chk&&sect)chk.addEventListener('change',function(){sect.style.display=chk.checked?'':'none';});
    }
    addToggle('sc-inc-bday','sc-details-row');

    function buildText(){
      var n=document.getElementById('sc-name'),yr=document.getElementById('sc-years');
      var name=n?n.textContent:'Your Name',years=yr?yr.textContent:'—';
      var wks=document.getElementById('sc-weeks'),hrs=document.getElementById('sc-hours');
      return name+"'s Age Insight \uD83C\uDF89\n"+years+' years old\nTotal weeks: '+(wks?wks.textContent:'—')+'\nTotal hours: '+(hrs?hrs.textContent:'—');
    }

    var btnCopyLink=document.getElementById('sc-btn-copy-link');
    if(btnCopyLink) btnCopyLink.addEventListener('click',function(){
      if(navigator.clipboard)navigator.clipboard.writeText(window.location.href).then(function(){T('Link copied!');});
    });
    var btnCopyTxt=document.getElementById('sc-btn-copy-text');
    if(btnCopyTxt) btnCopyTxt.addEventListener('click',function(){
      if(navigator.clipboard)navigator.clipboard.writeText(buildText()).then(function(){T('Text copied!');});
    });
    var btnWa=document.getElementById('sc-btn-whatsapp');
    if(btnWa) btnWa.addEventListener('click',function(){
      window.open('https://wa.me/?text='+encodeURIComponent(buildText()),'_blank');
    });
    var btnFb=document.getElementById('sc-btn-facebook');
    if(btnFb) btnFb.addEventListener('click',function(){
      window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(window.location.href),'_blank');
    });
    var btnDl=document.getElementById('sc-btn-download');
    if(btnDl) btnDl.addEventListener('click',function(){
      T('Right-click the preview card and choose "Save image as" to download.');
    });
  })();

  /* ════════════════════════════════════════════
     SPEED TEST — SUB-PAGE LINK UPDATER
  ════════════════════════════════════════════ */
  (function spSubLinks(){
    var spUrl=document.getElementById('sp-url');
    var spLinks=document.getElementById('sp-subpage-links');
    if(!spLinks) return;
    function updateLinks(){
      var url=spUrl?spUrl.value.trim():'';
      var base=window.location.href.split('?')[0];
      var rpt=document.getElementById('sp-link-report');
      var opp=document.getElementById('sp-link-opps');
      var wf=document.getElementById('sp-link-waterfall');
      var q=url?'&url='+encodeURIComponent(url):'';
      if(rpt)  rpt.href=base+'?view=full_report'+q;
      if(opp)  opp.href=base+'?view=opportunities'+q;
      if(wf)   wf.href=base+'?view=waterfall'+q;
    }
    updateLinks();
    if(spUrl) spUrl.addEventListener('input',updateLinks);
  })();

  /* ════════════════════════════════════════════
     SPEED REPORT — TAB SWITCHING
  ════════════════════════════════════════════ */
  (function spReportTabs(){
    var wrap=document.querySelector('.at-speed-report-tabs');if(!wrap)return;
    var tabs=wrap.querySelectorAll('.at-tab');
    tabs.forEach(function(b){
      b.addEventListener('click',function(){
        tabs.forEach(function(x){x.classList.remove('is-active');});
        b.classList.add('is-active');
        var pane=b.getAttribute('data-rtab');
        $$('.at-speed-tab-pane').forEach(function(p){
          p.style.display=(p.getAttribute('data-pane')===pane)?'':'none';
        });
      });
    });
  })();

  /* ════════════════════════════════════════════
     WATERFALL TYPE TABS
  ════════════════════════════════════════════ */
  (function spWaterfallTabs(){
    var wrap=document.getElementById('spw-type-tabs');if(!wrap)return;
    var tabs=wrap.querySelectorAll('.at-tab');
    tabs.forEach(function(b){
      b.addEventListener('click',function(){
        tabs.forEach(function(x){x.classList.remove('is-active');});
        b.classList.add('is-active');
      });
    });
  })();

  /* ════════════════════════════════════════════
     OPPORTUNITIES — PRIORITY FILTER
  ════════════════════════════════════════════ */
  (function spOppsFilter(){
    var filter=document.getElementById('spo-filter');if(!filter)return;
    filter.addEventListener('change',function(){
      var v=filter.value;
      $$('#spo-tbody tr[data-priority]').forEach(function(r){
        r.style.display=(!v||r.getAttribute('data-priority')===v)?'':'none';
      });
    });
  })();



  /* ════════════════════════════════════════════
     SPEED TEST — FULL REPORT / WATERFALL / OPPORTUNITIES
  ════════════════════════════════════════════ */
  (function speedSubPages(){
    var hasSpeedSubPage = document.getElementById('spr-run') || document.getElementById('spw-run') || document.getElementById('spo-run');
    if(!hasSpeedSubPage) return;

    var lastWaterfallData = null;
    var lastOppsData = null;

    function qp(name){ try{return new URLSearchParams(window.location.search).get(name)||'';}catch(e){return '';} }
    function txt(id,v){ var el=document.getElementById(id); if(el) el.textContent=(v===undefined||v===null||v==='')?'—':v; }
    function html(id,v){ var el=document.getElementById(id); if(el) el.innerHTML=v; }
    function esc(v){ return String(v===undefined||v===null?'':v).replace(/[&<>'"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c];}); }
    function num(v){ return parseFloat(String(v||'0').replace(/[^0-9.]/g,''))||0; }
    function priorityClass(p){ p=String(p||'Low').toLowerCase(); return p==='high'?'at-priority-high':(p==='medium'?'at-priority-medium':'at-priority-low'); }
    function ring(id,score){ var el=document.getElementById(id); if(!el) return; el.style.strokeDashoffset=(327-(327*score/100)); el.style.stroke=score>=90?'#10B981':(score>=75?'#3B82F6':(score>=50?'#F59E0B':'#EF4444')); }
    function setBar(id,value,max){ var el=document.getElementById(id); if(!el) return; var pct=Math.max(3,Math.min(100,(value/max)*100)); el.style.width=pct+'%'; el.classList.remove('at-cwv-good','at-cwv-warn','at-cwv-bad'); el.classList.add(pct<65?'at-cwv-good':(pct<90?'at-cwv-warn':'at-cwv-bad')); }
    function ratingMs(v,good,ok){ return v<=good?'Good':(v<=ok?'Needs work':'Poor'); }
    function getUrl(inputId){ var input=document.getElementById(inputId); var q=qp('url'); if(input && q && !input.value) input.value=q; return input ? input.value.trim() : q; }
    function runSpeed(btn,inputId,onDone){
      var input=document.getElementById(inputId), u=getUrl(inputId);
      if(!u){ T('Please enter a URL first.'); if(input) input.focus(); return; }
      ufxBusy(btn,true,'Testing...');
      ufxApi('speed',{url:u}).then(function(d){
        ufxBusy(btn,false);
        try{sessionStorage.setItem('ufx_speed_last_url',u);sessionStorage.setItem('ufx_speed_last_data',JSON.stringify(d));}catch(e){}
        onDone(d,u);
      }).catch(function(e){ ufxBusy(btn,false); T(e.message||'Speed test failed.'); });
    }
    function updateReportLinks(url){
      var base=window.location.href.split('?')[0];
      var q=url?'&url='+encodeURIComponent(url):'';
      var links=document.querySelectorAll('a[href*="view=opportunities"],a[href*="view=waterfall"]');
      links.forEach(function(a){
        if(a.href.indexOf('view=opportunities')>-1) a.href=base+'?view=opportunities'+q;
        if(a.href.indexOf('view=waterfall')>-1) a.href=base+'?view=waterfall'+q;
      });
    }

    function fillCommon(prefix,d){
      txt(prefix+'-date', ufxNow());
      txt(prefix+'-score', d.score);
      txt(prefix+'-rating', d.rating);
      txt(prefix+'-load', d.load_time);
      txt(prefix+'-fcp', d.fcp);
      txt(prefix+'-lcp', d.lcp);
      txt(prefix+'-tbt', d.tbt);
      txt(prefix+'-cls', d.cls);
      txt(prefix+'-size', d.page_size);
      txt(prefix+'-req', d.requests);
    }

    function renderFullReport(d,url){
      fillCommon('spr',d);
      txt('spr-score2',d.score); txt('spr-rating2',d.rating); txt('spr-env-time',ufxNow());
      ring('spr-ring',d.score); ring('spr-ring2',d.score);
      txt('spr-load-r',ratingMs(d.load_ms||0,2500,4000));
      txt('spr-fcp-r','Not measured');
      txt('spr-lcp-r','Not measured');
      txt('spr-tbt-r','Not measured');
      txt('spr-cls-r','Not measured');
      txt('spr-cwv-fcp',d.fcp); txt('spr-cwv-lcp',d.lcp); txt('spr-cwv-tbt',d.tbt); txt('spr-cwv-cls',d.cls); txt('spr-cwv-ttfb',d.ttfb);
      setBar('spr-cwv-fcp-bar',d.fcp_ms||0,3000); setBar('spr-cwv-lcp-bar',d.lcp_ms||0,4000); setBar('spr-cwv-tbt-bar',d.tbt_ms||0,600); setBar('spr-cwv-cls-bar',(d.cls_num||0)*1000,250); setBar('spr-cwv-ttfb-bar',d.ttfb_ms||0,1800);
      var opps=d.opportunities||[];
      html('spr-opps-tbody',opps.map(function(o){return '<tr><td>'+esc(o.title)+'</td><td>'+esc(o.saving)+'</td><td><span class="at-priority-badge '+priorityClass(o.priority)+'">'+esc(o.priority)+'</span></td></tr>';}).join('') || '<tr><td colspan="3" class="at-empty">No opportunities found.</td></tr>');
      var diag=d.diagnostics||[];
      html('spr-diag-list',diag.map(function(x){return '<li>✓ '+esc(x)+'</li>';}).join(''));
      var chart=document.getElementById('spr-history-chart');
      if(chart) chart.innerHTML='<div class="at-score-history-placeholder">This on-demand tool does not store test history.</div>';
      updateReportLinks(url);
    }

    function renderWaterfall(d,url){
      lastWaterfallData=d;
      txt('spw-date',ufxNow());
      txt('spw-requests',d.requests); txt('spw-size',d.page_size); txt('spw-load',d.load_time); txt('spw-third',(d.counts&&d.counts.third)||0);
      var resources=d.resources||[];
      var slow=resources.slice().sort(function(a,b){return (b.duration_ms||0)-(a.duration_ms||0);})[0];
      txt('spw-slowest',slow?slow.duration:'—'); txt('spw-slowest-name',slow?slow.name:'—');
      ['all','doc','js','css','img','font','third'].forEach(function(k){txt('spw-c-'+k,0);});
      var c=d.counts||{};
      txt('spw-c-all',c.all||resources.length); txt('spw-c-doc',c.document||0); txt('spw-c-js',c.script||0); txt('spw-c-css',c.stylesheet||0); txt('spw-c-img',c.image||0); txt('spw-c-font',c.font||0); txt('spw-c-third',c.third||0);
      txt('spw-render-blocking',d.insights&&d.insights.render_blocking); txt('spw-largest-res',d.insights&&d.insights.largest); txt('spw-duplicates',d.insights&&d.insights.duplicates);
      renderWaterfallRows('all');
    }

    function typeLabel(t){ return ({document:'Document',script:'Script',stylesheet:'Stylesheet',image:'Image',font:'Font'}[t]||t||'Other'); }
    function colorFor(t){ return ({document:'#10B981',script:'#F97316',stylesheet:'#2563EB',image:'#8B5CF6',font:'#14B8A6'}[t]||'#64748B'); }
    function renderWaterfallRows(filter){
      var d=lastWaterfallData, tbody=document.getElementById('spw-tbody'); if(!d||!tbody)return;
      var rows=(d.resources||[]).filter(function(r){ return filter==='all'||(filter==='third'&&r.is_third_party)||r.type===filter; });
      if(!rows.length){ tbody.innerHTML='<tr><td colspan="8" class="at-empty">No requests found for this filter.</td></tr>'; txt('spw-showing','Showing 0 requests'); return; }
      tbody.innerHTML=rows.map(function(r){
        return '<tr data-type="'+esc(r.type)+'" data-third="'+(r.is_third_party?'1':'0')+'">'
          +'<td><strong style="display:block;max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+esc(r.name||r.url)+'</strong><small style="display:block;max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#64748B;">'+esc(r.url)+'</small></td>'
          +'<td>'+(r.status?esc(r.status):'—')+'</td>'
          +'<td>'+typeLabel(r.type)+(r.is_third_party?' <small style="color:#64748B;">3rd</small>':'')+'</td>'
          +'<td>'+esc(r.size||'—')+'</td>'
          +'<td>'+esc(r.probe_order||1)+'</td>'
          +'<td>'+esc(r.duration||'—')+'</td>'
          +'<td>'+esc(r.blocking_time||'Not measured')+'</td>'
          +'<td><span class="at-badge">'+(r.probed?'Server probed':'Inventory only')+'</span></td>'
          +'</tr>';
      }).join('');
      txt('spw-showing','Showing '+rows.length+' requests');
    }

    function renderOpps(d,url){
      lastOppsData=d;
      txt('spo-date',ufxNow());
      var opps=d.opportunities||[], high=0,med=0,low=0,totalMs=0,totalBytes=0;
      opps.forEach(function(o){ var p=String(o.priority||'').toLowerCase(); if(p==='high')high++; else if(p==='medium')med++; else low++; var s=String(o.saving||''); if(s.indexOf('ms')>-1)totalMs+=num(s); if(s.indexOf('KB')>-1)totalBytes+=num(s)*1024; if(s.indexOf('MB')>-1)totalBytes+=num(s)*1048576; });
      txt('spo-high',high); txt('spo-med',med); txt('spo-low',low); txt('spo-count',opps.length); txt('spo-impact',totalMs?('-'+(totalMs/1000).toFixed(2)+' s'):'—'); txt('spo-savings',totalBytes?formatBytes(totalBytes):'—');
      renderOppRows('');
    }
    function formatBytes(bytes){ bytes=Number(bytes)||0; if(bytes>=1048576)return (bytes/1048576).toFixed(2)+' MB'; if(bytes>=1024)return (bytes/1024).toFixed(1)+' KB'; return Math.round(bytes)+' B'; }
    function renderOppRows(priority){
      var tbody=document.getElementById('spo-tbody'); if(!tbody||!lastOppsData)return;
      var rows=(lastOppsData.opportunities||[]).filter(function(o){return !priority||o.priority===priority;});
      if(!rows.length){ tbody.innerHTML='<tr><td colspan="8" class="at-empty">No opportunities found.</td></tr>'; txt('spo-showing','Showing 0 opportunities'); return; }
      tbody.innerHTML=rows.map(function(o,i){return '<tr data-priority="'+esc(o.priority)+'"><td>'+(i+1)+'</td><td><strong>'+esc(o.title)+'</strong></td><td>'+esc(o.saving)+'</td><td><span class="at-priority-badge '+priorityClass(o.priority)+'">'+esc(o.priority)+'</span></td><td>'+esc(o.affected)+'</td><td>'+esc(o.description)+'</td><td>'+esc(o.fix)+'</td><td><button type="button" class="at-btn at-btn-outline at-btn-sm">Review</button></td></tr>';}).join('');
      txt('spo-showing','Showing '+rows.length+' opportunities');
    }
    function exportCsv(filename, rows){
      var csv=rows.map(function(r){return r.map(function(v){return '"'+String(v===undefined?'':v).replace(/"/g,'""')+'"';}).join(',');}).join('\n');
      ufxDownloadBlob(new Blob([csv],{type:'text/csv;charset=utf-8'}),filename);
    }

    var sprBtn=document.getElementById('spr-run');
    if(sprBtn){
      var u=getUrl('spr-url'); if(u) runSpeed(sprBtn,'spr-url',renderFullReport);
      sprBtn.addEventListener('click',function(){runSpeed(sprBtn,'spr-url',renderFullReport);});
      var dl=document.getElementById('spr-download'); if(dl) dl.addEventListener('click',function(){ window.print(); });
      var sh=document.getElementById('spr-share'); if(sh) sh.addEventListener('click',function(){ var u=getUrl('spr-url'); if(navigator.clipboard) navigator.clipboard.writeText(location.href).then(function(){T('Report link copied!');}); });
    }

    var spwBtn=document.getElementById('spw-run');
    if(spwBtn){
      var wu=getUrl('spw-url'); if(wu) runSpeed(spwBtn,'spw-url',renderWaterfall);
      spwBtn.addEventListener('click',function(){runSpeed(spwBtn,'spw-url',renderWaterfall);});
      var tabs=document.querySelectorAll('#spw-type-tabs .at-tab');
      tabs.forEach(function(b){ b.addEventListener('click',function(){ tabs.forEach(function(x){x.classList.remove('is-active');}); b.classList.add('is-active'); renderWaterfallRows(b.getAttribute('data-wtype')||'all'); }); });
      var ex=document.getElementById('spw-export'); if(ex) ex.addEventListener('click',function(){ var rows=[['URL','Status','Type','Size','Probe Order','Probe Duration','Browser Blocking','Coverage']].concat((lastWaterfallData&&lastWaterfallData.resources||[]).map(function(r){return [r.url,r.status,r.type,r.size,r.probe_order||1,r.duration,r.blocking_time,r.probed?'Server probed':'Inventory only'];})); exportCsv('resource-inventory.csv',rows); });
      var share=document.getElementById('spw-share'); if(share) share.addEventListener('click',function(){ if(navigator.clipboard)navigator.clipboard.writeText(location.href).then(function(){T('Link copied!');}); });
    }

    var spoBtn=document.getElementById('spo-run');
    if(spoBtn){
      var ou=getUrl('spo-url'); if(ou) runSpeed(spoBtn,'spo-url',renderOpps);
      spoBtn.addEventListener('click',function(){runSpeed(spoBtn,'spo-url',renderOpps);});
      var filter=document.getElementById('spo-filter'); if(filter) filter.addEventListener('change',function(){renderOppRows(filter.value);});
      var exp=document.getElementById('spo-export'); if(exp) exp.addEventListener('click',function(){ var rows=[['Opportunity','Savings','Priority','Affected','Description','Recommended Fix']].concat((lastOppsData&&lastOppsData.opportunities||[]).map(function(o){return [o.title,o.saving,o.priority,o.affected,o.description,o.fix];})); exportCsv('opportunities.csv',rows); });
    }
  })();



  /* ════════════════════════════════════════════
     EXTRA PDF / IMAGE / DEV TOOLS
  ════════════════════════════════════════════ */
  function ufxFmtBytes(bytes){
    if(!bytes && bytes !== 0) return '—';
    var units=['B','KB','MB','GB']; var i=0; var n=bytes;
    while(n>=1024 && i<units.length-1){ n/=1024; i++; }
    return (i===0?Math.round(n):n.toFixed(2))+' '+units[i];
  }
  function ufxDownloadBlob(blob, filename){
    var a=document.createElement('a');
    a.href=URL.createObjectURL(blob);
    a.download=filename;
    document.body.appendChild(a); a.click();
    setTimeout(function(){ URL.revokeObjectURL(a.href); a.remove(); }, 1000);
  }
  function ufxLoadScript(src, test){
    return new Promise(function(resolve,reject){
      if(test && test()){ resolve(); return; }
      var existing=document.querySelector('script[data-ufx-src="'+src+'"]');
      if(existing){ existing.addEventListener('load',resolve); existing.addEventListener('error',reject); return; }
      var s=document.createElement('script'); s.src=src; s.async=true; s.dataset.ufxSrc=src;
      s.onload=resolve; s.onerror=function(){ reject(new Error('Could not load library.')); };
      document.head.appendChild(s);
    });
  }
  function ufxLoadPdfLib(){
    return ufxLoadScript('https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js', function(){ return window.PDFLib; });
  }
  function ufxLoadJsPdf(){
    return ufxLoadScript('https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js', function(){ return window.jspdf && window.jspdf.jsPDF; });
  }

  (function pdfCompressor(){
    var input=$('#ufx-pdf-compress-file'); if(!input) return;
    var btn=$('#ufx-pdf-compress-btn'), orig=$('#ufx-pdfc-original'), nw=$('#ufx-pdfc-new'), saved=$('#ufx-pdfc-saved');
    input.addEventListener('change',function(){ if(input.files[0] && orig) orig.textContent=ufxFmtBytes(input.files[0].size); });
    btn.addEventListener('click',function(){
      var file=input.files[0]; if(!file){ T('Please choose a PDF first.'); return; }
      btn.disabled=true; btn.textContent='Compressing...';
      ufxLoadPdfLib().then(function(){ return file.arrayBuffer(); }).then(function(buf){
        return PDFLib.PDFDocument.load(buf, { ignoreEncryption:true }).then(function(pdf){
          pdf.setTitle(''); pdf.setAuthor(''); pdf.setSubject(''); pdf.setKeywords([]); pdf.setProducer('Uptime Fixer'); pdf.setCreator('Uptime Fixer');
          return pdf.save({ useObjectStreams:true, addDefaultPage:false, objectsPerTick:50 });
        });
      }).then(function(bytes){
        var blob=new Blob([bytes],{type:'application/pdf'});
        if(nw) nw.textContent=ufxFmtBytes(blob.size);
        if(saved){ var pct=Math.max(0, Math.round((1 - blob.size/file.size)*100)); saved.textContent=pct+'%'; }
        ufxDownloadBlob(blob, file.name.replace(/\.pdf$/i,'')+'-compressed.pdf'); T('PDF ready!');
      }).catch(function(e){ console.error(e); T('PDF compression failed. Try another PDF.'); })
      .finally(function(){ btn.disabled=false; btn.innerHTML='Compress PDF'; });
    });
  })();

  (function mergePdf(){
    var input=$('#ufx-merge-pdf-files'); if(!input) return;
    var list=$('#ufx-merge-list'), btn=$('#ufx-merge-pdf-btn');
    function render(){
      if(!list) return; list.innerHTML='';
      Array.prototype.forEach.call(input.files,function(f,i){
        var div=document.createElement('div'); div.className='at-output-row';
        div.innerHTML='<div class="at-output-lbl">'+(i+1)+'. '+f.name+'</div><div class="at-output-val">'+ufxFmtBytes(f.size)+'</div>';
        list.appendChild(div);
      });
    }
    input.addEventListener('change',render);
    btn.addEventListener('click',function(){
      var files=Array.prototype.slice.call(input.files||[]); if(files.length<2){ T('Choose at least 2 PDFs.'); return; }
      btn.disabled=true; btn.textContent='Merging...';
      ufxLoadPdfLib().then(async function(){
        var merged=await PDFLib.PDFDocument.create();
        for(var i=0;i<files.length;i++){
          var buf=await files[i].arrayBuffer(); var src=await PDFLib.PDFDocument.load(buf,{ignoreEncryption:true});
          var pages=await merged.copyPages(src, src.getPageIndices()); pages.forEach(function(p){ merged.addPage(p); });
        }
        var bytes=await merged.save({useObjectStreams:true});
        ufxDownloadBlob(new Blob([bytes],{type:'application/pdf'}),'merged.pdf'); T('Merged PDF downloaded.');
      }).catch(function(e){ console.error(e); T('Merge failed. Check PDF files.'); })
      .finally(function(){ btn.disabled=false; btn.innerHTML='Merge PDFs'; });
    });
  })();

  (function splitPdf(){
    var input=$('#ufx-split-pdf-file'); if(!input) return;
    var btn=$('#ufx-split-pdf-btn'), range=$('#ufx-split-range'), pageOut=$('#ufx-split-pages'), selOut=$('#ufx-split-selected');
    var totalPages=0;
    function parseRange(text,total){
      if(!text.trim()) return [0];
      var set=[];
      text.split(',').forEach(function(part){
        part=part.trim(); if(!part) return;
        if(part.indexOf('-')>-1){ var a=part.split('-').map(function(x){return parseInt(x,10);}); var start=Math.min(a[0],a[1]), end=Math.max(a[0],a[1]); for(var i=start;i<=end;i++) if(i>=1 && i<=total) set.push(i-1); }
        else { var n=parseInt(part,10); if(n>=1 && n<=total) set.push(n-1); }
      });
      return Array.from(new Set(set));
    }
    input.addEventListener('change',function(){
      var f=input.files[0]; if(!f) return;
      ufxLoadPdfLib().then(function(){ return f.arrayBuffer(); }).then(function(buf){ return PDFLib.PDFDocument.load(buf,{ignoreEncryption:true}); }).then(function(pdf){
        totalPages=pdf.getPageCount(); if(pageOut) pageOut.textContent=totalPages; if(selOut) selOut.textContent='—';
      }).catch(function(){ T('Could not read this PDF.'); });
    });
    if(range) range.addEventListener('input',function(){ if(selOut) selOut.textContent=parseRange(range.value,totalPages).length || '—'; });
    btn.addEventListener('click',function(){
      var file=input.files[0]; if(!file){ T('Please choose a PDF first.'); return; }
      btn.disabled=true; btn.textContent='Splitting...';
      ufxLoadPdfLib().then(async function(){
        var src=await PDFLib.PDFDocument.load(await file.arrayBuffer(),{ignoreEncryption:true});
        totalPages=src.getPageCount(); var pages=parseRange(range.value,totalPages); if(!pages.length) throw new Error('No valid pages selected');
        var out=await PDFLib.PDFDocument.create(); var copied=await out.copyPages(src,pages); copied.forEach(function(p){ out.addPage(p); });
        var bytes=await out.save({useObjectStreams:true});
        ufxDownloadBlob(new Blob([bytes],{type:'application/pdf'}),file.name.replace(/\.pdf$/i,'')+'-split.pdf'); T('Split PDF downloaded.');
      }).catch(function(e){ console.error(e); T('Split failed. Check page range.'); })
      .finally(function(){ btn.disabled=false; btn.innerHTML='Split PDF'; });
    });
  })();

  (function jpgToPdf(){
    var input=$('#ufx-jpg-pdf-files'); if(!input) return;
    var list=$('#ufx-jpg-list'), btn=$('#ufx-jpg-pdf-btn'), sizeSel=$('#ufx-jpg-page-size'), oriSel=$('#ufx-jpg-orientation'), marginEl=$('#ufx-jpg-margin');
    input.addEventListener('change',function(){
      if(!list) return; list.innerHTML='';
      Array.prototype.forEach.call(input.files,function(f,i){ var div=document.createElement('div'); div.className='at-output-row'; div.innerHTML='<div class="at-output-lbl">'+(i+1)+'. '+f.name+'</div><div class="at-output-val">'+ufxFmtBytes(f.size)+'</div>'; list.appendChild(div); });
    });
    function fileToDataURL(f){ return new Promise(function(res,rej){ var r=new FileReader(); r.onload=function(){res(r.result);}; r.onerror=rej; r.readAsDataURL(f); }); }
    function imageSize(src){ return new Promise(function(res,rej){ var img=new Image(); img.onload=function(){res({w:img.naturalWidth,h:img.naturalHeight});}; img.onerror=rej; img.src=src; }); }
    btn.addEventListener('click',function(){
      var files=Array.prototype.slice.call(input.files||[]); if(!files.length){ T('Choose image files first.'); return; }
      btn.disabled=true; btn.textContent='Converting...';
      ufxLoadJsPdf().then(async function(){
        var margin=parseInt(marginEl.value||'10',10), fmt=sizeSel.value, ori=oriSel.value;
        var pdf=null;
        for(var i=0;i<files.length;i++){
          var data=await fileToDataURL(files[i]); var dim=await imageSize(data);
          var format=fmt==='letter'?'letter':'a4';
          if(fmt==='fit'){ format=[dim.w*0.264583, dim.h*0.264583]; ori=dim.w>dim.h?'landscape':'portrait'; }
          if(!pdf) pdf=new window.jspdf.jsPDF({orientation:ori,unit:'mm',format:format}); else pdf.addPage(format,ori);
          var pw=pdf.internal.pageSize.getWidth(), ph=pdf.internal.pageSize.getHeight();
          var maxW=pw-margin*2, maxH=ph-margin*2, ratio=Math.min(maxW/dim.w,maxH/dim.h);
          var w=dim.w*ratio, h=dim.h*ratio, x=(pw-w)/2, y=(ph-h)/2;
          pdf.addImage(data, files[i].type.indexOf('png')>-1?'PNG':'JPEG', x, y, w, h);
        }
        pdf.save('images.pdf'); T('PDF downloaded.');
      }).catch(function(e){ console.error(e); T('JPG to PDF failed.'); })
      .finally(function(){ btn.disabled=false; btn.innerHTML='Convert to PDF'; });
    });
  })();

  (function imageCompressor(){
    var input=$('#ufx-img-compress-file'); if(!input)return;
    var q=$('#ufx-img-quality'),qv=$('#ufx-img-quality-val'),fmt=$('#ufx-img-format'),btn=$('#ufx-img-compress-btn'),canvas=$('#ufx-img-preview'),orig=$('#ufx-img-original'),nw=$('#ufx-img-new');
    var img=new Image(),file=null,sourceUrl='',resultUrl='';
    var compare=document.createElement('div');compare.className='ufx-comparison';compare.hidden=true;
    var before=document.createElement('img'),after=document.createElement('img');before.alt='Original image';after.alt='Compressed image';after.className='ufx-after';compare.append(before,after);
    var slider=document.createElement('input');slider.type='range';slider.min=0;slider.max=100;slider.value=50;slider.setAttribute('aria-label','Before and after comparison');compare.appendChild(slider);slider.oninput=function(){after.style.clipPath='inset(0 '+(100-slider.value)+'% 0 0)';};canvas.after(compare);
    var link=document.createElement('a');link.className='at-btn at-btn-primary';link.textContent='Download image';link.hidden=true;canvas.parentElement.appendChild(link);
    function draw(){canvas.width=img.naturalWidth;canvas.height=img.naturalHeight;canvas.getContext('2d').drawImage(img,0,0);}
    input.addEventListener('change',function(){file=input.files[0];if(!file)return;if(sourceUrl)URL.revokeObjectURL(sourceUrl);sourceUrl=URL.createObjectURL(file);orig.textContent=ufxFmtBytes(file.size);link.hidden=true;compare.hidden=true;canvas.hidden=false;img.onload=draw;img.onerror=function(){T('This image could not be decoded. Try JPG, PNG or WebP.');file=null;};img.src=sourceUrl;});
    q.addEventListener('input',function(){qv.textContent=q.value+'%';});
    btn.addEventListener('click',function(){if(!file||!img.naturalWidth){T('Choose an image first.');return;}btn.disabled=true;draw();canvas.toBlob(function(blob){btn.disabled=false;if(!blob){T('Compression failed.');return;}nw.textContent=ufxFmtBytes(blob.size);if(resultUrl)URL.revokeObjectURL(resultUrl);resultUrl=URL.createObjectURL(blob);before.src=sourceUrl;after.src=resultUrl;compare.hidden=false;canvas.hidden=true;link.href=resultUrl;link.download=file.name.replace(/\.[^.]+$/,'')+'-compressed.'+(fmt.value==='image/webp'?'webp':fmt.value==='image/png'?'png':'jpg');link.hidden=false;T('Image ready to download.');},fmt.value,parseInt(q.value,10)/100);});
  })();

  (function colorPicker(){
    var input=$('#ufx-color-image'); if(!input) return;
    var canvas=$('#ufx-color-canvas'), ctx=canvas.getContext('2d'), hexEl=$('#ufx-color-hex'), rgbEl=$('#ufx-color-rgb'), hslEl=$('#ufx-color-hsl'), sw=$('#ufx-color-swatch'), copy=$('#ufx-color-copy');
    var currentHex='';
    function rgbToHex(r,g,b){ return '#'+[r,g,b].map(function(x){ var h=x.toString(16); return h.length===1?'0'+h:h; }).join('').toUpperCase(); }
    function rgbToHsl(r,g,b){ r/=255; g/=255; b/=255; var max=Math.max(r,g,b), min=Math.min(r,g,b), h,s,l=(max+min)/2; if(max===min){h=s=0;} else { var d=max-min; s=l>0.5?d/(2-max-min):d/(max+min); switch(max){case r:h=(g-b)/d+(g<b?6:0);break;case g:h=(b-r)/d+2;break;case b:h=(r-g)/d+4;break;} h/=6; } return 'hsl('+Math.round(h*360)+', '+Math.round(s*100)+'%, '+Math.round(l*100)+'%)'; }
    input.addEventListener('change',function(){ var file=input.files[0]; if(!file) return; var img=new Image(); img.onload=function(){ var max=900, r=Math.min(1,max/img.width,max/img.height); canvas.width=img.width*r; canvas.height=img.height*r; ctx.drawImage(img,0,0,canvas.width,canvas.height); }; img.src=URL.createObjectURL(file); });
    canvas.addEventListener('click',function(e){ var rect=canvas.getBoundingClientRect(); var x=Math.floor((e.clientX-rect.left)*(canvas.width/rect.width)); var y=Math.floor((e.clientY-rect.top)*(canvas.height/rect.height)); var d=ctx.getImageData(x,y,1,1).data; currentHex=rgbToHex(d[0],d[1],d[2]); if(hexEl) hexEl.textContent=currentHex; if(rgbEl) rgbEl.textContent='rgb('+d[0]+', '+d[1]+', '+d[2]+')'; if(hslEl) hslEl.textContent=rgbToHsl(d[0],d[1],d[2]); if(sw) sw.style.background=currentHex; });
    copy.addEventListener('click',function(){ if(!currentHex) return; navigator.clipboard.writeText(currentHex).then(function(){T('HEX copied!');}); });
  })();

  (function jsonFormatter(){
    var input=$('#ufx-json-input'); if(!input) return;
    var output=$('#ufx-json-output'), status=$('#ufx-json-status');
    function setStatus(msg, ok){ if(status){ status.textContent=msg; status.style.color=ok?'#10B981':'#EF4444'; } }
    function sortKeys(obj){ if(Array.isArray(obj)) return obj.map(sortKeys); if(obj && typeof obj==='object'){ return Object.keys(obj).sort().reduce(function(acc,k){ acc[k]=sortKeys(obj[k]); return acc; },{}); } return obj; }
    function parse(){ return JSON.parse(input.value); }
    function format(space, sorter){ try{ var obj=parse(); if(sorter) obj=sortKeys(obj); output.value=JSON.stringify(obj,null,space); setStatus('Valid JSON',true); }catch(e){ setStatus(e.message,false); T('Invalid JSON.'); } }
    $('#ufx-json-format').addEventListener('click',function(){ format(2,false); });
    $('#ufx-json-minify').addEventListener('click',function(){ format(0,false); });
    $('#ufx-json-sort').addEventListener('click',function(){ format(2,true); });
    $('#ufx-json-clear').addEventListener('click',function(){ input.value=''; output.value=''; setStatus('Waiting',true); });
    $('#ufx-json-copy').addEventListener('click',function(){ if(output.value) navigator.clipboard.writeText(output.value).then(function(){T('Copied!');}); });
    $('#ufx-json-download').addEventListener('click',function(){ if(output.value) ufxDownloadBlob(new Blob([output.value],{type:'application/json'}),'formatted.json'); });
  })();


  /* ════════════════════════════════════════════
     NEW WEBSITE / IMAGE / PDF / DEV TOOLS
  ════════════════════════════════════════════ */
  function ufxText(el, value){ if(el) el.textContent = value; }
  function ufxVal(id){ var el=$(id); return el ? el.value : ''; }
  function ufxCopyValue(value){ if(!value){ T('Nothing to copy.'); return; } navigator.clipboard.writeText(value).then(function(){ T('Copied!'); }).catch(function(){ T('Copy failed.'); }); }
  function ufxEscapeHtml(str){ return String(str==null?'':str).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }
  function ufxLoadPdfJs(){
    return ufxLoadScript('https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js', function(){ return window.pdfjsLib; }).then(function(){
      if(window.pdfjsLib && !window.pdfjsLib.GlobalWorkerOptions.workerSrc){ window.pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js'; }
    });
  }

  (function domainExpiry(){
    var btn=$('#ufx-domain-expiry-btn'); if(!btn) return;
    btn.addEventListener('click',function(){
      var domain=ufxVal('#ufx-domain-expiry-input').trim(); if(!domain){ T('Please enter a domain.'); return; }
      ufxBusy(btn,true,'Checking...');
      ufxApi('domain_expiry',{domain:domain}).then(function(d){
        ufxBusy(btn,false); $('#ufx-domain-expiry-result').style.display='block';
        ufxText($('#ufx-de-domain'),d.domain||'—'); ufxText($('#ufx-de-status'),d.status||'—'); ufxText($('#ufx-de-created'),d.created||'—'); ufxText($('#ufx-de-updated'),d.updated||'—'); ufxText($('#ufx-de-expires'),d.expires||'—'); ufxText($('#ufx-de-days'),d.days_left==null?'—':String(d.days_left)); ufxText($('#ufx-de-registrar'),d.registrar||'—'); ufxText($('#ufx-de-ns'),(d.nameservers||[]).join(', ')||'—');
      }).catch(function(e){ ufxBusy(btn,false); T(e.message); });
    });
  })();

  (function brokenLinks(){
    var btn=$('#ufx-bl-btn'); if(!btn) return;
    btn.addEventListener('click',function(){
      var url=ufxVal('#ufx-bl-url').trim(); if(!url){ T('Please enter a page URL.'); return; }
      var limit=ufxVal('#ufx-bl-limit') || '25';
      ufxBusy(btn,true,'Scanning...');
      ufxApi('broken_links',{url:url,limit:limit}).then(function(d){
        ufxBusy(btn,false); $('#ufx-bl-summary').style.display='grid';
        ufxText($('#ufx-bl-checked'),d.checked||0); ufxText($('#ufx-bl-ok'),d.ok||0); ufxText($('#ufx-bl-broken'),d.broken||0);
        var tbody=$('#ufx-bl-tbody');
        if(!d.results || !d.results.length){ tbody.innerHTML='<tr><td colspan="4" class="at-empty">No normal links found on this page.</td></tr>'; return; }
        tbody.innerHTML=d.results.map(function(r){ var col=r.broken?'#EF4444':'#10B981'; return '<tr><td style="word-break:break-all;">'+ufxEscapeHtml(r.url)+'</td><td style="color:'+col+';font-weight:700;">'+ufxEscapeHtml(r.status)+'</td><td>'+ufxEscapeHtml(r.message)+'</td><td>'+ufxEscapeHtml(r.time)+'</td></tr>'; }).join('');
      }).catch(function(e){ ufxBusy(btn,false); T(e.message); });
    });
  })();

  (function robotsGenerator(){
    var btn=$('#ufx-robots-generate'); if(!btn) return;
    function generate(){
      var site=ufxVal('#ufx-robots-site').trim().replace(/\/$/,'');
      var sitemap=ufxVal('#ufx-robots-sitemap').trim() || (site ? site + '/sitemap.xml' : '');
      var rule=ufxVal('#ufx-robots-rule'); var lines=['User-agent: *'];
      if(rule==='block-all') lines.push('Disallow: /');
      else if(rule==='block-admin'){ lines.push('Disallow: /wp-admin/','Allow: /wp-admin/admin-ajax.php','Disallow: /private/','Disallow: /tmp/'); }
      else if(rule==='custom'){
        var paths=ufxVal('#ufx-robots-paths').split(/\r?\n/).map(function(x){return x.trim();}).filter(Boolean);
        if(!paths.length) lines.push('Disallow:'); else paths.forEach(function(p){ lines.push('Disallow: '+(p.charAt(0)==='/'?p:'/'+p)); });
      } else lines.push('Disallow:');
      if(sitemap){ lines.push('', 'Sitemap: '+sitemap); }
      $('#ufx-robots-output').value=lines.join('\n');
    }
    btn.addEventListener('click',generate);
    var copy=$('#ufx-robots-copy'); if(copy) copy.addEventListener('click',function(){ ufxCopyValue($('#ufx-robots-output').value); });
    var dl=$('#ufx-robots-download'); if(dl) dl.addEventListener('click',function(){ var v=$('#ufx-robots-output').value; if(!v) generate(); ufxDownloadBlob(new Blob([$('#ufx-robots-output').value],{type:'text/plain'}),'robots.txt'); });
    generate();
  })();

  (function sitemapGenerator(){
    var btn=$('#ufx-sitemap-generate'); if(!btn) return;
    function xmlEsc(x){ return String(x).replace(/[<>&"']/g,function(c){ return {'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;',"'":'&apos;'}[c]; }); }
    function generate(){
      var urls=ufxVal('#ufx-sitemap-urls').split(/\r?\n/).map(function(x){return x.trim();}).filter(Boolean);
      var freq=ufxVal('#ufx-sitemap-freq')||'weekly'; var pr=ufxVal('#ufx-sitemap-priority')||'0.8'; var today=new Date().toISOString().slice(0,10);
      var xml=['<?xml version="1.0" encoding="UTF-8"?>','<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
      urls.forEach(function(u){ xml.push('  <url>','    <loc>'+xmlEsc(u)+'</loc>','    <lastmod>'+today+'</lastmod>','    <changefreq>'+xmlEsc(freq)+'</changefreq>','    <priority>'+xmlEsc(pr)+'</priority>','  </url>'); });
      xml.push('</urlset>'); $('#ufx-sitemap-output').value=xml.join('\n'); ufxText($('#ufx-sitemap-count'),urls.length);
    }
    btn.addEventListener('click',generate);
    $('#ufx-sitemap-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-sitemap-output').value); });
    $('#ufx-sitemap-download').addEventListener('click',function(){ if(!$('#ufx-sitemap-output').value) generate(); ufxDownloadBlob(new Blob([$('#ufx-sitemap-output').value],{type:'application/xml'}),'sitemap.xml'); });
  })();

  function ufxSimpleImageConverter(prefix){
    var input=$('#'+prefix+'-file'); if(!input) return;
    var btn=$('#'+prefix+'-btn'), canvas=$('#'+prefix+'-canvas'), q=$('#'+prefix+'-quality'), ql=$('#'+prefix+'-quality-label'), orig=$('#'+prefix+'-original'), out=$('#'+prefix+'-output'), bg=$('#'+prefix+'-bg');
    var file=null,img=new Image();
    function draw(cb){ if(!file) return; var ctx=canvas.getContext('2d'), max=1200, r=Math.min(1,max/img.width,max/img.height); canvas.width=Math.max(1,Math.round(img.width*r)); canvas.height=Math.max(1,Math.round(img.height*r)); if(bg){ ctx.fillStyle=bg.value || '#fff'; ctx.fillRect(0,0,canvas.width,canvas.height); } ctx.drawImage(img,0,0,canvas.width,canvas.height); if(cb) cb(); }
    input.addEventListener('change',function(){ file=input.files[0]; if(!file) return; if(orig) orig.textContent=ufxFmtBytes(file.size); img.onload=function(){ draw(); }; img.src=URL.createObjectURL(file); });
    if(q) q.addEventListener('input',function(){ if(ql) ql.textContent=q.value+'%'; });
    btn.addEventListener('click',function(){ if(!file){ T('Choose an image first.'); return; } draw(function(){ var mime=input.getAttribute('data-output-mime')||'image/png', ext=input.getAttribute('data-output-ext')||'png'; canvas.toBlob(function(blob){ if(!blob){ T('Conversion failed.'); return; } if(out) out.textContent=ufxFmtBytes(blob.size); ufxDownloadBlob(blob,file.name.replace(/\.[^.]+$/,'')+'.'+ext); T('Image converted.'); },mime,parseInt(q.value||'90',10)/100); }); });
  }
  ufxSimpleImageConverter('ufx-webp'); ufxSimpleImageConverter('ufx-jpgpng'); ufxSimpleImageConverter('ufx-pngjpg');

  (function imageCropper(){
    var input=$('#ufx-crop-file'); if(!input) return;
    var canvas=$('#ufx-crop-canvas'), ctx=canvas.getContext('2d'), img=new Image(), file=null;
    function n(id,def){ var v=parseInt(ufxVal(id),10); return isNaN(v)?def:v; }
    function preview(){ if(!file) return; var x=n('#ufx-crop-x',0), y=n('#ufx-crop-y',0), w=n('#ufx-crop-w',Math.min(500,img.width)), h=n('#ufx-crop-h',Math.min(500,img.height)); canvas.width=Math.min(w,900); canvas.height=Math.round(h*(canvas.width/w)); ctx.drawImage(img,x,y,w,h,0,0,canvas.width,canvas.height); ufxText($('#ufx-crop-info'),'Image size: '+img.width+' × '+img.height+' px'); }
    input.addEventListener('change',function(){ file=input.files[0]; if(!file) return; img.onload=function(){ $('#ufx-crop-w').value=Math.min(500,img.width); $('#ufx-crop-h').value=Math.min(500,img.height); preview(); }; img.src=URL.createObjectURL(file); });
    ['#ufx-crop-x','#ufx-crop-y','#ufx-crop-w','#ufx-crop-h','#ufx-crop-format'].forEach(function(id){ var el=$(id); if(el) el.addEventListener('input',preview); });
    $('#ufx-crop-btn').addEventListener('click',function(){ if(!file){ T('Choose an image first.'); return; } var x=n('#ufx-crop-x',0), y=n('#ufx-crop-y',0), w=n('#ufx-crop-w',500), h=n('#ufx-crop-h',500), mime=ufxVal('#ufx-crop-format')||'image/png'; var c=document.createElement('canvas'), cctx=c.getContext('2d'); c.width=w; c.height=h; cctx.drawImage(img,x,y,w,h,0,0,w,h); c.toBlob(function(blob){ var ext=mime.indexOf('jpeg')>-1?'jpg':(mime.indexOf('webp')>-1?'webp':'png'); ufxDownloadBlob(blob,file.name.replace(/\.[^.]+$/,'')+'-cropped.'+ext); T('Cropped image downloaded.'); },mime,0.92); });
  })();

  (function pdfToJpg(){
    var input=$('#ufx-pdf-jpg-file'); if(!input) return;
    function parseRange(text,total){ if(!text.trim()){ return Array.from({length:total},function(_,i){return i+1;}); } var out=[]; text.split(',').forEach(function(part){ part=part.trim(); if(!part)return; if(part.indexOf('-')>-1){ var a=part.split('-').map(function(x){return parseInt(x,10);}); for(var i=Math.min(a[0],a[1]);i<=Math.max(a[0],a[1]);i++) if(i>=1&&i<=total) out.push(i); } else { var n=parseInt(part,10); if(n>=1&&n<=total) out.push(n); } }); return Array.from(new Set(out)); }
    $('#ufx-pdf-jpg-btn').addEventListener('click',function(){
      var file=input.files[0], btn=$('#ufx-pdf-jpg-btn'), res=$('#ufx-pdf-jpg-results'); if(!file){ T('Choose a PDF first.'); return; }
      btn.disabled=true; btn.textContent='Converting...'; res.innerHTML='';
      ufxLoadPdfJs().then(async function(){
        var data=new Uint8Array(await file.arrayBuffer()); var pdf=await pdfjsLib.getDocument({data:data}).promise; var pages=parseRange(ufxVal('#ufx-pdf-jpg-range'),pdf.numPages); var scale=parseFloat(ufxVal('#ufx-pdf-jpg-scale')||'1.5'), quality=(parseInt(ufxVal('#ufx-pdf-jpg-quality')||'90',10)/100);
        for(var i=0;i<pages.length;i++){
          var page=await pdf.getPage(pages[i]); var viewport=page.getViewport({scale:scale}); var canvas=document.createElement('canvas'); var ctx=canvas.getContext('2d'); canvas.width=viewport.width; canvas.height=viewport.height; await page.render({canvasContext:ctx,viewport:viewport}).promise;
          await new Promise(function(resolve){ canvas.toBlob(function(blob){ var name=file.name.replace(/\.pdf$/i,'')+'-page-'+pages[i]+'.jpg'; var row=document.createElement('div'); row.className='at-output-row'; row.innerHTML='<div class="at-output-lbl">Page '+pages[i]+'</div><div class="at-output-val"><a href="'+URL.createObjectURL(blob)+'" download="'+ufxEscapeHtml(name)+'">Download JPG</a></div>'; res.appendChild(row); resolve(); },'image/jpeg',quality); });
        }
        T('PDF pages converted.');
      }).catch(function(e){ console.error(e); T('PDF conversion failed.'); }).finally(function(){ btn.disabled=false; btn.innerHTML='Convert Pages'; });
    });
  })();




  (function invoiceGenerator(){
    var btn=$('#ufx-inv-download'); if(!btn) return;
    var body=$('#ufx-inv-items-body');
    var logoData='';
    function today(offset){ var d=new Date(); d.setDate(d.getDate()+(offset||0)); return d.toISOString().slice(0,10); }
    if($('#ufx-inv-date')) $('#ufx-inv-date').value=today(0);
    if($('#ufx-inv-due')) $('#ufx-inv-due').value=today(14);

    function cleanNum(v){
      var n=parseFloat(String(v||'').replace(/,/g,'').replace(/%/g,'').replace(/[^0-9.\-]/g,''));
      return isNaN(n)?0:n;
    }
    function cur(){ return (ufxVal('#ufx-inv-currency')||'$').trim() || '$'; }
    function fmtMoney(v){ return cur() + cleanNum(v).toFixed(2); }
    function safeText(v, fallback){ return (String(v||'').trim() || fallback || ''); }
    function textDate(v){ if(!v) return ''; var d=new Date(v+'T00:00:00'); return isNaN(d)?v:d.toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'}); }
    function setAccent(hex){
      hex = hex || '#2563eb';
      var bar=$('#ufx-inv-preview-bar'); if(bar) bar.style.background=hex;
      document.querySelectorAll('.ufx-preview-items th').forEach(function(th){ th.style.background=hex; });
    }
    function applyTemplate(){
      var tpl=ufxVal('#ufx-inv-template'), color='#2563eb';
      if(tpl==='charcoal') color='#111827';
      if(tpl==='emerald') color='#059669';
      var accent=$('#ufx-inv-accent');
      if(accent && accent.value !== color) accent.value=color;
      setAccent(color);
      updateSummary();
    }

    function rowHtml(item){
      item=item||{};
      return '<tr class="ufx-inv-row">'
        + '<td><input class="at-input ufx-inv-desc" placeholder="Item description" value="'+ufxEscapeHtml(item.desc||'')+'"></td>'
        + '<td><input type="text" class="at-input ufx-inv-qty" value="'+ufxEscapeHtml(item.qty||1)+'"></td>'
        + '<td><input type="text" class="at-input ufx-inv-price" value="'+ufxEscapeHtml(item.price||'')+'" placeholder="0.00"></td>'
        + '<td><input type="text" class="at-input ufx-inv-disc" value="'+ufxEscapeHtml(item.disc||0)+'" placeholder="0"></td>'
        + '<td><input type="text" class="at-input ufx-inv-tax" value="'+ufxEscapeHtml(item.tax||0)+'" placeholder="0"></td>'
        + '<td class="ufx-inv-amount" style="font-weight:800;white-space:nowrap;">'+fmtMoney(0)+'</td>'
        + '<td><button type="button" class="at-btn at-btn-outline ufx-inv-remove">Remove</button></td>'
        + '</tr>';
    }
    function addRow(item){ body.insertAdjacentHTML('beforeend', rowHtml(item)); bindRows(); updateSummary(); }
    function bindRows(){
      body.querySelectorAll('input').forEach(function(el){ el.oninput=updateSummary; });
      body.querySelectorAll('.ufx-inv-remove').forEach(function(removeBtn){
        removeBtn.onclick=function(){
          var rows=body.querySelectorAll('tr');
          if(rows.length>1){ removeBtn.closest('tr').remove(); }
          else {
            removeBtn.closest('tr').querySelectorAll('input').forEach(function(input, idx){
              input.value = idx===0 ? '' : (idx===1 ? '1' : '0');
            });
          }
          updateSummary();
        };
      });
    }
    function parseRows(){
      return Array.from(body.querySelectorAll('tr')).map(function(tr){
        var desc=safeText(tr.querySelector('.ufx-inv-desc').value,'');
        var qty=cleanNum(tr.querySelector('.ufx-inv-qty').value || 1) || 1;
        var price=cleanNum(tr.querySelector('.ufx-inv-price').value);
        var disc=Math.min(100, Math.max(0, cleanNum(tr.querySelector('.ufx-inv-disc').value)));
        var tax=Math.max(0, cleanNum(tr.querySelector('.ufx-inv-tax').value));
        var base=qty*price;
        var discAmount=base*(disc/100);
        var taxable=Math.max(0, base-discAmount);
        var taxAmount=taxable*(tax/100);
        var total=taxable+taxAmount;
        var amountCell=tr.querySelector('.ufx-inv-amount');
        if(amountCell) amountCell.textContent=fmtMoney(total);
        return desc ? {desc:desc,qty:qty,price:price,disc:disc,tax:tax,base:base,discAmount:discAmount,taxAmount:taxAmount,total:total} : null;
      }).filter(Boolean);
    }
    function updatePreview(items){
      var accent=ufxVal('#ufx-inv-accent') || '#2563eb';
      setAccent(accent);
      var logoPrev=$('#ufx-inv-preview-logo');
      if(logoPrev){
        logoPrev.innerHTML = logoData ? '<img src="'+logoData+'" alt="Logo">' : ufxEscapeHtml(safeText(ufxVal('#ufx-inv-business'),'Logo').slice(0,18));
      }
      ufxText($('#ufx-inv-preview-number'), safeText(ufxVal('#ufx-inv-number'),'INV-001'));
      ufxText($('#ufx-inv-preview-dates'), 'Issue: '+textDate(ufxVal('#ufx-inv-date'))+'  •  Due: '+textDate(ufxVal('#ufx-inv-due')));
      var from=safeText(ufxVal('#ufx-inv-business'),'Your Company') + (ufxVal('#ufx-inv-business-details').trim() ? '\n'+ufxVal('#ufx-inv-business-details') : '');
      var to=safeText(ufxVal('#ufx-inv-client'),'Client Name') + (ufxVal('#ufx-inv-client-details').trim() ? '\n'+ufxVal('#ufx-inv-client-details') : '');
      ufxText($('#ufx-inv-preview-from'), from);
      ufxText($('#ufx-inv-preview-to'), to);
      var out=$('#ufx-inv-preview-items');
      if(out){
        out.innerHTML = items.length ? items.slice(0,6).map(function(it){
          return '<tr><td>'+ufxEscapeHtml(it.desc)+'</td><td>'+ufxEscapeHtml(it.qty)+'</td><td>'+ufxEscapeHtml(fmtMoney(it.total))+'</td></tr>';
        }).join('') : '<tr><td>No items yet</td><td>—</td><td>—</td></tr>';
      }
    }
    function updateSummary(){
      var items=parseRows();
      var sub=items.reduce(function(a,b){return a+b.base;},0);
      var disc=items.reduce(function(a,b){return a+b.discAmount;},0);
      var tax=items.reduce(function(a,b){return a+b.taxAmount;},0);
      var total=items.reduce(function(a,b){return a+b.total;},0);
      ufxText($('#ufx-inv-count'),items.length);
      ufxText($('#ufx-inv-subtotal'),fmtMoney(sub));
      ufxText($('#ufx-inv-discount-total'),fmtMoney(disc));
      ufxText($('#ufx-inv-tax-total'),fmtMoney(tax));
      ufxText($('#ufx-inv-total'),fmtMoney(total));
      updatePreview(items);
      return {items:items,sub:sub,disc:disc,tax:tax,total:total};
    }

    var logoInput=$('#ufx-inv-logo');
    if(logoInput){
      logoInput.addEventListener('change',function(){
        var file=logoInput.files && logoInput.files[0]; if(!file) return;
        var fr=new FileReader();
        fr.onload=function(){
          logoData=fr.result;
          var small=$('#ufx-inv-logo-preview');
          if(small) small.innerHTML='<img src="'+logoData+'" alt="Logo">';
          updateSummary();
        };
        fr.readAsDataURL(file);
      });
    }
    ['#ufx-inv-business','#ufx-inv-client','#ufx-inv-business-details','#ufx-inv-client-details','#ufx-inv-number','#ufx-inv-date','#ufx-inv-due','#ufx-inv-currency','#ufx-inv-accent','#ufx-inv-notes'].forEach(function(id){
      var el=$(id); if(el){ el.addEventListener('input',updateSummary); el.addEventListener('change',updateSummary); }
    });
    var tpl=$('#ufx-inv-template'); if(tpl) tpl.addEventListener('change',applyTemplate);
    $('#ufx-inv-add-item').addEventListener('click',function(){ addRow({qty:1,price:'',disc:0,tax:0}); });
    $('#ufx-inv-sample').addEventListener('click',function(){
      body.innerHTML='';
      addRow({desc:'Website design and development',qty:1,price:1200,disc:0,tax:10});
      addRow({desc:'Hosting setup and security configuration',qty:1,price:180,disc:5,tax:0});
      addRow({desc:'Monthly maintenance package',qty:2,price:95,disc:0,tax:10});
      $('#ufx-inv-business').value = $('#ufx-inv-business').value || 'Uptime Fixer';
      $('#ufx-inv-client').value = $('#ufx-inv-client').value || 'Client Company';
      updateSummary();
    });
    $('#ufx-inv-preview').addEventListener('click',updateSummary);
    addRow({qty:1,price:'',disc:0,tax:0});
    applyTemplate();

    btn.addEventListener('click',function(){
      var data=updateSummary();
      if(!data.items.length){ T('Please add at least one invoice item.'); return; }
      ufxLoadJsPdf().then(function(){
        var doc=new window.jspdf.jsPDF({unit:'mm',format:'a4'});
        var pageW=doc.internal.pageSize.getWidth(), pageH=doc.internal.pageSize.getHeight();
        var left=14, right=pageW-14, y=16, accent=ufxVal('#ufx-inv-accent')||'#2563eb';

        function hexToRgb(hex){
          var h=String(hex||'').replace('#',''); if(h.length===3) h=h.split('').map(function(ch){return ch+ch;}).join('');
          var n=parseInt(h,16); return isNaN(n)?[37,99,235]:[(n>>16)&255,(n>>8)&255,n&255];
        }
        function wrap(text,w){ return doc.splitTextToSize(String(text||''), w); }
        function rightText(text,x,yy){ doc.text(String(text||''),x,yy,{align:'right'}); }
        function ensure(h){ if(y+h>pageH-22){ doc.addPage(); y=18; drawPageHeader(true); } }
        function addLogo(x, yy){
          if(!logoData) return false;
          try {
            var fmt = logoData.indexOf('image/jpeg')>-1 ? 'JPEG' : 'PNG';
            doc.addImage(logoData, fmt, x, yy, 34, 20, undefined, 'FAST');
            return true;
          } catch(e) { return false; }
        }
        function drawPageHeader(continued){
          var c=hexToRgb(accent);
          doc.setFillColor(248,250,252); doc.rect(0,0,pageW,42,'F');
          doc.setFillColor(c[0],c[1],c[2]); doc.rect(0,0,pageW,6,'F');
          doc.setTextColor(15,23,42);
          if(!continued){
            if(!addLogo(left,13)){
              doc.setFillColor(255,255,255); doc.roundedRect(left,12,36,21,2,2,'F');
              doc.setDrawColor(226,232,240); doc.roundedRect(left,12,36,21,2,2,'S');
              doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.text(safeText(ufxVal('#ufx-inv-business'),'LOGO').slice(0,12), left+18, 24, {align:'center'});
            }
            doc.setFont('helvetica','bold'); doc.setFontSize(15); doc.text(safeText(ufxVal('#ufx-inv-business'),'Your Company'), left+44, 19);
            doc.setFont('helvetica','normal'); doc.setFontSize(9);
            var bd=wrap(ufxVal('#ufx-inv-business-details').replace(/\r?\n/g,'  •  '), 78);
            if(bd.length) doc.text(bd.slice(0,2), left+44, 25);
          }
          doc.setFont('helvetica','bold'); doc.setFontSize(26); rightText(continued?'INVOICE CONT.':'INVOICE', right, 18);
          doc.setFont('helvetica','normal'); doc.setFontSize(10);
          rightText('Invoice #: '+safeText(ufxVal('#ufx-inv-number'),'INV-001'), right, 27);
          rightText('Issue: '+textDate(ufxVal('#ufx-inv-date'))+'   Due: '+textDate(ufxVal('#ufx-inv-due')), right, 34);
        }
        drawPageHeader(false);
        y=52;

        function block(label, text, x, width){
          doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(100,116,139); doc.text(label.toUpperCase(),x,y);
          doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(15,23,42);
          var lines=[]; String(text||'').split(/\r?\n/).forEach(function(line){ lines=lines.concat(wrap(line||' ', width)); });
          doc.setDrawColor(226,232,240); doc.setFillColor(255,255,255); doc.roundedRect(x,y+4,width+4,Math.max(24,lines.length*4.7+10),2,2,'FD');
          doc.text(lines,x+3,y+11);
          return y+4+Math.max(24,lines.length*4.7+10);
        }
        var from=safeText(ufxVal('#ufx-inv-business'),'Your Company') + (ufxVal('#ufx-inv-business-details').trim() ? '\n'+ufxVal('#ufx-inv-business-details') : '');
        var to=safeText(ufxVal('#ufx-inv-client'),'Client Name') + (ufxVal('#ufx-inv-client-details').trim() ? '\n'+ufxVal('#ufx-inv-client-details') : '');
        var b1=block('From',from,left,78), b2=block('Bill To',to,110,78);
        y=Math.max(b1,b2)+10;

        var cols=[{t:'Description',w:78,a:'left'},{t:'Qty',w:14,a:'right'},{t:'Unit',w:25,a:'right'},{t:'Disc',w:18,a:'right'},{t:'Tax',w:16,a:'right'},{t:'Amount',w:26,a:'right'}], tableW=cols.reduce(function(a,b){return a+b.w;},0);
        function tableHeader(){
          ensure(14);
          var c=hexToRgb(accent);
          doc.setFillColor(c[0],c[1],c[2]); doc.roundedRect(left,y,tableW,9,2,2,'F');
          doc.setTextColor(255,255,255); doc.setFont('helvetica','bold'); doc.setFontSize(8.5);
          var x=left;
          cols.forEach(function(col){
            doc.text(col.t, col.a==='right'?x+col.w-2:x+2, y+6, {align:col.a==='right'?'right':'left'});
            x+=col.w;
          });
          y+=10; doc.setTextColor(15,23,42);
        }
        function drawItem(it, index){
          var desc=wrap(it.desc,74), h=Math.max(9,desc.length*4.4+4);
          if(y+h>pageH-44){ doc.addPage(); y=18; drawPageHeader(true); y=48; tableHeader(); }
          doc.setFillColor(index%2===0?255:248,index%2===0?255:250,index%2===0?255:252);
          doc.rect(left,y,tableW,h,'F');
          doc.setDrawColor(226,232,240); doc.line(left,y+h,left+tableW,y+h);
          doc.setFont('helvetica','normal'); doc.setFontSize(9); doc.setTextColor(15,23,42);
          doc.text(desc,left+2,y+5.5);
          rightText(it.qty,left+78+14-2,y+5.5);
          rightText(fmtMoney(it.price),left+78+14+25-2,y+5.5);
          rightText(it.disc.toFixed(2)+'%',left+78+14+25+18-2,y+5.5);
          rightText(it.tax.toFixed(2)+'%',left+78+14+25+18+16-2,y+5.5);
          doc.setFont('helvetica','bold'); rightText(fmtMoney(it.total),left+tableW-2,y+5.5);
          y+=h;
        }
        tableHeader();
        data.items.forEach(drawItem);
        y+=10; ensure(45);

        var boxX=116, boxW=right-boxX;
        doc.setFillColor(248,250,252); doc.roundedRect(boxX,y,boxW,35,3,3,'F');
        doc.setDrawColor(226,232,240); doc.roundedRect(boxX,y,boxW,35,3,3,'S');
        doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(71,85,105);
        doc.text('Subtotal',boxX+5,y+8); rightText(fmtMoney(data.sub),right-5,y+8);
        doc.text('Discount',boxX+5,y+15); rightText('- '+fmtMoney(data.disc),right-5,y+15);
        doc.text('Tax',boxX+5,y+22); rightText(fmtMoney(data.tax),right-5,y+22);
        doc.setDrawColor(203,213,225); doc.line(boxX+5,y+25,right-5,y+25);
        doc.setFont('helvetica','bold'); doc.setFontSize(13); doc.setTextColor(15,23,42);
        doc.text('Total',boxX+5,y+31); rightText(fmtMoney(data.total),right-5,y+31);
        y+=44;

        var notes=ufxVal('#ufx-inv-notes').trim();
        if(notes){
          ensure(24);
          doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(15,23,42); doc.text('Notes / Terms',left,y);
          doc.setFont('helvetica','normal'); doc.setFontSize(9); doc.setTextColor(71,85,105);
          doc.text(wrap(notes,178),left,y+6);
        }

        doc.setFontSize(8); doc.setTextColor(148,163,184);
        doc.text('Generated by Uptime Fixer Premium Invoice Generator',left,pageH-9);
        doc.save((safeText(ufxVal('#ufx-inv-number'),'invoice'))+'.pdf');
        T('Premium invoice downloaded.');
      }).catch(function(e){ console.error(e); T('Could not generate invoice PDF.'); });
    });
  })();




  (function base64Tool(){
    var input=$('#ufx-base64-input'); if(!input) return;
    function enc(){ try{ $('#ufx-base64-output').value=btoa(unescape(encodeURIComponent(input.value))); }catch(e){ T('Could not encode.'); } }
    function dec(){ try{ $('#ufx-base64-output').value=decodeURIComponent(escape(atob(input.value.trim()))); }catch(e){ T('Invalid Base64.'); } }
    $('#ufx-base64-encode').addEventListener('click',enc); $('#ufx-base64-decode').addEventListener('click',dec); $('#ufx-base64-clear').addEventListener('click',function(){ input.value=''; $('#ufx-base64-output').value=''; }); $('#ufx-base64-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-base64-output').value); });
  })();

  (function jwtDecoder(){
    var input=$('#ufx-jwt-input'); if(!input) return;
    function b64urlDecode(s){ s=s.replace(/-/g,'+').replace(/_/g,'/'); while(s.length%4)s+='='; return decodeURIComponent(escape(atob(s))); }
    $('#ufx-jwt-decode').addEventListener('click',function(){ try{ var p=input.value.trim().split('.'); if(p.length<2) throw new Error('Invalid token'); $('#ufx-jwt-header').value=JSON.stringify(JSON.parse(b64urlDecode(p[0])),null,2); $('#ufx-jwt-payload').value=JSON.stringify(JSON.parse(b64urlDecode(p[1])),null,2); ufxText($('#ufx-jwt-signature'),p[2]?p[2].slice(0,24)+'…':'No signature'); }catch(e){ T('Invalid JWT token.'); } });
  })();

  (function urlTool(){
    var input=$('#ufx-url-input'); if(!input) return;
    $('#ufx-url-encode').addEventListener('click',function(){ $('#ufx-url-output').value=encodeURI(input.value); });
    $('#ufx-url-component').addEventListener('click',function(){ $('#ufx-url-output').value=encodeURIComponent(input.value); });
    $('#ufx-url-decode').addEventListener('click',function(){ try{ $('#ufx-url-output').value=decodeURIComponent(input.value); }catch(e){ T('Invalid encoded text.'); } });
    $('#ufx-url-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-url-output').value); });
  })();

  (function uuidTool(){
    var btn=$('#ufx-uuid-generate'); if(!btn) return;
    function uuid(){ if(crypto.randomUUID) return crypto.randomUUID(); var b=new Uint8Array(16); crypto.getRandomValues(b); b[6]=(b[6]&15)|64; b[8]=(b[8]&63)|128; var h=Array.from(b,function(x){return x.toString(16).padStart(2,'0');}).join(''); return h.slice(0,8)+'-'+h.slice(8,12)+'-'+h.slice(12,16)+'-'+h.slice(16,20)+'-'+h.slice(20); }
    btn.addEventListener('click',function(){ var c=Math.min(100,Math.max(1,parseInt(ufxVal('#ufx-uuid-count'),10)||1)); var arr=[]; for(var i=0;i<c;i++) arr.push(uuid()); $('#ufx-uuid-output').value=arr.join('\n'); });
    $('#ufx-uuid-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-uuid-output').value); }); btn.click();
  })();

  (function timestampTool(){
    var btn=$('#ufx-ts-to-date'); if(!btn) return;
    $('#ufx-date-input').value=new Date(Date.now()-new Date().getTimezoneOffset()*60000).toISOString().slice(0,16);
    btn.addEventListener('click',function(){ var raw=ufxVal('#ufx-ts-input').trim(); var n=parseInt(raw,10); if(!n){ T('Enter a timestamp.'); return; } if(String(Math.abs(n)).length<=10) n*=1000; var d=new Date(n); ufxText($('#ufx-ts-local'),d.toLocaleString()); ufxText($('#ufx-ts-utc'),d.toUTCString()); });
    $('#ufx-date-to-ts').addEventListener('click',function(){ var d=new Date(ufxVal('#ufx-date-input')); if(isNaN(d)){ T('Choose a valid date.'); return; } ufxText($('#ufx-date-sec'),Math.floor(d.getTime()/1000)); ufxText($('#ufx-date-ms'),d.getTime()); });
  })();

  (function hashTool(){
    var btn=$('#ufx-hash-generate'); if(!btn) return;
    function md5cycle(x,k){var a=x[0],b=x[1],c=x[2],d=x[3];function cmn(q,a,b,x,s,t){a=(a+q+x+t)|0;return (((a<<s)|(a>>>(32-s)))+b)|0;}function ff(a,b,c,d,x,s,t){return cmn((b&c)|((~b)&d),a,b,x,s,t);}function gg(a,b,c,d,x,s,t){return cmn((b&d)|(c&(~d)),a,b,x,s,t);}function hh(a,b,c,d,x,s,t){return cmn(b^c^d,a,b,x,s,t);}function ii(a,b,c,d,x,s,t){return cmn(c^(b|(~d)),a,b,x,s,t);}a=ff(a,b,c,d,k[0],7,-680876936);d=ff(d,a,b,c,k[1],12,-389564586);c=ff(c,d,a,b,k[2],17,606105819);b=ff(b,c,d,a,k[3],22,-1044525330);a=ff(a,b,c,d,k[4],7,-176418897);d=ff(d,a,b,c,k[5],12,1200080426);c=ff(c,d,a,b,k[6],17,-1473231341);b=ff(b,c,d,a,k[7],22,-45705983);a=ff(a,b,c,d,k[8],7,1770035416);d=ff(d,a,b,c,k[9],12,-1958414417);c=ff(c,d,a,b,k[10],17,-42063);b=ff(b,c,d,a,k[11],22,-1990404162);a=ff(a,b,c,d,k[12],7,1804603682);d=ff(d,a,b,c,k[13],12,-40341101);c=ff(c,d,a,b,k[14],17,-1502002290);b=ff(b,c,d,a,k[15],22,1236535329);a=gg(a,b,c,d,k[1],5,-165796510);d=gg(d,a,b,c,k[6],9,-1069501632);c=gg(c,d,a,b,k[11],14,643717713);b=gg(b,c,d,a,k[0],20,-373897302);a=gg(a,b,c,d,k[5],5,-701558691);d=gg(d,a,b,c,k[10],9,38016083);c=gg(c,d,a,b,k[15],14,-660478335);b=gg(b,c,d,a,k[4],20,-405537848);a=gg(a,b,c,d,k[9],5,568446438);d=gg(d,a,b,c,k[14],9,-1019803690);c=gg(c,d,a,b,k[3],14,-187363961);b=gg(b,c,d,a,k[8],20,1163531501);a=gg(a,b,c,d,k[13],5,-1444681467);d=gg(d,a,b,c,k[2],9,-51403784);c=gg(c,d,a,b,k[7],14,1735328473);b=gg(b,c,d,a,k[12],20,-1926607734);a=hh(a,b,c,d,k[5],4,-378558);d=hh(d,a,b,c,k[8],11,-2022574463);c=hh(c,d,a,b,k[11],16,1839030562);b=hh(b,c,d,a,k[14],23,-35309556);a=hh(a,b,c,d,k[1],4,-1530992060);d=hh(d,a,b,c,k[4],11,1272893353);c=hh(c,d,a,b,k[7],16,-155497632);b=hh(b,c,d,a,k[10],23,-1094730640);a=hh(a,b,c,d,k[13],4,681279174);d=hh(d,a,b,c,k[0],11,-358537222);c=hh(c,d,a,b,k[3],16,-722521979);b=hh(b,c,d,a,k[6],23,76029189);a=hh(a,b,c,d,k[9],4,-640364487);d=hh(d,a,b,c,k[12],11,-421815835);c=hh(c,d,a,b,k[15],16,530742520);b=hh(b,c,d,a,k[2],23,-995338651);a=ii(a,b,c,d,k[0],6,-198630844);d=ii(d,a,b,c,k[7],10,1126891415);c=ii(c,d,a,b,k[14],15,-1416354905);b=ii(b,c,d,a,k[5],21,-57434055);a=ii(a,b,c,d,k[12],6,1700485571);d=ii(d,a,b,c,k[3],10,-1894986606);c=ii(c,d,a,b,k[10],15,-1051523);b=ii(b,c,d,a,k[1],21,-2054922799);a=ii(a,b,c,d,k[8],6,1873313359);d=ii(d,a,b,c,k[15],10,-30611744);c=ii(c,d,a,b,k[6],15,-1560198380);b=ii(b,c,d,a,k[13],21,1309151649);a=ii(a,b,c,d,k[4],6,-145523070);d=ii(d,a,b,c,k[11],10,-1120210379);c=ii(c,d,a,b,k[2],15,718787259);b=ii(b,c,d,a,k[9],21,-343485551);x[0]=(x[0]+a)|0;x[1]=(x[1]+b)|0;x[2]=(x[2]+c)|0;x[3]=(x[3]+d)|0;}
    function md5blk(s){var md5blks=[],i;for(i=0;i<64;i+=4)md5blks[i>>2]=s.charCodeAt(i)+(s.charCodeAt(i+1)<<8)+(s.charCodeAt(i+2)<<16)+(s.charCodeAt(i+3)<<24);return md5blks;}function md51(s){var n=s.length,state=[1732584193,-271733879,-1732584194,271733878],i;for(i=64;i<=n;i+=64)md5cycle(state,md5blk(s.substring(i-64,i)));s=s.substring(i-64);var tail=Array(16).fill(0);for(i=0;i<s.length;i++)tail[i>>2]|=s.charCodeAt(i)<<((i%4)<<3);tail[i>>2]|=0x80<<((i%4)<<3);if(i>55){md5cycle(state,tail);tail=Array(16).fill(0);}tail[14]=n*8;md5cycle(state,tail);return state;}function rhex(n){var s='',j;for(j=0;j<4;j++)s+=('0'+((n>>(j*8+4))&15).toString(16)).slice(-1)+('0'+((n>>(j*8))&15).toString(16)).slice(-1);return s;}function md5(s){return md51(unescape(encodeURIComponent(s))).map(rhex).join('');}
    async function sha(algo,text){ var buf=await crypto.subtle.digest(algo,new TextEncoder().encode(text)); return Array.from(new Uint8Array(buf)).map(function(b){return b.toString(16).padStart(2,'0');}).join(''); }
    btn.addEventListener('click',function(){ var text=ufxVal('#ufx-hash-input'); Promise.all([sha('SHA-1',text),sha('SHA-256',text),sha('SHA-512',text)]).then(function(v){ var rows=[['MD5',md5(text)],['SHA-1',v[0]],['SHA-256',v[1]],['SHA-512',v[2]]]; $('#ufx-hash-output').innerHTML=rows.map(function(r){return '<div class="at-output-row"><div class="at-output-lbl">'+r[0]+'</div><div class="at-output-val" style="word-break:break-all;">'+r[1]+'</div></div>';}).join(''); }); });
  })();

  (function regexTester(){
    var btn=$('#ufx-regex-test'); if(!btn) return;
    btn.addEventListener('click',function(){ try{ var flags=ufxVal('#ufx-regex-flags')||'g'; if(flags.indexOf('g')===-1) flags+='g'; var re=new RegExp(ufxVal('#ufx-regex-pattern'),flags), text=ufxVal('#ufx-regex-text'), m, arr=[]; while((m=re.exec(text))!==null){ arr.push({value:m[0],index:m.index}); if(m[0]==='') re.lastIndex++; } ufxText($('#ufx-regex-count'),arr.length); $('#ufx-regex-results').innerHTML=arr.length?arr.slice(0,100).map(function(x,i){return '<div class="at-output-row"><div class="at-output-lbl">#'+(i+1)+' @ '+x.index+'</div><div class="at-output-val">'+ufxEscapeHtml(x.value)+'</div></div>';}).join(''):'<p class="at-help">No matches found.</p>'; }catch(e){ T('Invalid regex pattern.'); } });
  })();

  (function cronGenerator(){
    var btn=$('#ufx-cron-generate'); if(!btn) return;
    function gen(){ var p=ufxVal('#ufx-cron-preset'), min=ufxVal('#ufx-cron-min')||'0', hr=ufxVal('#ufx-cron-hour')||'9', exp='0 * * * *', mean='Every hour'; if(p==='daily'){exp=min+' '+hr+' * * *'; mean='Every day at '+hr.padStart(2,'0')+':'+min.padStart(2,'0');} else if(p==='weekly'){exp=min+' '+hr+' * * 1'; mean='Every Monday at '+hr.padStart(2,'0')+':'+min.padStart(2,'0');} else if(p==='monthly'){exp=min+' '+hr+' 1 * *'; mean='On day 1 of every month at '+hr.padStart(2,'0')+':'+min.padStart(2,'0');} else if(p==='custom'){exp=min+' '+hr+' * * *'; mean='Every day at custom time';} $('#ufx-cron-output').value=exp; ufxText($('#ufx-cron-meaning'),mean); }
    btn.addEventListener('click',gen); $('#ufx-cron-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-cron-output').value); }); gen();
  })();

  (function passwordGenerator(){
    var btn=$('#ufx-pass-generate'); if(!btn) return;
    function rand(chars){ var a=new Uint32Array(1); crypto.getRandomValues(a); return chars[a[0]%chars.length]; }
    btn.addEventListener('click',function(){ var len=Math.min(64,Math.max(6,parseInt(ufxVal('#ufx-pass-length'),10)||16)), count=Math.min(50,Math.max(1,parseInt(ufxVal('#ufx-pass-count'),10)||5)); var sets=[]; if($('#ufx-pass-upper').checked)sets.push('ABCDEFGHIJKLMNOPQRSTUVWXYZ'); if($('#ufx-pass-lower').checked)sets.push('abcdefghijklmnopqrstuvwxyz'); if($('#ufx-pass-num').checked)sets.push('0123456789'); if($('#ufx-pass-symbol').checked)sets.push('!@#$%^&*()-_=+[]{};:,.?/'); if(!sets.length){ T('Select at least one option.'); return; } var all=sets.join(''), out=[]; for(var i=0;i<count;i++){ var p=''; sets.forEach(function(s){p+=rand(s);}); while(p.length<len)p+=rand(all); out.push(p.split('').sort(function(){return Math.random()-.5;}).join('')); } $('#ufx-pass-output').value=out.join('\n'); });
    $('#ufx-pass-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-pass-output').value); }); btn.click();
  })();

  (function loremGenerator(){
    var btn=$('#ufx-lorem-generate'); if(!btn) return; var words='lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat'.split(' ');
    function makeWords(n){ var out=[]; for(var i=0;i<n;i++) out.push(words[i%words.length]); return out.join(' '); }
    function sentence(){ var w=makeWords(12+Math.floor(Math.random()*10)); return w.charAt(0).toUpperCase()+w.slice(1)+'.'; }
    btn.addEventListener('click',function(){ var type=ufxVal('#ufx-lorem-type'), count=Math.min(100,Math.max(1,parseInt(ufxVal('#ufx-lorem-count'),10)||3)), out=[]; if(type==='words') out=[makeWords(count)]; else if(type==='sentences'){ for(var i=0;i<count;i++) out.push(sentence()); } else { for(var p=0;p<count;p++){ var s=[]; for(var j=0;j<4;j++) s.push(sentence()); out.push(s.join(' ')); } } $('#ufx-lorem-output').value=out.join(type==='paragraphs'?'\n\n':' '); });
    $('#ufx-lorem-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-lorem-output').value); }); btn.click();
  })();

  /* ===== MORE TOOLS ===== */
  function setupPresetResizer(prefix, width, height){
    var input=$('#'+prefix+'-file'); if(!input) return; var canvas=$('#'+prefix+'-canvas'); var info=$('#'+prefix+'-info'); var btn=$('#'+prefix+'-btn'); var modeEl=$('#'+prefix+'-mode'); var bgEl=$('#'+prefix+'-bg'); var img=null;
    function draw(){ var ctx=canvas.getContext('2d'); canvas.width=width; canvas.height=height; ctx.clearRect(0,0,width,height); ctx.fillStyle=(bgEl?bgEl.value:'#ffffff'); ctx.fillRect(0,0,width,height); if(!img){ if(info) info.textContent='Upload an image to preview.'; return; } var mode=modeEl?modeEl.value:'cover'; var s=Math[mode==='contain'?'min':'max'](width/img.width, height/img.height); var dw=img.width*s, dh=img.height*s, dx=(width-dw)/2, dy=(height-dh)/2; ctx.drawImage(img,dx,dy,dw,dh); if(info) info.textContent='Preview ready — '+width+' × '+height+' px'; }
    input.addEventListener('change',function(){ var file=input.files[0]; if(!file) return; var fr=new FileReader(); fr.onload=function(){ img=new Image(); img.onload=draw; img.src=fr.result; }; fr.readAsDataURL(file); });
    if(modeEl) modeEl.addEventListener('change',draw); if(bgEl) bgEl.addEventListener('input',draw);
    btn.addEventListener('click',function(){ if(!img){ T('Please choose an image first.'); return; } var a=document.createElement('a'); a.href=canvas.toDataURL('image/jpeg',0.92); a.download=prefix+'.jpg'; a.click(); });
  }
  setupPresetResizer('ufx-yt-thumb',1280,720); setupPresetResizer('ufx-ig-post',1080,1080); setupPresetResizer('ufx-fb-cover',820,312); setupPresetResizer('ufx-li-banner',1584,396); setupPresetResizer('ufx-x-header',1500,500);

  (function socialResizer(){ var input=$('#ufx-social-file'); if(!input) return; var canvas=$('#ufx-social-canvas'), preset=$('#ufx-social-preset'), mode=$('#ufx-social-mode'), bg=$('#ufx-social-bg'), btn=$('#ufx-social-btn'), info=$('#ufx-social-info'); var img=null; function dims(){ var p=(preset.value||'1280x720').split('x'); return {w:parseInt(p[0],10),h:parseInt(p[1],10)}; } function draw(){ var d=dims(), ctx=canvas.getContext('2d'); canvas.width=d.w; canvas.height=d.h; ctx.fillStyle=bg.value; ctx.fillRect(0,0,d.w,d.h); if(!img){ info.textContent='Choose a preset and upload an image.'; return; } var s=Math[mode.value==='contain'?'min':'max'](d.w/img.width, d.h/img.height); var dw=img.width*s, dh=img.height*s, dx=(d.w-dw)/2, dy=(d.h-dh)/2; ctx.drawImage(img,dx,dy,dw,dh); info.textContent='Ready: '+d.w+' × '+d.h+' px'; } input.addEventListener('change',function(){ var file=input.files[0]; if(!file) return; var fr=new FileReader(); fr.onload=function(){ img=new Image(); img.onload=draw; img.src=fr.result; }; fr.readAsDataURL(file); }); [preset,mode,bg].forEach(function(el){ el.addEventListener('change',draw); el.addEventListener('input',draw); }); btn.addEventListener('click',function(){ if(!img){ T('Please choose an image first.'); return; } var a=document.createElement('a'); a.href=canvas.toDataURL('image/jpeg',0.92); a.download='social-media-image.jpg'; a.click(); }); })();

  (function watermarkTool(){ var input=$('#ufx-watermark-file'); if(!input) return; var canvas=$('#ufx-watermark-canvas'), txt=$('#ufx-watermark-text'), color=$('#ufx-watermark-color'), opacity=$('#ufx-watermark-opacity'), size=$('#ufx-watermark-size'), pos=$('#ufx-watermark-position'), btn=$('#ufx-watermark-btn'); var img=null; function draw(){ var ctx=canvas.getContext('2d'); if(!img){ canvas.width=640; canvas.height=360; ctx.clearRect(0,0,canvas.width,canvas.height); return; } canvas.width=img.width; canvas.height=img.height; ctx.drawImage(img,0,0); ctx.save(); ctx.globalAlpha=Math.max(0.01,Math.min(1,parseFloat(opacity.value||45)/100)); ctx.fillStyle=color.value; ctx.font='bold '+(parseInt(size.value,10)||36)+'px Arial'; var text=txt.value||'Watermark'; var m=ctx.measureText(text), x=20, y=50; if(pos.value==='bottom-right'){x=canvas.width-m.width-20; y=canvas.height-20;} else if(pos.value==='bottom-left'){x=20; y=canvas.height-20;} else if(pos.value==='top-right'){x=canvas.width-m.width-20; y=50;} else if(pos.value==='center'){x=(canvas.width-m.width)/2; y=canvas.height/2;} ctx.fillText(text,x,y); ctx.restore(); } input.addEventListener('change',function(){ var f=input.files[0]; if(!f) return; var fr=new FileReader(); fr.onload=function(){ img=new Image(); img.onload=draw; img.src=fr.result; }; fr.readAsDataURL(f); }); [txt,color,opacity,size,pos].forEach(function(el){ el.addEventListener('input',draw); el.addEventListener('change',draw); }); btn.addEventListener('click',function(){ if(!img){ T('Please choose an image first.'); return; } var a=document.createElement('a'); a.href=canvas.toDataURL('image/png'); a.download='watermarked-image.png'; a.click(); }); })();

  (function imageToPdf(){ var input=$('#ufx-imgpdf-files'); if(!input) return; var list=$('#ufx-imgpdf-list'); $('#ufx-imgpdf-btn').addEventListener('click',function(){ var files=Array.from(input.files||[]); if(!files.length){ T('Please choose one or more images.'); return; } list.innerHTML=files.map(function(f){ return '<div class="at-output-row"><div class="at-output-lbl">'+ufxEscapeHtml(f.name)+'</div><div class="at-output-val">'+Math.round(f.size/1024)+' KB</div></div>'; }).join(''); ufxLoadJsPdf().then(async function(){ var size=ufxVal('#ufx-imgpdf-size')||'a4', ori=ufxVal('#ufx-imgpdf-ori')||'portrait', margin=parseFloat(ufxVal('#ufx-imgpdf-margin')||'10'); var pdf=new window.jspdf.jsPDF({orientation:ori,unit:'mm',format:size}); for(var i=0;i<files.length;i++){ var data=await new Promise(function(res){ var fr=new FileReader(); fr.onload=function(){ res(fr.result); }; fr.readAsDataURL(files[i]); }); var img=await new Promise(function(res){ var im=new Image(); im.onload=function(){ res(im); }; im.src=data; }); if(i>0) pdf.addPage(size,ori); var pageW=pdf.internal.pageSize.getWidth(), pageH=pdf.internal.pageSize.getHeight(), maxW=pageW-(margin*2), maxH=pageH-(margin*2), scale=Math.min(maxW/img.width, maxH/img.height), w=img.width*scale, h=img.height*scale, x=(pageW-w)/2, y=(pageH-h)/2; pdf.addImage(data, 'JPEG', x, y, w, h); } pdf.save('images-to-pdf.pdf'); T('PDF downloaded.'); }).catch(function(e){ console.error(e); T('Could not create PDF.'); }); }); })();

  (function bulkCompressor(){ var input=$('#ufx-bulkcomp-files'); if(!input) return; var q=$('#ufx-bulkcomp-quality'), ql=$('#ufx-bulkcomp-quality-label'), out=$('#ufx-bulkcomp-results'); q.addEventListener('input',function(){ ql.textContent=q.value+'%'; }); $('#ufx-bulkcomp-btn').addEventListener('click',async function(){ var files=Array.from(input.files||[]); if(!files.length){ T('Please choose images first.'); return; } out.innerHTML=''; var quality=(parseInt(q.value,10)||80)/100; for(var i=0;i<files.length;i++){ var f=files[i]; var data=await new Promise(function(res){ var fr=new FileReader(); fr.onload=function(){ res(fr.result); }; fr.readAsDataURL(f); }); var img=await new Promise(function(res){ var im=new Image(); im.onload=function(){ res(im); }; im.src=data; }); var c=document.createElement('canvas'), ctx=c.getContext('2d'); c.width=img.width; c.height=img.height; ctx.drawImage(img,0,0); await new Promise(function(resolve){ c.toBlob(function(blob){ var url=URL.createObjectURL(blob); out.innerHTML += '<div class="at-output-row"><div class="at-output-lbl">'+ufxEscapeHtml(f.name)+'</div><div class="at-output-val"><a href="'+url+'" download="compressed-'+ufxEscapeHtml(f.name.replace(/\.[^.]+$/,''))+'.jpg">Download</a></div></div>'; resolve(); }, 'image/jpeg', quality); }); } T('Compressed images are ready.'); }); })();

  (function metaTagGenerator(){ var btn=$('#ufx-meta-generate'); if(!btn) return; function run(){ var title=ufxVal('#ufx-meta-title'), desc=ufxVal('#ufx-meta-desc'), url=ufxVal('#ufx-meta-url'), image=ufxVal('#ufx-meta-image'), site=ufxVal('#ufx-meta-site'), card=ufxVal('#ufx-meta-card')||'summary_large_image'; var lines=[]; if(title) lines.push('<title>'+title+'</title>'); if(desc) lines.push('<meta name="description" content="'+desc.replace(/"/g,'&quot;')+'">'); if(url) lines.push('<link rel="canonical" href="'+url+'">'); if(title) lines.push('<meta property="og:title" content="'+title.replace(/"/g,'&quot;')+'">'); if(desc) lines.push('<meta property="og:description" content="'+desc.replace(/"/g,'&quot;')+'">'); if(url) lines.push('<meta property="og:url" content="'+url+'">'); if(site) lines.push('<meta property="og:site_name" content="'+site.replace(/"/g,'&quot;')+'">'); if(image) lines.push('<meta property="og:image" content="'+image+'">'); lines.push('<meta name="twitter:card" content="'+card+'">'); if(title) lines.push('<meta name="twitter:title" content="'+title.replace(/"/g,'&quot;')+'">'); if(desc) lines.push('<meta name="twitter:description" content="'+desc.replace(/"/g,'&quot;')+'">'); if(image) lines.push('<meta name="twitter:image" content="'+image+'">'); $('#ufx-meta-output').value=lines.join('\n'); }
    btn.addEventListener('click',run); $('#ufx-meta-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-meta-output').value); });
  })();

  (function ogPreview(){ var btn=$('#ufx-og-update'); if(!btn) return; function run(){ ufxText($('#ufx-og-sitename'), ufxVal('#ufx-og-site')||'Example Site'); ufxText($('#ufx-og-title-out'), ufxVal('#ufx-og-title')||'Your Open Graph Title'); ufxText($('#ufx-og-desc-out'), ufxVal('#ufx-og-desc')||'Your description will appear here.'); var img=ufxVal('#ufx-og-image'); $('#ufx-og-imagebox').innerHTML = img ? '<img src="'+ufxEscapeHtml(img)+'" alt="" style="width:100%;height:100%;object-fit:cover;">' : 'Image Preview'; } btn.addEventListener('click',run); run(); })();
  (function twPreview(){ var btn=$('#ufx-tw-update'); if(!btn) return; function run(){ ufxText($('#ufx-tw-title-out'), ufxVal('#ufx-tw-title')||'Your Twitter Card Title'); ufxText($('#ufx-tw-desc-out'), ufxVal('#ufx-tw-desc')||'Your description will appear here.'); var img=ufxVal('#ufx-tw-image'); $('#ufx-tw-imagebox').innerHTML = img ? '<img src="'+ufxEscapeHtml(img)+'" alt="" style="width:100%;height:100%;object-fit:cover;">' : 'Image Preview'; } btn.addEventListener('click',run); run(); })();
  (function serpPreview(){ var btn=$('#ufx-serp-update'); if(!btn) return; function run(){ var title=ufxVal('#ufx-serp-title'), url=ufxVal('#ufx-serp-url'), desc=ufxVal('#ufx-serp-desc'); ufxText($('#ufx-serp-title-out'), title); ufxText($('#ufx-serp-url-out'), url); ufxText($('#ufx-serp-desc-out'), desc); ufxText($('#ufx-serp-title-len'), title.length); ufxText($('#ufx-serp-desc-len'), desc.length); } btn.addEventListener('click',run); run(); })();
  (function keywordDensity(){ var btn=$('#ufx-kd-analyze'); if(!btn) return; btn.addEventListener('click',function(){ var txt=(ufxVal('#ufx-kd-text')||'').toLowerCase(); var words=txt.match(/[a-z0-9]+/g)||[]; var total=words.length, freq={}; words.forEach(function(w){ freq[w]=(freq[w]||0)+1; }); var rows=Object.keys(freq).sort(function(a,b){ return freq[b]-freq[a]; }).slice(0,20).map(function(k){ return '<div class="at-output-row"><div class="at-output-lbl">'+ufxEscapeHtml(k)+'</div><div class="at-output-val">'+freq[k]+' ('+((freq[k]/Math.max(1,total))*100).toFixed(2)+'%)</div></div>'; }).join(''); $('#ufx-kd-results').innerHTML=rows||'<p class="at-help">No words found.</p>'; ufxText($('#ufx-kd-count'), total); var focus=(ufxVal('#ufx-kd-keyword')||'').toLowerCase().trim(); if(focus){ var escaped=focus.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); var matches=(txt.match(new RegExp(escaped,'g'))||[]).length; ufxText($('#ufx-kd-density'), ((matches/Math.max(1,total))*100).toFixed(2)+'%'); } else { ufxText($('#ufx-kd-density'),'—'); } }); })();
  (function robotsChecker(){ var btn=$('#ufx-robots-check-btn'); if(!btn) return; btn.addEventListener('click',function(){ var txt=ufxVal('#ufx-robots-check-input'); var lines=txt.split(/\r?\n/).map(function(x){ return x.trim(); }).filter(Boolean); var issues=[]; if(!/user-agent\s*:/i.test(txt)) issues.push('Missing a User-agent directive.'); if(!/(disallow|allow)\s*:/i.test(txt)) issues.push('No Allow or Disallow directives found.'); if(/sitemap\s*:/i.test(txt)===false) issues.push('No Sitemap directive found (optional but recommended).'); var html='<div class="at-output-row"><div class="at-output-lbl">Lines</div><div class="at-output-val">'+lines.length+'</div></div>'; html += issues.length ? issues.map(function(i){ return '<div class="at-output-row"><div class="at-output-lbl">Notice</div><div class="at-output-val">'+ufxEscapeHtml(i)+'</div></div>'; }).join('') : '<p class="at-help">Looks good. No obvious issues found.</p>'; $('#ufx-robots-check-results').innerHTML=html; }); })();
  (function sitemapExtractor(){ var btn=$('#ufx-sitemap-extract-btn'); if(!btn) return; btn.addEventListener('click',function(){ var txt=ufxVal('#ufx-sitemap-extract-input'); var urls=[], m, re=/<loc>(.*?)<\/loc>/gi; while((m=re.exec(txt))!==null) urls.push(m[1].trim()); $('#ufx-sitemap-extract-output').value=urls.join('\n'); T(urls.length+' URL(s) extracted.'); }); $('#ufx-sitemap-extract-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-sitemap-extract-output').value); }); })();
  (function hreflangGen(){ var btn=$('#ufx-hreflang-generate'); if(!btn) return; btn.addEventListener('click',function(){ var out=(ufxVal('#ufx-hreflang-input')||'').split(/\r?\n/).map(function(line){ var p=line.split('|').map(function(x){ return x.trim(); }); return p[0]&&p[1] ? '<link rel="alternate" hreflang="'+p[0]+'" href="'+p[1]+'">' : ''; }).filter(Boolean).join('\n'); $('#ufx-hreflang-output').value=out; }); $('#ufx-hreflang-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-hreflang-output').value); }); })();
  (function schemaGen(){ var btn=$('#ufx-schema-generate'); if(!btn) return; btn.addEventListener('click',function(){ var type=ufxVal('#ufx-schema-type')||'Organization', obj={'@context':'https://schema.org','@type':type}; var name=ufxVal('#ufx-schema-name'), url=ufxVal('#ufx-schema-url'), desc=ufxVal('#ufx-schema-desc'), image=ufxVal('#ufx-schema-image'); if(name) obj.name=name; if(type==='Article') obj.headline=name||''; if(url) obj.url=url; if(desc) obj.description=desc; if(image) obj.image=image; $('#ufx-schema-output').value=JSON.stringify(obj,null,2); }); $('#ufx-schema-copy').addEventListener('click',function(){ ufxCopyValue($('#ufx-schema-output').value); }); })();
  (function canonicalChecker(){ var btn=$('#ufx-canonical-check'); if(!btn) return; btn.addEventListener('click',function(){ var txt=ufxVal('#ufx-canonical-html'); var m=txt.match(/<link[^>]+rel=["']canonical["'][^>]+href=["']([^"']+)/i) || txt.match(/href=["']([^"']+)/i); ufxText($('#ufx-canonical-result'), m?m[1]:'Not found'); }); })();

  function simpleMinify(str){ return String(str||'').replace(/\/\*[\s\S]*?\*\//g,'').replace(/<!--([\s\S]*?)-->/g,'').replace(/\s+/g,' ').replace(/>\s+</g,'><').trim(); }
  function setupSimpleTransformer(runSel,inSel,outSel,copySel,transform){ var btn=$(runSel); if(!btn) return; btn.addEventListener('click',function(){ $(outSel).value=transform(ufxVal(inSel)); }); $(copySel).addEventListener('click',function(){ ufxCopyValue($(outSel).value); }); }
  setupSimpleTransformer('#ufx-htmlmin-run','#ufx-htmlmin-input','#ufx-htmlmin-output','#ufx-htmlmin-copy',simpleMinify);
  setupSimpleTransformer('#ufx-cssmin-run','#ufx-cssmin-input','#ufx-cssmin-output','#ufx-cssmin-copy',function(s){ return String(s||'').replace(/\/\*[\s\S]*?\*\//g,'').replace(/\s+/g,' ').replace(/\s*([{}:;,])\s*/g,'$1').replace(/;}/g,'}').trim(); });
  setupSimpleTransformer('#ufx-jsmin-run','#ufx-jsmin-input','#ufx-jsmin-output','#ufx-jsmin-copy',function(s){ return String(s||'').replace(/\/\/.*$/gm,'').replace(/\/\*[\s\S]*?\*\//g,'').replace(/\s+/g,' ').trim(); });
  setupSimpleTransformer('#ufx-htmlbeaut-run','#ufx-htmlbeaut-input','#ufx-htmlbeaut-output','#ufx-htmlbeaut-copy',function(s){ return String(s||'').replace(/>\s*</g,'>\n<'); });
  setupSimpleTransformer('#ufx-cssbeaut-run','#ufx-cssbeaut-input','#ufx-cssbeaut-output','#ufx-cssbeaut-copy',function(s){ return String(s||'').replace(/}/g,'}\n').replace(/{/g,'{\n  ').replace(/;/g,';\n  '); });

  (function contrastChecker(){ var btn=$('#ufx-contrast-check'); if(!btn) return; function hex2rgb(h){ h=(h||'#000').replace('#',''); if(h.length===3) h=h.split('').map(function(x){return x+x;}).join(''); var n=parseInt(h,16); return [(n>>16)&255,(n>>8)&255,n&255]; } function lum(h){ var c=hex2rgb(h).map(function(v){ v/=255; return v<=0.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4); }); return 0.2126*c[0]+0.7152*c[1]+0.0722*c[2]; } function run(){ var fg=ufxVal('#ufx-contrast-fg'), bg=ufxVal('#ufx-contrast-bg'); var L1=lum(fg), L2=lum(bg), ratio=(Math.max(L1,L2)+0.05)/(Math.min(L1,L2)+0.05); $('#ufx-contrast-preview').style.color=fg; $('#ufx-contrast-preview').style.background=bg; ufxText($('#ufx-contrast-ratio'), ratio.toFixed(2)+':1'); ufxText($('#ufx-contrast-aa'), ratio>=4.5?'Pass':'Fail'); ufxText($('#ufx-contrast-aaa'), ratio>=7?'Pass':'Fail'); } btn.addEventListener('click',run); run(); })();

  function setupDocTool(prefix,label){ var btn=$('#'+prefix+'-download'); if(!btn) return; function fmtDoc(n){ var cur=(ufxVal('#'+prefix+'-currency')||'$').trim()||'$'; return cur + (parseFloat(n)||0).toFixed(2); } function parse(){ var rows=(ufxVal('#'+prefix+'-items')||'').split(/\r?\n/).map(function(line){ var p=line.split('|').map(function(x){ return x.trim(); }); if(!p[0]) return null; var qty=parseFloat(String(p[1]||1).replace(/[^0-9.\-]/g,''))||1; var price=parseFloat(String(p[2]||0).replace(/[^0-9.\-]/g,''))||0; return {desc:p[0],qty:qty,price:price,total:qty*price}; }).filter(Boolean); var total=rows.reduce(function(a,b){ return a+b.total; },0); ufxText($('#'+prefix+'-count'), rows.length); ufxText($('#'+prefix+'-total'), fmtDoc(total)); return {rows:rows,total:total}; } var ta=$('#'+prefix+'-items'); if(ta) ta.addEventListener('input',parse); parse(); btn.addEventListener('click',function(){ var d=parse(); if(!d.rows.length){ T('Please add at least one item.'); return; } ufxLoadJsPdf().then(function(){ var doc=new window.jspdf.jsPDF({unit:'mm',format:'a4'}), y=18, left=14, right=196; doc.setFont('helvetica','bold'); doc.setFontSize(22); doc.text(label.toUpperCase(), left, y); doc.setFontSize(10); doc.setFont('helvetica','normal'); doc.text(label+' #: '+(ufxVal('#'+prefix+'-number')||''), left, y+8); doc.text('Date: '+(ufxVal('#'+prefix+'-date')||''), right, y+8, {align:'right'}); y+=18; doc.setFont('helvetica','bold'); doc.text('From', left, y); doc.text('Client', 110, y); y+=6; doc.setFont('helvetica','normal'); doc.text(ufxVal('#'+prefix+'-business')||'Your Company', left, y); doc.text(ufxVal('#'+prefix+'-client')||'Client Name', 110, y); y+=12; doc.setDrawColor(226,232,240); doc.line(left,y,right,y); y+=6; doc.setFont('helvetica','bold'); doc.text('Description', left, y); doc.text('Qty', 130, y, {align:'right'}); doc.text('Price', 158, y, {align:'right'}); doc.text('Amount', right, y, {align:'right'}); y+=5; doc.line(left,y,right,y); y+=6; doc.setFont('helvetica','normal'); d.rows.forEach(function(r){ doc.text(doc.splitTextToSize(r.desc, 100), left, y); doc.text(String(r.qty), 130, y, {align:'right'}); doc.text(fmtDoc(r.price), 158, y, {align:'right'}); doc.text(fmtDoc(r.total), right, y, {align:'right'}); y+=7; if(y>260){ doc.addPage(); y=20; } }); y+=4; doc.line(120,y,right,y); y+=8; doc.setFont('helvetica','bold'); doc.text('Total', 140, y); doc.text(fmtDoc(d.total), right, y, {align:'right'}); var notes=ufxVal('#'+prefix+'-notes'); if(notes){ y+=14; doc.setFontSize(10); doc.setFont('helvetica','bold'); doc.text('Notes', left, y); doc.setFont('helvetica','normal'); doc.text(doc.splitTextToSize(notes, 180), left, y+6); } doc.save((ufxVal('#'+prefix+'-number')||label.toLowerCase())+'.pdf'); T(label+' downloaded.'); }); }); }
  setupDocTool('ufx-quote','Quotation'); setupDocTool('ufx-receipt','Receipt'); setupDocTool('ufx-estimate','Estimate');

  (function taxCalc(){ var btn=$('#ufx-tax-calc'); if(!btn) return; function run(){ var amt=parseFloat(ufxVal('#ufx-tax-amount'))||0, rate=(parseFloat(ufxVal('#ufx-tax-rate'))||0)/100, mode=ufxVal('#ufx-tax-mode'); var sub=amt, tax=0, total=0; if(mode==='inclusive'){ total=amt; sub=rate?amt/(1+rate):amt; tax=total-sub; } else { sub=amt; tax=amt*rate; total=sub+tax; } ufxText($('#ufx-tax-sub'), sub.toFixed(2)); ufxText($('#ufx-tax-tax'), tax.toFixed(2)); ufxText($('#ufx-tax-total'), total.toFixed(2)); } btn.addEventListener('click',run); run(); })();
  (function marginCalc(){ var btn=$('#ufx-margin-calc'); if(!btn) return; function run(){ var c=parseFloat(ufxVal('#ufx-margin-cost'))||0, s=parseFloat(ufxVal('#ufx-margin-sell'))||0, p=s-c, margin=s?((p/s)*100):0, markup=c?((p/c)*100):0; ufxText($('#ufx-margin-profit'), p.toFixed(2)); ufxText($('#ufx-margin-margin'), margin.toFixed(2)+'%'); ufxText($('#ufx-margin-markup'), markup.toFixed(2)+'%'); } btn.addEventListener('click',run); run(); })();
  (function paypalCalc(){ var btn=$('#ufx-paypal-calc'); if(!btn) return; function run(){ var a=parseFloat(ufxVal('#ufx-paypal-amount'))||0, r=(parseFloat(ufxVal('#ufx-paypal-rate'))||0)/100, f=parseFloat(ufxVal('#ufx-paypal-fixed'))||0, fee=(a*r)+f, net=a-fee, gross=(a+f)/(1-r||1); ufxText($('#ufx-paypal-fee'), fee.toFixed(2)); ufxText($('#ufx-paypal-net'), net.toFixed(2)); ufxText($('#ufx-paypal-gross'), gross.toFixed(2)); } btn.addEventListener('click',run); run(); })();
  (function stripeCalc(){ var btn=$('#ufx-stripe-calc'); if(!btn) return; function run(){ var a=parseFloat(ufxVal('#ufx-stripe-amount'))||0, r=(parseFloat(ufxVal('#ufx-stripe-rate'))||0)/100, f=parseFloat(ufxVal('#ufx-stripe-fixed'))||0, fee=(a*r)+f, net=a-fee; ufxText($('#ufx-stripe-fee'), fee.toFixed(2)); ufxText($('#ufx-stripe-net'), net.toFixed(2)); } btn.addEventListener('click',run); run(); })();
  (function freelanceCalc(){ var btn=$('#ufx-free-calc'); if(!btn) return; function run(){ var income=parseFloat(ufxVal('#ufx-free-income'))||0, costs=parseFloat(ufxVal('#ufx-free-costs'))||0, hours=parseFloat(ufxVal('#ufx-free-hours'))||1; ufxText($('#ufx-free-rate'), ((income+costs)/hours).toFixed(2)); } btn.addEventListener('click',run); run(); })();


  /* ===== NO-API EXTRA TOOLS ===== */
  function ufxParsePageRanges(text,total){
    if(!text || !String(text).trim() || /^all$/i.test(String(text).trim())){
      return Array.from({length:total},function(_,i){return i;});
    }
    var out=[];
    String(text).split(',').forEach(function(part){
      part=part.trim(); if(!part) return;
      if(part.indexOf('-')>-1){
        var p=part.split('-').map(function(x){return parseInt(x,10);});
        if(isNaN(p[0])||isNaN(p[1])) return;
        for(var i=Math.min(p[0],p[1]); i<=Math.max(p[0],p[1]); i++){ if(i>=1 && i<=total) out.push(i-1); }
      } else {
        var n=parseInt(part,10); if(n>=1 && n<=total) out.push(n-1);
      }
    });
    return Array.from(new Set(out));
  }

  (function pdfRotateTool(){
    var input=$('#ufx-pdf-rotate-file'); if(!input) return;
    $('#ufx-pdf-rotate-btn').addEventListener('click',function(){
      var file=input.files[0]; if(!file){ T('Choose a PDF first.'); return; }
      var btn=$('#ufx-pdf-rotate-btn'); btn.disabled=true; btn.textContent='Rotating...';
      ufxLoadPdfLib().then(async function(){
        var bytes=await file.arrayBuffer();
        var pdf=await PDFLib.PDFDocument.load(bytes);
        var pages=pdf.getPages();
        var selected=ufxParsePageRanges(ufxVal('#ufx-pdf-rotate-pages'),pages.length);
        var angle=parseInt(ufxVal('#ufx-pdf-rotate-angle'),10)||90;
        selected.forEach(function(i){
          var current=pages[i].getRotation().angle || 0;
          pages[i].setRotation(PDFLib.degrees((current+angle)%360));
        });
        var out=await pdf.save();
        var name=ufxVal('#ufx-pdf-rotate-name') || file.name.replace(/\.pdf$/i,'')+'-rotated.pdf';
        ufxDownloadBlob(new Blob([out],{type:'application/pdf'}), name);
        $('#ufx-pdf-rotate-result').innerHTML='<div class="at-output-row"><div class="at-output-lbl">Rotated Pages</div><div class="at-output-val">'+selected.length+'</div></div>';
        T('PDF rotated.');
      }).catch(function(e){ console.error(e); T('Could not rotate PDF.'); }).finally(function(){ btn.disabled=false; btn.textContent='Rotate PDF'; });
    });
  })();

  (function pdfWatermarkTool(){
    var input=$('#ufx-pdf-watermark-file'); if(!input) return;
    $('#ufx-pdf-watermark-btn').addEventListener('click',function(){
      var file=input.files[0]; if(!file){ T('Choose a PDF first.'); return; }
      var btn=$('#ufx-pdf-watermark-btn'); btn.disabled=true; btn.textContent='Adding watermark...';
      ufxLoadPdfLib().then(async function(){
        var bytes=await file.arrayBuffer();
        var pdf=await PDFLib.PDFDocument.load(bytes);
        var pages=pdf.getPages();
        var font=await pdf.embedFont(PDFLib.StandardFonts.HelveticaBold);
        var text=ufxVal('#ufx-pdf-watermark-text') || 'CONFIDENTIAL';
        var size=parseInt(ufxVal('#ufx-pdf-watermark-size'),10)||48;
        var opacity=Math.max(0.01,Math.min(1,(parseInt(ufxVal('#ufx-pdf-watermark-opacity'),10)||18)/100));
        pages.forEach(function(page){
          var wh=page.getSize(), w=wh.width, h=wh.height;
          var tw=font.widthOfTextAtSize(text,size);
          page.drawText(text,{x:(w-tw)/2,y:h/2,size:size,font:font,color:PDFLib.rgb(0.45,0.45,0.45),opacity:opacity,rotate:PDFLib.degrees(-35)});
        });
        var out=await pdf.save();
        ufxDownloadBlob(new Blob([out],{type:'application/pdf'}), file.name.replace(/\.pdf$/i,'')+'-watermarked.pdf');
        $('#ufx-pdf-watermark-result').innerHTML='<div class="at-output-row"><div class="at-output-lbl">Watermarked Pages</div><div class="at-output-val">'+pages.length+'</div></div>';
        T('Watermark added.');
      }).catch(function(e){ console.error(e); T('Could not watermark PDF.'); }).finally(function(){ btn.disabled=false; btn.textContent='Add Watermark'; });
    });
  })();

  (function pdfPageRemover(){
    var input=$('#ufx-pdf-remove-file'); if(!input) return;
    $('#ufx-pdf-remove-btn').addEventListener('click',function(){
      var file=input.files[0]; if(!file){ T('Choose a PDF first.'); return; }
      var btn=$('#ufx-pdf-remove-btn'); btn.disabled=true; btn.textContent='Removing...';
      ufxLoadPdfLib().then(async function(){
        var bytes=await file.arrayBuffer();
        var src=await PDFLib.PDFDocument.load(bytes);
        var total=src.getPageCount();
        var remove=ufxParsePageRanges(ufxVal('#ufx-pdf-remove-pages'),total);
        if(!remove.length){ T('Enter pages to remove.'); return; }
        var keep=[];
        for(var i=0;i<total;i++){ if(remove.indexOf(i)===-1) keep.push(i); }
        if(!keep.length){ T('You cannot remove all pages.'); return; }
        var outPdf=await PDFLib.PDFDocument.create();
        var copied=await outPdf.copyPages(src,keep);
        copied.forEach(function(p){ outPdf.addPage(p); });
        var out=await outPdf.save();
        var name=ufxVal('#ufx-pdf-remove-name') || file.name.replace(/\.pdf$/i,'')+'-cleaned.pdf';
        ufxDownloadBlob(new Blob([out],{type:'application/pdf'}), name);
        $('#ufx-pdf-remove-result').innerHTML='<div class="at-output-row"><div class="at-output-lbl">Removed</div><div class="at-output-val">'+remove.length+' page(s)</div></div><div class="at-output-row"><div class="at-output-lbl">Remaining</div><div class="at-output-val">'+keep.length+' page(s)</div></div>';
        T('Pages removed.');
      }).catch(function(e){ console.error(e); T('Could not remove pages.'); }).finally(function(){ btn.disabled=false; btn.textContent='Remove Pages'; });
    });
  })();

  (function imageTargetCompressor(){
    var input=$('#ufx-img-target-file'); if(!input) return;
    var img=null, file=null, canvas=$('#ufx-img-target-canvas'), ctx=canvas.getContext('2d');
    input.addEventListener('change',function(){
      file=input.files[0]; if(!file) return;
      img=new Image(); img.onload=function(){ drawPreview(0.9); }; img.src=URL.createObjectURL(file);
    });
    function drawPreview(q){
      if(!img) return null;
      var maxW=parseInt(ufxVal('#ufx-img-target-width'),10)||1600;
      var scale=Math.min(1,maxW/img.width);
      canvas.width=Math.round(img.width*scale); canvas.height=Math.round(img.height*scale);
      ctx.fillStyle='#fff'; ctx.fillRect(0,0,canvas.width,canvas.height);
      ctx.drawImage(img,0,0,canvas.width,canvas.height);
      return canvas;
    }
    $('#ufx-img-target-btn').addEventListener('click',async function(){
      if(!img||!file){ T('Choose an image first.'); return; }
      var target=(parseInt(ufxVal('#ufx-img-target-kb'),10)||100)*1024;
      var q=0.92, blob=null;
      drawPreview(q);
      for(var i=0;i<12;i++){
        blob=await new Promise(function(resolve){ canvas.toBlob(resolve,'image/jpeg',q); });
        if(blob.size<=target || q<=0.15) break;
        q-=0.07;
      }
      ufxDownloadBlob(blob,file.name.replace(/\.[^.]+$/,'')+'-'+Math.round(blob.size/1024)+'kb.jpg');
      $('#ufx-img-target-info').innerHTML='<div class="at-output-row"><div class="at-output-lbl">Original</div><div class="at-output-val">'+ufxFmtBytes(file.size)+'</div></div><div class="at-output-row"><div class="at-output-lbl">Output</div><div class="at-output-val">'+ufxFmtBytes(blob.size)+'</div></div>';
      T('Image compressed.');
    });
  })();

  function ufxLoadImageFromInput(input, cb){
    var file=input.files[0]; if(!file){ T('Choose an image first.'); return; }
    var img=new Image(); img.onload=function(){ cb(img,file); }; img.src=URL.createObjectURL(file);
  }
  function ufxCoverDraw(ctx,img,w,h,zoom,yOffset,bg,clipCircle){
    ctx.clearRect(0,0,w,h); if(bg){ ctx.fillStyle=bg; ctx.fillRect(0,0,w,h); }
    if(clipCircle){ ctx.save(); ctx.beginPath(); ctx.arc(w/2,h/2,Math.min(w,h)/2,0,Math.PI*2); ctx.clip(); }
    var base=Math.max(w/img.width,h/img.height)*(zoom||1);
    var dw=img.width*base, dh=img.height*base;
    ctx.drawImage(img,(w-dw)/2,(h-dh)/2+(yOffset||0),dw,dh);
    if(clipCircle){ ctx.restore(); }
  }

  (function passportPhotoMaker(){
    var input=$('#ufx-passport-file'); if(!input) return; var img=null,file=null,canvas=$('#ufx-passport-canvas'),ctx=canvas.getContext('2d');
    function draw(){ if(!img) return; var p=(ufxVal('#ufx-passport-size')||'600x600').split('x'), w=parseInt(p[0],10), h=parseInt(p[1],10); canvas.width=w; canvas.height=h; ufxCoverDraw(ctx,img,w,h,(parseInt(ufxVal('#ufx-passport-zoom'),10)||100)/100,parseInt(ufxVal('#ufx-passport-y'),10)||0,ufxVal('#ufx-passport-bg'),false); }
    input.addEventListener('change',function(){ ufxLoadImageFromInput(input,function(i,f){ img=i; file=f; draw(); }); });
    ['#ufx-passport-size','#ufx-passport-bg','#ufx-passport-zoom','#ufx-passport-y'].forEach(function(id){ var el=$(id); if(el){ el.addEventListener('input',draw); el.addEventListener('change',draw); } });
    $('#ufx-passport-btn').addEventListener('click',function(){ if(!img){ T('Choose an image first.'); return; } canvas.toBlob(function(blob){ ufxDownloadBlob(blob,'passport-photo.png'); },'image/png'); });
  })();

  (function profilePictureMaker(){
    var input=$('#ufx-profile-file'); if(!input) return; var img=null,canvas=$('#ufx-profile-canvas'),ctx=canvas.getContext('2d');
    function draw(){ if(!img) return; var size=parseInt(ufxVal('#ufx-profile-size'),10)||800; canvas.width=size; canvas.height=size; var circle=ufxVal('#ufx-profile-shape')==='circle'; ufxCoverDraw(ctx,img,size,size,(parseInt(ufxVal('#ufx-profile-zoom'),10)||100)/100,0,ufxVal('#ufx-profile-bg'),circle); }
    input.addEventListener('change',function(){ ufxLoadImageFromInput(input,function(i){ img=i; draw(); }); });
    ['#ufx-profile-shape','#ufx-profile-size','#ufx-profile-zoom','#ufx-profile-bg'].forEach(function(id){ var el=$(id); if(el){ el.addEventListener('input',draw); el.addEventListener('change',draw); } });
    $('#ufx-profile-btn').addEventListener('click',function(){ if(!img){ T('Choose an image first.'); return; } var circle=ufxVal('#ufx-profile-shape')==='circle'; canvas.toBlob(function(blob){ ufxDownloadBlob(blob,circle?'profile-picture.png':'profile-picture.jpg'); },circle?'image/png':'image/jpeg',0.92); });
  })();

  (function circleCropTool(){
    var input=$('#ufx-circle-file'); if(!input) return; var img=null,canvas=$('#ufx-circle-canvas'),ctx=canvas.getContext('2d');
    function draw(){ if(!img) return; var size=parseInt(ufxVal('#ufx-circle-size'),10)||800; canvas.width=size; canvas.height=size; ufxCoverDraw(ctx,img,size,size,1,0,null,true); }
    input.addEventListener('change',function(){ ufxLoadImageFromInput(input,function(i){ img=i; draw(); }); });
    $('#ufx-circle-size').addEventListener('input',draw);
    $('#ufx-circle-btn').addEventListener('click',function(){ if(!img){ T('Choose an image first.'); return; } canvas.toBlob(function(blob){ ufxDownloadBlob(blob,'circle-crop.png'); },'image/png'); });
  })();

  function ufxAjaxTool(action,url,doneEl,render){
    if(!url){ T('Please enter a URL.'); return; }
    doneEl.innerHTML='<p class="at-help">Checking...</p>';
    ufxApi(String(action||'').replace(/^ufx_/,''),{url:url}).then(function(data){
      doneEl.innerHTML=render(data);
    }).catch(function(e){ doneEl.innerHTML='<p class="at-help" style="color:#ef4444;">'+ufxEscapeHtml(e.message)+'</p>'; });
  }

  (function securityHeadersChecker(){
    var btn=$('#ufx-security-check'); if(!btn) return;
    btn.addEventListener('click',function(){
      ufxAjaxTool('ufx_security_headers',ufxVal('#ufx-security-url'),$('#ufx-security-results'),function(data){
        return data.results.map(function(r){
          return '<div class="at-output-row"><div class="at-output-lbl">'+ufxEscapeHtml(r.header)+'</div><div class="at-output-val" style="color:'+(r.present?'#10b981':'#ef4444')+';font-weight:700;">'+(r.present?'Present':'Missing')+(r.value?' — '+ufxEscapeHtml(String(r.value).slice(0,90)):'')+'</div></div>';
        }).join('');
      });
    });
  })();

  (function mixedContentChecker(){
    var btn=$('#ufx-mixed-check'); if(!btn) return;
    btn.addEventListener('click',function(){
      ufxAjaxTool('ufx_mixed_content',ufxVal('#ufx-mixed-url'),$('#ufx-mixed-results'),function(data){
        if(!data.count) return '<p class="at-help" style="color:#10b981;">No mixed content URLs found in page HTML.</p>';
        return '<div class="at-output-row"><div class="at-output-lbl">Found</div><div class="at-output-val">'+data.count+' insecure URL(s)</div></div>' + data.items.map(function(u){
          return '<div class="at-output-row"><div class="at-output-lbl">HTTP Asset</div><div class="at-output-val" style="word-break:break-all;">'+ufxEscapeHtml(u)+'</div></div>';
        }).join('');
      });
    });
  })();

})();
