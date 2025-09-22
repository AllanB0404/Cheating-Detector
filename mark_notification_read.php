<?php
header('Content-Type: application/json');
include 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? null;

if (!$notification_id) {
    echo json_encode(['status' => 'error', 'message' => 'Notification ID is required']);
    exit;
}

$sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $notification_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to mark notification as read']);
}

$stmt->close();
$conn->close();
?>
