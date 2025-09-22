<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['cheating_detected' => false]);
    exit();
}

include 'db_connect.php';

$studentNo = $_SESSION['user_id'];
$subject = $_POST['subject'] ?? null;

if (!$subject) {
    echo json_encode(['cheating_detected' => false]);
    exit();
}

// Get exam_id from subject
$stmt = $conn->prepare("SELECT id FROM exams WHERE LOWER(subject) = LOWER(?) LIMIT 1");
$stmt->bind_param("s", $subject);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['cheating_detected' => false]);
    exit();
}
$row = $result->fetch_assoc();
$exam_id = $row['id'];
$stmt->close();

// Check if cheating record exists
$stmt = $conn->prepare("SELECT id FROM cheating_log WHERE studentNo = ? AND exam_id = ?");
$stmt->bind_param("si", $studentNo, $exam_id);
$stmt->execute();
$stmt->store_result();
$cheating_detected = $stmt->num_rows > 0;
$stmt->close();
$conn->close();

echo json_encode(['cheating_detected' => $cheating_detected]);
?>
