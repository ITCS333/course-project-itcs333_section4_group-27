<?php
// api/resources.php

/**
 * Course Resources REST API (PHP + PDO + MySQL)
 *
 * Usage:
 *  - GET    /api/resources.php                     -> all resources (with optional ?search=&sort=&order=)
 *  - GET    /api/resources.php?id={id}             -> single resource
 *  - POST   /api/resources.php                     -> create resource (JSON body)
 *  - PUT    /api/resources.php                     -> update resource (JSON body, include id)
 *  - DELETE /api/resources.php?id={id}             -> delete resource (and its comments)
 *
 *  - GET    /api/resources.php?action=comments&resource_id={id}       -> comments for resource
 *  - POST   /api/resources.php?action=comment                        -> create comment (JSON body)
 *  - DELETE /api/resources.php?action=delete_comment&comment_id={id}  -> delete comment
 *
 * Make sure to configure DB connection settings below.
 */

// ---------------------------
// CONFIG: update these to your DB
// ---------------------------
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHAR', 'utf8mb4');

// ---------------------------
// HEADERS & CORS
// ---------------------------
header('Content-Type: application/json; charset=utf-8');

// Allow CORS (adjust origin as needed)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("Access-Control-Allow-Origin: *");
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ---------------------------
// HELPER FUNCTIONS
// ---------------------------
function sendResponse($data, $statusCode = 200) {
    if (!is_array($data)) {
        $data = ['data' => $data];
    }
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $s = trim($data);
    $s = strip_tags($s);
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function validateRequiredFields($data, $requiredFields) {
    $missing = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
            $missing[] = $field;
        }
    }
    return ['valid' => count($missing) === 0, 'missing' => $missing];
}

// ---------------------------
// DB Connection (PDO)
// ---------------------------
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    sendResponse(['success' => false, 'message' => 'Database connection failed.'], 500);
}

// ---------------------------
// PARSE REQUEST
// ---------------------------
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : null;
$id = isset($_GET['id']) ? $_GET['id'] : null;
$resource_id = isset($_GET['resource_id']) ? $_GET['resource_id'] : null;
$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : null;

// Get raw body for POST/PUT
$rawBody = file_get_contents('php://input');
$body = json_decode($rawBody, true); // associative array or null

// ---------------------------
// RESOURCE FUNCTIONS
// ---------------------------

function getAllResources($pdo) {
    // Query params
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $allowedSort = ['title', 'created_at'];
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : 'created_at';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

    $sql = "SELECT id, title, description, link, created_at FROM resources";
    $params = [];

    if ($search) {
        $sql .= " WHERE title LIKE :search OR description LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY {$sort} {$order}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    sendResponse(['success' => true, 'data' => $rows]);
}

function getResourceById($pdo, $resourceId) {
    if (!$resourceId || !is_numeric($resourceId)) {
        sendResponse(['success' => false, 'message' => 'Invalid resource id.'], 400);
    }

    $stmt = $pdo->prepare("SELECT id, title, description, link, created_at FROM resources WHERE id = ?");
    $stmt->execute([$resourceId]);
    $res = $stmt->fetch();

    if (!$res) {
        sendResponse(['success' => false, 'message' => 'Resource not found.'], 404);
    }

    sendResponse(['success' => true, 'data' => $res]);
}

function createResource($pdo, $data) {
    // Validate required fields
    $check = validateRequiredFields($data, ['title', 'link']);
    if (!$check['valid']) {
        sendResponse(['success' => false, 'message' => 'Missing fields: ' . implode(',', $check['missing'])], 400);
    }
    // Sanitize
    $title = sanitizeInput($data['title']);
    $description = isset($data['description']) ? sanitizeInput($data['description']) : '';
    $link = sanitizeInput($data['link']);

    if (!validateUrl($link)) {
        sendResponse(['success' => false, 'message' => 'Invalid URL format for link.'], 400);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO resources (title, description, link, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $description, $link]);
        $newId = $pdo->lastInsertId();
        sendResponse(['success' => true, 'message' => 'Resource created.', 'id' => $newId], 201);
    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to create resource.'], 500);
    }
}

function updateResource($pdo, $data) {
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        sendResponse(['success' => false, 'message' => 'Resource id is required for update.'], 400);
    }
    $id = $data['id'];

    // Check resource exists
    $stmt = $pdo->prepare("SELECT id FROM resources WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Resource not found.'], 404);
    }

    // Build dynamic update
    $fields = [];
    $values = [];

    if (isset($data['title'])) {
        $fields[] = 'title = ?';
        $values[] = sanitizeInput($data['title']);
    }
    if (isset($data['description'])) {
        $fields[] = 'description = ?';
        $values[] = sanitizeInput($data['description']);
    }
    if (isset($data['link'])) {
        $link = sanitizeInput($data['link']);
        if (!validateUrl($link)) {
            sendResponse(['success' => false, 'message' => 'Invalid URL format for link.'], 400);
        }
        $fields[] = 'link = ?';
        $values[] = $link;
    }

    if (empty($fields)) {
        sendResponse(['success' => false, 'message' => 'No fields to update.'], 400);
    }

    $values[] = $id; // for WHERE

    $sql = "UPDATE resources SET " . implode(', ', $fields) . " WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        sendResponse(['success' => true, 'message' => 'Resource updated.']);
    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to update resource.'], 500);
    }
}

