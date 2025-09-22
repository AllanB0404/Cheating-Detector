<?php
header('Content-Type: application/json');

session_start();

include 'db_connect.php';

// Check if user is logged in as proctor (assuming proctor role)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'proctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Fetch all student results
    $sql = "SELECT e.title AS exam_title, sr.studentNo, u.name AS student_name, e.subject AS subject, u.section AS section, sr.score, sr.taken_at
            FROM student_results sr
            JOIN users u ON sr.studentNo = u.studentNo
            JOIN exams e ON sr.exam_id = e.id
            ORDER BY sr.taken_at DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    echo json_encode(['status' => 'success', 'results' => $results]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
