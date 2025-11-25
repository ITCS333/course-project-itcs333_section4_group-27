/*
  Requirement: Populate the single topic page and manage replies.

  Instructions:
  1. Link this file to `topic.html` using:
     <script src="topic.js" defer></script>

  2. In `topic.html`, make sure these IDs exist:
     - <h1 id="topic-subject">
     - <p id="op-message"> inside original post
     - <footer id="op-footer"> inside original post
     - <div id="reply-list-container"> for replies
     - <form id="reply-form"> for posting new reply
*/

// --- Global Data Store ---
let currentTopicId = null;
let currentReplies = []; // Will hold replies for *this* topic

// --- Element Selections ---
const topicSubject = document.getElementById("topic-subject"); // h1 for topic title
const opMessage = document.getElementById("op-message"); // paragraph for original post message
const opFooter = document.getElementById("op-footer"); // footer for original post metadata
const replyListContainer = document.getElementById("reply-list-container"); // container for replies
const replyForm = document.getElementById("reply-form"); // form to submit new reply
const newReplyText = document.getElementById("new-reply"); // textarea inside form

// --- Functions ---

// Get topic ID from URL query parameter
function getTopicIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

// Render the original post
function renderOriginalPost(topic) {
  topicSubject.textContent = topic.subject;
  opMessage.textContent = topic.message;
  opFooter.textContent = `Posted by: ${topic.author} on ${topic.date}`;
  // Optional: Add delete button for OP if needed
}

// Create a single reply <article>
function createReplyArticle(reply) {
  const article = document.createElement("article");
  article.classList.add("reply");

  const p = document.createElement("p");
  p.textContent = reply.text;
  article.appendChild(p);

  const footer = document.createElement("footer");
  footer.textContent = `Posted by: ${reply.author} on ${reply.date}`;
  article.appendChild(footer);

  const actionsDiv = document.createElement("div");
  actionsDiv.classList.add("actions");
  const deleteBtn = document.createElement("button");
  deleteBtn.classList.add("delete-reply-btn");
  deleteBtn.setAttribute("data-id", reply.id);
  deleteBtn.textContent = "Delete";
  actionsDiv.appendChild(deleteBtn);
  article.appendChild(actionsDiv);

  return article;
}

// Render all replies in the container
function renderReplies() {
  replyListContainer.innerHTML = ""; // clear existing replies
  currentReplies.forEach(reply => {
    const replyArticle = createReplyArticle(reply);
    replyListContainer.appendChild(replyArticle);
  });
}

// Handle posting a new reply
function handleAddReply(event) {
  event.preventDefault();
  const text = newReplyText.value.trim();
  if (!text) return;

  const newReply = {
    id: `reply_${Date.now()}`,
    author: "Student", // hardcoded
    date: new Date().toISOString().split("T")[0],
    text: text
  };

  currentReplies.push(newReply); // in-memory only
  renderReplies();
  newReplyText.value = ""; // clear textarea
}

// Handle click events inside reply list (delete delegation)
function handleReplyListClick(event) {
  if (event.target.classList.contains("delete-reply-btn")) {
    const idToDelete = event.target.getAttribute("data-id");
    currentReplies = currentReplies.filter(r => r.id !== idToDelete);
    renderReplies();
  }
}

// Initialize the page
async function initializePage() {
  currentTopicId = getTopicIdFromURL();
  if (!currentTopicId) {
    topicSubject.textContent = "Topic not found.";
    return;
  }

  try {
    // Fetch topics and replies JSON
    const [topicsRes, repliesRes] = await Promise.all([
      fetch("topics.json"),
      fetch("comments.json")
    ]);

    const topicsData = await topicsRes.json();
    const repliesData = await repliesRes.json();

    // Find current topic
    const topic = topicsData.find(t => t.id === currentTopicId);
    if (!topic) {
      topicSubject.textContent = "Topic not found.";
      return;
    }

    // Get replies for this topic
    currentReplies = repliesData[currentTopicId] || [];

    renderOriginalPost(topic);
    renderReplies();

    // Add event listeners
    replyForm.addEventListener("submit", handleAddReply);
    replyListContainer.addEventListener("click", handleReplyListClick);

  } catch (error) {
    console.error(error);
    topicSubject.textContent = "Error loading topic.";
  }
}

// --- Initial Page Load ---
initializePage();
