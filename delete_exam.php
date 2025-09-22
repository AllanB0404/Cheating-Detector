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
        // Start transaction
        $conn->begin_transaction();

        // Delete exam answers first
        $stmt = $conn->prepare("DELETE FROM exam_answers WHERE exam_id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();

        // Delete exam results
        $stmt = $conn->prepare("DELETE FROM student_results WHERE exam_id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();

        // Delete cheating incidents
        $stmt = $conn->prepare("DELETE FROM cheating_log WHERE exam_id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();

        // Delete the exam
        $stmt = $conn->prepare("DELETE FROM exams WHERE id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();

            // Log the exam deletion in audit logs
            $user_id = $_SESSION['user_id'] ?? 'UNKNOWN';
            $user_name = $_SESSION['username'] ?? 'UNKNOWN';
            $action = "DELETE_EXAM";
            $details = "Deleted exam ID $exam_id";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

            $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("sssss", $user_id, $user_name, $action, $details, $ip_address);
            $log_stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'Exam deleted successfully']);
        } else {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Exam not found']);
        }

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
