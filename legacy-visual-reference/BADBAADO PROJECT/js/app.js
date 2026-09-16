/* ================================================================
   BADBAADO â€” Shared application logic
   Handles sidebar, notifications, toasts, modals, the doctor
   referral widget, QR generation and per-page rendering.
   ================================================================ */
(function () {
  var D = window.BD;
  if (!D) return;

  var $ = function (s, el) { return (el || document).querySelector(s); };
  var $$ = function (s, el) { return Array.prototype.slice.call((el || document).querySelectorAll(s)); };

  /* ---------------- Icons ---------------- */
  var ICONS = {
    dashboard: '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    send: '<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/>',
    file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/>',
    alert: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    users: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    hospital: '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/><path d="M9 9h.01M15 9h.01M12 12h.01"/>',
    chart: '<path d="M3 3v18h18"/><rect x="7" y="10" width="2.5" height="6" rx="1"/><rect x="11.75" y="6" width="2.5" height="10" rx="1"/><rect x="16.5" y="12" width="2.5" height="4" rx="1"/>',
    settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
    menu: '<path d="M3 6h18M3 12h18M3 18h18"/>',
    x: '<path d="M18 6 6 18M6 6l12 12"/>',
    search: '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
    check: '<path d="M20 6 9 17l-5-5"/>',
    clock: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    pin: '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
    phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
    activity: '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
    user: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    calendar: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    arrow: '<path d="M5 12h14M12 5l7 7-7 7"/>',
    external: '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/>',
    print: '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
    download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
    filter: '<path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>',
    qr: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3z"/><path d="M21 14h.01M18 18h.01M14 21h.01M17 21h.01"/>',
    shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    heart: '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
    steth: '<path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6 6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v1a6 6 0 0 0 6 6 6 6 0 0 0 6-6v-4"/><circle cx="20" cy="10" r="2"/>',
    bed: '<path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/>',
    ambulance: '<path d="M10 17H4V6a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2"/><path d="M14 10h4l3 4v3h-7"/><circle cx="7" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/>',
    eye: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
    eyeoff: '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/>',
    zap: '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
    refresh: '<path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/>',
    chevron: '<path d="M6 9l6 6 6-6"/>',
    chevL: '<path d="M15 18l-6-6 6-6"/>',
    link: '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
    doll: '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
    clockfast: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/><path d="M9 2h6"/>',
    trendup: '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>',
    target: '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>'
  };
  function icon(name, cls) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + (cls || 'h-5 w-5') + '" aria-hidden="true">' + (ICONS[name] || '') + '</svg>';
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function initials(name) {
    if (!name) return 'DR';
    var parts = name.replace('Dr. ', '').split(' ').filter(Boolean);
    var a = parts[0] ? parts[0][0] : '';
    var b = parts[parts.length - 1] ? parts[parts.length - 1][0] : '';
    return (a + b).toUpperCase();
  }

  /* ---------------- Toasts ---------------- */
  function toast(msg, type) {
    var host = $('#toasts');
    if (!host) {
      host = document.createElement('div');
      host.id = 'toasts';
      document.body.appendChild(host);
    }
    var t = document.createElement('div');
    t.className = 'toast toast-' + (type || 'success');
    var ic = type === 'error' ? 'x' : (type === 'warn' ? 'alert' : (type === 'info' ? 'activity' : 'check'));
    var colors = { success: '#12a8be', error: '#dc2626', warn: '#f59e0b', info: '#386fae' };
    t.innerHTML =
      '<span class="t-icon" style="background:' + (colors[type] || colors.success) + '">' + icon(ic, 'h-4 w-4') + '</span>' +
      '<div class="flex-1 pt-0.5"><p class="font-semibold text-slate-800 leading-snug">' + msg + '</p>' +
      (type === 'success' ? '<p class="text-xs text-slate-500 mt-0.5">Just now</p>' : '') + '</div>';
    host.appendChild(t);
    setTimeout(function () {
      t.classList.add('out');
      setTimeout(function () { t.remove(); }, 260);
    }, 4200);
  }

  /* ---------------- Modals ---------------- */
  function openModal(m) { m && m.classList.add('open'); }
  function closeModal(m) { m && m.classList.remove('open'); }

  var confirmElements = null;
  function ensureConfirmModal() {
    if (confirmElements) return confirmElements;
    var wrap = document.createElement('div');
    wrap.id = 'confirmModal';
    wrap.className = 'modal';
    wrap.innerHTML =
      '<div class="modal-backdrop" data-close></div>' +
      '<div class="modal-shell"><div class="modal-panel w-full max-w-sm p-6">' +
      '<div id="confirmIcon" class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-700">' + icon('alert', 'h-6 w-6') + '</div>' +
      '<h3 id="confirmTitle" class="text-center text-lg font-bold text-slate-900"></h3>' +
      '<p id="confirmMsg" class="mt-1.5 text-center text-sm leading-relaxed text-slate-500"></p>' +
      '<div class="mt-6 grid grid-cols-2 gap-3">' +
      '<button id="confirmCancel" class="btn btn-outline !py-2.5">Cancel</button>' +
      '<button id="confirmOk" class="btn !py-2.5">Confirm</button>' +
      '</div></div></div>';
    document.body.appendChild(wrap);
    confirmElements = {
      wrap: wrap, title: $('#confirmTitle', wrap), msg: $('#confirmMsg', wrap),
      ok: $('#confirmOk', wrap), icon: $('#confirmIcon', wrap), cancel: $('#confirmCancel', wrap)
    };
    wrap.addEventListener('click', function (e) { if (e.target.getAttribute('data-close') !== null) closeModal(wrap); });
    $('#confirmCancel', wrap).addEventListener('click', function () { closeModal(wrap); });
    return confirmElements;
  }
  function confirmDialog(cfg) {
    var c = ensureConfirmModal();
    c.title.textContent = cfg.title;
    c.msg.textContent = cfg.message;
    c.ok.textContent = cfg.ok || 'Confirm';
    c.ok.className = 'btn ' + (cfg.okClass || 'btn-primary') + ' !py-2.5';
    c.icon.innerHTML = icon(cfg.icon || 'alert', 'h-6 w-6');
    c.icon.className = 'mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl ' + (cfg.iconBg || 'bg-slate-100 text-slate-700');
    c.wrap._onOk = cfg.onOk || null;
    c.ok.onclick = function () {
      closeModal(c.wrap);
      if (c.wrap._onOk) c.wrap._onOk();
    };
    openModal(c.wrap);
  }

  /* ---------------- Referral widget (locked to 60s) ---------------- */
  var widget = null;
  function ensureWidget() {
    if (widget) return widget;
    var wrap = document.createElement('div');
    wrap.id = 'widgetModal';
    wrap.className = 'modal';
    var opts = '';
    var deps = '';
    for (var i = 0; i < D.HOSPITALS.length; i++) opts += '<option value="' + D.HOSPITALS[i].id + '">' + esc(D.HOSPITALS[i].name) + '</option>';
    for (var j = 0; j < D.DEPTS.length; j++) deps += '<option value="' + esc(D.DEPTS[j]) + '">' + esc(D.DEPTS[j]) + '</option>';
    wrap.innerHTML =
      '<div class="modal-backdrop" data-close></div>' +
      '<div class="modal-shell"><div class="modal-panel w-full max-w-lg overflow-hidden">' +
      '<div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5" style="background:linear-gradient(120deg,#0b2637,#0d3d4d)">' +
      '<div class="flex items-center gap-3 text-white">' +
      '<span class="grid h-11 w-11 place-items-center rounded-xl bg-white/10 ring-1 ring-white/20">' + icon('zap', 'h-5 w-5 text-amber-300') + '</span>' +
      '<div><h3 class="font-bold leading-tight">External Referral via Badbaado</h3>' +
      '<p class="text-xs text-brand-600">Quick referral â€” target under 60 seconds</p></div>' +
      '</div>' +
      '<div class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-white ring-1 ring-white/20"><span id="widgetTimer">00</span>s</div>' +
      '</div>' +
      '<div class="p-6">' +
      '<div class="grid gap-4 sm:grid-cols-2">' +
      '<div class="sm:col-span-1"><label class="fld" for="wl-to">Receiving Hospital</label>' +
      '<select id="wl-to" class="input">' + opts + '</select></div>' +
      '<div><label class="fld" for="wl-dept">Department / Specialist</label>' +
      '<select id="wl-dept" class="input">' + deps + '</select></div>' +
      '</div>' +
      '<div class="mt-4"><p class="fld">Triage Level</p>' +
      '<div class="grid grid-cols-4 gap-2" id="wl-triage">' +
      '<button type="button" data-v="1" class="triage-opt selected !p-2.5 text-center"><span class="t-level tl-1 mx-auto">1</span><span class="mt-1 block text-[11px] font-bold text-slate-700">Critical</span></button>' +
      '<button type="button" data-v="2" class="triage-opt !p-2.5 text-center"><span class="t-level tl-2 mx-auto">2</span><span class="mt-1 block text-[11px] font-bold text-slate-700">Emergent</span></button>' +
      '<button type="button" data-v="3" class="triage-opt !p-2.5 text-center"><span class="t-level tl-3 mx-auto">3</span><span class="mt-1 block text-[11px] font-bold text-slate-700">Urgent</span></button>' +
      '<button type="button" data-v="4" class="triage-opt !p-2.5 text-center"><span class="t-level tl-4 mx-auto">4</span><span class="mt-1 block text-[11px] font-bold text-slate-700">Routine</span></button>' +
      '</div></div>' +
      '<div class="mt-4"><label class="fld" for="wl-name">Patient Name</label>' +
      '<input id="wl-name" class="input" placeholder="e.g. Md. Shariful Haque" autocomplete="off"></div>' +
      '<div class="mt-4 grid gap-4 sm:grid-cols-2">' +
      '<div><label class="fld" for="wl-age">Age</label><input id="wl-age" type="number" min="0" max="120" class="input" placeholder="Years"></div>' +
      '<div><label class="fld" for="wl-gender">Gender</label><select id="wl-gender" class="input"><option>Male</option><option>Female</option><option>Other</option></select></div>' +
      '</div>' +
      '<div id="wl-detail" class="mt-4 hidden"><label class="fld" for="wl-reason">Reason / Note</label>' +
      '<input id="wl-reason" class="input" placeholder="Short clinical note (optional)"></div>' +
      '<div class="mt-6 flex items-center justify-between gap-4">' +
      '<button type="button" class="btn btn-ghost" data-close>Cancel</button>' +
      '<button type="button" id="widgetSend" class="btn btn-primary !py-3 !px-6">' + icon('send', 'h-4 w-4') + ' Send Referral</button>' +
      '</div></div></div></div>';
    document.body.appendChild(wrap);
    wrap.addEventListener('click', function (e) {
      if (e.target.getAttribute('data-close') !== null) { closeWidget(); }
    });
    $$('#wl-triage .triage-opt', wrap).forEach(function (b) {
      b.addEventListener('click', function () {
        $$('#wl-triage .triage-opt', wrap).forEach(function (x) { x.classList.remove('selected'); });
        b.classList.add('selected');
      });
    });
    widget = { wrap: wrap, timer: null, seconds: 0 };
    $('#widgetSend', wrap).addEventListener('click', sendWidget);
    return widget;
  }
  function openWidget() {
    var w = ensureWidget();
    w.seconds = 0;
    $('#widgetTimer', w.wrap).textContent = '00';
    $('#wl-name', w.wrap).value = '';
    $('#wl-age', w.wrap).value = '';
    $('#wl-reason', w.wrap).value = '';
    openModal(w.wrap);
    w.timer = setInterval(function () {
      w.seconds++;
      $('#widgetTimer', w.wrap).textContent = ('0' + w.seconds).slice(-2);
      var el = $('#widgetTimer', w.wrap);
      if (w.seconds > 60) { el.classList.add('text-amber-300'); }
    }, 1000);
  }
  function closeWidget() {
    var w = widget;
    if (!w) return;
    clearInterval(w.timer);
    closeModal(w.wrap);
  }
  function sendWidget() {
    var w = widget;
    var name = $('#wl-name', w.wrap).value.trim();
    var hosp = $('#wl-to', w.wrap).value;
    var dept = $('#wl-dept', w.wrap).value;
    var triage = parseInt($('#wl-triage .triage-opt.selected', w.wrap).getAttribute('data-v'), 10);
    if (!name) { toast('Patient name is required.', 'error'); $('#wl-name', w.wrap).focus(); return; }
    var btn = $('#widgetSend', w.wrap);
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Sendingâ€¦';
    createReferral({
      patientName: name,
      age: parseInt($('#wl-age', w.wrap).value, 10) || 35,
      gender: $('#wl-gender', w.wrap).value,
      to: hosp,
      dept: dept,
      triage: triage,
      reason: $('#wl-reason', w.wrap).value.trim() || 'Equick referral request',
      seconds: w.seconds
    }, function (ref) {
      setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = icon('send', 'h-4 w-4') + ' Send Referral';
        closeWidget();
        toast('Referral <b>' + esc(ref.id) + '</b> sent â€” receiving ER alerted.');
        openSuccessModal(ref);
      }, 700);
    });
  }
  window.openBadbaadoWidget = openWidget;

  /* ---------------- Referral creation + success modal ---------------- */
  function createReferral(data, cb) {
    var last = D.REFERRALS[D.REFERRALS.length - 1];
    var num = last ? parseInt(last.id.split('-')[2], 10) + 1 : 1026;
    var ref = {
      id: 'REF-2026-' + num,
      patientId: 'PT-' + (1010 + num),
      patientName: data.patientName,
      age: data.age,
      gender: data.gender,
      blood: data.blood || 'O+',
      from: data.from || D.CURRENT_USER.hospital,
      to: data.to,
      dept: data.dept,
      reason: data.reason,
      notes: data.notes || 'No additional notes provided.',
      triage: data.triage,
      time: new Date().toISOString(),
      status: 'pending',
      vital: { bp: 'â€”', hr: 'â€”', spo2: 'â€”', resp: 'â€”' }
    };
    D.REFERRALS.unshift(ref);
    try { localStorage.setItem('bd_last_ref', JSON.stringify(ref)); } catch (e) {}
    if (cb) cb(ref);
    return ref;
  }

  var successModalEl = null;
  function ensureSuccessModal() {
    if (successModalEl) return successModalEl;
    var wrap = document.createElement('div');
    wrap.id = 'successModal';
    wrap.className = 'modal';
    wrap.innerHTML =
      '<div class="modal-backdrop" data-close></div>' +
      '<div class="modal-shell"><div class="modal-panel w-full max-w-md p-6 text-center">' +
      '<div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand-600 ring-8 ring-brand-50/60">' + icon('check', 'h-8 w-8') + '</div>' +
      '<h3 class="mt-4 text-xl font-bold text-slate-900">Referral sent successfully</h3>' +
      '<p class="mt-1 text-sm text-slate-500">The receiving Emergency Room has been alerted.<br>Pre-arrival notification delivered in <b id="smTime">0</b>s.</p>' +
      '<div id="smSummary" class="mt-5"></div>' +
      '<div class="mt-6 grid grid-cols-2 gap-3">' +
      '<a href="#" id="smQrLink" class="btn btn-outline !py-2.5">' + icon('qr', 'h-4 w-4') + ' View QR Code</a>' +
      '<a href="referrals.html" class="btn btn-primary !py-2.5">' + icon('file', 'h-4 w-4') + ' View Referrals</a>' +
      '</div>' +
      '<button type="button" class="btn btn-ghost mt-3 w-full" data-close>Close</button>' +
      '</div></div>';
    document.body.appendChild(wrap);
    wrap.addEventListener('click', function (e) { if (e.target.getAttribute('data-close') !== null) closeModal(wrap); });
    successModalEl = wrap;
    return wrap;
  }
  function openSuccessModal(ref) {
    var wrap = ensureSuccessModal();
    var to = D.hospital(ref.to);
    $('#smTime', wrap).textContent = '0';
    $('#smSummary', wrap).innerHTML =
      '<div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-left text-sm">' +
      '<div class="flex items-center justify-between gap-3"><span class="font-bold text-slate-800">' + esc(ref.patientName) + '</span>' +
      '<span class="badge ' + (D.TRIAGE[ref.triage].cls) + '">' + esc(D.TRIAGE[ref.triage].short) + '</span></div>' +
      '<div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">' +
      '<span class="font-semibold text-slate-600">' + esc(ref.id) + '</span>' +
      '<span class="text-slate-300">â€¢</span><span>' + esc(ref.dept) + '</span>' +
      '<span class="text-slate-300">â€¢</span><span>To: ' + esc(to ? to.short : ref.to) + '</span>' +
      '</div></div>';
    $('#smQrLink', wrap).setAttribute('href', 'qr.html?ref=' + encodeURIComponent(ref.id));
    openModal(wrap);
  }

  /* ---------------- QR ---------------- */
  function renderQR(el, text) {
    el.innerHTML = '';
    if (window.QRCode && typeof QRCode === 'function') {
      try {
        new QRCode(el, { text: text, width: 168, height: 168, colorDark: '#0b2637', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
        return true;
      } catch (e) {}
    }
    el.innerHTML = '<div class="flex h-40 w-40 flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 text-center text-xs text-slate-400"><span class="text-slate-300">' + icon('qr', 'h-8 w-8') + '</span>QR engine offline<br>Connect to the internet</div>';
    return false;
  }
  function downloadQR(host) {
    var canvas = host && host.querySelector('canvas');
    if (canvas) {
      var a = document.createElement('a');
      a.href = canvas.toDataURL('image/png');
      a.download = 'badbaado-referral-qr.png';
      document.body.appendChild(a); a.click(); a.remove();
      toast('QR code downloaded.', 'info');
    } else {
      toast('QR engine not available to export.', 'error');
    }
  }

  /* ---------------- Sidebar / navigation ---------------- */
  function bindSidebar() {
    var tg = $('#sidebarToggle'), oc = $('#sidebarClose'), ov = $('#sidebarOverlay'), sb = $('#sidebar');
    if (!sb) return;
    var open = function () { sb.classList.remove('-translate-x-full'); ov.classList.remove('hidden'); document.body.style.overflow = 'hidden'; };
    var close = function () { sb.classList.add('-translate-x-full'); ov.classList.add('hidden'); document.body.style.overflow = ''; };
    if (tg) tg.addEventListener('click', open);
    if (oc) oc.addEventListener('click', close);
    if (ov) ov.addEventListener('click', close);

    var page = document.body.getAttribute('data-page') || '';
    var map = { dashboard: 'dashboard', referral: 'referral', referrals: 'referrals', emergency: 'emergency', patients: 'patients', hospitals: 'hospitals', reports: 'reports', settings: 'settings', details: 'patients' };
    var activeKey = map[page];
    if (activeKey) {
      var link = $('[data-nav="' + activeKey + '"]', sb);
      if (link) link.classList.add('active');
    }
    var name = $('#sidebarName'), role = $('#sidebarRole'), av = $('#sidebarAvatar');
    if (name) name.textContent = D.CURRENT_USER.name;
    if (role) role.textContent = D.CURRENT_USER.title;
    if (av) av.textContent = initials(D.CURRENT_USER.name);
    var hn = $('#headerName'), hh = $('#headerHospital');
    if (hn) hn.textContent = D.CURRENT_USER.name;
    if (hh) { var ho = D.hospital(D.CURRENT_USER.hospital); hh.textContent = ho ? ho.name : ''; }
  }

  /* ---------------- Notifications ---------------- */
  function bindNotif() {
    var tg = $('#notifToggle'), panel = $('#notifPanel'), list = $('#notifList'), count = $('#notifCount');
    if (!tg || !panel) return;
    var build = function () {
      var items = [];
      D.REFERRALS.forEach(function (r) {
        if ((r.triage === 1 || r.triage === 2) && (r.status === 'pending' || r.status === 'in_transit' || r.status === 'arrived')) {
          var from = D.hospital(r.from), to = D.hospital(r.to);
          items.push({
            tone: r.triage === 1 ? 'critical' : 'emergent',
            title: (r.triage === 1 ? 'Critical alert â€” ' : 'Emergent â€” ') + r.patientName,
            sub: from.short + ' â†’ ' + to.short + ' Â· ' + D.TRIAGE[r.triage].short,
            time: r.time
          });
        }
      });
      if (items.length < 4) {
        items.push({ tone: 'info', title: 'System: daily backup succeeded', sub: 'All referral records synced', time: '2026-09-12T02:00:00' });
      }
      var html = items.slice(0, 5).map(function (it) {
        var dot = it.tone === 'critical' ? 'bg-red-500' : (it.tone === 'emergent' ? 'bg-amber-400' : 'bg-brand-500');
        return '<div class="flex gap-3 border-b border-slate-100 px-4 py-3 last:border-0 hover:bg-slate-50">' +
          '<span class="mt-1.5 h-2 w-2 flex-none rounded-full ' + dot + '"></span>' +
          '<div class="min-w-0 flex-1"><p class="text-[13px] font-semibold leading-snug text-slate-800">' + esc(it.title) + '</p>' +
          '<p class="mt-0.5 truncate text-xs text-slate-500">' + esc(it.sub) + '</p></div>' +
          '<span class="flex-none text-[11px] text-slate-400">' + D.timeAgo(it.time) + '</span></div>';
      }).join('');
      list.innerHTML = html || '<p class="px-4 py-6 text-center text-sm text-slate-400">No new notifications</p>';
      var n = items.length;
      count.textContent = n;
      count.style.display = n ? 'grid' : 'none';
    };
    build();
    var visible = false;
    tg.addEventListener('click', function (e) {
      e.stopPropagation();
      visible = !visible;
      panel.classList.toggle('opacity-0', !visible);
      panel.classList.toggle('pointer-events-none', !visible);
      panel.classList.toggle('scale-95', !visible);
    });
    $$('#markRead', panel)[0] && $$('#markRead', panel)[0].addEventListener('click', function () {
      count.textContent = '0'; count.style.display = 'none';
      build();
      toast('All notifications marked as read.', 'info');
    });
    document.addEventListener('click', function (e) {
      if (visible && !panel.contains(e.target) && e.target !== tg && !tg.contains(e.target)) {
        visible = false;
        panel.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
      }
    });
  }

  /* ---------------- Skeleton helpers ---------------- */
  function skeletonBlocks(n, rows) {
    var h = '';
    for (var i = 0; i < n; i++) {
      h += '<div class="card p-4">';
      for (var r = 0; r < (rows || 3); r++) h += '<div class="skeleton mb-3 h-3.5" style="width:' + (92 - r * 12) + '%"></div>';
      h += '</div>';
    }
    return h;
  }
  function skeletonTableRows(n) {
    var h = '';
    for (var i = 0; i < n; i++) {
      h += '<tr><td colspan="8"><div class="skeleton my-2 h-4 w-full"></div></td></tr>';
    }
    return h;
  }

  /* ================================================================
     PAGE RENDERS
     ================================================================ */

  /* ---------- Login ---------- */
  function renderLogin() {
    var form = $('#loginForm');
    var email = $('#loginEmail'), pass = $('#loginPass');
    if (!form) return;
    $('#loginEmail') && $('#loginEmail').focus();

    var eye = $('#passEye');
    if (eye) eye.addEventListener('click', function () {
      var show = pass.type === 'password';
      pass.type = show ? 'text' : 'password';
      eye.innerHTML = icon(show ? 'eyeoff' : 'eye', 'h-4 w-4');
    });

    $('#useDemo') && $('#useDemo').addEventListener('click', function () {
      email.value = 'farhan@birdem.org.bd';
      pass.value = 'demo1234';
      toast('Demo credentials filled â€” press Sign In.', 'info');
      email.focus();
    });

    $('#forgotLink') && $('#forgotLink').addEventListener('click', function (e) { e.preventDefault(); $('#forgotModal') && openModal($('#forgotModal')); });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var ok = true;
      var mailErr = $('#emailErr');
      var val = email.value.trim();
      if (!val) { mailErr.style.display = 'block'; ok = false; } else { mailErr.style.display = 'none'; }
      if (!pass.value) { $('#passErr').style.display = 'block'; ok = false; } else { $('#passErr').style.display = 'none'; }
      if (!ok) return;
      var btn = $('#loginBtn');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner"></span> Signing inâ€¦';
      setTimeout(function () { window.location.href = 'dashboard.html'; }, 900);
    });
  }

  /* ---------- Dashboard ---------- */
  function renderDashboard() {
    var total = D.REFERRALS.length;
    var critical = D.REFERRALS.filter(function (r) { return r.triage === 1 && (r.status === 'pending' || r.status === 'in_transit' || r.status === 'arrived'); }).length;
    var pending = D.REFERRALS.filter(function (r) { return r.status === 'pending'; }).length;
    var accepted = D.REFERRALS.filter(function (r) { return r.status === 'accepted' || r.status === 'in_transit' || r.status === 'arrived'; }).length;

    var kpis = [
      { label: 'Total Referrals', value: total, icon: 'file', tone: 'from-brand-500 to-brand-700', trend: '+18% this month', up: true },
      { label: 'Critical Patients', value: critical, icon: 'alert', tone: 'from-red-500 to-red-700', trend: 'â€” live', up: true },
      { label: 'Pending Referrals', value: pending, icon: 'clock', tone: 'from-amber-500 to-amber-700', trend: 'Need action', up: false },
      { label: 'Accepted Referrals', value: accepted, icon: 'check', tone: 'from-[#386fae] to-[#0f519e]', trend: '+9% this week', up: true }
    ];
    $('#kpiHost') && ($('#kpiHost').innerHTML = kpis.map(function (k) {
      return '<div class="card card-hover p-5"><div class="flex items-start justify-between">' +
        '<div><p class="text-[13px] font-semibold text-slate-500">' + k.label + '</p>' +
        '<p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">' + k.value + '</p>' +
        '<p class="mt-1.5 flex items-center gap-1 text-xs font-medium ' + (k.up ? 'text-emerald-600' : 'text-amber-600') + '">' +
        icon(k.up ? 'trendup' : 'clock', 'h-3.5 w-3.5') + k.trend + '</p></div>' +
        '<span class="grid h-11 w-11 flex-none place-items-center rounded-xl bg-gradient-to-br ' + k.tone + ' text-white">' + icon(k.icon, 'h-5 w-5') + '</span>' +
        '</div></div>';
    }).join(''));

    var week = [
      { d: 'Sat', v: 11 }, { d: 'Sun', v: 8 }, { d: 'Mon', v: 14 }, { d: 'Tue', v: 10 },
      { d: 'Wed', v: 16 }, { d: 'Thu', v: 12 }, { d: 'Fri', v: 4 }
    ];
    var max = 16;
    $('#chartHost') && ($('#chartHost').innerHTML = week.map(function (b) {
      return '<div class="flex flex-1 flex-col items-center gap-2"><div class="flex w-full flex-1 items-end justify-center">' +
        '<div class="w-full max-w-[26px] rounded-t-lg" style="height:' + (b.v / max * 120) + 'px;background:linear-gradient(180deg,#22c0d6,#0f8fa3)" title="' + b.v + ' referrals"></div></div>' +
        '<span class="text-[11px] font-semibold text-slate-400">' + b.d + '</span></div>';
    }).join(''));

    var recent = D.REFERRALS.slice(0, 5);
    $('#recentHost') && ($('#recentHost').innerHTML = recent.map(function (r, i) {
      var from = D.hospital(r.from), to = D.hospital(r.to);
      return '<tr class="' + (i === 0 && r.triage <= 2 ? 'bg-red-50/40' : '') + '">' +
        '<td class="font-semibold text-slate-800">' + esc(r.id) + '</td>' +
        '<td><p class="font-semibold text-slate-800">' + esc(r.patientName) + '</p><p class="text-xs text-slate-400">' + r.age + ' yrs Â· ' + esc(r.dept) + '</p></td>' +
        '<td class="text-slate-500">' + esc(from.short) + '</td>' +
        '<td class="text-slate-500">' + esc(to.short) + '</td>' +
        '<td><span class="badge ' + D.TRIAGE[r.triage].cls + '">' + D.TRIAGE[r.triage].short + '</span></td>' +
        '<td class="whitespace-nowrap text-slate-500">' + D.timeAgo(r.time) + '</td>' +
        '<td><span class="badge ' + D.STATUS[r.status].cls + '">' + D.STATUS[r.status].label + '</span></td>' +
        '<td><a href="patient-details.html?pid=' + esc(r.patientId) + '&ref=' + esc(r.id) + '" class="btn btn-ghost !p-1.5" title="View">' + icon('external', 'h-4 w-4') + '</a></td>' +
        '</tr>';
    }).join(''));

    var alerts = D.REFERRALS.filter(function (r) { return r.triage <= 2 && ['pending', 'in_transit', 'arrived'].indexOf(r.status) !== -1; }).slice(0, 3);
    $('#alertsHost') && ($('#alertsHost').innerHTML = alerts.length ? alerts.map(function (r) {
      return '<a href="emergency.html" class="' + (r.triage === 1 ? 'crit-strip' : '') + ' flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:border-red-200 hover:shadow-sm transition">' +
        '<span class="' + (r.triage === 1 ? 'bg-red-500' : 'bg-amber-400') + ' grid h-9 w-9 flex-none place-items-center rounded-lg text-white">' + icon('alert', 'h-4 w-4') + '</span>' +
        '<span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-slate-800">' + esc(r.patientName) + '</span>' +
        '<span class="block truncate text-xs text-slate-500">' + esc(D.hospital(r.from).short) + ' â†’ ' + esc(D.hospital(r.to).short) + '</span></span>' +
        '<span class="badge ' + D.TRIAGE[r.triage].cls + ' flex-none">' + D.TRIAGE[r.triage].short + '</span>' +
        '</a>';
    }).join('') : '<div class="flex flex-col items-center py-8 text-slate-400"><span class="mb-2 text-slate-300">' + icon('check', 'h-8 w-8') + '</span><p class="text-sm">No live alerts</p></div>');

    var hottest = D.HOSPITALS.slice(0, 5);
    $('#hospHost') && ($('#hospHost').innerHTML = hottest.map(function (h) {
      return '<a href="hospitals.html" class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition">' +
        '<span class="grid h-9 w-9 flex-none place-items-center rounded-lg bg-slate-100 text-slate-500">' + icon('hospital', 'h-4 w-4') + '</span>' +
        '<span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-slate-800">' + esc(h.short) + '</span>' +
        '<span class="block truncate text-xs text-slate-500">' + esc(h.er === 'offline' ? 'ER unavailable' : 'ER ' + D.ER[h.er].label.toLowerCase()) + '</span></span>' +
        '<span class="dot ' + D.ER[h.er].dot + '"></span></a>';
    }).join(''));
  }

  /* ---------- New Referral ---------- */
  function renderReferral() {
    var sel = $('#selTo'), selFrom = $('#selFrom'), selDept = $('#selDept');
    var optStr = function (id, extra) { return '<option value="' + id + '">' + esc(D.hospital(id).name) + '</option>'; };
    if (sel) sel.innerHTML = D.HOSPITALS.map(function (h) { return optStr(h.id); }).join('');
    if (selFrom) {
      selFrom.innerHTML = D.HOSPITALS.map(function (h) { return optStr(h.id); }).join('');
      selFrom.value = D.CURRENT_USER.hospital;
    }
    if (selDept) selDept.innerHTML = '<option value="">Select departmentâ€¦</option>' + D.DEPTS.map(function (d) { return '<option>' + esc(d) + '</option>'; }).join('');

    var params = new URLSearchParams(window.location.search);
    if (params.get('to')) sel && (sel.value = params.get('to'));

    var btns = $$('#triageOpts .triage-opt');
    btns.forEach(function (b) {
      b.addEventListener('click', function () {
        btns.forEach(function (x) { x.classList.remove('selected'); });
        b.classList.add('selected');
        b.querySelector('input').checked = true;
      });
    });

    $('#fillDemo') && $('#fillDemo').addEventListener('click', function () {
      $('#inName').value = 'Md. Shariful Haque';
      $('#inAge').value = '52';
      $('#inGender').value = 'Male';
      $('#inReason').value = 'Persistent chest pain with dyspnoea on exertion, suspected unstable angina.';
      $('#inNotes').value = 'ECG: flat T waves in V4â€“V6. Troponin negative on initial run. Nitroglycerin given with partial relief.';
      toast('Demo patient details filled.', 'info');
    });

    var nameEl = $('#inName');
    if (nameEl) nameEl.addEventListener('blur', function () {
      if (!nameEl.value.trim()) return;
      $('#inPatId').value = 'PT-' + Math.floor(1000 + Math.random() * 900);
    });

    var form = $('#referralForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var errs = [];
      [['#inName', 'errName'], ['#selTo', 'errTo'], ['#selDept', 'errDept'], ['#inReason', 'errReason']].forEach(function (p) {
        var el = $(p[0]), err = $('#' + p[1]);
        if (!el.value.trim()) { if (err) err.style.display = 'block'; errs.push(el); }
        else if (err) err.style.display = 'none';
      });
      var triage = $('#triageOpts input:checked');
      var tErr = $('#errTriage');
      if (!triage) { if (tErr) tErr.style.display = 'block'; errs.push($('#triageOpts .triage-opt:first-child')); }
      else if (tErr) tErr.style.display = 'none';
      if (errs.length) {
        toast('Please complete the highlighted fields.', 'warn');
        var first = errs[0];
        if (first && first.focus) { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); first.focus(); }
        return;
      }
      var btn = $('#sendBtn');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner"></span> Sending referralâ€¦';
      var ref = createReferral({
        patientName: nameEl.value.trim(),
        age: parseInt($('#inAge').value, 10) || 30,
        gender: $('#inGender').value,
        blood: $('#inBlood').value,
        to: $('#selTo').value,
        dept: $('#selDept').value,
        reason: $('#inReason').value.trim(),
        notes: $('#inNotes').value.trim(),
        triage: parseInt($('#triageOpts input:checked').value, 10)
      });
      setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = icon('send', 'h-4 w-4') + ' Send Referral';
        form.reset();
        btns.forEach(function (x) { x.classList.remove('selected'); });
        $('#triageOpts .triage-opt:first-child input').checked = true;
        $('#triageOpts .triage-opt:first-child').classList.add('selected');
        selFrom.value = D.CURRENT_USER.hospital;
        toast('Referral ' + ref.id + ' sent to ' + esc(D.hospital(ref.to).short) + ' â€” ER alerted.');
        openSuccessModal(ref);
      }, 1100);
    });
  }

  /* ---------- Referrals list ---------- */
  var refFilter = { q: '', status: 'all', triage: 'all', hosp: 'all' };
  function renderRefTable() {
    var rows = D.REFERRALS.filter(function (r) {
      var q = refFilter.q.toLowerCase();
      var hitQ = !q || (r.patientName + ' ' + r.id + ' ' + r.reason + ' ' + r.dept).toLowerCase().indexOf(q) !== -1;
      var hitS = refFilter.status === 'all' || r.status === refFilter.status;
      var hitT = refFilter.triage === 'all' || r.triage === parseInt(refFilter.triage, 10);
      var hitH = refFilter.hosp === 'all' || r.to === refFilter.hosp || r.from === refFilter.hosp;
      return hitQ && hitS && hitT && hitH;
    });
    $('#refCount') && ($('#refCount').textContent = rows.length + ' of ' + D.REFERRALS.length);
    var host = $('#refTableBody');
    if (!host) return;
    if (!rows.length) {
      $('#refEmpty').classList.remove('hidden');
      host.innerHTML = '';
      return;
    }
    $('#refEmpty').classList.add('hidden');
    host.innerHTML = rows.map(function (r) {
      var from = D.hospital(r.from), to = D.hospital(r.to);
      return '<tr>' +
        '<td class="font-semibold text-slate-700">' + esc(r.id) + '</td>' +
        '<td><p class="font-semibold text-slate-800">' + esc(r.patientName) + '</p><p class="text-xs text-slate-400">' + r.age + ' yrs Â· ' + esc(r.gender) + '</p></td>' +
        '<td class="whitespace-nowrap text-slate-500">' + esc(from.short) + '</td>' +
        '<td class="whitespace-nowrap text-slate-500">' + esc(to.short) + '</td>' +
        '<td><span class="badge ' + D.TRIAGE[r.triage].cls + '">' + D.TRIAGE[r.triage].short + '</span></td>' +
        '<td class="whitespace-nowrap text-slate-500">' + D.fmtShort(r.time) + '</td>' +
        '<td><span class="badge ' + D.STATUS[r.status].cls + '">' + D.STATUS[r.status].label + '</span></td>' +
        '<td><a href="patient-details.html?pid=' + esc(r.patientId) + '&ref=' + esc(r.id) + '" class="btn btn-outline !px-3 !py-1.5 text-xs">' + icon('external', 'h-3.5 w-3.5') + ' View</a></td>' +
        '</tr>';
    }).join('');
  }
  function renderReferrals() {
    var search = $('#refSearch'), fStatus = $('#fStatus'), fTriage = $('#fTriage'), fHosp = $('#fHosp'), clear = $('#clearFilters');
    if (search) search.addEventListener('input', function () { refFilter.q = this.value; renderRefTable(); });
    if (fStatus) fStatus.addEventListener('change', function () { refFilter.status = this.value; renderRefTable(); });
    if (fTriage) fTriage.addEventListener('change', function () { refFilter.triage = this.value; renderRefTable(); });
    if (fHosp) fHosp.addEventListener('change', function () { refFilter.hosp = this.value; renderRefTable(); });
    if (clear) clear.addEventListener('click', function () {
      refFilter = { q: '', status: 'all', triage: 'all', hosp: 'all' };
      search.value = ''; fStatus.value = 'all'; fTriage.value = 'all'; fHosp.value = 'all';
      renderRefTable();
      toast('Filters cleared.', 'info');
    });
    if (fHosp) fHosp.innerHTML = '<option value="all">All hospitals</option>' + D.HOSPITALS.map(function (h) { return '<option value="' + h.id + '">' + esc(h.name) + '</option>'; }).join('');
    renderRefTable();
  }

  /* ---------- Emergency ---------- */
  var emergFilter = 'all';
  var simulated = false;
  function emergencyBadges(r) {
    var v = r.vital || { bp: 'â€”', hr: 'â€”', spo2: 'â€”', resp: 'â€”' };
    return '<div class="flex flex-wrap gap-2">' +
      '<span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">BP ' + esc(v.bp) + '</span>' +
      '<span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">HR ' + esc(v.hr) + '</span>' +
      '<span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">SpO2 ' + esc(v.spo2) + '</span>' +
      '<span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">RR ' + esc(v.resp) + '</span>' +
      '</div>';
  }
  function emergencyCard(r) {
    var from = D.hospital(r.from), to = D.hospital(r.to);
    var critical = r.triage === 1;
    var waiting = r.status === 'pending';
    var body =
      '<div class="flex flex-wrap items-start justify-between gap-3">' +
      '<div class="flex items-start gap-3">' +
      '<span class="grid h-11 w-11 flex-none place-items-center rounded-xl ' + (critical ? 'bg-red-500' : 'bg-amber-400') + ' text-white">' + icon('alert', 'h-5 w-5') + '</span>' +
      '<div><h3 class="text-base font-bold text-slate-900">' + esc(r.patientName) + '</h3>' +
      '<p class="text-xs text-slate-500">' + r.age + ' yrs Â· ' + esc(r.gender) + ' Â· ' + esc(r.patientId) + '</p></div></div>' +
      '<div class="flex flex-wrap items-center gap-2">' +
      '<span class="badge ' + D.TRIAGE[r.triage].cls + '">' + D.TRIAGE[r.triage].short + '</span>' +
      '<span class="badge ' + D.STATUS[r.status].cls + '">' + D.STATUS[r.status].label + '</span></div></div>' +
      '<p class="mt-3 text-sm text-slate-600">' + esc(r.reason) + '</p>' +
      '<div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-slate-500">' +
      '<span class="flex items-center gap-1">' + icon('hospital', 'h-3.5 w-3.5') + esc(from.short) + '</span>' +
      '<span class="text-slate-300">â†’</span>' +
      '<span class="flex items-center gap-1 text-slate-700">' + icon('pin', 'h-3.5 w-3.5') + esc(to.short) + '</span>' +
      '<span class="flex items-center gap-1">' + icon('clock', 'h-3.5 w-3.5') + D.timeAgo(r.time) + '</span></div>' +
      '<div class="mt-3">' + emergencyBadges(r) + '</div>';
    if (critical && waiting) {
      body += '<div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-red-50 p-3 ring-1 ring-red-100">' +
        '<p class="flex items-center gap-2 text-sm font-bold text-red-700"><span class="pulse-ring text-red-500">' + icon('zap', 'h-4 w-4') + '</span>Room preparation required â€” arriving shortly</p>' +
        '<div class="flex gap-2">' +
        '<button type="button" class="btn btn-danger !py-2 !px-4 text-sm" data-accept="' + esc(r.id) + '">' + icon('bed', 'h-4 w-4') + ' Accept &amp; Prepare Room</button>' +
        '<a href="patient-details.html?pid=' + esc(r.patientId) + '&ref=' + esc(r.id) + '" class="btn btn-outline !py-2 !px-4 text-sm">' + icon('external', 'h-4 w-4') + ' View Patient</a>' +
        '</div></div>';
    } else if (critical) {
      body += '<div class="mt-4 rounded-xl bg-red-600 p-3 text-center text-sm font-bold text-white">ðŸš‘ Patient in transit â€” resuscitation team on standby</div>';
    }
    return body;
  }
  function renderEmergency() {
    var rows = D.REFERRALS.filter(function (r) {
      return ['pending', 'in_transit', 'arrived'].indexOf(r.status) !== -1;
    });
    if (emergFilter !== 'all') rows = rows.filter(function (r) { return r.triage === parseInt(emergFilter, 10); });
    rows.sort(function (a, b) { return a.triage - b.triage || new Date(b.time) - new Date(a.time); });
    var host = $('#emergHost');
    if (!host) return;
    if (!rows.length) {
      $('#emergEmpty').classList.remove('hidden');
      host.innerHTML = '';
      return;
    }
    $('#emergEmpty').classList.add('hidden');
    host.innerHTML = rows.map(function (r) {
      return '<div class="card card-hover p-5 ' + (r.triage === 1 ? 'crit-strip' : '') + '" data-ref="' + esc(r.id) + '">' + emergencyCard(r) + '</div>';
    }).join('');
    bindAcceptButtons();
  }
  var ROOM_POOL = ['ER-03', 'ER-07', 'ER-11', 'TT-02'];
  var roomIdx = 0;
  function bindAcceptButtons() {
    $$('[data-accept]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-accept');
        var ref = D.byId(id);
        if (!ref) return;
        confirmDialog({
          title: 'Accept & prepare room?',
          message: 'A resuscitation bay and the ' + ref.dept + ' team will be prepared for ' + ref.patientName + '. The sending hospital will be notified.',
          ok: 'Accept & Prepare Room',
          okClass: 'btn-danger',
          icon: 'bed',
          iconBg: 'bg-red-50 text-red-600',
          onOk: function () {
            ref.status = 'accepted';
            var room = ROOM_POOL[roomIdx++ % ROOM_POOL.length];
            toast(ref.patientName + ' accepted â€” ' + room + ' ready. Sending hospital notified.');
            renderEmergency();
            $('[data-ref="' + id + '"]') && $('[data-ref="' + id + '"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        });
      });
    });
  }
  function simulateAlert() {
    if (simulated) return;
    simulated = true;
    setTimeout(function () {
      var ref = createReferral({
        patientName: 'Mahbubur Rahman',
        age: 55, gender: 'Male',
        to: 'H04', dept: 'Neurosurgery',
        triage: 1,
        reason: 'Severe head injury from fall, GCS 7. Intubated en route, CT done â€” acute subdural haematoma.',
        notes: 'Pupils unequal, right 4mm sluggish. Osmotic therapy started. Neurosurgeon on standby.',
        from: 'H05'
      });
      ref.time = new Date(Date.now() - 8 * 60000).toISOString();
      ref.vital = { bp: '138/90', hr: 108, spo2: 95, resp: 20 };
      toast('ðŸš¨ New critical alert â€” ' + ref.patientName + ' (Neurosurgery â†’ United Hospital)', 'error');
      renderEmergency();
    }, 9000);
  }

  /* ---------- Hospitals ---------- */
  var hospFilter = 'all';
  function renderHospitals() {
    var rows = D.HOSPITALS;
    if (hospFilter !== 'all') rows = rows.filter(function (h) { return h.er === hospFilter; });
    var host = $('#hospGrid');
    if (!host) return;
    if (!rows.length) {
      host.innerHTML = '<div class="col-span-full flex flex-col items-center py-16 text-slate-400"><span class="mb-2 text-slate-300">' + icon('hospital', 'h-10 w-10') + '</span><p class="text-sm">No hospitals in this state</p></div>';
      return;
    }
    host.innerHTML = rows.map(function (h) {
      var barColor = h.er === 'available' ? 'bg-emerald-500' : (h.er === 'busy' ? 'bg-amber-500' : 'bg-slate-300');
      var occupancy = h.er === 'offline' ? 0 : h.occupancy;
      return '<div class="card card-hover flex flex-col p-5">' +
        '<div class="flex items-start justify-between gap-3">' +
        '<div class="flex items-center gap-3"><span class="grid h-11 w-11 flex-none place-items-center rounded-xl bg-slate-100 text-slate-600">' + icon('hospital', 'h-5 w-5') + '</span>' +
        '<div><h3 class="font-bold text-slate-900">' + esc(h.name) + '</h3>' +
        '<p class="text-xs font-medium text-slate-500">' + esc(h.level) + '</p></div></div>' +
        '<span class="badge ' + D.ER[h.er].cls + '"><span class="dot ' + D.ER[h.er].dot + '"></span>' + D.ER[h.er].label + '</span></div>' +
        '<div class="mt-4 space-y-2 text-sm text-slate-500">' +
        '<p class="flex items-center gap-2">' + icon('pin', 'h-4 w-4 text-slate-400') + esc(h.location) + '</p>' +
        '<p class="flex items-center gap-2">' + icon('phone', 'h-4 w-4 text-slate-400') + esc(h.phone) + '</p>' +
        '<p class="flex items-center gap-2">' + icon('bed', 'h-4 w-4 text-slate-400') + h.beds + ' beds Â· ' + h.depts.length + ' departments</p></div>' +
        (h.er !== 'offline' ? '<div class="mt-4"><div class="mb-1.5 flex justify-between text-xs font-semibold"><span class="text-slate-500">Bed occupancy</span><span class="' + (occupancy > 85 ? 'text-red-600' : 'text-slate-600') + '">' + occupancy + '%</span></div>' +
        '<div class="h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full ' + barColor + '" style="width:' + occupancy + '%"></div></div></div>'
        : '<div class="mt-4 rounded-lg bg-slate-100 px-3 py-2 text-center text-xs font-semibold text-slate-500">Not accepting transfers right now</div>') +
        '<div class="mt-4 flex gap-2"><a href="referral.html?to=' + h.id + '" class="btn btn-primary flex-1 !py-2 text-sm">' + icon('plus', 'h-4 w-4') + ' Refer a patient</a>' +
        '<a href="tel:' + esc(h.phone.replace(/[^0-9+]/g, '')) + '" class="btn btn-outline !p-2.5" title="Call ER">' + icon('phone', 'h-4 w-4') + '</a></div>' +
        '</div>';
    }).join('');
  }

  /* ---------- Patients ---------- */
  function renderPatients() {
    var q = ($('#patientSearch') || {}).value || '';
    var rows = D.PATIENTS.filter(function (p) {
      return !q || (p.name + ' ' + p.id + ' ' + p.condition).toLowerCase().indexOf(q.toLowerCase()) !== -1;
    });
    var host = $('#patientList');
    if (!host) return;
    if (!rows.length) {
      host.innerHTML = '<tr><td colspan="7"><div class="flex flex-col items-center py-14 text-slate-400"><span class="mb-2 text-slate-300">' + icon('users', 'h-10 w-10') + '</span><p class="text-sm">No patients match "' + esc(q) + '"</p></div></td></tr>';
      return;
    }
    host.innerHTML = rows.map(function (p) {
      var ref = D.REFERRALS.filter(function (r) { return r.patientId === p.id; });
      var last = ref[ref.length - 1];
      return '<tr>' +
        '<td><div class="flex items-center gap-3"><span class="grid h-9 w-9 flex-none place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">' + initials(p.name) + '</span>' +
        '<div><p class="font-semibold text-slate-800">' + esc(p.name) + '</p><p class="text-xs text-slate-400">' + esc(p.id) + '</p></div></div></td>' +
        '<td class="text-slate-500">' + p.age + ' yrs</td>' +
        '<td class="text-slate-500">' + esc(p.gender) + '</td>' +
        '<td><span class="badge tb-4">' + esc(p.blood) + '</span></td>' +
        '<td class="max-w-56 truncate text-slate-600">' + esc(p.condition) + '</td>' +
        '<td class="whitespace-nowrap text-slate-500">' + p.lastVisit + '</td>' +
        '<td>' + (last ? '<span class="badge ' + D.TRIAGE[last.triage].cls + '">' + D.TRIAGE[last.triage].short + '</span>' : '<span class="text-xs text-slate-400">â€”</span>') + '</td>' +
        '<td><a href="patient-details.html?pid=' + esc(p.id) + '" class="btn btn-outline !px-3 !py-1.5 text-xs">' + icon('external', 'h-3.5 w-3.5') + ' View</a></td>' +
        '</tr>';
    }).join('');
  }

  /* ---------- Patient details ---------- */
  function renderPatientDetails() {
    var params = new URLSearchParams(window.location.search);
    var pid = params.get('pid') || 'PT-1018';
    var refId = params.get('ref');
    var patient = D.patientById(pid) || D.PATIENTS[0];
    var refs = D.REFERRALS.filter(function (r) { return r.patientId === patient.id; });
    var ref = refId ? D.byId(refId) : (refs[0] || refs[refs.length - 1]);
    var qrBox = $('#pdQr');

    $('#pdBack').setAttribute('href', document.referrer && document.referrer.indexOf('emergency') !== -1 ? 'emergency.html' : 'patients.html');
    $('#pdName').textContent = patient.name;
    $('#pdId').textContent = patient.id;
    $('#pdMeta').textContent = patient.age + ' years Â· ' + patient.gender + ' Â· Blood ' + patient.blood;
    $('#pdPhone').textContent = patient.phone;
    $('#pdAge').textContent = patient.age;
    $('#pdGender').textContent = patient.gender;
    $('#pdBlood').textContent = patient.blood;
    $('#pdCondition').textContent = patient.condition;

    var act = D.REFERRALS.filter(function (r) { return r.patientId === patient.id && ['pending', 'in_transit', 'arrived', 'accepted'].indexOf(r.status) !== -1; })[0] || ref;
    var triageHost = $('#pdTriage');
    triageHost.className = 'badge ' + D.TRIAGE[act.triage].cls;
    triageHost.textContent = D.TRIAGE[act.triage].label;

    if (ref) {
      $('#refBlock') && ($('#refBlock').innerHTML =
        '<div class="grid gap-4 sm:grid-cols-2">' +
        refRow('Referral ID', ref.id) + refRow('Status', '<span class="badge ' + D.STATUS[ref.status].cls + '">' + D.STATUS[ref.status].label + '</span>') +
        refRow('Sending hospital', D.hospital(ref.from).name) + refRow('Receiving hospital', D.hospital(ref.to).name) +
        refRow('Department / Specialist', ref.dept) + refRow('Triage level', '<span class="badge ' + D.TRIAGE[ref.triage].cls + '">' + D.TRIAGE[ref.triage].short + '</span>') +
        '</div>' +
        '<div class="mt-4"><p class="fld !mb-1">Reason for referral</p><p class="text-sm text-slate-600">' + esc(ref.reason) + '</p></div>' +
        '<div class="mt-4"><p class="fld !mb-1">Medical notes</p><p class="rounded-xl bg-slate-50 p-3 text-sm text-slate-600">' + esc(ref.notes) + '</p></div>' +
        '<div class="mt-4 flex items-center justify-between rounded-xl bg-brand-50 p-3 ring-1 ring-brand-100"><p class="text-sm font-semibold text-brand-800">Referral date &amp; time</p><p class="text-sm font-bold text-brand-900">' + D.fmtShort(ref.time) + '</p></div>');
      var v = ref.vital;
      $('#vitalHost').innerHTML =
        vitalChip('Blood pressure', v.bp) + vitalChip('Heart rate', v.hr + ' bpm') +
        vitalChip('SpOâ‚‚', v.spo2 + '%') + vitalChip('Resp. rate', v.resp + '/min');

      $('#histHost').innerHTML = (patient.history || []).map(function (h, i) {
        return '<li class="flex gap-3"><span class="mt-1 ' + (i === 0 ? 'bg-red-100 text-red-500' : 'bg-slate-100 text-slate-400') + ' grid h-6 w-6 flex-none place-items-center rounded-lg">' + icon(i === 0 ? 'heart' : 'check', 'h-3.5 w-3.5') + '</span>' +
          '<span class="text-sm text-slate-600">' + esc(h) + '</span></li>';
      }).join('') || '<p class="text-sm text-slate-400">No recorded history</p>';

      var steps = [
        { t: 'Referral created', d: D.fmtShort(ref.time), ic: 'file', done: true },
        { t: 'Sending hospital verified', d: D.hospital(ref.from).short + ' â€” records attached', ic: 'check', done: true },
        { t: 'Receiving ER alerted', d: D.hospital(ref.to).short + ' â€” pre-arrival alert sent', ic: 'zap', done: true },
        null
      ];
      var mid = { pending: ['Waiting for receiving ER response', true], accepted: ['Referral accepted â€” room prepared', true], in_transit: ['Ambulance en route â€” ETA updated live', true], arrived: ['Patient arrived at receiving ER', true], completed: ['Transfer completed & records closed', true], declined: ['Referral declined by receiving hospital', false] }[ref.status];
      steps[3] = { t: mid[0], d: mid[1] ? 'Receiving hospital action pending' : 'Receiving hospital declined', ic: mid[1] ? 'check' : 'x', done: mid[1] };
      steps.push({ t: 'Patient handover & QR scan', d: 'Point-of-care verification', ic: 'qr', done: ref.status === 'arrived' || ref.status === 'completed' });
      $('#timelineHost').innerHTML = steps.map(function (s) {
        return '<li class="flex gap-3.5">' +
          '<div class="flex flex-col items-center"><span class="tl-dot ' + (s.done ? 'border-brand-200 bg-brand-50 text-brand-600' : 'border-slate-200 bg-slate-50 text-slate-400') + '">' + icon(s.ic, 'h-4 w-4') + '</span>' +
          '<span class="tl-line bg-slate-100"></span></div>' +
          '<div class="pb-6"><h4 class="text-sm font-semibold text-slate-800">' + esc(s.t) + '</h4><p class="text-xs text-slate-500">' + esc(s.d) + '</p></div></li>';
      }).join('');

      $('#pdQrId').textContent = patient.id;
      $('#pdQrRef').textContent = ref.id;
      renderQR(qrBox, JSON.stringify({ type: 'BADBAADO_REFERRAL', ref: ref.id, patient: patient.id, to: D.hospital(ref.to).short, triage: D.TRIAGE[ref.triage].short }));
      $('#months') && ($('#months').textContent = D.fmtShort(ref.time));
    }
  }
  function refRow(label, val) {
    return '<div><p class="fld !mb-1">' + label + '</p><p class="text-sm font-semibold text-slate-800">' + val + '</p></div>';
  }
  function vitalChip(label, val) {
    return '<div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center"><p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">' + label + '</p><p class="mt-1 text-lg font-extrabold text-slate-900">' + esc(val) + '</p></div>';
  }

  /* ---------- Reports ---------- */
  function renderReports() {
    var dep = {};
    D.REFERRALS.forEach(function (r) { dep[r.dept] = (dep[r.dept] || 0) + 1; });
    var top = Object.keys(dep).sort(function (a, b) { return dep[b] - dep[a]; }).slice(0, 5);

    $('#repCount').textContent = D.REFERRALS.length;
    var crit = D.REFERRALS.filter(function (r) { return r.triage === 1; }).length;
    $('#repCrit').textContent = Math.round(crit / D.REFERRALS.length * 100) + '%';
    $('#repAccept').textContent = '38s';

    var activeH = 0;
    D.HOSPITALS.forEach(function (h) { if (h.er !== 'offline') activeH++; });
    $('#repHosp').textContent = activeH + '/' + D.HOSPITALS.length;

    var sums = [11, 8, 14, 10, 16, 12, 9];
    var labels = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    var max = Math.max.apply(null, sums);
    $('#monthChart').innerHTML = sums.map(function (v, i) {
      return '<div class="flex flex-1 flex-col items-center gap-2"><div class="flex w-full flex-1 items-end justify-center"><div class="w-full max-w-7 rounded-t-md" style="height:' + (v / max * 120) + 'px;background:linear-gradient(180deg,#4fcede,#12a8be)" title="' + v + ' referrals"></div></div><span class="text-[10px] font-semibold text-slate-400">' + labels[i] + '</span></div>';
    }).join('');

    var colors = ['#22c0d6', '#386fae', '#0f519e', '#0f8fa3', '#8ecfe0'];
    var total = D.REFERRALS.length || 1;
    var seg = '', acc = 0, legend = '';
    top.forEach(function (d, i) {
      var pct = Math.round(dep[d] / total * 100);
      seg += (i ? ',' : '') + colors[i] + ' ' + acc + '% ' + (acc + pct) + '%';
      acc += pct;
      legend += '<div class="flex items-center gap-2 text-sm"><span class="h-2.5 w-2.5 rounded-full" style="background:' + colors[i] + '"></span><span class="text-slate-600">' + esc(d) + '</span><span class="ml-auto font-semibold text-slate-800">' + dep[d] + '</span></div>';
    });
    $('#donut').style.background = 'conic-gradient(' + seg + ')';
    $('#donutLegend').innerHTML = legend;

    var depsHtml = '';
    D.DEPTS.map(function (d) {
      var c = dep[d] || 0;
      var idx = top.indexOf(d);
      depsHtml += '<tr><td class="font-semibold text-slate-700">' + esc(d) + '</td><td><div class="flex items-center gap-3"><div class="h-1.5 w-full max-w-40 rounded-full bg-slate-100"><div class="h-full rounded-full" style="width:' + (c / total * 100 * 2) + '%' + (c === 0 ? '' : ';background:' + (idx >= 0 ? colors[idx] : '#cbd5e1')) + '"></div></div><span class="w-8 text-sm font-bold text-slate-700">' + c + '</span></div></td></tr>';
      return c;
    });
    $('#deptTable').innerHTML = depsHtml;

    $('#exportCsv') && $('#exportCsv').addEventListener('click', function () {
      var head = ['Referral ID', 'Patient', 'Age', 'Sending', 'Receiving', 'Department', 'Triage', 'Status', 'Time'];
      var csvRows = D.REFERRALS.map(function (r) {
        return [r.id, r.patientName, r.age, D.hospital(r.from).short, D.hospital(r.to).short, r.dept, D.TRIAGE[r.triage].short, D.STATUS[r.status].label, r.time].map(function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(',');
      });
      var csv = [head.join(',')].concat(csvRows).join('\n');
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob); a.download = 'badbaado-referrals-report.csv';
      document.body.appendChild(a); a.click(); a.remove();
      toast('CSV export downloaded.', 'success');
    });
    $('#exportPrint') && ($('#exportPrint').addEventListener('click', function () { window.print(); }));
    $('#exportMail') && ($('#exportMail').addEventListener('click', function () { toast('Draft generated â€” open in your email client.', 'info'); }));
  }

  /* ---------- Settings ---------- */
  function renderSettings() {
    var host = $('#setHospital');
    if (host) host.innerHTML = D.HOSPITALS.map(function (h) { return '<option value="' + h.id + '">' + esc(h.name) + '</option>'; }).join('');
    $('#setName') && ($('#setName').value = D.CURRENT_USER.name.replace('Dr. ', ''));
    $('#setEmail') && ($('#setEmail').value = D.CURRENT_USER.email);
    $('#setPhone') && ($('#setPhone').value = '+880 1711-223344');

    $('#pingApi') && $('#pingApi').addEventListener('click', function () {
      var btn = this, dot = $('#apiState');
      btn.disabled = true; btn.innerHTML = '<span class="spinner spinner-dark"></span> Pingingâ€¦';
      setTimeout(function () {
        btn.disabled = false; btn.innerHTML = icon('refresh', 'h-4 w-4') + ' Test again';
        dot.className = 'badge st-accepted';
        dot.innerHTML = '<span class="dot dot-available"></span>API reachable (200 OK)';
        toast('Backend health check passed â€” Laravel API responding.', 'success');
      }, 1100);
    });

    var toggles = $$('#settingsForm .toggle');
    toggles.forEach(function (t) {
      t.addEventListener('click', function () {
        t.classList.toggle('checked');
        var inp = t.querySelector('input');
        if (inp) inp.checked = !inp.checked;
        toast('Preference updated.', 'info');
      });
    });

    $('#saveProfile') && $('#saveProfile').addEventListener('click', function () { toast('Profile saved successfully.', 'success'); });
    $('#savePassword') && $('#savePassword').addEventListener('click', function () {
      var n = $('#np'), c = $('#cp');
      if (n.value.length < 6) { toast('New password must be at least 6 characters.', 'error'); return; }
      if (n.value !== c.value) { toast('Passwords do not match.', 'error'); return; }
      n.value = ''; c.value = $('#op').value = '';
      toast('Password changed successfully.', 'success');
    });
  }

  /* ---------- QR page ---------- */
  function renderQr() {
    var params = new URLSearchParams(window.location.search);
    var ref = null;
    if (params.get('ref')) ref = D.byId(params.get('ref'));
    if (!ref) {
      try {
        var stored = JSON.parse(localStorage.getItem('bd_last_ref') || 'null');
        if (stored) ref = D.byId(stored.id) || stored;
      } catch (e) {}
    }
    if (!ref) ref = D.REFERRALS[0];
    var patient = D.patientById(ref.patientId);
    var to = D.hospital(ref.to);

    $('#qrName').textContent = ref.patientName;
    $('#qrAge').textContent = (patient ? patient.age : ref.age) + ' years Â· ' + ref.gender + ' Â· ' + ref.blood;
    $('#qrPatId').textContent = patient ? patient.id : ref.patientId;
    $('#qrRefId').textContent = ref.id;
    $('#qrTo').textContent = to ? to.name : ref.to;
    $('#qrDept').textContent = ref.dept;
    $('#qrTriage').className = 'badge ' + D.TRIAGE[ref.triage].cls;
    $('#qrTriage').textContent = D.TRIAGE[ref.triage].short;
    $('#qrWhen').textContent = D.fmtShort(ref.time);

    var payload = JSON.stringify({
      app: 'BADBAADO',
      type: 'REFERRAL',
      ref: ref.id,
      patient: patient ? patient.id : '',
      receiving: to ? to.short : '',
      triage: D.TRIAGE[ref.triage].short,
      generated: ref.time
    });
    renderQR($('#qrGraphic'), payload);
    $('#qrPrint') && $('#qrPrint').addEventListener('click', function () { window.print(); });
    $('#qrDownload') && $('#qrDownload').addEventListener('click', function () { downloadQR($('#qrGraphic')); });
  }

  /* ================================================================
     INIT
     ================================================================ */
  var PAGES = {
    login: renderLogin,
    dashboard: renderDashboard,
    referral: renderReferral,
    referrals: renderReferrals,
    emergency: renderEmergency,
    hospitals: renderHospitals,
    patients: renderPatients,
    details: renderPatientDetails,
    reports: renderReports,
    settings: renderSettings,
    qr: renderQr
  };

  document.addEventListener('DOMContentLoaded', function () {
    bindSidebar();
    bindNotif();
    var page = document.body.getAttribute('data-page') || '';
    var run = PAGES[page];
    if (run) {
      var hadSkeleton = !!$('.skeleton-area');
      setTimeout(function () { run(); }, hadSkeleton ? 450 : 0);
    }
    if (page === 'emergency') simulateAlert();
  });

  window.BD_PAGE = {
    icon: icon,
    esc: esc,
    toast: toast,
    confirmDialog: confirmDialog,
    openModal: openModal,
    closeModal: closeModal,
    renderQR: renderQR,
    downloadQR: downloadQR,
    openWidget: openWidget,
    renderPatients: renderPatients,
    setHospFilter: function (v) { hospFilter = v; renderHospitals(); },
    setEmergFilter: function (v) { emergFilter = v; renderEmergency(); },
    findByRef: D.byId,
    hospital: D.hospital
  };
})();
