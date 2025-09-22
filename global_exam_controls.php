<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Function to get system status
function getSystemStatus() {
    global $conn;

    $stmt = $conn->prepare("
        SELECT
            COUNT(*) as ongoing_exams,
            COUNT(CASE WHEN status = 'open' THEN 1 END) as open_exams,
            COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_exams
        FROM exams
    ");
    $stmt->execute();
    $examStats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Get active students count (students currently taking exams)
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT student_id) as active_students
        FROM student_answers sa
        INNER JOIN exams e ON sa.exam_id = e.exam_id
        WHERE e.status = 'open' AND sa.submitted_at IS NULL
    ");
    $stmt->execute();
    $activeStudents = $stmt->get_result()->fetch_assoc()['active_students'];
    $stmt->close();

    // Get system load (simplified - could be enhanced with actual server metrics)
    $systemLoad = 'Normal'; // Placeholder

    // Get last backup time
    $lastBackup = 'N/A';
    $backupDir = 'backups/';
    if (is_dir($backupDir)) {
        $files = scandir($backupDir);
        $backupFiles = array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'json';
        });
        if (!empty($backupFiles)) {
            $latestBackup = max(array_map(function($file) use ($backupDir) {
                return filemtime($backupDir . $file);
            }, $backupFiles));
            $lastBackup = date('Y-m-d H:i:s', $latestBackup);
        }
    }

    return [
        'status' => 'success',
        'system_status' => [
            'ongoing_exams' => $examStats['ongoing_exams'],
            'open_exams' => $examStats['open_exams'],
            'closed_exams' => $examStats['closed_exams'],
            'active_students' => $activeStudents,
            'system_load' => $systemLoad,
            'last_backup' => $lastBackup
        ]
    ];
}

