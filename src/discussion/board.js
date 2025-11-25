/*
  Requirement: Make the "Discussion Board" page interactive.
  Notes are included in English.
*/

// --- Global Data Store ---
// This array will hold all topics in memory (loaded from JSON or added via form)
let topics = [];

// --- Element Selections ---
const newTopicForm = document.querySelector('#new-topic-form'); // Form for creating new topic
const topicListContainer = document.querySelector('#topic-list-container'); // Container for topic articles

// --- Functions ---

/**
 * Create an <article> element for a single topic.
 * @param {Object} topic - {id, subject, author, date, message}
 * @returns {HTMLElement} article element
 */
function createTopicArticle(topic) {
    const article = document.createElement('article');

    // Heading with link to topic page
    const h3 = document.createElement('h3');
    const link = document.createElement('a');
    link.href = `topic.html?id=${topic.id}`; // Link to topic detail page
    link.textContent = topic.subject;
    h3.appendChild(link);

    // Footer with author and date
    const footer = document.createElement('footer');
    footer.textContent = `Posted by: ${topic.author} on ${topic.date}`;

    // Actions container with Edit and Delete buttons
    const actionsDiv = document.createElement('div');
    actionsDiv.classList.add('actions');

    const editBtn = document.createElement('button');
    editBtn.classList.add('edit-btn');
    editBtn.textContent = 'Edit';

    const deleteBtn = document.createElement('button');
    deleteBtn.classList.add('delete-btn');
    deleteBtn.dataset.id = topic.id; // store topic ID
    deleteBtn.textContent = 'Delete';

    actionsDiv.appendChild(editBtn);
    actionsDiv.appendChild(deleteBtn);

    // Assemble article
    article.appendChild(h3);
    article.appendChild(footer);
    article.appendChild(actionsDiv);

    return article;
}

/**
 * Render all topics inside the container.
 */
function renderTopics() {
    // Clear existing topics
    topicListContainer.innerHTML = '';

    // Loop through topics and append each article
    topics.forEach(topic => {
        const article = createTopicArticle(topic);
        topicListContainer.appendChild(article);
    });
}

/**
 * Handle the form submission to create a new topic.
 * @param {Event} event 
 */
function handleCreateTopic(event) {
    event.preventDefault(); // Prevent page reload

    // Get form values
    const subjectInput = document.querySelector('#topic-subject');
    const messageInput = document.querySelector('#topic-message');

    const newTopic = {
        id: `topic_${Date.now()}`, // unique ID
        subject: subjectInput.value,
        message: messageInput.value,
        author: 'Student', // hardcoded author for demo
        date: new Date().toISOString().split('T')[0] // YYYY-MM-DD
    };

    // Add to global topics array
    topics.push(newTopic);

    // Re-render topics list
    renderTopics();

    // Reset form
    newTopicForm.reset();
}

/**
 * Handle clicks inside the topic list (delegation)
 * @param {Event} event 
 */
function handleTopicListClick(event) {
    if (event.target.classList.contains('delete-btn')) {
        const topicId = event.target.dataset.id;

        // Filter out the topic to delete
        topics = topics.filter(t => t.id !== topicId);

        // Re-render list
        renderTopics();
    }
}

/**
 * Load topics from JSON and initialize event listeners
 */
async function loadAndInitialize() {
    try {
        // Fetch topics.json (assume it exists in same folder)
        const response = await fetch('topics.json');
        const data = await response.json();

        topics = data; // store in global array

        // Initial render
        renderTopics();

        // Event listeners
        newTopicForm.addEventListener('submit', handleCreateTopic);
        topicListContainer.addEventListener('click', handleTopicListClick);

    } catch (error) {
        console.error('Error loading topics:', error);
    }
}

// --- Initial Page Load ---
loadAndInitialize();
