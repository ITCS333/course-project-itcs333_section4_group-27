// details.js
let currentWeekId = null;
let currentComments = [];

const weekTitle = document.getElementById('week-title');
const weekStartDate = document.getElementById('week-start-date');
const weekDescription = document.getElementById('week-description');
const weekLinksList = document.getElementById('week-links-list');
const commentList = document.getElementById('comment-list');
const commentForm = document.getElementById('comment-form');
const newCommentText = document.getElementById('new-comment-text');

function getWeekIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get('id');
}

function renderWeekDetails(week) {
  weekTitle.textContent = week.title;
  weekStartDate.textContent = 'Starts on: ' + week.startDate;
  weekDescription.textContent = week.description;
  
  weekLinksList.innerHTML = '';
  if (week.links && week.links.length > 0) {
    week.links.forEach(function(linkUrl) {
      const li = document.createElement('li');
      const a = document.createElement('a');
      a.href = linkUrl;
      a.textContent = linkUrl;
      a.target = '_blank';
      li.appendChild(a);
      weekLinksList.appendChild(li);
    });
  }
}

function createCommentArticle(comment) {
  const article = document.createElement('article');
  article.className = 'comment';
  
  const p = document.createElement('p');
  p.textContent = comment.text;
  
  const footer = document.createElement('footer');
  footer.textContent = 'Posted by: ' + comment.author;
  
  article.appendChild(p);
  article.appendChild(footer);
  
  return article;
}

function renderComments() {
  commentList.innerHTML = '';
  
  if (currentComments.length === 0) {
    const emptyMsg = document.createElement('p');
    emptyMsg.className = 'no-comments';
    emptyMsg.textContent = 'No comments yet. Be the first to ask a question!';
    commentList.appendChild(emptyMsg);
    return;
  }
  
  currentComments.forEach(function(comment) {
    const article = createCommentArticle(comment);
    commentList.appendChild(article);
  });
}

function handleAddComment(event) {
  event.preventDefault();
  
  const commentText = newCommentText.value.trim();
  if (!commentText) {
    return;
  }
  
  const newComment = {
    author: 'Student',
    text: commentText
  };
  
  currentComments.push(newComment);
  renderComments();
  newCommentText.value = '';
}

async function initializePage() {
  currentWeekId = getWeekIdFromURL();
  
  if (!currentWeekId) {
    weekTitle.textContent = 'Week not found.';
    return;
  }
  
  try {
    const [weeksResponse, commentsResponse] = await Promise.all([
      fetch('api/weeks.json'),
      fetch('api/comments.json')
    ]);
    
    const weeks = await weeksResponse.json();
    const allComments = await commentsResponse.json();
    
    const week = weeks.find(function(w) {
      return w.id === currentWeekId;
    });
    
    currentComments = allComments[currentWeekId] || [];
    
    if (week) {
      renderWeekDetails(week);
      renderComments();
      commentForm.addEventListener('submit', handleAddComment);
    } else {
      weekTitle.textContent = 'Week not found.';
    }
  } catch (error) {
    console.error('Error loading week details:', error);
    weekTitle.textContent = 'Error loading week details.';
  }
}

initializePage();
