<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $exam_id = $input['exam_id'] ?? null;

    if (!$exam_id) {
        echo json_encode(['status' => 'error', 'message' => 'Exam ID is required']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE exams SET status = 'open' WHERE id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Exam opened successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Exam not found or already open']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
