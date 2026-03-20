/* ===========================
   RealityOS AI - Shared API layer
   =========================== */

const API = {
  // Dynamic base path: handles /RealityOSAI/ vs root / hosting automatically
  BASE: (() => {
    const path = window.location.pathname;
    const parts = path.split('/');
    // If we're in a subfolder (like /RealityOSAI/), use that, else root
    return (parts[1] && parts[1].toLowerCase() === 'realityosai') ? '/RealityOSAI/api' : '/api';
  })(),

  async _fetch(endpoint, method = 'GET', body = null) {
    const opts = {
      method,
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    };
    if (body) opts.body = JSON.stringify(body);

    try {
      const res = await fetch(`${API.BASE}${endpoint}`, opts);
      const data = await res.json();
      return { ok: res.ok, status: res.status, data };
    } catch (err) {
      return { ok: false, status: 0, data: { success: false, error: 'Network error. Please check your connection.' } };
    }
  },

  get:    (ep)       => API._fetch(ep, 'GET'),
  post:   (ep, body) => API._fetch(ep, 'POST', body),
  delete: (ep)       => API._fetch(ep, 'DELETE'),

  // Auth
  register:     (data) => API.post('/register.php', data),
  login:        (data) => API.post('/login.php', data),
  logout:       ()     => API.post('/logout.php'),
  profile:      ()     => API.get('/profile.php'),

  // Simulations
  analyze:         (data) => API.post('/analyze_decision.php', data),
  saveSimulation:  (data) => API.post('/create_simulation.php', data),
  getSimulations:  (category='') => API.get(`/get_simulations.php${category ? `?category=${category}` : ''}`),
  getSimulation:   (id)  => API.get(`/get_simulation.php?id=${id}`),
  deleteSimulation:(id)  => API._fetch(`/delete_simulation.php?id=${id}`, 'DELETE'),
};
