<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $exam_id = $_GET['exam_id'] ?? null;

    if (!$exam_id) {
        echo json_encode(['status' => 'error', 'message' => 'Exam ID is required']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM exams WHERE id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $exam = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $exam]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Exam not found']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
