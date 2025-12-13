<?php
session_start();
/**
 * Student Management API
 * 
 * This is a RESTful API that handles all CRUD operations for student management.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structure (for reference):
 * Table: students
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - student_id (VARCHAR(50), UNIQUE) - The student's university ID
 *   - name (VARCHAR(100))
 *   - email (VARCHAR(100), UNIQUE)
 *   - password (VARCHAR(255)) - Hashed password
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve student(s)
 *   - POST: Create a new student OR change password
 *   - PUT: Update an existing student
 *   - DELETE: Delete a student
 * 
 * Response Format: JSON
 */

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
require_once __DIR__ . '/Database.php';

// TODO: Get the PDO database connection
$dbInstance = new Database();
$db = $dbInstance->getConnection();

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);
if (!is_array($inputData)) {
    $inputData = [];
}

// TODO: Parse query parameters for filtering and searching
$queryParams = $_GET ?? [];

/**
 * Function: Get all students or search for specific students
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by name, student_id, or email
 *   - sort: Optional field to sort by (name, student_id, email)
 *   - order: Optional sort order (asc or desc)
 */
function getStudents($db) {
    // TODO: Check if search parameter exists
    // If yes, prepare SQL query with WHERE clause using LIKE
    // Search should work on name, student_id, and email fields
     $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $hasSearch = $search !== '';
    // TODO: Check if sort and order parameters exist
    // If yes, add ORDER BY clause to the query
    // Validate sort field to prevent SQL injection (only allow: name, student_id, email)
    // Validate order to prevent SQL injection (only allow: asc, desc)
     $allowedSortFields = ['name', 'student_id', 'email'];
    $sortField = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSortFields, true)
        ? $_GET['sort']
        : 'name';
     $order = isset($_GET['order']) ? strtolower($_GET['order']) : 'asc';
    $order = in_array($order, ['asc', 'desc'], true) ? $order : 'asc';
    // TODO: Prepare the SQL query using PDO
    // Note: Do NOT select the password field
     $sql = "SELECT id, student_id, name, email, created_at FROM students";

    if ($hasSearch) {
        $sql .= " WHERE name LIKE :search OR student_id LIKE :search OR email LIKE :search";
    }

    $sql .= " ORDER BY {$sortField} {$order}";

    $stmt = $db->prepare($sql);

    // TODO: Bind parameters if using search
     if ($hasSearch) {
        $like = '%' . $search . '%';
        $stmt->bindValue(':search', $like, PDO::PARAM_STR);
    }

    // TODO: Execute the query
     $stmt->execute();

    // TODO: Fetch all results as an associative array
     $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // TODO: Return JSON response with success status and data
     sendResponse([
        'success' => true,
        'data'    => $students
    ]);
}


/**
 * Function: Get a single student by student_id
 * Method: GET
 * 
 * Query Parameters:
 *   - student_id: The student's university ID
 */
function getStudentById($db, $studentId) {
    // TODO: Prepare SQL query to select student by student_id
    $sql = "SELECT id, student_id, name, email, created_at FROM students WHERE student_id = :student_id LIMIT 1";
    $stmt = $db->prepare($sql);
    // TODO: Bind the student_id parameter
     $stmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);

    // TODO: Execute the query
    $stmt->execute();
    // TODO: Fetch the result
      $student = $stmt->fetch(PDO::FETCH_ASSOC);
    // TODO: Check if student exists
    // If yes, return success response with student data
    // If no, return error response with 404 status
 if ($student) {
        sendResponse([
            'success' => true,
            'data'    => $student
        ]);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Student not found'
        ], 404);
    }
}


/**
 * Function: Create a new student
 * Method: POST
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (must be unique)
 *   - name: Student's full name
 *   - email: Student's email (must be unique)
 *   - password: Default password (will be hashed)
 */
function createStudent($db, $data) {
    // TODO: Validate required fields
    // Check if student_id, name, email, and password are provided
    // If any field is missing, return error response with 400 status
      $requiredFields = ['student_id', 'name', 'email', 'password'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            sendResponse([
                'success' => false,
                'message' => "Missing required field: {$field}"
            ], 400);
        }
    }
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    // Validate email format using filter_var()
     $studentId = sanitizeInput($data['student_id']);
    $name      = sanitizeInput($data['name']);
    $email     = sanitizeInput($data['email']);
    $password  = $data['password']; // hashed later

    if (!validateEmail($email)) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid email format'
        ], 400);
    }
    // TODO: Check if student_id or email already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
    $checkSql = "SELECT id FROM students WHERE student_id = :student_id OR email = :email LIMIT 1";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
    $checkStmt->bindValue(':email', $email, PDO::PARAM_STR);
    $checkStmt->execute();

    if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
        sendResponse([
            'success' => false,
            'message' => 'Student with the same ID or email already exists'
        ], 409);
    }

    // TODO: Hash the password
    // Use password_hash() with PASSWORD_DEFAULT
     $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    // TODO: Prepare INSERT query
     $insertSql = "INSERT INTO students (student_id, name, email, password, created_at)
                  VALUES (:student_id, :name, :email, :password, NOW())";
    $insertStmt = $db->prepare($insertSql);
    // TODO: Bind parameters
    // Bind student_id, name, email, and hashed password
     $insertStmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
    $insertStmt->bindValue(':name', $name, PDO::PARAM_STR);
    $insertStmt->bindValue(':email', $email, PDO::PARAM_STR);
    $insertStmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);

    // TODO: Execute the query
        $success = $insertStmt->execute();
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created)
    // If no, return error response with 500 status
    if ($success) {
        sendResponse([
            'success' => true,
            'message' => 'Student created successfully',
            'data'    => [
                'student_id' => $studentId,
                'name'       => $name,
                'email'      => $email
            ]
        ], 201);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Failed to create student'
        ], 500);
    }
}