function deleteResource($pdo, $resourceId) {
    if (!$resourceId || !is_numeric($resourceId)) {
        sendResponse(['success' => false, 'message' => 'Invalid resource id.'], 400);
    }

    // Check exists
    $stmt = $pdo->prepare("SELECT id FROM resources WHERE id = ?");
    $stmt->execute([$resourceId]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Resource not found.'], 404);
    }

    try {
        $pdo->beginTransaction();

        // Delete comments first
        $stmt = $pdo->prepare("DELETE FROM comments WHERE resource_id = ?");
        $stmt->execute([$resourceId]);

        // Delete resource
        $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->execute([$resourceId]);

        $pdo->commit();
        sendResponse(['success' => true, 'message' => 'Resource and its comments deleted.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendResponse(['success' => false, 'message' => 'Failed to delete resource.'], 500);
    }
}

// ---------------------------
// COMMENT FUNCTIONS
// ---------------------------

function getCommentsByResourceId($pdo, $resourceId) {
    if (!$resourceId || !is_numeric($resourceId)) {
        sendResponse(['success' => false, 'message' => 'Invalid resource_id.'], 400);
    }

    $stmt = $pdo->prepare("SELECT id, resource_id, author, text, created_at FROM comments WHERE resource_id = ? ORDER BY created_at ASC");
    $stmt->execute([$resourceId]);
    $rows = $stmt->fetchAll();

    sendResponse(['success' => true, 'data' => $rows]);
}

function createComment($pdo, $data) {
    $check = validateRequiredFields($data, ['resource_id', 'author', 'text']);
    if (!$check['valid']) {
        sendResponse(['success' => false, 'message' => 'Missing fields: ' . implode(',', $check['missing'])], 400);
    }

    if (!is_numeric($data['resource_id'])) {
        sendResponse(['success' => false, 'message' => 'resource_id must be numeric.'], 400);
    }

    // Check resource exists
    $stmt = $pdo->prepare("SELECT id FROM resources WHERE id = ?");
    $stmt->execute([$data['resource_id']]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Resource not found.'], 404);
    }

    $author = sanitizeInput($data['author']);
    $text = sanitizeInput($data['text']);

    try {
        $stmt = $pdo->prepare("INSERT INTO comments (resource_id, author, text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$data['resource_id'], $author, $text]);
        $newId = $pdo->lastInsertId();
        sendResponse(['success' => true, 'message' => 'Comment created.', 'id' => $newId], 201);
    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to create comment.'], 500);
    }
}

function deleteComment($pdo, $commentId) {
    if (!$commentId || !is_numeric($commentId)) {
        sendResponse(['success' => false, 'message' => 'Invalid comment_id.'], 400);
    }

    $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Comment not found.'], 404);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        sendResponse(['success' => true, 'message' => 'Comment deleted.']);
    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to delete comment.'], 500);
    }
}

// ---------------------------
// MAIN ROUTER
// ---------------------------
try {
    if ($method === 'GET') {
        if ($action === 'comments' && $resource_id) {
            getCommentsByResourceId($pdo, $resource_id);
        } elseif ($id) {
            getResourceById($pdo, $id);
        } else {
            getAllResources($pdo);
        }
    } elseif ($method === 'POST') {
        if ($action === 'comment') {
            // expect JSON body
            if (!$body) sendResponse(['success' => false, 'message' => 'Invalid JSON body.'], 400);
            createComment($pdo, $body);
        } else {
            if (!$body) sendResponse(['success' => false, 'message' => 'Invalid JSON body.'], 400);
            createResource($pdo, $body);
        }
    } elseif ($method === 'PUT') {
        if (!$body) sendResponse(['success' => false, 'message' => 'Invalid JSON body.'], 400);
        updateResource($pdo, $body);
    } elseif ($method === 'DELETE') {
        if ($action === 'delete_comment') {
            // comment id can be in query string or JSON body
            if ($comment_id) {
                deleteComment($pdo, $comment_id);
            } else {
                $bodyId = $body['comment_id'] ?? null;
                deleteComment($pdo, $bodyId);
            }
        } else {
            // delete resource
            if ($id) {
                deleteResource($pdo, $id);
            } else {
                $bodyId = $body['id'] ?? null;
                deleteResource($pdo, $bodyId);
            }
        }
    } else {
        sendResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
} catch (PDOException $e) {
    // log error message on server if needed: error_log($e->getMessage());
    sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Server error occurred.'], 500);
}
?>
