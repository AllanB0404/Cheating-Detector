<?php
header('Content-Type: application/json');

include 'db_connect.php';

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

if ($exam_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Exam ID parameter is required']);
    exit;
}

// Get exam info by id
$stmt = $conn->prepare("SELECT subject, title, status FROM exams WHERE id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Exam not found']);
    exit;
}

$exam = $result->fetch_assoc();
$exam_status = $exam['status'];
$stmt->close();

if ($exam_status === 'closed') {
    echo json_encode(['status' => 'error', 'message' => 'This exam is closed and no longer accepting answers.']);
    exit;
}

// Get questions for the exam with question_type
$stmt = $conn->prepare("SELECT id, question_text, question_type FROM questions WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    // For multiple-choice questions, get options from answers table
    if ($row['question_type'] === 'multiple-choice') {
        $stmt_options = $conn->prepare("SELECT answer_text FROM answers WHERE question_id = ? ORDER BY id ASC");
        $stmt_options->bind_param("i", $row['id']);
        $stmt_options->execute();
        $result_options = $stmt_options->get_result();
        $options = [];
        while ($opt = $result_options->fetch_assoc()) {
            $options[] = $opt['answer_text'];
        }
        $stmt_options->close();
        $row['options'] = $options;
    }
    $questions[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'exam' => $exam, 'questions' => $questions]);
?>
