<?php
header('Content-Type: application/json');

session_start();

include 'db_connect.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get date range filters if provided
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    // Build WHERE clause for date filters
    $whereClause = "";
    $params = [];
    $types = "";

    if ($startDate && $endDate) {
        $whereClause = "WHERE cl.timestamp BETWEEN ? AND ?";
        $params[] = $startDate . " 00:00:00";
        $params[] = $endDate . " 23:59:59";
        $types = "ss";
    }

    // Query to get total cheating incidents
    $totalCheatingQuery = "SELECT COUNT(*) as total FROM cheating_log cl $whereClause";
    $totalStmt = $conn->prepare($totalCheatingQuery);
    if (!empty($params)) {
        $totalStmt->bind_param($types, ...$params);
    }
    $totalStmt->execute();
    $totalCheating = $totalStmt->get_result()->fetch_assoc()['total'];

    // Query to get cheating incidents per student
    $studentCheatingQuery = "
        SELECT cl.studentNo, u.name, COUNT(*) as count
        FROM cheating_log cl
        JOIN users u ON cl.studentNo = u.studentNo
        $whereClause
        GROUP BY cl.studentNo, u.name
        ORDER BY count DESC
        LIMIT 10
    ";
    $studentStmt = $conn->prepare($studentCheatingQuery);
    if (!empty($params)) {
        $studentStmt->bind_param($types, ...$params);
    }
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();
    $studentCheating = [];
    while ($row = $studentResult->fetch_assoc()) {
        $studentCheating[] = $row;
    }

    // Query to get cheating incidents per exam
    $examCheatingQuery = "
        SELECT cl.exam_id, e.title as exam_title, e.subject, COUNT(*) as count
        FROM cheating_log cl
        JOIN exams e ON cl.exam_id = e.id
        $whereClause
        GROUP BY cl.exam_id, e.title, e.subject
        ORDER BY count DESC
        LIMIT 10
    ";
    $examStmt = $conn->prepare($examCheatingQuery);
    if (!empty($params)) {
        $examStmt->bind_param($types, ...$params);
    }
    $examStmt->execute();
    $examResult = $examStmt->get_result();
    $examCheating = [];
    while ($row = $examResult->fetch_assoc()) {
        $examCheating[] = $row;
    }

    // Query to get recent cheating incidents
    $recentCheatingQuery = "
        SELECT cl.studentNo, u.name as student_name, cl.exam_id, e.title as exam_title,
               e.subject, cl.timestamp
        FROM cheating_log cl
        JOIN users u ON cl.studentNo = u.studentNo
        LEFT JOIN exams e ON cl.exam_id = e.id
        $whereClause
        ORDER BY cl.timestamp DESC
        LIMIT 20
    ";
    $recentStmt = $conn->prepare($recentCheatingQuery);
    if (!empty($params)) {
        $recentStmt->bind_param($types, ...$params);
    }
    $recentStmt->execute();
    $recentResult = $recentStmt->get_result();
    $recentCheating = [];
    while ($row = $recentResult->fetch_assoc()) {
        $recentCheating[] = $row;
    }

    // Get cheating trends over time (last 30 days)
    $trendsQuery = "
        SELECT DATE(cl.timestamp) as date, COUNT(*) as incidents
        FROM cheating_log cl
        WHERE cl.timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(cl.timestamp)
        ORDER BY date
    ";
    $trendsResult = $conn->query($trendsQuery);
    $cheatingTrends = [];
    while ($row = $trendsResult->fetch_assoc()) {
        $cheatingTrends[] = $row;
    }

    // Get cheating by subject
    $subjectCheatingQuery = "
        SELECT e.subject, COUNT(*) as count
        FROM cheating_log cl
        JOIN exams e ON cl.exam_id = e.id
        $whereClause
        GROUP BY e.subject
        ORDER BY count DESC
    ";
    $subjectStmt = $conn->prepare($subjectCheatingQuery);
    if (!empty($params)) {
        $subjectStmt->bind_param($types, ...$params);
    }
    $subjectStmt->execute();
    $subjectResult = $subjectStmt->get_result();
    $subjectCheating = [];
    while ($row = $subjectResult->fetch_assoc()) {
        $subjectCheating[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_cheating' => $totalCheating,
            'student_cheating' => $studentCheating,
            'exam_cheating' => $examCheating,
            'recent_cheating' => $recentCheating,
            'cheating_trends' => $cheatingTrends,
            'subject_cheating' => $subjectCheating
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
