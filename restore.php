<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Function to restore database from backup
function restoreBackup($filename) {
    global $conn;

    try {
        $backupDir = 'backups/';
        $filepath = $backupDir . $filename;

        if (!file_exists($filepath)) {
            throw new Exception('Backup file not found');
        }

        // Read backup file
        $backupData = json_decode(file_get_contents($filepath), true);
        if (!$backupData) {
            throw new Exception('Invalid backup file format');
        }

        // Start transaction
        $conn->begin_transaction();

        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Clear existing data and restore from backup
        foreach ($backupData['tables'] as $tableName => $tableData) {
            // Drop and recreate table structure
            $conn->query("DROP TABLE IF EXISTS `$tableName`");
            $conn->query($tableData['structure']);

            // Insert data
            if (!empty($tableData['data'])) {
                foreach ($tableData['data'] as $row) {
                    $columns = array_keys($row);
                    $values = array_values($row);

                    $placeholders = str_repeat('?,', count($values) - 1) . '?';
                    $columnsStr = '`' . implode('`,`', $columns) . '`';

                    $stmt = $conn->prepare("INSERT INTO `$tableName` ($columnsStr) VALUES ($placeholders)");
                    $stmt->bind_param(str_repeat('s', count($values)), ...$values);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        // Commit transaction
        $conn->commit();

        // Log the restore
        logAudit('DATABASE_RESTORED', "Database restored from backup: $filename");

        return [
            'status' => 'success',
            'message' => 'Database restored successfully from backup: ' . $filename
        ];

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        return [
            'status' => 'error',
            'message' => 'Restore failed: ' . $e->getMessage()
        ];
    }
}

// Function to validate backup file
function validateBackup($filename) {
    $backupDir = 'backups/';
    $filepath = $backupDir . $filename;

    if (!file_exists($filepath)) {
        return ['status' => 'error', 'message' => 'Backup file not found'];
    }

    $backupData = json_decode(file_get_contents($filepath), true);
    if (!$backupData) {
        return ['status' => 'error', 'message' => 'Invalid backup file format'];
    }

    if (!isset($backupData['tables']) || !is_array($backupData['tables'])) {
        return ['status' => 'error', 'message' => 'Backup file missing tables data'];
    }

    return [
        'status' => 'success',
        'message' => 'Backup file is valid',
        'info' => [
            'timestamp' => $backupData['timestamp'] ?? 'Unknown',
            'table_count' => count($backupData['tables'])
        ]
    ];
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'restore':
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['status' => 'error', 'message' => 'Filename is required']);
            } else {
                echo json_encode(restoreBackup($filename));
            }
            break;

        case 'validate':
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['status' => 'error', 'message' => 'Filename is required']);
            } else {
                echo json_encode(validateBackup($filename));
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
