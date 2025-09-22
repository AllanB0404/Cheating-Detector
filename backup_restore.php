<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Function to create database backup
function createBackup() {
    global $conn;

    try {
        // Get all tables
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }

        $backupData = [];
        $backupData['timestamp'] = date('Y-m-d H:i:s');
        $backupData['tables'] = [];

        foreach ($tables as $table) {
            // Get table structure
            $structureResult = $conn->query("SHOW CREATE TABLE `$table`");
            $structure = $structureResult->fetch_assoc();
            $backupData['tables'][$table]['structure'] = $structure["Create Table"];

            // Get table data
            $dataResult = $conn->query("SELECT * FROM `$table`");
            $backupData['tables'][$table]['data'] = [];
            while ($row = $dataResult->fetch_assoc()) {
                $backupData['tables'][$table]['data'][] = $row;
            }
        }

        // Create backup directory if it doesn't exist
        $backupDir = 'backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Generate filename
        $filename = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.json';

        // Save backup to file
        if (file_put_contents($filename, json_encode($backupData, JSON_PRETTY_PRINT))) {
            // Log the backup creation
            logAudit('BACKUP_CREATED', "Database backup created: $filename");

            return [
                'status' => 'success',
                'message' => 'Backup created successfully',
                'filename' => $filename,
                'size' => filesize($filename)
            ];
        } else {
            throw new Exception('Failed to save backup file');
        }

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Backup failed: ' . $e->getMessage()
        ];
    }
}

// Function to get backup history
function getBackupHistory() {
    $backupDir = 'backups/';
    $backups = [];

    if (is_dir($backupDir)) {
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $filepath = $backupDir . $file;
                $backups[] = [
                    'filename' => $file,
                    'filepath' => $filepath,
                    'size' => filesize($filepath),
                    'created' => date('Y-m-d H:i:s', filemtime($filepath))
                ];
            }
        }
    }

    // Sort by creation date (newest first)
    usort($backups, function($a, $b) {
        return strtotime($b['created']) - strtotime($a['created']);
    });

    return [
        'status' => 'success',
        'backups' => $backups
    ];
}

// Function to delete backup
function deleteBackup($filename) {
    $backupDir = 'backups/';
    $filepath = $backupDir . $filename;

    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            logAudit('BACKUP_DELETED', "Backup deleted: $filename");
            return [
                'status' => 'success',
                'message' => 'Backup deleted successfully'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to delete backup file'
            ];
        }
    } else {
        return [
            'status' => 'error',
            'message' => 'Backup file not found'
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
    case 'create':
        echo json_encode(createBackup());
        break;

    case 'history':
        echo json_encode(getBackupHistory());
        break;

    case 'delete':
        $filename = $_GET['filename'] ?? '';
        if (empty($filename)) {
            echo json_encode(['status' => 'error', 'message' => 'Filename is required']);
        } else {
            echo json_encode(deleteBackup($filename));
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>
