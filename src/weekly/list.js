// list.js
// Assumes #week-list-section exists
const listSection = document.querySelector('#week-list-section');
const API_BASE = '/api/weeks.php';
function createWeekArticle(week) {
 const article = document.createElement('article');
 article.className = 'col-12 col-md-6 col-lg-4';
 article.innerHTML = `
<div class="card h-100">
<div class="card-body d-flex flex-column">
<h2 class="card-title h5 mb-2">${escapeHtml(week.title)}</h2>
<p class="mb-1 text-muted">Starts on: ${escapeHtml(week.startDate || week.start_date || '')}</p>
<p class="card-text flex-grow-1">${escapeHtml(truncate(week.description || '', 160))}</p>
<div class="mt-3">
<a class="btn btn-sm btn-primary" href="details.html?id=${encodeURIComponent(week.id || week.week_id)}">View Details &amp; Discussion</a>
</div>
</div>
</div>
 `;
 return article;
}
function truncate(str, n) {
 if (!str) return '';
 return (str.length > n) ? str.slice(0, n-1) + '…' : str;
}
function escapeHtml(str) {
 if (str === null || str === undefined) return '';
 return String(str)
   .replace(/&/g, '&amp;')
   .replace(/</g, '&lt;')
   .replace(/>/g, '&gt;')
   .replace(/"/g, '&quot;')
   .replace(/'/g, '&#39;');
}
async function loadWeeks() {
 try {
   const res = await fetch(API_BASE);
   if (!res.ok) throw new Error(`Failed to fetch weeks (${res.status})`);
   const payload = await res.json();
   // payload format expected: { success: true, data: [ ... ] } OR just an array
   const weeks = Array.isArray(payload) ? payload : (payload.data || []);
   listSection.innerHTML = '';
   if (weeks.length === 0) {
     listSection.innerHTML = '<div class="col-12"><p class="text-muted">No weekly entries available.</p></div>';
     return;
   }
   weeks.forEach(w => {
     // Normalize id name
     if (!w.id && w.week_id) w.id = w.week_id;
     const art = createWeekArticle(w);
     listSection.appendChild(art);
   });
 } catch (err) {
   listSection.innerHTML = `<div class="col-12"><div class="alert alert-danger">Error loading weeks: ${escapeHtml(err.message)}</div></div>`;
   console.error(err);
 }
}
// initial load
loadWeeks();
