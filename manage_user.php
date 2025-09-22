<?php
include 'db_connect.php';

header('Content-Type: application/json');

// Get the POST input
$input = $_POST;

if (!$input || !isset($input['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$action = $input['action'];

switch ($action) {
    case 'add':
        handleAddUser($input, $conn);
        break;
    case 'update':
        handleUpdateUser($input, $conn);
        break;
    case 'delete':
        handleDeleteUser($input, $conn);
        break;
    case 'toggle_status':
        handleToggleStatus($input, $conn);
        break;
    case 'bulk_delete':
        handleBulkDelete($input, $conn);
        break;
    case 'bulk_change_role':
        handleBulkChangeRole($input, $conn);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
        break;
}

$conn->close();

function handleAddUser($data, $conn) {
    // Validate required fields
    if (!isset($data['studentNo']) || !isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $studentNo = $conn->real_escape_string($data['studentNo']);
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = isset($data['role']) ? $conn->real_escape_string($data['role']) : 'student';
    $section = isset($data['section']) ? $conn->real_escape_string($data['section']) : '';

    // Check if studentNo already exists
    $checkSql = "SELECT studentNo FROM users WHERE studentNo = '$studentNo'";
    $result = $conn->query($checkSql);
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Student number already exists']);
        return;
    }

    $sql = "INSERT INTO users (studentNo, password, name, email, role, section) VALUES ('$studentNo', '$password', '$name', '$email', '$role', '$section')";

    if ($conn->query($sql) === TRUE) {
        // Log the user creation in audit logs
        $user_id = $studentNo;
        $user_name = $name;
        $action = "USER_CREATED";
        $details = "Created new user $studentNo";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("sssss", $user_id, $user_name, $action, $details, $ip_address);
        $log_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding user: ' . $conn->error]);
    }
}

function handleUpdateUser($data, $conn) {
    if (!isset($data['studentNo'])) {
        echo json_encode(['status' => 'error', 'message' => 'Student number is required']);
        return;
    }

    $studentNo = $conn->real_escape_string($data['studentNo']);
    $updates = [];

    if (isset($data['name'])) {
        $updates[] = "name = '" . $conn->real_escape_string($data['name']) . "'";
    }
    if (isset($data['email'])) {
        $updates[] = "email = '" . $conn->real_escape_string($data['email']) . "'";
    }
    if (isset($data['role'])) {
        $updates[] = "role = '" . $conn->real_escape_string($data['role']) . "'";
    }
    if (isset($data['section'])) {
        $updates[] = "section = '" . $conn->real_escape_string($data['section']) . "'";
    }
    if (isset($data['password']) && !empty($data['password'])) {
        $updates[] = "password = '" . password_hash($data['password'], PASSWORD_DEFAULT) . "'";
    }

    if (empty($updates)) {
        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        return;
    }

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE studentNo = '$studentNo'";

    if ($conn->query($sql) === TRUE) {
        // Log the user update in audit logs
        $user_id = $studentNo;
        $user_name = $conn->real_escape_string($data['name'] ?? '');
        $action = "USER_UPDATED";
        $details = "Updated user $studentNo";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("sssss", $user_id, $user_name, $action, $details, $ip_address);
        $log_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating user: ' . $conn->error]);
    }
}

function handleDeleteUser($data, $conn) {
    if (!isset($data['studentNo'])) {
        echo json_encode(['status' => 'error', 'message' => 'Student number is required']);
        return;
    }

    $studentNo = $conn->real_escape_string($data['studentNo']);

    $sql = "DELETE FROM users WHERE studentNo = '$studentNo'";

    if ($conn->query($sql) === TRUE) {
        // Log the user deletion in audit logs
        $user_id = $studentNo;
        $user_name = ''; // Name is not available here
        $action = "USER_DELETED";
        $details = "Deleted user $studentNo";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("sssss", $user_id, $user_name, $action, $details, $ip_address);
        $log_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting user: ' . $conn->error]);
    }
}

function handleToggleStatus($data, $conn) {
    // Note: Since the users table doesn't have a status column in the current schema,
    // this functionality would require adding a status column or using a different approach.
    // For now, we'll return an error indicating this needs to be implemented.
    echo json_encode(['status' => 'error', 'message' => 'Status toggle not implemented - requires database schema change']);
}

function handleBulkDelete($data, $conn) {
    if (!isset($data['studentNos']) || !is_array($data['studentNos'])) {
        echo json_encode(['status' => 'error', 'message' => 'Student numbers array is required']);
        return;
    }

    $studentNos = array_map(function($no) use ($conn) {
        return "'" . $conn->real_escape_string($no) . "'";
    }, $data['studentNos']);

    $sql = "DELETE FROM users WHERE studentNo IN (" . implode(', ', $studentNos) . ")";

    if ($conn->query($sql) === TRUE) {
        // Log the bulk user deletion in audit logs
        $user_id = 'BULK_DELETE';
        $user_name = '';
        $action = "BULK_USER_DELETED";
        $details = "Deleted users: " . implode(', ', $data['studentNos']);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("sssss", $user_id, $user_name, $action, $details, $ip_address);
        $log_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Users deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting users: ' . $conn->error]);
    }
}

function handleBulkChangeRole($data, $conn) {
    if (!isset($data['studentNos']) || !is_array($data['studentNos']) || !isset($data['role'])) {
        echo json_encode(['status' => 'error', 'message' => 'Student numbers array and role are required']);
        return;
    }

    $role = $conn->real_escape_string($data['role']);
    $studentNos = array_map(function($no) use ($conn) {
        return "'" . $conn->real_escape_string($no) . "'";
    }, $data['studentNos']);

    $sql = "UPDATE users SET role = '$role' WHERE studentNo IN (" . implode(', ', $studentNos) . ")";

    if ($conn->query($sql) === TRUE) {
        // Log the bulk role change in audit logs
        $user_id = 'BULK_ROLE_CHANGE';
        $user_name = '';
        $action = "BULK_ROLE_CHANGED";
        $details = "Changed role to $role for users: " . implode(', ', $data['studentNos']);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        $log_sql = "INSERT INTO audit_logs (user_id, user_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("sssss", $user_id, $user_name, $action, $details, $ip_address);
        $log_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'User roles updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating user roles: ' . $conn->error]);
    }
}
?>
