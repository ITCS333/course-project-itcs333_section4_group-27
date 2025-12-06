<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

/* ============================
   DATABASE CONNECTION
   ============================ */
$host = "localhost";
$dbname = "your_database_name";
$username = "your_db_user";
$password = "your_db_password";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                   $username, $password,
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}


/* ============================
   HELPERS
   ============================ */
function respond($success, $data = null, $message = "")
{
    echo json_encode([
        "success" => $success,
        "data" => $data,
        "message" => $message
    ]);
    exit;
}

function getJsonBody()
{
    $input = file_get_contents("php://input");
    return json_decode($input, true);
}


/* ============================
   ROUTING
   ============================ */

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

/* ----------------------------
   GET COMMENTS
   ---------------------------- */
if ($action === "comments") {
    $week_id = $_GET['week_id'] ?? "";
    if (!$week_id) respond(false, null, "week_id required");

    $stmt = $pdo->prepare("SELECT * FROM comments WHERE week_id = ? ORDER BY created_at ASC");
    $stmt->execute([$week_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    respond(true, $comments);
}

/* ----------------------------
   POST COMMENT
   ---------------------------- */
if ($action === "comment" && $method === "POST") {
    $body = getJsonBody();

    if (!isset($body["week_id"], $body["text"])) {
        respond(false, null, "Missing fields (week_id, text)");
    }

    $stmt = $pdo->prepare("INSERT INTO comments (week_id, author, text, created_at)
                           VALUES (?, ?, ?, NOW())");
    $ok = $stmt->execute([
        $body["week_id"],
        $body["author"] ?? "Student",
        $body["text"]
    ]);

    if (!$ok) respond(false, null, "Failed to insert comment");

    respond(true, ["id" => $pdo->lastInsertId()], "Comment added");
}

/* ----------------------------
   DELETE COMMENT
   ---------------------------- */
if ($action === "delete_comment" && $method === "DELETE") {
    $cid = $_GET["comment_id"] ?? "";
    if (!$cid) respond(false, null, "comment_id required");

    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$cid]);

    respond(true, null, "Comment deleted");
}


/* ============================================================================
   WEEKS MAIN ENDPOINTS
   ============================================================================ */

if ($method === "GET") {

    // GET one week
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $stmt = $pdo->prepare("SELECT * FROM weeks WHERE week_id = ?");
        $stmt->execute([$id]);
        $week = $stmt->fetch(PDO::FETCH_ASSOC);
        respond(true, $week ?: null);
    }

    // GET all weeks
    $stmt = $pdo->query("SELECT * FROM weeks ORDER BY start_date ASC");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond(true, $all);
}

if ($method === "POST") {
    $body = getJsonBody();

    if (!isset($body["week_id"], $body["title"], $body["start_date"])) {
        respond(false, null, "Missing required fields (week_id, title, start_date)");
    }

    $links_json = json_encode($body["links"] ?? []);

    $stmt = $pdo->prepare("INSERT INTO weeks (week_id, title, start_date, description, links)
                           VALUES (?, ?, ?, ?, ?)");
    $ok = $stmt->execute([
        $body["week_id"],
        $body["title"],
        $body["start_date"],
        $body["description"] ?? "",
        $links_json
    ]);

    if (!$ok) respond(false, null, "Failed to create week");

    respond(true, ["created_id" => $body["week_id"]], "Week created");
}

if ($method === "PUT") {
    $body = getJsonBody();

    if (!isset($body["week_id"])) {
        respond(false, null, "week_id is required");
    }

    $links_json = json_encode($body["links"] ?? []);

    $stmt = $pdo->prepare("UPDATE weeks
                           SET title = ?, start_date = ?, description = ?, links = ?
                           WHERE week_id = ?");
    $stmt->execute([
        $body["title"],
        $body["start_date"],
        $body["description"],
        $links_json,
        $body["week_id"]
    ]);

    respond(true, null, "Week updated");
}

if ($method === "DELETE") {
    $id = $_GET["id"] ?? null;
    if (!$id) respond(false, null, "id required");

    // delete comments too
    $pdo->prepare("DELETE FROM comments WHERE week_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM weeks WHERE week_id = ?")->execute([$id]);

    respond(true, null, "Week deleted");
}

respond(false, null, "Invalid route");
