/*
  Interactive Manage Resources Page
*/

let resources = []; 
let editingResourceId = null; 

const resourceForm = document.querySelector('#resource-form');
const resourcesTableBody = document.querySelector('#resources-tbody');
const titleInput = document.querySelector('#resource-title');
const descriptionInput = document.querySelector('#resource-description');
const linkInput = document.querySelector('#resource-link');


function createResourceRow(resource) {
  const tr = document.createElement('tr');

  const titleTd = document.createElement('td');
  titleTd.textContent = resource.title;

  const descriptionTd = document.createElement('td');
  descriptionTd.textContent = resource.description;

  const actionsTd = document.createElement('td');

  const editBtn = document.createElement('button');
  editBtn.textContent = 'Edit';
  editBtn.classList.add('edit-btn');
  editBtn.dataset.id = resource.id;

  const deleteBtn = document.createElement('button');
  deleteBtn.textContent = 'Delete';
  deleteBtn.classList.add('delete-btn');
  deleteBtn.dataset.id = resource.id;

  actionsTd.appendChild(editBtn);
  actionsTd.appendChild(deleteBtn);

  tr.appendChild(titleTd);
  tr.appendChild(descriptionTd);
  tr.appendChild(actionsTd);

  return tr;
}


function renderTable() {
  resourcesTableBody.innerHTML = '';
  resources.forEach(resource => {
    const tr = createResourceRow(resource);
    resourcesTableBody.appendChild(tr);
  });
}


function handleAddResource(event) {
  event.preventDefault();

  const title = titleInput.value.trim();
  const description = descriptionInput.value.trim();
  const link = linkInput.value.trim();

  if (!title || !description || !link) return;

  if (editingResourceId) {
   
    const resource = resources.find(r => r.id === editingResourceId);
    resource.title = title;
    resource.description = description;
    resource.link = link;

    editingResourceId = null; 
  } else {
  
    const newResource = {
      id: `res_${Date.now()}`,
      title,
      description,
      link
    };
    resources.push(newResource);
  }

  renderTable();
  resourceForm.reset();
}


function handleTableClick(event) {
  const target = event.target;

  if (target.classList.contains('delete-btn')) {
    const id = target.dataset.id;
    resources = resources.filter(r => r.id !== id);
    renderTable();
  }

  if (target.classList.contains('edit-btn')) {
    const id = target.dataset.id;
    const resource = resources.find(r => r.id === id);

    titleInput.value = resource.title;
    descriptionInput.value = resource.description;
    linkInput.value = resource.link;

    editingResourceId = id;
  }
}


async function loadAndInitialize() {
  try {
    
    resources = [
      {
        id: "res_1",
        title: "Chapter 1 Notes",
        description: "A comprehensive summary of the first chapter, covering all key concepts.",
        link: "https://example.com/notes/chapter1.pdf"
      },
      {
        id: "res_2",
        title: "Interactive Git Tutorial",
        description: "An external website that lets you practice Git commands in your browser.",
        link: "https://learngitbranching.js.org/"
      },
      {
        id: "res_3",
        title: "CSS Flexbox Guide",
        description: "A complete visual guide to CSS Flexbox, with examples.",
        link: "https://css-tricks.com/snippets/css/a-guide-to-flexbox/"
      }
    ];

    renderTable();

    resourceForm.addEventListener('submit', handleAddResource);
    resourcesTableBody.addEventListener('click', handleTableClick);

   } catch (error) {
    console.error('Error initializing resources:', error);
  }
}

loadAndInitialize();

    

