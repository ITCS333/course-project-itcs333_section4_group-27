// details.js
const API_BASE = '/api/weeks.php';
let currentWeekId = null;
let currentComments = [];
const weekTitle = document.querySelector('#week-title');
const weekStartDate = document.querySelector('#week-start-date');
const weekDescription = document.querySelector('#week-description');
const weekLinksList = document.querySelector('#week-links-list');
const commentList = document.querySelector('#comment-list');
const commentForm = document.querySelector('#comment-form');
const newCommentText = document.querySelector('#new-comment-text');
function getWeekIdFromURL() {
 const params = new URLSearchParams(window.location.search);
 return params.get('id');
}
function escapeHtml(s) {
 if (s === null || s === undefined) return '';
 return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function createCommentArticle(c) {
 const article = document.createElement('article');
 article.className = 'mb-3 p-3 border rounded';
 article.innerHTML = `
<p class="mb-2">${escapeHtml(c.text)}</p>
<footer class="small text-muted">Posted by: ${escapeHtml(c.author || 'Student')} • ${escapeHtml(c.created_at || '')}
     ${c.id ? `<button class="btn btn-sm btn-link text-danger float-end delete-comment-btn" data-id="${c.id}">Delete</button>` : ''}
</footer>
 `;
 return article;
}
function renderComments() {
 commentList.innerHTML = '';
 if (!currentComments || currentComments.length === 0) {
   commentList.innerHTML = '<p class="text-muted">No comments yet. Be the first to ask a question!</p>';
   return;
 }
 currentComments.forEach(c => {
   const el = createCommentArticle(c);
   commentList.appendChild(el);
 });
}
function renderWeekDetails(week) {
 weekTitle.textContent = week.title || 'Untitled week';
 weekStartDate.textContent = 'Starts on: ' + (week.startDate || week.start_date || '');
 weekDescription.textContent = week.description || '';
 weekLinksList.innerHTML = '';
 const links = week.links || week.links_list || [];
 (Array.isArray(links) ? links : (typeof links === 'string' ? JSON.parseSafe(links) || [] : [])).forEach(link => {
   const li = document.createElement('li');
   const a = document.createElement('a');
   a.href = link;
   a.textContent = link;
   a.target = '_blank';
   li.appendChild(a);
   weekLinksList.appendChild(li);
 });
}
// helper: safe JSON parse for non-strict data
JSON.parseSafe = function (s) {
 try { return JSON.parse(s); } catch { return null; }
};
async function fetchWeekAndComments(id) {
 try {
   // fetch week
   const weekRes = await fetch(`${API_BASE}?id=${encodeURIComponent(id)}`);
   if (!weekRes.ok) throw new Error('Failed to fetch week');
   const weekPayload = await weekRes.json();
   const week = Array.isArray(weekPayload) ? weekPayload[0] : (weekPayload.data || weekPayload);
   if (!week) {
     weekTitle.textContent = 'Week not found.';
     return;
   }
   // normalize id
   if (!week.id && week.week_id) week.id = week.week_id;
   renderWeekDetails(week);
   // fetch comments
   const commRes = await fetch(`${API_BASE}?action=comments&week_id=${encodeURIComponent(id)}`);
   if (!commRes.ok) throw new Error('Failed to fetch comments');
   const commPayload = await commRes.json();
   currentComments = commPayload.data || commPayload || [];
   renderComments();
 } catch (err) {
   console.error(err);
   weekTitle.textContent = 'Error loading week.';
   commentList.innerHTML = `<div class="alert alert-danger">Unable to load comments or week: ${escapeHtml(err.message)}</div>`;
 }
}
async function handleAddComment(evt) {
 evt.preventDefault();
 const text = newCommentText.value.trim();
 if (!text) return;
 const payload = {
   week_id: currentWeekId,
   author: 'Student',
   text
 };
 try {
   const res = await fetch(`${API_BASE}?action=comment`, {
     method: 'POST',
     headers: { 'Content-Type': 'application/json' },
     body: JSON.stringify(payload)
   });
   const json = await res.json();
   if (!res.ok || !json.success) throw new Error(json.message || 'Failed to post comment');
   // push to local list (server should return created comment but if not, create local)
   const created = json.id ? { id: json.id, author: payload.author, text: payload.text, created_at: new Date().toISOString() } : { author: payload.author, text: payload.text, created_at: new Date().toISOString() };
   currentComments.push(created);
   renderComments();
   newCommentText.value = '';
 } catch (err) {
   alert('Error posting comment: ' + err.message);
   console.error(err);
 }
}
async function handleCommentListClick(e) {
 if (e.target.matches('.delete-comment-btn')) {
   const cid = e.target.dataset.id;
   if (!confirm('Delete this comment?')) return;
   try {
     const res = await fetch(`${API_BASE}?action=delete_comment&comment_id=${encodeURIComponent(cid)}`, { method: 'DELETE' });
     const json = await res.json();
     if (!res.ok || !json.success) throw new Error(json.message || 'Failed to delete');
     // remove locally
     currentComments = currentComments.filter(c => String(c.id) !== String(cid));
     renderComments();
   } catch (err) {
     alert('Error deleting comment: ' + err.message);
   }
 }
}
async function initializePage() {
 const id = getWeekIdFromURL();
 currentWeekId = id;
 if (!id) {
   weekTitle.textContent = 'Week not found.';
   return;
 }
 await fetchWeekAndComments(id);
 commentForm.addEventListener('submit', handleAddComment);
 commentList.addEventListener('click', handleCommentListClick);
}
initializePage();
