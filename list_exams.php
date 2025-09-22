<?php
include 'db_connect.php';

header('Content-Type: text/plain');

$sql = "SELECT e.id as exam_id, e.subject, e.title, e.status, q.id as question_id, q.question_text,
        (SELECT COUNT(DISTINCT studentNo) FROM student_results sr WHERE sr.exam_id = e.id) AS student_count
        FROM exams e
        LEFT JOIN questions q ON e.id = q.exam_id
        ORDER BY e.created_at DESC, q.id";

$result = $conn->query($sql);

if (!$result) {
    echo "Error: " . $conn->error;
    exit;
}

$current_exam_id = null;
while ($row = $result->fetch_assoc()) {
    if ($row['exam_id'] !== $current_exam_id) {
        $current_exam_id = $row['exam_id'];
        echo "Exam ID: " . $row['exam_id'] . "\n";
        echo "Subject: " . $row['subject'] . "\n";
        echo "Title: " . $row['title'] . "\n";
        echo "Status: " . ($row['status'] ?? 'open') . "\n";
        echo "Student Count: " . $row['student_count'] . "\n";
        echo "Questions:\n";
    }
    if ($row['question_id']) {
        echo "  - (" . $row['question_id'] . ") " . $row['question_text'] . "\n";
    } else {
        echo "  - No questions\n";
    }
    echo "\n";
}

$conn->close();
?>
