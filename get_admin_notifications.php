<?php
header('Content-Type: application/json');
include 'db_connect.php';

// Get unread notifications
$sql = "SELECT id, type, title, message, created_at FROM notifications WHERE is_read = 0 ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Get total unread count
$sql_count = "SELECT COUNT(*) as unread_count FROM notifications WHERE is_read = 0";
$result_count = $conn->query($sql_count);
$unread_count = 0;
if ($result_count->num_rows > 0) {
    $row_count = $result_count->fetch_assoc();
    $unread_count = $row_count['unread_count'];
}

echo json_encode([
    'status' => 'success',
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);

$conn->close();
?>
