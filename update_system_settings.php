<?php
session_start();
header('Content-Type: application/json');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get POST data
    $default_exam_duration = intval($_POST['default_exam_duration'] ?? 60);
    $cheating_detection_threshold = floatval($_POST['cheating_detection_threshold'] ?? 0.8);
    $password_min_length = intval($_POST['password_min_length'] ?? 8);
    $password_require_special = isset($_POST['password_require_special']) ? 1 : 0;
    $session_timeout = intval($_POST['session_timeout'] ?? 30);
    $email_alert_cheating = isset($_POST['email_alert_cheating']) ? 1 : 0;
    $email_alert_exam_closure = isset($_POST['email_alert_exam_closure']) ? 1 : 0;

    // Validation
    if ($default_exam_duration < 1 || $default_exam_duration > 480) {
        echo json_encode(['status' => 'error', 'message' => 'Exam duration must be between 1 and 480 minutes']);
        exit;
    }
    if ($cheating_detection_threshold < 0 || $cheating_detection_threshold > 1) {
        echo json_encode(['status' => 'error', 'message' => 'Cheating detection threshold must be between 0 and 1']);
        exit;
    }
    if ($password_min_length < 4 || $password_min_length > 50) {
        echo json_encode(['status' => 'error', 'message' => 'Password minimum length must be between 4 and 50']);
        exit;
    }
    if ($session_timeout < 5 || $session_timeout > 480) {
        echo json_encode(['status' => 'error', 'message' => 'Session timeout must be between 5 and 480 minutes']);
        exit;
    }

    // Get current settings for logging
    $current_sql = "SELECT * FROM system_settings ORDER BY id DESC LIMIT 1";
    $current_result = $conn->query($current_sql);
    $old_settings = $current_result->num_rows > 0 ? $current_result->fetch_assoc() : null;

    // Insert or update settings
    if ($old_settings) {
        $update_sql = "UPDATE system_settings SET
            default_exam_duration = ?,
            cheating_detection_threshold = ?,
            password_min_length = ?,
            password_require_special = ?,
            session_timeout = ?,
            email_alert_cheating = ?,
            email_alert_exam_closure = ?
            WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("idiiiiii",
            $default_exam_duration,
            $cheating_detection_threshold,
            $password_min_length,
            $password_require_special,
            $session_timeout,
            $email_alert_cheating,
            $email_alert_exam_closure,
            $old_settings['id']
        );
    } else {
        $insert_sql = "INSERT INTO system_settings
            (default_exam_duration, cheating_detection_threshold, password_min_length,
             password_require_special, session_timeout, email_alert_cheating, email_alert_exam_closure)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("idiiiii",
            $default_exam_duration,
            $cheating_detection_threshold,
            $password_min_length,
            $password_require_special,
            $session_timeout,
            $email_alert_cheating,
            $email_alert_exam_closure
        );
    }

    if ($stmt->execute()) {
        // Log the changes
        $admin_id = $_SESSION['user_id'] ?? null;
        if ($admin_id === null) {
            echo json_encode(['status' => 'error', 'message' => 'Admin not logged in']);
            exit;
        }
        $action = "SETTINGS_CHANGED";

        $user_name = $_SESSION['username'] ?? null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $details = "Updated system settings";
        $log_stmt->bind_param("sssss", $user_name, $user_name, $action, $details, $ip_address);
        $log_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'System settings updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update system settings']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
