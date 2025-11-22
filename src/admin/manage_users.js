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

// --- Element Selections ---
// We can safely select elements here because 'defer' guarantees
// the HTML document is parsed before this script runs.


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
const tableHeaders = document.querySelectorAll('thead th');

// --- Functions ---

/**
 * TODO: Implement the createStudentRow function.
 * This function should take a student object {name, id, email} and return a <tr> element.
 * The <tr> should contain:
 * 1. A <td> for the student's name.
 * 2. A <td> for the student's ID.
 * 3. A <td> for the student's email.
 * 4. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and a data-id attribute set to the student's ID.
 * - A "Delete" button with class "delete-btn" and a data-id attribute set to the student's ID.
 */
function createStudentRow(student) {
  const tr = document.createElement('tr');
  
  // 2. Create and append the <td> for the student's name.
 const nameTd = document.createElement('td');
  nameTd.textContent = student.name;
  tr.appendChild(nameTd);
  
   // 3. Create and append the <td> for the student's ID.
   const idTd = document.createElement('td');
  idTd.textContent = student.id;
  tr.appendChild(idTd);
  
  // 4. Create and append the <td> for the student's email.
   const emailTd = document.createElement('td');
  emailTd.textContent = student.email;
  tr.appendChild(emailTd);
  
  // 5. Create the <td> for the actions (Edit/Delete).
   const actionsTd = document.createElement('td');
  
   //    - "Edit" button with class "edit-btn" and data-id set to student.id
  const editBtn = document.createElement('button');
  editBtn.type = 'button';
  editBtn.textContent = 'Edit';
  editBtn.classList.add('edit-btn');
  editBtn.dataset.id = student.id;
  
   //    - "Delete" button with class "delete-btn" and data-id set to student.id
   const deleteBtn = document.createElement('button');
  deleteBtn.type = 'button';
  deleteBtn.textContent = 'Delete';
  deleteBtn.classList.add('delete-btn');
  deleteBtn.dataset.id = student.id;
  
  //    - Append buttons to the actions <td>.
actionsTd.appendChild(editBtn);
  actionsTd.appendChild(deleteBtn);
  
// 6. Append the actions <td> to the <tr>.
  tr.appendChild(actionsTd);

  // 7. Return the complete <tr>.
  return tr;
}

/**
 * TODO: Implement the renderTable function.
 * This function takes an array of student objects.
 * It should:
 * 1. Clear the current content of the `studentTableBody`.
 * 2. Loop through the provided array of students.
 * 3. For each student, call `createStudentRow` and append the returned <tr> to `studentTableBody`.
 */
function renderTable(studentArray) {
  // 1. Clear the current content of the `studentTableBody`.
  studentTableBody.innerHTML = '';

  // 2. Loop through the provided array of students.
  studentArray.forEach((student) => {
    // 3. For each student, create a row and append it.
    const row = createStudentRow(student);
    studentTableBody.appendChild(row);
  });
}

/**
 * TODO: Implement the handleChangePassword function.
 * This function will be called when the "Update Password" button is clicked.
 * It should:
 * 1. Prevent the form's default submission behavior.
 * 2. Get the values from "current-password", "new-password", and "confirm-password" inputs.
 * 3. Perform validation:
 * - If "new-password" and "confirm-password" do not match, show an alert: "Passwords do not match."
 * - If "new-password" is less than 8 characters, show an alert: "Password must be at least 8 characters."
 * 4. If validation passes, show an alert: "Password updated successfully!"
 * 5. Clear all three password input fields.
 */
function handleChangePassword(event) {
  // 1. Prevent the form's default submission behavior.
  event.preventDefault();

  // 2. Get the values from the three password inputs.
  const currentPasswordInput = document.getElementById('current-password');
  const newPasswordInput = document.getElementById('new-password');
  const confirmPasswordInput = document.getElementById('confirm-password');

  const currentPassword = currentPasswordInput.value;
  const newPassword = newPasswordInput.value;
  const confirmPassword = confirmPasswordInput.value;

  // 3. Perform validation.
  //    - Check if new-password and confirm-password match.
  if (newPassword !== confirmPassword) {
    alert('Passwords do not match.');
    return;
  }

  //    - Check if new-password is at least 8 characters.
  if (newPassword.length < 8) {
    alert('Password must be at least 8 characters.');
    return;
  }

  // 4. If validation passes, show success alert.
  alert('Password updated successfully!');

  // 5. Clear all three password input fields.
  currentPasswordInput.value = '';
  newPasswordInput.value = '';
  confirmPasswordInput.value = '';
}


