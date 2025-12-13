// admin.js

const API_BASE = '/api/weeks.php';

let weeks = [];

// Element selections

const weekForm = document.querySelector('#week-form');

const weekIdInput = document.querySelector('#week-id');

const titleInput = document.querySelector('#week-title');

const startDateInput = document.querySelector('#week-start-date');

const descriptionInput = document.querySelector('#week-description');

const linksInput = document.querySelector('#week-links');

const weeksTbody = document.querySelector('#weeks-tbody');

const resetBtn = document.querySelector('#reset-form');

function escapeHtml(s) {

  if (s === null || s === undefined) return '';

  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');

}

function createWeekRow(week) {

  const tr = document.createElement('tr');

  tr.innerHTML = `
<td>${escapeHtml(week.title)}</td>
<td>${escapeHtml(week.startDate || week.start_date || '')}</td>
<td>${escapeHtml(week.description || '').slice(0, 120)}</td>
<td>
<button class="btn btn-sm btn-outline-primary edit-btn" data-id="${escapeHtml(week.id || week.week_id)}">Edit</button>
<button class="btn btn-sm btn-outline-danger delete-btn ms-2" data-id="${escapeHtml(week.id || week.week_id)}">Delete</button>
</td>

  `;

  return tr;

}

function renderTable() {

  weeksTbody.innerHTML = '';

  if (!weeks || weeks.length === 0) {

    weeksTbody.innerHTML = `<tr><td colspan="4" class="text-muted">No weeks found.</td></tr>`;

    return;

  }

  weeks.forEach(w => {

    if (!w.id && w.week_id) w.id = w.week_id;

    weeksTbody.appendChild(createWeekRow(w));

  });

}

function resetForm() {

  weekIdInput.value = '';

  titleInput.value = '';

  startDateInput.value = '';

  descriptionInput.value = '';

  linksInput.value = '';

  document.querySelector('#add-week').textContent = 'Save Week';

}

async function handleAddWeek(e) {

  e.preventDefault();

  const id = weekIdInput.value || null;

  const title = titleInput.value.trim();

  const startDate = startDateInput.value;

  const description = descriptionInput.value.trim();

  const links = linksInput.value.split('\n').map(s => s.trim()).filter(Boolean);

  if (!title || !startDate) {

    alert('Title and start date are required.');

    return;

  }

  // Build payload

  // If id present -> update (PUT); otherwise create (POST)

  try {

    if (!id) {

      // create week_id client-side to keep uniqueness in-memory (server may overwrite)

      const newWeekId = `week_${Date.now()}`;

      const payload = {

        week_id: newWeekId,

        title,

        start_date: startDate,

        description,

        links

      };

      const res = await fetch(API_BASE, {

        method: 'POST',

        headers: { 'Content-Type': 'application/json' },

        body: JSON.stringify(payload)

      });

      const json = await res.json();

      if (!res.ok || !json.success) throw new Error(json.message || 'Failed to create week');

      // reflect created week (server may return created data; if not, add approximated)

      const created = {

        id: payload.week_id,

        week_id: payload.week_id,

        title: payload.title,

        startDate: payload.start_date,

        description: payload.description,

        links: payload.links

      };

      weeks.push(created);

    } else {

      // update

      const payload = {

        week_id: id,

        title,

        start_date: startDate,

        description,

        links

      };

      const res = await fetch(API_BASE, {

        method: 'PUT',

        headers: { 'Content-Type': 'application/json' },

        body: JSON.stringify(payload)

      });

      const json = await res.json();

      if (!res.ok || !json.success) throw new Error(json.message || 'Failed to update week');

      // update local array

      weeks = weeks.map(w => ( (w.id === id || w.week_id === id) ? { ...w, title, startDate: startDate, description, links } : w ));

    }

    renderTable();

    resetForm();

  } catch (err) {

    alert('Error saving week: ' + err.message);

    console.error(err);

  }

}

async function handleTableClick(e) {

  const btn = e.target.closest('button');

  if (!btn) return;

  if (btn.classList.contains('delete-btn')) {

    const id = btn.dataset.id;

    if (!confirm('Delete this week and its comments?')) return;

    try {

      const res = await fetch(`${API_BASE}?id=${encodeURIComponent(id)}`, { method: 'DELETE' });

      const json = await res.json();

      if (!res.ok || !json.success) throw new Error(json.message || 'Failed to delete');

      // remove locally

      weeks = weeks.filter(w => (w.id !== id && w.week_id !== id));

      renderTable();

    } catch (err) {

      alert('Error deleting week: ' + err.message);

    }

  } else if (btn.classList.contains('edit-btn')) {

    const id = btn.dataset.id;

    const week = weeks.find(w => (w.id === id || w.week_id === id));

    if (!week) return;

    // populate form for editing

    weekIdInput.value = week.id || week.week_id || '';

    titleInput.value = week.title || '';

    startDateInput.value = week.startDate || week.start_date || '';

    descriptionInput.value = week.description || '';

    linksInput.value = (Array.isArray(week.links) ? week.links : (typeof week.links === 'string' ? JSON.parseSafe(week.links) || [] : [])).join('\n');

    document.querySelector('#add-week').textContent = 'Update Week';

  }

}

// helper parse safe

JSON.parseSafe = function (s) {

  try { return JSON.parse(s); } catch { return null; }

};

async function loadAndInitialize() {

  try {

    const res = await fetch(API_BASE);

    if (!res.ok) throw new Error('Failed to load weeks');

    const payload = await res.json();

    weeks = Array.isArray(payload) ? payload : (payload.data || []);

    // normalize week id fields

    weeks.forEach(w => { if (!w.id && w.week_id) w.id = w.week_id; });

    renderTable();

  } catch (err) {

    weeksTbody.innerHTML = `<tr><td colspan="4" class="text-danger">Error loading weeks: ${escapeHtml(err.message)}</td></tr>`;

    console.error(err);

  }

  weekForm.addEventListener('submit', handleAddWeek);

  weeksTbody.addEventListener('click', handleTableClick);

  resetBtn.addEventListener('click', resetForm);

}

// init

loadAndInitialize();
 
