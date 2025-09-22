<?php
include 'db_connect.php';

header('Content-Type: application/json');

// Query to get all users
$sql = "SELECT studentNo, name, role, section, email, created_at, updated_at FROM users ORDER BY created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Database query failed: ' . $conn->error]);
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['status' => 'success', 'users' => $users]);

$conn->close();
?>
