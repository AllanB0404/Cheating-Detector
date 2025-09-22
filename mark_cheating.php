<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

include 'db_connect.php';

$studentNo = $_SESSION['user_id'];
$exam_id = $_POST['exam_id'] ?? null;

if (!$exam_id) {
    echo json_encode(['status' => 'error', 'message' => 'Exam ID missing']);
    exit();
}

// Assume table cheating_log exists with columns: id, studentNo, exam_id, timestamp
$stmt = $conn->prepare("INSERT INTO cheating_log (studentNo, exam_id, timestamp) VALUES (?, ?, NOW())");
$stmt->bind_param("si", $studentNo, $exam_id);
if ($stmt->execute()) {
    // Log the cheating incident in audit logs
    $user_id = $studentNo;
    $user_name = $studentNo;
    $action = "CHEATING_DETECTED";
    $details = "Marked cheating for exam ID $exam_id";
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

    $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("sssss", $user_id, $user_name, $action, $details, $ip_address);
    $log_stmt->execute();

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to mark cheating']);
}
$stmt->close();
$conn->close();
?>
