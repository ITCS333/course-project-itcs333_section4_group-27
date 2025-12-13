/*
 Requirement: Make the "Discussion Board" page interactive.
 Instructions:
 1. Link this file to `board.html` (or `baord.html`) using:
<script src="board.js" defer></script>
 2. In `board.html`, add an `id="topic-list-container"` to the 'div'
    that holds the list of topic articles.
 3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the topics loaded from the JSON file.
let topics = [];

// --- Element Selections ---
// TODO: Select the new topic form ('#new-topic-form').
// TODO: Select the topic list container ('#topic-list-container').
const newTopicForm = document.getElementById('new-topic-form');
const topicListContainer = document.getElementById('topic-list-container');

// --- Functions ---
/**
* TODO: Implement the createTopicArticle function.
* It takes one topic object {id, subject, author, date}.
* It should return an <article> element matching the structure in `board.html`.
* - The main link's `href` MUST be `topic.html?id=${id}`.
* - The footer should contain the author and date.
* - The actions div should contain an "Edit" button and a "Delete" button.
* - The "Delete" button should have a class "delete-btn" and `data-id="${id}"`.
*/
function createTopicArticle(topic) {
  const { id, subject, author, date } = topic;

  // <article>
  const article = document.createElement('article');

  // <h3><a href="topic.html?id=...">subject</a></h3>
  const h3 = document.createElement('h3');
  const link = document.createElement('a');
  link.href = `topic.html?id=${id}`;
  link.textContent = subject || 'Untitled topic';
  h3.appendChild(link);

  // <footer>Posted by ...</footer>
  const footer = document.createElement('footer');
  footer.textContent = `Posted by: ${author} on ${date}`;

  // actions container
  const actionsDiv = document.createElement('div');
  actionsDiv.classList.add('topic-actions');

  // "Edit" button/link (dummy for now)
  const editLink = document.createElement('a');
  editLink.href = '#';
  editLink.textContent = 'Edit';
  editLink.classList.add('secondary');

  // "Delete" button
  const deleteButton = document.createElement('button');
  deleteButton.type = 'button';
  deleteButton.textContent = 'Delete';
  deleteButton.classList.add('delete-btn');
  deleteButton.dataset.id = id;

  actionsDiv.appendChild(editLink);
  actionsDiv.appendChild(deleteButton);

  // assemble article
  article.appendChild(h3);
  article.appendChild(footer);
  article.appendChild(actionsDiv);

  return article;
}

/**
* TODO: Implement the renderTopics function.
* It should:
* 1. Clear the `topicListContainer`.
* 2. Loop through the global `topics` array.
* 3. For each topic, call `createTopicArticle()`, and
* append the resulting <article> to `topicListContainer`.
*/
function renderTopics() {
  if (!topicListContainer) return;

  // 1. Clear container
  topicListContainer.innerHTML = '';

  // 2 & 3. Loop and append
  topics.forEach((topic) => {
    const article = createTopicArticle(topic);
    topicListContainer.appendChild(article);
  });
}

/**
* TODO: Implement the handleCreateTopic function.
* This is the event handler for the form's 'submit' event.
* It should:
* 1. Prevent the form's default submission.
* 2. Get the values from the '#topic-subject' and '#topic-message' inputs.
* 3. Create a new topic object with the structure:
* {
* id: `topic_${Date.now()}`,
* subject: (subject value),
* message: (message value),
* author: 'Student' (use a hardcoded author for this exercise),
* date: new Date().toISOString().split('T')[0] // Gets today's date YYYY-MM-DD
* }
* 4. Add this new topic object to the global `topics` array (in-memory only).
* 5. Call `renderTopics()` to refresh the list.
* 6. Reset the form.
*/
function handleCreateTopic(event) {
  event.preventDefault();

  if (!newTopicForm) return;

  const subjectInput = document.getElementById('topic-subject');
  const messageInput = document.getElementById('topic-message');

  const subject = subjectInput.value.trim();
  const message = messageInput.value.trim();

  if (subject === '' || message === '') {
// Simple validation: we do not show an error in the UI to keep it basic
    return;
  }

  const newTopic = {
    id: `topic_${Date.now()}`,
    subject: subject,
    message: message,
    author: 'Student', // hardcoded as requested
    date: new Date().toISOString().split('T')[0],
  };

  topics.push(newTopic);
  renderTopics();
  newTopicForm.reset();
}

/**
* TODO: Implement the handleTopicListClick function.
* This is an event listener on the `topicListContainer` (for delegation).
* It should:
* 1. Check if the clicked element (`event.target`) has the class "delete-btn".
* 2. If it does, get the `data-id` attribute from the button.
* 3. Update the global `topics` array by filtering out the topic
* with the matching ID (in-memory only).
* 4. Call `renderTopics()` to refresh the list.
*/
function handleTopicListClick(event) {
  const target = event.target;

  if (!target.classList.contains('delete-btn')) {
    return;
  }

  const idToDelete = target.dataset.id;
  topics = topics.filter((topic) => topic.id !== idToDelete);
  renderTopics();
}

/**
* TODO: Implement the loadAndInitialize function.
* This function needs to be 'async'.
* It should:
* 1. Use `fetch()` to get data from 'topics.json'.
* 2. Parse the JSON response and store the result in the global `topics` array.
* 3. Call `renderTopics()` to populate the list for the first time.
* 4. Add the 'submit' event listener to `newTopicForm` (calls `handleCreateTopic`).
* 5. Add the 'click' event listener to `topicListContainer` (calls `handleTopicListClick`).
*/
async function loadAndInitialize() {
  try {
    const response = await fetch('api/topics.json');
    if (!response.ok) {
      throw new Error('Failed to load topics.json');
    }

    const data = await response.json();
// We expect the data to be an array of topics
    topics = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Error loading topics:', error);
    topics = [];
  }

  renderTopics();

  if (newTopicForm) {
    newTopicForm.addEventListener('submit', handleCreateTopic);
  }

  if (topicListContainer) {
    topicListContainer.addEventListener('click', handleTopicListClick);
  }
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
