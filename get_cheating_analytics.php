<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is a proctor (assuming role is stored in session)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'proctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Query to get total cheating incidents
    $totalCheatingQuery = "SELECT COUNT(*) as total FROM cheating_log";
    $totalResult = $conn->query($totalCheatingQuery);
    $totalCheating = $totalResult->fetch_assoc()['total'];

    // Query to get cheating incidents per student
    $studentCheatingQuery = "
        SELECT studentNo, COUNT(*) as count
        FROM cheating_log
        GROUP BY studentNo
        ORDER BY count DESC
        LIMIT 10
    ";
    $studentResult = $conn->query($studentCheatingQuery);
    $studentCheating = [];
    while ($row = $studentResult->fetch_assoc()) {
        $studentCheating[] = $row;
    }

    // Query to get cheating incidents per exam
    $examCheatingQuery = "
        SELECT exam_id, COUNT(*) as count
        FROM cheating_log
        GROUP BY exam_id
        ORDER BY count DESC
        LIMIT 10
    ";
    $examResult = $conn->query($examCheatingQuery);
    $examCheating = [];
    while ($row = $examResult->fetch_assoc()) {
        $examCheating[] = $row;
    }

    // Query to get recent cheating incidents
    $recentCheatingQuery = "
        SELECT cl.studentNo, cl.exam_id, cl.timestamp, e.title as exam_title
        FROM cheating_log cl
        LEFT JOIN exams e ON cl.exam_id = e.id
        ORDER BY cl.timestamp DESC
        LIMIT 20
    ";
    $recentResult = $conn->query($recentCheatingQuery);
    $recentCheating = [];
    while ($row = $recentResult->fetch_assoc()) {
        $recentCheating[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_cheating' => $totalCheating,
            'student_cheating' => $studentCheating,
            'exam_cheating' => $examCheating,
            'recent_cheating' => $recentCheating
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
