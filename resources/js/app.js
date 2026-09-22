/* ==========================================================================
   app.js — Centralized front-end helpers
   Import point for Vite. Exposes window.App with reusable utilities so Blade
   views never duplicate fetch/toast/modal logic.
   ========================================================================== */
// import './bootstrap.js';

import 'bootstrap';
/* =============================================================================
   bootstrap.js — vendor bundle
   Imported first by app.js so Bootstrap's JS (modals, dropdowns, etc.) and Font
   Awesome's CSS are bundled through Vite instead of CDN-loaded. Exposes
   window.bootstrap so app.js's modal helpers and any inline Blade can use it.
   ========================================================================== */

import * as bootstrap from 'bootstrap';
// import '@fortawesome/fontawesome-free/css/all.min.css';

window.bootstrap = bootstrap;


/* ---------- CSRF + base fetch ----------------------------------------- */
function csrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function firebaseIdToken() {
  // Set by auth flow when a Firebase session is active (used for API calls).
  return window.__ID_TOKEN__ || null;
}

async function request(method, url, body = null, opts = {}) {
  const headers = {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken(),
    ...(opts.headers || {}),
  };
  const token = firebaseIdToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;

  const config = { method, headers, credentials: 'same-origin' };

  if (body instanceof FormData) {
    config.body = body;                          // browser sets content-type
  } else if (body !== null) {
    headers['Content-Type'] = 'application/json';
    config.body = JSON.stringify(body);
  }

  const res = await fetch(url, config);
  const isJson = (res.headers.get('content-type') || '').includes('application/json');
  const data = isJson ? await res.json() : await res.text();

  if (!res.ok) {
    const message = (isJson && (data.message || data.error)) || `Error ${res.status}`;
    throw { status: res.status, message, data };
  }
  return data;
}

const http = {
  get:    (url, opts)       => request('GET', url, null, opts),
  post:   (url, body, opts) => request('POST', url, body, opts),
  put:    (url, body, opts) => request('PUT', url, body, opts),
  patch:  (url, body, opts) => request('PATCH', url, body, opts),
  delete: (url, body, opts) => request('DELETE', url, body, opts),
};

/* ---------- Toasts (Spanish-facing messages passed by caller) --------- */
function ensureToastStack() {
  let stack = document.querySelector('.toast-stack');
  if (!stack) {
    stack = document.createElement('div');
    stack.className = 'toast-stack';
    document.body.appendChild(stack);
  }
  return stack;
}

const ICONS = { ok: 'fa-circle-check', bad: 'fa-circle-exclamation', warn: 'fa-triangle-exclamation', info: 'fa-circle-info' };

function toast(message, type = 'info', timeout = 3500) {
  const stack = ensureToastStack();
  const item = document.createElement('div');
  item.className = `toast-item ${type}`;
  item.innerHTML = `<i class="fa-solid ${ICONS[type] || ICONS.info} mt-1"></i><div>${message}</div>`;
  stack.appendChild(item);
  setTimeout(() => {
    item.style.opacity = '0';
    setTimeout(() => item.remove(), 200);
  }, timeout);
}

/* ---------- Loading state helper -------------------------------------- */
function withLoading(el, promiseFactory) {
  const target = typeof el === 'string' ? document.querySelector(el) : el;
  if (target) target.classList.add('is-loading');
  const original = target ? target.innerHTML : null;
  if (target && target.tagName === 'BUTTON') {
    target.dataset._label = target.innerHTML;
    target.innerHTML = '<span class="spinner-inline"></span>';
  }
  return Promise.resolve(promiseFactory()).finally(() => {
    if (target) {
      target.classList.remove('is-loading');
      if (target.dataset._label) { target.innerHTML = target.dataset._label; delete target.dataset._label; }
    }
  });
}

/* ---------- Modal helper (Bootstrap wrapper) -------------------------- */
function modal(selector) {
  const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
  if (!el) return null;
  const instance = bootstrap.Modal.getOrCreateInstance(el);
  return { show: () => instance.show(), hide: () => instance.hide(), el };
}

/* ---------- Form serialization --------------------------------------- */
function serializeForm(form) {
  const el = typeof form === 'string' ? document.querySelector(form) : form;
  const out = {};
  new FormData(el).forEach((value, key) => {
    if (key.endsWith('[]')) {
      const k = key.slice(0, -2);
      (out[k] = out[k] || []).push(value);
    } else {
      out[key] = value;
    }
  });
  return out;
}

/* ---------- Theme toggle (light/dark via cookie) ---------------------- */
function setTheme(mode) {
  document.documentElement.setAttribute('data-theme', mode);
  document.cookie = `theme=${mode};path=/;max-age=31536000;samesite=lax`;
}
function initTheme() {
  const btn = document.querySelector('[data-theme-toggle]');
  if (!btn) return;
  btn.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    setTheme(next);
  });
}

/* ---------- Mobile sidebar ------------------------------------------- */
function initSidebar() {
  const menuBtn = document.querySelector('[data-menu-toggle]');
  const sidebar = document.querySelector('.sidebar');
  const backdrop = document.querySelector('.sidebar-backdrop');
  if (!menuBtn || !sidebar) return;
  const open = () => { sidebar.classList.add('open'); backdrop?.classList.add('show'); };
  const close = () => { sidebar.classList.remove('open'); backdrop?.classList.remove('show'); };
  menuBtn.addEventListener('click', open);
  backdrop?.addEventListener('click', close);
}

/* ---------- Public surface ------------------------------------------- */
window.App = { http, toast, withLoading, modal, serializeForm, setTheme };

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
});
