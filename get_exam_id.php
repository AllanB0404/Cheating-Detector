<?php
header('Content-Type: application/json');
include 'db_connect.php';

$subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';

if (empty($subject)) {
    echo json_encode(['status' => 'error', 'message' => 'Subject parameter is required']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM exams WHERE LOWER(subject) = LOWER(?) LIMIT 1");
$stmt->bind_param("s", $subject);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Exam not found for the subject']);
    exit;
}

$row = $result->fetch_assoc();
$exam_id = $row['id'];

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'exam_id' => $exam_id]);
?>
