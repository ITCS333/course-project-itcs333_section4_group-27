/*
  Requirement: Add interactivity and data management to the Admin Portal.

  Instructions:
  1. Link this file to your HTML using a <script> tag with the 'defer' attribute.
     Example: <script src="manage_users.js" defer></script>
  2. Implement the JavaScript functionality as described in the TODO comments.
  3. All data management will be done by manipulating the 'students' array
     and re-rendering the table.
*/

// --- Global Data Store ---
// This array will be populated with data fetched from 'students.json'.
let students = [];

// Track which student is being edited (null when adding new)
let editingStudentId = null;

// --- Element Selections ---

// TODO: Select the student table body (tbody).
const studentTableBody = document.querySelector('#student-table tbody');

// TODO: Select the "Add Student" form.
// (You'll need to add id="add-student-form" to this form in your HTML).
const addStudentForm = document.querySelector('#add-student-form');

// TODO: Select the "Change Password" form.
// (You'll need to add id="password-form" to this form in your HTML).
const changePasswordForm = document.querySelector('#password-form');

// TODO: Select the search input field.
// (You'll need to add id="search-input" to this input in your HTML).
const searchInput = document.querySelector('#search-input');

// TODO: Select all table header (th) elements in thead.
const tableHeaders = document.querySelectorAll('#student-table thead th');

// Inputs used by both add + edit
const nameInput = document.querySelector('#student-name');
const idInput = document.querySelector('#student-id');
const emailInput = document.querySelector('#student-email');
const defaultPasswordInput = document.querySelector('#default-password');
const addButton = document.querySelector('#add');

// --- Functions ---

/**
 * Create a <tr> element for a student row.
 * student = { name, id, email }
 */
function createStudentRow(student) {
  const tr = document.createElement('tr');

  const nameTd = document.createElement('td');
  nameTd.textContent = student.name;

  const idTd = document.createElement('td');
  idTd.textContent = student.id;

  const emailTd = document.createElement('td');
  emailTd.textContent = student.email;

  const actionsTd = document.createElement('td');

  const editBtn = document.createElement('button');
  editBtn.type = 'button';
  editBtn.textContent = 'Edit';
  editBtn.classList.add('edit-btn');
  editBtn.dataset.id = student.id;

  const deleteBtn = document.createElement('button');
  deleteBtn.type = 'button';
  deleteBtn.textContent = 'Delete';
  deleteBtn.classList.add('delete-btn');
  deleteBtn.dataset.id = student.id;

  actionsTd.appendChild(editBtn);
  actionsTd.appendChild(deleteBtn);

  tr.appendChild(nameTd);
  tr.appendChild(idTd);
  tr.appendChild(emailTd);
  tr.appendChild(actionsTd);

  return tr;
}

/**
 * Render the table from a given array of students.
 */
function renderTable(studentArray) {
  if (!studentTableBody) return;

  // Clear current rows
  studentTableBody.innerHTML = '';

  // Add rows
  studentArray.forEach((student) => {
    const row = createStudentRow(student);
    studentTableBody.appendChild(row);
  });
}

/**
 * Handle password change form submit.
 */
function handleChangePassword(event) {
  event.preventDefault();

  const current = document.querySelector('#current-password');
  const newPass = document.querySelector('#new-password');
  const confirmPass = document.querySelector('#confirm-password');

  const currentVal = current.value.trim();
  const newVal = newPass.value.trim();
  const confirmVal = confirmPass.value.trim();

  // Basic validation
  if (newVal !== confirmVal) {
    alert('Passwords do not match.');
    return;
  }

  if (newVal.length < 8) {
    alert('Password must be at least 8 characters.');
    return;
  }

  // (Here you would send to backend in Phase 3)
  alert('Password updated successfully!');

  // Clear fields
  current.value = '';
  newPass.value = '';
  confirmPass.value = '';
}

/**
 * Handle Add/Edit Student form submit.
 * If editingStudentId is null -> add new student
 * If editingStudentId has a value -> update existing student
 */
