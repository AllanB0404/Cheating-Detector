<?php
header('Content-Type: application/json');

session_start();

include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    $studentNo = $_SESSION['user_id'];

    $sql = "SELECT sr.id, sr.studentNo, u.name AS student_name, sr.exam_id, e.title AS exam_title, sr.score, sr.taken_at, COALESCE(sr.section, u.section) AS section
            FROM student_results sr
            JOIN users u ON sr.studentNo = u.studentNo
            JOIN exams e ON sr.exam_id = e.id
            WHERE sr.studentNo = ?
            ORDER BY sr.taken_at DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("s", $studentNo);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'results' => $results]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
