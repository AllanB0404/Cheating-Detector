<?php
ob_start();
error_reporting(0);
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    include 'db_connect.php';

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['answers']) || !is_array($data['answers']) || !isset($data['studentNo']) || !isset($data['exam_id'])) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        exit;
    }

    $answers = $data['answers'];
    $studentNo = $data['studentNo'];
    $exam_id = $data['exam_id'];
    $section = isset($data['section']) ? $data['section'] : '';

    // Check if exam is closed
    $stmt_check = $conn->prepare("SELECT status FROM exams WHERE id = ?");
    $stmt_check->bind_param("i", $exam_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows === 0) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Exam not found']);
        exit;
    }
    $exam = $result_check->fetch_assoc();
    if ($exam['status'] === 'closed') {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'This exam is closed and no longer accepting answers.']);
        exit;
    }
    $stmt_check->close();

    $total = count($answers);
    $score = 0;

    foreach ($answers as $key => $answer) {
        // Extract question ID from key, e.g., "answer-3"
        if (preg_match('/answer-(\d+)/', $key, $matches)) {
            $question_id = intval($matches[1]);
            $user_answer = trim($answer);

            // Get correct answer from answers table instead of questions.correct_answer_json
            $stmt_answers = $conn->prepare("SELECT answer_text FROM answers WHERE question_id = ? AND is_correct = 1 LIMIT 1");
            $stmt_answers->bind_param("i", $question_id);
            $stmt_answers->execute();
            $result_answers = $stmt_answers->get_result();

            $is_correct = false;

            if ($result_answers->num_rows > 0) {
                $row_answer = $result_answers->fetch_assoc();
                $correct_answer_text = $row_answer['answer_text'];

                // Debug log
                error_log("Comparing user answer '{$user_answer}' with correct answer text: " . $correct_answer_text);

                if (strcasecmp($user_answer, $correct_answer_text) === 0) {
                    $is_correct = true;
                }
            }
            $stmt_answers->close();

            if ($is_correct) {
                $score++;
            }
        }
    }

    error_log("Attempting to save results for studentNo: {$studentNo}, exam_id: {$exam_id}, score: {$score}");

    // Validate studentNo exists in users table before insert
    $stmt_check_student = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE studentNo = ?");
    $stmt_check_student->bind_param("s", $studentNo);
    $stmt_check_student->execute();
    $result_check_student = $stmt_check_student->get_result();
    $row_check_student = $result_check_student->fetch_assoc();
    $stmt_check_student->close();

    if ($row_check_student['count'] == 0) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Invalid student number.']);
        exit;
    }

    // Save the score in student_results table including section
    $stmt = $conn->prepare("INSERT INTO student_results (studentNo, exam_id, section, score) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE score = VALUES(score), section = VALUES(section), taken_at = CURRENT_TIMESTAMP");
    $stmt->bind_param("sisi", $studentNo, $exam_id, $section, $score);
    $stmt->execute();
    $stmt->close();

    ob_end_clean();
    echo json_encode(['status' => 'success', 'score' => $score, 'total' => $total]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
