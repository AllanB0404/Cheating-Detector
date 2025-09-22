<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$user = isset($_GET['user']) ? $_GET['user'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : null;

try {
    // Build query
    $query = "SELECT * FROM audit_logs WHERE 1=1";
    $params = array();
    $types = "";

    if ($start_date) {
        $query .= " AND timestamp >= ?";
        $params[] = $start_date . " 00:00:00";
        $types .= "s";
    }

    if ($end_date) {
        $query .= " AND timestamp <= ?";
        $params[] = $end_date . " 23:59:59";
        $types .= "s";
    }

    if ($user) {
        $query .= " AND (user_name LIKE ? OR user_id LIKE ?)";
        $params[] = "%$user%";
        $params[] = "%$user%";
        $types .= "ss";
    }

    if ($action) {
        $query .= " AND action = ?";
        $params[] = $action;
        $types .= "s";
    }

    // Get total count
    $count_query = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
    $stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total_logs = $result->fetch_assoc()['total'];

    // Add ordering and pagination
    $query .= " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = ($page - 1) * $per_page;
    $types .= "ii";

    // Execute main query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = array();
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    $total_pages = ceil($total_logs / $per_page);

    echo json_encode(array(
        'status' => 'success',
        'data' => array(
            'logs' => $logs,
            'total_logs' => $total_logs,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        )
    ));

} catch (Exception $e) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ));
}

$conn->close();
?>
