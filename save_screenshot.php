<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

include 'db_connect.php';

$studentNo = $_SESSION['user_id'];
$exam_id = $_POST['exam_id'] ?? null;
$imageData = $_POST['image'] ?? null;

if (!$exam_id || !$imageData) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit();
}

// Decode the base64 image
$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = str_replace(' ', '+', $imageData);
$image = base64_decode($imageData);

// Create a unique filename
$filename = 'screenshot_' . $studentNo . '_' . $exam_id . '_' . time() . '.png';
$filepath = 'screenshots/' . $filename;

// Ensure the screenshots directory exists
if (!is_dir('screenshots')) {
    mkdir('screenshots', 0755, true);
}

// Save the image
if (file_put_contents($filepath, $image)) {
    // Update the cheating_log with the screenshot path
    $stmt = $conn->prepare("UPDATE cheating_log SET screenshot = ? WHERE studentNo = ? AND exam_id = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param("ssi", $filepath, $studentNo, $exam_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'filepath' => $filepath]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update log']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save image']);
}

$conn->close();
?>
