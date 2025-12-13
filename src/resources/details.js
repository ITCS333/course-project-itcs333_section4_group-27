// --- Global Data Store ---
let currentResourceId = null;
let currentComments = [];

// --- Element Selections ---
const resourceTitle = document.querySelector('#resource-title');
const resourceDescription = document.querySelector('#resource-description');
const resourceLink = document.querySelector('#resource-link');
const commentList = document.querySelector('#comment-list');
const commentForm = document.querySelector('#comment-form');
const newComment = document.querySelector('#new-comment');

// --- Functions ---

// Get the 'id' parameter from the URL
function getResourceIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get('id');
}

// Render the resource details
function renderResourceDetails(resource) {
  resourceTitle.textContent = resource.title;
  resourceDescription.textContent = resource.description;
  resourceLink.href = resource.link;
}

// Create a single comment <article>
function createCommentArticle(comment) {
  const article = document.createElement('article');

  const p = document.createElement('p');
  p.textContent = comment.text;

  const footer = document.createElement('footer');
  footer.textContent = By: ${comment.author};

  article.appendChild(p);
  article.appendChild(footer);

  return article;
}

// Render all comments
function renderComments() {
  commentList.innerHTML = '';
  currentComments.forEach(comment => {
    const article = createCommentArticle(comment);
    commentList.appendChild(article);
  });
}

// Handle adding a new comment
function handleAddComment(event) {
  event.preventDefault();
  const text = newComment.value.trim();
  if (!text) return;

  const comment = {
    author: 'Student',  // hardcoded for this exercise
    text
  };

  currentComments.push(comment);
  renderComments();
  newComment.value = '';
}

// Initialize page
async function initializePage() {
  currentResourceId = getResourceIdFromURL();

  if (!currentResourceId) {
    resourceTitle.textContent = "Resource not found.";
    return;
  }

  try {
    // Fetch resources and comments
    const [resourcesResponse, commentsResponse] = await Promise.all([
      fetch('resources.json'),
      fetch('resource-comments.json')
    ]);

    const resources = await resourcesResponse.json();
    const allComments = await commentsResponse.json();

    // Find current resource and comments
    const resource = resources.find(r => r.id === currentResourceId);
    currentComments = allComments[currentResourceId] || [];

    if (!resource) {
      resourceTitle.textContent = "Resource not found.";
      return;
    }

    // Render resource and comments
    renderResourceDetails(resource);
    renderComments();

    // Set up event listener for adding comment
    commentForm.addEventListener('submit', handleAddComment);

  } catch (error) {
    console.error('Error loading resource details:', error);
    resourceTitle.textContent = "Failed to load resource.";
  }
}

// --- Initial Page Load ---
initializePage();