// Function to emergency stop all exams
function emergencyStopAllExams($reason) {
    global $conn;

    try {
        $conn->begin_transaction();

        // Update all open exams to closed
        $stmt = $conn->prepare("
            UPDATE exams
            SET status = 'closed', updated_at = NOW()
            WHERE status = 'open'
        ");
        $stmt->execute();
        $affectedExams = $stmt->affected_rows;
        $stmt->close();

        // Log all students who were affected
        $stmt = $conn->prepare("
            SELECT DISTINCT sa.student_id, sa.exam_id
            FROM student_answers sa
            INNER JOIN exams e ON sa.exam_id = e.exam_id
            WHERE e.status = 'closed' AND sa.submitted_at IS NULL
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $affectedStudents = [];
        while ($row = $result->fetch_assoc()) {
            $affectedStudents[] = $row;
        }
        $stmt->close();

        // Auto-submit all unsubmitted answers for closed exams
        $stmt = $conn->prepare("
            UPDATE student_answers
            SET submitted_at = NOW()
            WHERE exam_id IN (
                SELECT exam_id FROM exams WHERE status = 'closed'
            ) AND submitted_at IS NULL
        ");
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        // Log the emergency action
        logAudit('EMERGENCY_STOP_ALL', "Emergency stop all exams. Reason: $reason. Affected exams: $affectedExams, Affected students: " . count($affectedStudents));

        return [
            'status' => 'success',
            'message' => "Emergency stop completed. $affectedExams exams closed, " . count($affectedStudents) . " students affected.",
            'affected_exams' => $affectedExams,
            'affected_students' => count($affectedStudents)
        ];

    } catch (Exception $e) {
        $conn->rollback();
        return [
            'status' => 'error',
            'message' => 'Emergency stop failed: ' . $e->getMessage()
        ];
    }
}

// Function to pause all exams
function pauseAllExams() {
    global $conn;

    try {
        $stmt = $conn->prepare("
            UPDATE exams
            SET status = 'paused', updated_at = NOW()
            WHERE status = 'open'
        ");
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        logAudit('PAUSE_ALL_EXAMS', "All exams paused. Affected: $affectedRows");

        return [
            'status' => 'success',
            'message' => "$affectedRows exams paused successfully"
        ];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to pause exams: ' . $e->getMessage()
        ];
    }
}

// Function to resume all exams
function resumeAllExams() {
    global $conn;

    try {
        $stmt = $conn->prepare("
            UPDATE exams
            SET status = 'open', updated_at = NOW()
            WHERE status = 'paused'
        ");
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        logAudit('RESUME_ALL_EXAMS', "All exams resumed. Affected: $affectedRows");

        return [
            'status' => 'success',
            'message' => "$affectedRows exams resumed successfully"
        ];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to resume exams: ' . $e->getMessage()
        ];
    }
}

// Function to send global notification
function sendGlobalNotification($recipient, $message) {
    global $conn;

    try {
        $recipients = [];

        if ($recipient === 'all_students') {
            $stmt = $conn->prepare("SELECT studentNo FROM users WHERE role = 'student'");
        } elseif ($recipient === 'all_proctors') {
            $stmt = $conn->prepare("SELECT studentNo FROM users WHERE role = 'proctor'");
        } elseif ($recipient === 'all_users') {
            $stmt = $conn->prepare("SELECT studentNo FROM users");
        } else {
            return ['status' => 'error', 'message' => 'Invalid recipient type'];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $recipients[] = $row['studentNo'];
        }
        $stmt->close();

        // Insert notifications
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, created_at)
            VALUES (?, 'System Notification', ?, NOW())
        ");

        $insertedCount = 0;
        foreach ($recipients as $userId) {
            $stmt->bind_param("ss", $userId, $message);
            $stmt->execute();
            $insertedCount++;
        }
        $stmt->close();

        logAudit('GLOBAL_NOTIFICATION', "Global notification sent to $recipient. Recipients: $insertedCount");

        return [
            'status' => 'success',
            'message' => "Notification sent to $insertedCount recipients"
        ];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to send notification: ' . $e->getMessage()
        ];
    }
}

// Function to execute bulk exam operations
function executeBulkExamOperation($subject, $operation, $value) {
    global $conn;

    try {
        $conn->begin_transaction();

        $whereClause = $subject === 'all' ? '' : "WHERE subject = '$subject'";

        switch ($operation) {
            case 'close':
                $stmt = $conn->prepare("UPDATE exams SET status = 'closed', updated_at = NOW() $whereClause");
                break;
            case 'open':
                $stmt = $conn->prepare("UPDATE exams SET status = 'open', updated_at = NOW() $whereClause");
                break;
            case 'extend':
                if (empty($value) || !is_numeric($value)) {
                    throw new Exception('Valid extension time required');
                }
                $stmt = $conn->prepare("UPDATE exams SET duration = duration + ?, updated_at = NOW() $whereClause");
                $stmt->bind_param("i", $value);
                break;
            case 'reset':
                $stmt = $conn->prepare("DELETE sa FROM student_answers sa INNER JOIN exams e ON sa.exam_id = e.exam_id $whereClause");
                break;
            default:
                throw new Exception('Invalid operation');
        }

        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        $conn->commit();

        logAudit('BULK_EXAM_OPERATION', "Bulk operation: $operation on subject: $subject. Affected: $affectedRows");

        return [
            'status' => 'success',
            'message' => "Operation completed successfully. Affected exams: $affectedRows"
        ];

    } catch (Exception $e) {
        $conn->rollback();
        return [
            'status' => 'error',
            'message' => 'Bulk operation failed: ' . $e->getMessage()
        ];
    }
}

// Function to log audit events
function logAudit($action, $details) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $userId = $_SESSION['user_id'] ?? 'SYSTEM';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt->bind_param("ssss", $userId, $action, $details, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

// Handle requests
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_status':
        echo json_encode(getSystemStatus());
        break;

    case 'emergency_stop':
        $reason = $_POST['reason'] ?? '';
        if (empty($reason)) {
            echo json_encode(['status' => 'error', 'message' => 'Reason is required']);
        } else {
            echo json_encode(emergencyStopAllExams($reason));
        }
        break;

    case 'pause_all':
        echo json_encode(pauseAllExams());
        break;

    case 'resume_all':
        echo json_encode(resumeAllExams());
        break;

    case 'send_notification':
        $recipient = $_POST['recipient'] ?? '';
        $message = $_POST['message'] ?? '';
        if (empty($recipient) || empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'Recipient and message are required']);
        } else {
            echo json_encode(sendGlobalNotification($recipient, $message));
        }
        break;

    case 'bulk_operation':
        $subject = $_POST['subject'] ?? 'all';
        $operation = $_POST['operation'] ?? '';
        $value = $_POST['value'] ?? '';
        if (empty($operation)) {
            echo json_encode(['status' => 'error', 'message' => 'Operation is required']);
        } else {
            echo json_encode(executeBulkExamOperation($subject, $operation, $value));
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>
