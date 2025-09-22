<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

include 'db_connect.php';

$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$questions_json = isset($_POST['questions']) ? $_POST['questions'] : '[]';
$answers_json = isset($_POST['answers']) ? $_POST['answers'] : '[]';

if (empty($subject) || empty($title)) {
    echo json_encode(['status' => 'error', 'message' => 'Subject and title are required']);
    exit;
}

$questions = json_decode($questions_json, true);
$answers = json_decode($answers_json, true);
$questionTypes_json = isset($_POST['questionTypes']) ? $_POST['questionTypes'] : '[]';
$questionTypes = json_decode($questionTypes_json, true);

if (!is_array($questions) || !is_array($answers) || !is_array($questionTypes) || count($questions) !== count($answers) || count($questions) !== count($questionTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid questions, answers, or question types data']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert exam
    $stmt = $conn->prepare("INSERT INTO exams (subject, title) VALUES (?, ?)");
    $stmt->bind_param("ss", $subject, $title);
    $stmt->execute();
    $exam_id = $stmt->insert_id;
    $stmt->close();

    // Insert questions and answers
    $stmt_q = $conn->prepare("INSERT INTO questions (exam_id, question_text, question_type) VALUES (?, ?, ?)");
    $stmt_a = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");

    for ($i = 0; $i < count($questions); $i++) {
        $question_text = $questions[$i];
        $correct_answer = $answers[$i];
        $question_type = $questionTypes[$i];

        // Insert question with type
        $stmt_q->bind_param("iss", $exam_id, $question_text, $question_type);
        $stmt_q->execute();
        $question_id = $stmt_q->insert_id;

        // Insert correct answer(s)
        $is_correct = 1;

        if ($question_type === 'multiple-choice' && is_array($correct_answer)) {
            // Insert all options, mark the correct one
            foreach ($correct_answer['options'] as $index => $option_text) {
                $is_correct_option = ($index === $correct_answer['correctIndex']) ? 1 : 0;
                $stmt_a->bind_param("isi", $question_id, $option_text, $is_correct_option);
                $stmt_a->execute();
            }
        } else {
            // For short-answer and identification, insert single correct answer
            $stmt_a->bind_param("isi", $question_id, $correct_answer, $is_correct);
            $stmt_a->execute();
        }
    }

    $stmt_q->close();
    $stmt_a->close();

    $conn->commit();

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