/**
 * TODO: Implement the handleAddStudent function.
 * This function will be called when the "Add Student" button is clicked.
 * It should:
 * 1. Prevent the form's default submission behavior.
 * 2. Get the values from "student-name", "student-id", and "student-email".
 * 3. Perform validation:
 * - If any of the three fields are empty, show an alert: "Please fill out all required fields."
 * - (Optional) Check if a student with the same ID already exists in the 'students' array.
 * 4. If validation passes:
 * - Create a new student object: { name, id, email }.
 * - Add the new student object to the global 'students' array.
 * - Call `renderTable(students)` to update the view.
 * 5. Clear the "student-name", "student-id", "student-email", and "default-password" input fields.
 */
function handleAddStudent(event) {
// 1. Prevent the form's default submission behavior.
  event.preventDefault();

  // 2. Get the values from the inputs.
  const nameInput = document.getElementById('student-name');
  const idInput = document.getElementById('student-id');
  const emailInput = document.getElementById('student-email');
  const defaultPasswordInput = document.getElementById('default-password');

  const name = nameInput.value.trim();
  const id = idInput.value.trim();
  const email = emailInput.value.trim();

  // 3. Perform validation.
  //    - Check if any field is empty.
  if (!name || !id || !email) {
    alert('Please fill out all required fields.');
    return;
  }

  //    - (Optional) Check if a student with the same ID already exists.
  const exists = students.some((student) => student.id === id);
  if (exists) {
    alert('A student with this ID already exists.');
    return;
  }

  // 4. If validation passes:
  //    - Create a new student object.
  const newStudent = {
    name,
    id,
    email
  };

  //    - Add the new student to the global 'students' array.
  students.push(newStudent);

  //    - Re-render the table.
  renderTable(students);

  // 5. Clear the input fields.
  nameInput.value = '';
  idInput.value = '';
  emailInput.value = '';
  if (defaultPasswordInput) {
    defaultPasswordInput.value = '';
  }
}

/**
 * TODO: Implement the handleTableClick function.
 * This function will be an event listener on the `studentTableBody` (event delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it is a "delete-btn":
 * - Get the `data-id` attribute from the button.
 * - Update the global 'students' array by filtering out the student with the matching ID.
 * - Call `renderTable(students)` to update the view.
 * 3. (Optional) Check for "edit-btn" and implement edit logic.
 */
function handleTableClick(event) {
 const target = event.target;

  // 1. Check if the clicked element has the class "delete-btn".
  if (target.classList.contains('delete-btn')) {
    // 2. Get the `data-id` attribute from the button.
    const studentId = target.dataset.id;

    //    - Update the global 'students' array by filtering out the matching student.
    students = students.filter((student) => student.id !== studentId);

    //    - Re-render the table.
    renderTable(students);
  }
}
  



/**
 * TODO: Implement the handleSearch function.
 * This function will be called on the "input" event of the `searchInput`.
 * It should:
 * 1. Get the search term from `searchInput.value` and convert it to lowercase.
 * 2. If the search term is empty, call `renderTable(students)` to show all students.
 * 3. If the search term is not empty:
 * - Filter the global 'students' array to find students whose name (lowercase)
 * includes the search term.
 * - Call `renderTable` with the *filtered array*.
 */
function handleSearch(event) {
 // 1. Get the search term and convert to lowercase.
  const term = event.target.value.toLowerCase();

  // 2. If empty, show all students.
  if (!term) {
    renderTable(students);
    return;
  }

  // 3. Otherwise, filter students by name and render the filtered list.
  const filtered = students.filter((student) =>
    student.name.toLowerCase().includes(term)
  );

  renderTable(filtered);
}

