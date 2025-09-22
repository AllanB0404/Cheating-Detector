<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');  // Disable display errors to avoid corrupting JSON output
ini_set('log_errors', '1');      // Enable error logging to file
header('Content-Type: application/json');
// Removed include 'db_connect.php';

function db_connect() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_cheating_detection";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        return null;
    }
    return $conn;
}

$conn = db_connect();
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$exam_id = $input['exam_id'] ?? null;
$subject = $input['subject'] ?? null;
$title = $input['title'] ?? null;
$status = $input['status'] ?? null;

if (!$exam_id || !$subject || !$title) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    error_log("Starting transaction for exam update: exam_id=$exam_id");
    $conn->begin_transaction();

    // Update exam main info
    $stmt = $conn->prepare("UPDATE exams SET subject = ?, title = ?, status = ? WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Failed to prepare update exam statement");
    }
    $stmt->bind_param("sssi", $subject, $title, $status, $exam_id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Failed to update exam info");
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Exam updated successfully']);
    exit;
} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

$conn->close();
