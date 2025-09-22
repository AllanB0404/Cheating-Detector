<?php
header('Content-Type: application/json');
include 'db_connect.php';

try {
    // Get current system settings
    $sql = "SELECT * FROM system_settings ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $settings = $result->fetch_assoc();
        echo json_encode([
            'status' => 'success',
            'settings' => $settings
        ]);
    } else {
        // Return default settings if none exist
        echo json_encode([
            'status' => 'success',
            'settings' => [
                'default_exam_duration' => 60,
                'cheating_detection_threshold' => 0.8,
                'password_min_length' => 8,
                'password_require_special' => true,
                'session_timeout' => 30,
                'email_alert_cheating' => true,
                'email_alert_exam_closure' => true
            ]
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch system settings: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