/**
 * TODO: Implement the handleSort function.
 * This function will be called when any `th` in the `thead` is clicked.
 * It should:
 * 1. Identify which column was clicked (e.g., `event.currentTarget.cellIndex`).
 * 2. Determine the property to sort by ('name', 'id', 'email') based on the index.
 * 3. Determine the sort direction. Use a data-attribute (e.g., `data-sort-dir="asc"`) on the `th`
 * to track the current direction. Toggle between "asc" and "desc".
 * 4. Sort the global 'students' array *in place* using `array.sort()`.
 * - For 'name' and 'email', use `localeCompare` for string comparison.
 * - For 'id', compare the values as numbers.
 * 5. Respect the sort direction (ascending or descending).
 * 6. After sorting, call `renderTable(students)` to update the view.
 */
function handleSort(event) {
 // 1. Identify which column was clicked.
  const th = event.currentTarget;
  const columnIndex = th.cellIndex;

  // 2. Determine the property to sort by.
  let key;
  if (columnIndex === 0) {
    key = 'name';
  } else if (columnIndex === 1) {
    key = 'id';
  } else if (columnIndex === 2) {
    key = 'email';
  } else {
    // Ignore the "Actions" column.
    return;
  }

  // 3. Determine and toggle sort direction.
  let currentDir = th.dataset.sortDir || 'asc';
  const newDir = currentDir === 'asc' ? 'desc' : 'asc';
  th.dataset.sortDir = newDir;

  // (Optional) Clear sort directions on other headers.
  tableHeaders.forEach((header) => {
    if (header !== th) {
      header.removeAttribute('data-sort-dir');
    }
  });

  // 4. Sort the global 'students' array in place.
  students.sort((a, b) => {
    let comparison = 0;

    if (key === 'name' || key === 'email') {
      // String comparison.
      comparison = a[key].localeCompare(b[key]);
    } else if (key === 'id') {
      // Compare as numbers if possible.
      const numA = Number(a.id);
      const numB = Number(b.id);

      if (!Number.isNaN(numA) && !Number.isNaN(numB)) {
        comparison = numA - numB;
      } else {
        // Fallback to string compare if not numeric.
        comparison = a.id.localeCompare(b.id);
      }
    }

    // 5. Respect the sort direction.
    return newDir === 'asc' ? comparison : -comparison;
  });

  // 6. Re-render the table.
  renderTable(students);
}
}

/**
 * TODO: Implement the loadStudentsAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use the `fetch()` API to get data from 'students.json'.
 * 2. Check if the response is 'ok'. If not, log an error.
 * 3. Parse the JSON response (e.g., `await response.json()`).
 * 4. Assign the resulting array to the global 'students' variable.
 * 5. Call `renderTable(students)` to populate the table for the first time.
 * 6. After data is loaded, set up all the event listeners:
 * - "submit" on `changePasswordForm` -> `handleChangePassword`
 * - "submit" on `addStudentForm` -> `handleAddStudent`
 * - "click" on `studentTableBody` -> `handleTableClick`
 * - "input" on `searchInput` -> `handleSearch`
 * - "click" on each header in `tableHeaders` -> `handleSort`
 */
async function loadStudentsAndInitialize() {
  try {
    // 1. Use fetch() to get data from 'students.json'.
    const response = await fetch('students.json');

    // 2. Check if the response is ok.
    if (!response.ok) {
      console.error('Failed to load students.json:', response.status);
      students = [];
    } else {
      // 3. Parse the JSON response.
      const data = await response.json();

      // 4. Assign the resulting array to the global 'students' variable.
      if (Array.isArray(data)) {
        students = data;
      } else {
        console.error('students.json is not an array.');
        students = [];
      }
    }
  } catch (error) {
    console.error('Error fetching students.json:', error);
    students = [];
  }

  // 5. Populate the table for the first time.
  renderTable(students);

  // 6. Set up all the event listeners.

  // - "submit" on `changePasswordForm` -> `handleChangePassword`
  if (changePasswordForm) {
    changePasswordForm.addEventListener('submit', handleChangePassword);
  }

  // - "submit" on `addStudentForm` -> `handleAddStudent`
  if (addStudentForm) {
    addStudentForm.addEventListener('submit', handleAddStudent);
  }

  // - "click" on `studentTableBody` -> `handleTableClick`
  if (studentTableBody) {
    studentTableBody.addEventListener('click', handleTableClick);
  }

  // - "input" on `searchInput` -> `handleSearch`
  if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
  }

  // - "click" on each header in `tableHeaders` -> `handleSort`
  tableHeaders.forEach((th) => {
    th.addEventListener('click', handleSort);
  });
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadStudentsAndInitialize();
