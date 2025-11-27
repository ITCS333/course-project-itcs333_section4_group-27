/*
  list.js
  Populates the student view with course resources
*/

// --- Element Selection ---
const listSection = document.querySelector('#resource-list-section');

// --- Functions ---

/**
 * Creates an article element for one resource
 * @param {Object} resource - {id, title, description, link}
 * @returns {HTMLElement} article element
 */
function createResourceArticle(resource) {
  const article = document.createElement('article');

  const title = document.createElement('h2');
  title.textContent = resource.title;

  const description = document.createElement('p');
  description.textContent = resource.description;

  const link = document.createElement('a');
  link.href = details.html?id=${resource.id};
  link.textContent = 'View Resource & Discussion';

  article.appendChild(title);
  article.appendChild(description);
  article.appendChild(link);

  return article;
}

/**
 * Loads resources from JSON and renders them
 */
async function loadResources() {
  try {
    const response = await fetch('resources/api/resources.json');
    const resources = await response.json();

    listSection.innerHTML = ''; // Clear any existing content

    resources.forEach(resource => {
      const article = createResourceArticle(resource);
      listSection.appendChild(article);
    });

  } catch (error) {
    console.error('Error loading resources:', error);
    listSection.textContent = 'Failed to load resources.';
  }
}

// --- Initial Page Load ---
loadResources();
