/* ===========================
   RealityOS AI - App Utilities
   =========================== */

// ---- Auth state ----
const Auth = {
  getUser() {
    try { return JSON.parse(sessionStorage.getItem('ros_user') || 'null'); } catch { return null; }
  },
  setUser(user) { sessionStorage.setItem('ros_user', JSON.stringify(user)); },
  clearUser()   { sessionStorage.removeItem('ros_user'); },
  isLoggedIn()  { return !!this.getUser(); },

  redirectIfNotAuth() {
    if (!this.isLoggedIn()) {
      window.location.href = 'login.html';
    }
  },
  redirectIfAuth() {
    if (this.isLoggedIn()) {
      window.location.href = 'dashboard.html';
    }
  }
};

// ---- UI helpers ----
function showAlert(containerId, message, type = 'error') {
  const container = document.getElementById(containerId);
  if (!container) return;
  const icons = { error: '⚠', success: '✓', info: 'ℹ' };
  container.innerHTML = `
    <div class="alert alert-${type}">
      <span>${icons[type] || '!'}</span>
      <span>${message}</span>
    </div>`;
  container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function clearAlert(containerId) {
  const el = document.getElementById(containerId);
  if (el) el.innerHTML = '';
}

function setLoading(btnId, loading, text = 'Loading...') {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  if (loading) {
    btn._origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<div class="spinner spinner-sm" style="border-top-color:#fff;margin-right:8px;"></div>${text}`;
  } else {
    btn.disabled = false;
    btn.innerHTML = btn._origText || text;
  }
}

function showAILoading(steps = ['Analyzing your decision context...', 'Evaluating risks & opportunities...', 'Generating AI recommendations...', 'Finalizing report...']) {
  const overlay = document.getElementById('ai-loading-overlay');
  if (overlay) {
    overlay.classList.remove('hidden');
    let i = 0;
    const step = overlay.querySelector('#loading-step');
    const interval = setInterval(() => {
      if (step && i < steps.length) step.textContent = steps[i++];
      else clearInterval(interval);
    }, 2800);
    overlay._interval = interval;
  }
}

function hideAILoading() {
  const overlay = document.getElementById('ai-loading-overlay');
  if (overlay) {
    if (overlay._interval) clearInterval(overlay._interval);
    overlay.classList.add('hidden');
  }
}

// ---- Date formatting ----
function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function timeAgo(dateStr) {
  if (!dateStr) return '';
  const diff = Date.now() - new Date(dateStr).getTime();
  const m = Math.floor(diff / 60000);
  if (m < 1) return 'just now';
  if (m < 60) return `${m}m ago`;
  const h = Math.floor(m / 60);
  if (h < 24) return `${h}h ago`;
  const d = Math.floor(h / 24);
  return `${d}d ago`;
}

// ---- Category icons ----
const CATEGORY_ICONS = {
  'Career': '💼', 'Education': '🎓', 'Business': '🚀',
  'Finance': '💰', 'Productivity': '⚡', 'Technology': '💻',
  'Personal Growth': '🌱', 'default': '🧠'
};

function getCategoryIcon(cat) {
  return CATEGORY_ICONS[cat] || CATEGORY_ICONS.default;
}

// ---- Risk badge ----
function getRiskBadge(level) {
  const map = { 'Low': 'badge-green', 'Medium': 'badge-amber', 'High': 'badge-red' };
  return `<span class="badge ${map[level] || 'badge-blue'}">${level || 'N/A'} Risk</span>`;
}

// ---- Score color ----
function scoreColor(score) {
  if (score >= 75) return 'var(--accent-green)';
  if (score >= 50) return 'var(--accent-amber)';
  return 'var(--accent-red)';
}

// ---- Animated meter fill ----
function animateMeter(el, value, color = null) {
  setTimeout(() => {
    el.style.width = value + '%';
    if (color) el.style.background = color;
  }, 100);
}

// ---- Sidebar toggle ----
function initSidebar() {
  const ham  = document.getElementById('hamburger');
  const side = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  if (!ham || !side) return;

  ham.addEventListener('click', () => {
    side.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
  });
  if (overlay) overlay.addEventListener('click', () => {
    side.classList.remove('open');
    overlay.classList.remove('open');
  });
}

// ---- Logout handler ----
async function handleLogout() {
  await API.logout();
  Auth.clearUser();
  window.location.href = 'index.html';
}

// ---- Populate navbar user name ----
function populateNavUser() {
  const user = Auth.getUser();
  const el = document.getElementById('nav-user-name');
  if (el && user) el.textContent = user.full_name;
  const els = document.querySelectorAll('.user-name-display');
  els.forEach(e => { if (user) e.textContent = user.full_name; });
}

// ---- Init common on DOM ready ----
document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  populateNavUser();

  // Hook logout buttons
  document.querySelectorAll('[data-action="logout"]').forEach(btn => {
    btn.addEventListener('click', (e) => { e.preventDefault(); handleLogout(); });
  });

  // Label active sidebar link
  const current = window.location.pathname;
  document.querySelectorAll('.sidebar-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && current.toLowerCase().endsWith(href.toLowerCase())) {
      link.classList.add('active');
    }
  });
});
