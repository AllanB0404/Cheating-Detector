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
    $subjectFilter = isset($_GET['subject']) ? $_GET['subject'] : null;

    // Build WHERE clause for filters
    $whereClause = "";
    $params = [];
    $types = "";

    if ($startDate && $endDate) {
        $whereClause .= " AND sr.taken_at BETWEEN ? AND ?";
        $params[] = $startDate . " 00:00:00";
        $params[] = $endDate . " 23:59:59";
        $types .= "ss";
    }

    if ($subjectFilter && $subjectFilter !== 'all') {
        $whereClause .= " AND e.subject = ?";
        $params[] = $subjectFilter;
        $types .= "s";
    }

    // Fetch all student results with filters, including average_score and exam_count per student per subject
    $sql = "SELECT 
                e.title AS exam_title, 
                sr.studentNo, 
                u.name AS student_name, 
                e.subject AS subject,
                u.section AS section, 
                sr.score, 
                sr.taken_at, 
                e.id AS exam_id,
                AVG(sr2.score) AS average_score,
                COUNT(sr2.exam_id) AS exam_count
            FROM student_results sr
            JOIN users u ON sr.studentNo = u.studentNo
            JOIN exams e ON sr.exam_id = e.id
            LEFT JOIN student_results sr2 ON sr.studentNo = sr2.studentNo AND e.subject = (SELECT subject FROM exams WHERE id = sr2.exam_id)
            WHERE 1=1 $whereClause
            GROUP BY sr.studentNo, e.subject, sr.taken_at, sr.score, e.title, u.name, u.section, e.id
            ORDER BY sr.taken_at DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception($conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        // Cast average_score and exam_count to appropriate types or null if empty
        $row['average_score'] = $row['average_score'] !== null ? round(floatval($row['average_score']), 2) : null;
        $row['exam_count'] = $row['exam_count'] !== null ? intval($row['exam_count']) : null;
        $results[] = $row;
    }

    // Get unique subjects for filter dropdown
    $subjectsQuery = "SELECT DISTINCT subject FROM exams ORDER BY subject";
    $subjectsResult = $conn->query($subjectsQuery);
    $subjects = [];
    while ($row = $subjectsResult->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }

    // Get summary statistics
    $statsQuery = "SELECT
        COUNT(DISTINCT sr.studentNo) as total_students,
        COUNT(DISTINCT e.id) as total_exams,
        AVG(sr.score) as avg_score,
        COUNT(*) as total_results
        FROM student_results sr
        JOIN exams e ON sr.exam_id = e.id
        WHERE 1=1 $whereClause";

    $statsStmt = $conn->prepare($statsQuery);
    if (!empty($params)) {
        $statsStmt->bind_param($types, ...$params);
    }
    $statsStmt->execute();
    $stats = $statsStmt->get_result()->fetch_assoc();

    // Get exam completion rates
    $completionQuery = "SELECT
        e.id, e.title, e.subject,
        COUNT(DISTINCT sr.studentNo) as completed_count,
        (SELECT COUNT(*) FROM users WHERE role = 'student') as total_students
        FROM exams e
        LEFT JOIN student_results sr ON e.id = sr.exam_id
        GROUP BY e.id, e.title, e.subject
        ORDER BY e.id DESC LIMIT 20";

    $completionResult = $conn->query($completionQuery);
    $completionRates = [];
    while ($row = $completionResult->fetch_assoc()) {
        $completionRates[] = $row;
    }

    // Get user activity data (last 30 days)
    $activityQuery = "SELECT
        DATE(sr.taken_at) as date,
        COUNT(*) as exams_taken,
        COUNT(DISTINCT sr.studentNo) as active_students
        FROM student_results sr
        WHERE sr.taken_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(sr.taken_at)
        ORDER BY date";

    $activityResult = $conn->query($activityQuery);
    $userActivity = [];
    while ($row = $activityResult->fetch_assoc()) {
        $userActivity[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'results' => $results,
            'subjects' => $subjects,
            'stats' => $stats,
            'completion_rates' => $completionRates,
            'user_activity' => $userActivity
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