function handleAddStudent(event) {
  event.preventDefault();

  const nameVal = nameInput.value.trim();
  const idVal = idInput.value.trim();
  const emailVal = emailInput.value.trim();
  const defaultPassVal = defaultPasswordInput.value.trim(); // not used in front-end logic

  // Validation
  if (!nameVal || !idVal || !emailVal) {
    alert('Please fill out all required fields.');
    return;
  }

  // Optional: check if student ID already exists (for add mode)
  if (!editingStudentId) {
    const exists = students.some((s) => s.id === idVal);
    if (exists) {
      alert('A student with this ID already exists.');
      return;
    }
  }

  if (editingStudentId) {
    // --- UPDATE (Edit mode) ---
    const index = students.findIndex((s) => s.id === editingStudentId);
    if (index !== -1) {
      students[index].name = nameVal;
      students[index].id = idVal;
      students[index].email = emailVal;
    }

    // Reset edit mode
    editingStudentId = null;
    addButton.textContent = 'Add Student';
  } else {
    // --- CREATE (Add mode) ---
    const newStudent = {
      name: nameVal,
      id: idVal,
      email: emailVal
      // defaultPassword not stored here (would go to backend in Phase 3)
    };

    students.push(newStudent);
  }

  // Re-render table
  renderTable(students);

  // Clear form
  addStudentForm.reset();
  // If you want to restore default password value:
  defaultPasswordInput.value = 'password123';
}

/**
 * Event delegation for table click (Delete + Edit)
 */
function handleTableClick(event) {
  const target = event.target;

  // DELETE
  if (target.classList.contains('delete-btn')) {
    const studentId = target.dataset.id;
    students = students.filter((s) => s.id !== studentId);
    renderTable(students);
    return;
  }

  // EDIT
  if (target.classList.contains('edit-btn')) {
    const studentId = target.dataset.id;
    const student = students.find((s) => s.id === studentId);
    if (!student) return;

    // Fill the form with this student's data
    nameInput.value = student.name;
    idInput.value = student.id;
    emailInput.value = student.email;
    // You can clear or leave default password as is
    defaultPasswordInput.value = '';

    // Mark we are in edit mode
    editingStudentId = student.id;
    addButton.textContent = 'Save Changes';

    // Optionally open the <details> section
    const details = addStudentForm.closest('details');
    if (details) {
      details.open = true;
    }
  }
}

/**
 * Search by name (case-insensitive).
 */
function handleSearch(event) {
  const term = event.target.value.trim().toLowerCase();

  if (!term) {
    renderTable(students);
    return;
  }

  const filtered = students.filter((s) =>
    s.name.toLowerCase().includes(term)
  );

  renderTable(filtered);
}

/**
 * Sort column when header is clicked.
 * Columns: 0 = Name, 1 = Student ID, 2 = Email
 */
function handleSort(event) {
  const th = event.currentTarget;
  const columnIndex = th.cellIndex;

  // Determine sort field
  let field = null;
  if (columnIndex === 0) field = 'name';
  if (columnIndex === 1) field = 'id';
  if (columnIndex === 2) field = 'email';

  if (!field) return;

  // Toggle sort direction on this column
  const currentDir = th.dataset.sortDir || 'asc';
  const newDir = currentDir === 'asc' ? 'desc' : 'asc';
  th.dataset.sortDir = newDir;

  // Sort in place
  students.sort((a, b) => {
    let aVal = a[field];
    let bVal = b[field];

    // If sorting ID, compare numerically when possible
    if (field === 'id') {
      const aNum = Number(aVal);
      const bNum = Number(bVal);
      if (!Number.isNaN(aNum) && !Number.isNaN(bNum)) {
        return newDir === 'asc' ? aNum - bNum : bNum - aNum;
      }
    }

    // String compare for name/email or fallback
    aVal = String(aVal);
    bVal = String(bVal);

    return newDir === 'asc'
      ? aVal.localeCompare(bVal)
      : bVal.localeCompare(aVal);
  });

  renderTable(students);
}

/**
 * Load students.json and set up everything.
 */
async function loadStudentsAndInitialize() {
  try {
    const response = await fetch('students.json');
    if (!response.ok) {
      console.error('Failed to load students.json');
      students = [];
    } else {
      students = await response.json();
    }
  } catch (error) {
    console.error('Error fetching students.json:', error);
    students = [];
  }

  // Initial render
  renderTable(students);

  // Event listeners
  if (changePasswordForm) {
    changePasswordForm.addEventListener('submit', handleChangePassword);
  }

  if (addStudentForm) {
    addStudentForm.addEventListener('submit', handleAddStudent);
  }

  if (studentTableBody) {
    studentTableBody.addEventListener('click', handleTableClick);
  }

  if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
  }

  tableHeaders.forEach((th, index) => {
    // Only allow sorting for Name, ID, Email (not Actions)
    if (index < 3) {
      th.style.cursor = 'pointer';
      th.addEventListener('click', handleSort);
    }
  });
}

// --- Initial Page Load ---
loadStudentsAndInitialize();