/**
 * Function: Update an existing student
 * Method: PUT
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (to identify which student to update)
 *   - name: Updated student name (optional)
 *   - email: Updated student email (optional)
 */
function updateStudent($db, $data) {
    // TODO: Validate that student_id is provided
    // If not, return error response with 400 status
     if (empty($data['student_id'])) {
        sendResponse([
            'success' => false,
            'message' => 'student_id is required'
        ], 400);
    }
       $studentId = sanitizeInput($data['student_id']);
    // TODO: Check if student exists
    // Prepare and execute a SELECT query to find the student
    // If not found, return error response with 404 status
     $checkSql = "SELECT id, email FROM students WHERE student_id = :student_id LIMIT 1";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        sendResponse([
            'success' => false,
            'message' => 'Student not found'
        ], 404);
    }
    // TODO: Build UPDATE query dynamically based on provided fields
    // Only update fields that are provided in the request
     $fields = [];
    $params = [':student_id' => $studentId];

    if (isset($data['name']) && $data['name'] !== '') {
        $fields[] = 'name = :name';
        $params[':name'] = sanitizeInput($data['name']);
    }

    if (isset($data['email']) && $data['email'] !== '') {
        $newEmail = sanitizeInput($data['email']);
        if (!validateEmail($newEmail)) {
            sendResponse([
                'success' => false,
                'message' => 'Invalid email format'
            ], 400);
        }
    // TODO: If email is being updated, check if new email already exists
    // Prepare and execute a SELECT query
    // Exclude the current student from the check
    // If duplicate found, return error response with 409 status
     $emailCheckSql = "SELECT id FROM students WHERE email = :email AND student_id != :student_id LIMIT 1";
        $emailCheckStmt = $db->prepare($emailCheckSql);
        $emailCheckStmt->bindValue(':email', $newEmail, PDO::PARAM_STR);
        $emailCheckStmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
        $emailCheckStmt->execute();
        if ($emailCheckStmt->fetch(PDO::FETCH_ASSOC)) {
            sendResponse([
                'success' => false,
                'message' => 'Email already in use by another student'
            ], 409);
        }

        $fields[] = 'email = :email';
        $params[':email'] = $newEmail;
    }

    if (empty($fields)) {
        sendResponse([
            'success' => false,
            'message' => 'No fields to update'
        ], 400);
    }

    // TODO: Bind parameters dynamically
    // Bind only the parameters that are being updated
     $updateSql = "UPDATE students SET " . implode(', ', $fields) . " WHERE student_id = :student_id";
    $updateStmt = $db->prepare($updateSql);

    foreach ($params as $key => $value) {
        $updateStmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    // TODO: Execute the query
     $success = $updateStmt->execute();
    // TODO: Check if update was successful
    // If yes, return success response
    // If no, return error response with 500 status
     if ($success) {
        sendResponse([
            'success' => true,
            'message' => 'Student updated successfully'
        ]);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Failed to update student'
        ], 500);
    }
}


/**
 * Function: Delete a student
 * Method: DELETE
 * 
 * Query Parameters or JSON Body:
 *   - student_id: The student's university ID
 */
function deleteStudent($db, $studentId) {
    // TODO: Validate that student_id is provided
    // If not, return error response with 400 status
    if (empty($studentId)) {
        sendResponse([
            'success' => false,
            'message' => 'student_id is required'
        ], 400);
    }
      $studentId = sanitizeInput($studentId);
    // TODO: Check if student exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
     $checkSql = "SELECT id FROM students WHERE student_id = :student_id LIMIT 1";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
    $checkStmt->execute();

    if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
        sendResponse([
            'success' => false,
            'message' => 'Student not found'
        ], 404);
    }
    // TODO: Prepare DELETE query
      $deleteSql = "DELETE FROM students WHERE student_id = :student_id";
    $deleteStmt = $db->prepare($deleteSql);
    // TODO: Bind the student_id parameter
     $deleteStmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
    // TODO: Execute the query
     $success = $deleteStmt->execute();
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error response with 500 status
     if ($success) {
        sendResponse([
            'success' => true,
            'message' => 'Student deleted successfully'
        ]);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Failed to delete student'
        ], 500);
    }
}


/**
 * Function: Change password
 * Method: POST with action=change_password
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (identifies whose password to change)
 *   - current_password: The student's current password
 *   - new_password: The new password to set
 */
function changePassword($db, $data) {
    // TODO: Validate required fields
    // Check if student_id, current_password, and new_password are provided
    // If any field is missing, return error response with 400 status
     $requiredFields = ['student_id', 'current_password', 'new_password'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            sendResponse([
                'success' => false,
                'message' => "Missing required field: {$field}"
            ], 400);
        }
    }
    $studentId       = sanitizeInput($data['student_id']);
    $currentPassword = $data['current_password'];
    $newPassword     = $data['new_password'];
    // TODO: Validate new password strength
    // Check minimum length (at least 8 characters)
    // If validation fails, return error response with 400 status
     if (strlen($newPassword) < 8) {
        sendResponse([
            'success' => false,
            'message' => 'New password must be at least 8 characters long'
        ], 400);
    }
    // TODO: Retrieve current password hash from database
    // Prepare and execute SELECT query to get password
     $sql = "SELECT password FROM students WHERE student_id = :student_id LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        sendResponse([
            'success' => false,
            'message' => 'Student not found'
        ], 404);
    }

    $currentHash = $row['password'];
    // TODO: Verify current password
    // Use password_verify() to check if current_password matches the hash
    // If verification fails, return error response with 401 status (Unauthorized)
    if (!password_verify($currentPassword, $currentHash)) {
        sendResponse([
            'success' => false,
            'message' => 'Current password is incorrect'
        ], 401);
    }
    // TODO: Hash the new password
    // Use password_hash() with PASSWORD_DEFAULT
     $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // TODO: Update password in database
    // Prepare UPDATE query
      $updateSql = "UPDATE students SET password = :password WHERE student_id = :student_id";
    $updateStmt = $db->prepare($updateSql);
    // TODO: Bind parameters and execute
     $updateStmt->bindValue(':password', $newHash, PDO::PARAM_STR);
    $updateStmt->bindValue(':student_id', $studentId, PDO::PARAM_STR);
    $success = $updateStmt->execute();
    // TODO: Check if update was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if ($success) {
        sendResponse([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Failed to update password'
        ], 500);
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on HTTP method
    
    if ($method === 'GET') {
        // TODO: Check if student_id is provided in query parameters
        // If yes, call getStudentById()
        // If no, call getStudents() to get all students (with optional search/sort)
         if (!empty($queryParams['student_id'])) {
            getStudentById($db, $queryParams['student_id']);
        } else {
            getStudents($db);
        }
    } elseif ($method === 'POST') {
        // TODO: Check if this is a change password request
        // Look for action=change_password in query parameters
        // If yes, call changePassword()
        // If no, call createStudent()
         $action = isset($queryParams['action']) ? $queryParams['action'] : '';
        if ($action === 'change_password') {
            changePassword($db, $inputData);
        } else {
            createStudent($db, $inputData);
        }
    } elseif ($method === 'PUT') {
        // TODO: Call updateStudent()
          updateStudent($db, $inputData);
    } elseif ($method === 'DELETE') {
        // TODO: Get student_id from query parameter or request body
        // Call deleteStudent()
         $studentId = isset($queryParams['student_id'])
            ? $queryParams['student_id']
            : ($inputData['student_id'] ?? null);
        deleteStudent($db, $studentId);
        
    } else {
        // TODO: Return error for unsupported methods
        // Set HTTP status to 405 (Method Not Allowed)
        // Return JSON error message
         sendResponse([
            'success' => false,
            'message' => 'Method Not Allowed'
        ], 405);
    
    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    // Log the error message (optional)
    // Return generic error response with 500 status
     error_log('PDOException: ' . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error'
    ], 500);
} catch (Exception $e) {
    // TODO: Handle general errors
    // Return error response with 500 status
    error_log('Exception: ' . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Server error'
    ], 500);
}


// ============================================================================
// HELPER FUNCTIONS (Optional but Recommended)
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param mixed $data - Data to send
 * @param int $statusCode - HTTP status code
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
     http_response_code($statusCode);
    // TODO: Echo JSON encoded data
     echo json_encode($data);
    // TODO: Exit to prevent further execution
    exit;
}


/**
 * Helper function to validate email format
 * 
 * @param string $email - Email address to validate
 * @return bool - True if valid, false otherwise
 */
function validateEmail($email) {
    // TODO: Use filter_var with FILTER_VALIDATE_EMAIL
    // Return true if valid, false otherwise
     return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace
    // TODO: Strip HTML tags using strip_tags()
    // TODO: Convert special characters using htmlspecialchars()
    // Return sanitized data
     $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

?>
