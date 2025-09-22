<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: Login.html");
    exit();
}
$studentNo = $_SESSION['user_id'];

include 'db_connect.php';

// Server-side cheating check to block access if cheating detected
$subject = $_GET['subject'] ?? '';
if ($subject) {
    // Get exam_id for the subject
    $stmt = $conn->prepare("SELECT id FROM exams WHERE subject = ?");
    $stmt->bind_param("s", $subject);
    $stmt->execute();
    $stmt->bind_result($exam_id);
    if ($stmt->fetch()) {
        $stmt->close();
        // Check cheating_log for this student and exam
        $stmt2 = $conn->prepare("SELECT id FROM cheating_log WHERE studentNo = ? AND exam_id = ?");
        $stmt2->bind_param("si", $studentNo, $exam_id);
        $stmt2->execute();
        $stmt2->store_result();
        if ($stmt2->num_rows > 0) {
            $stmt2->close();
            $conn->close();
            // Redirect or show message and exit
            header("Location: userdashboard.html?cheating_detected=1&message=cheating_detected");
            exit();
        }
        $stmt2->close();
    } else {
        $stmt->close();
    }
}

$section = '';
$stmt = $conn->prepare("SELECT section FROM users WHERE studentNo = ?");
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$stmt->bind_result($section);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Exam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body>
    <div class="container my-4">
        <h1 class="mb-4">Exam Questions</h1>
        <form id="exam-form" class="bg-white p-4 rounded shadow-sm">
            <div id="question-container">
                <!-- The questions posted by the proctor will be displayed here -->
            </div>
            <button type="submit" class="btn btn-success mt-3">Submit Answers</button>
        </form>
        <div id="feedback" class="mt-3 fw-bold"></div>
        <video id="webcam" autoplay muted style="display:none;"></video>
    </div>
    <script>
        const studentNo = "<?php echo htmlspecialchars($studentNo); ?>";
        const section = "<?php echo htmlspecialchars($section); ?>";
        let globalExamId = null;

async function loadQuestions() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const subject = urlParams.get('subject') || '';

        if (!subject) {
            document.getElementById('question-container').textContent = 'No subject specified in URL.';
            return;
        }

        // Get exam_id for the subject using new API endpoint
        let exam_id = null;
        try {
            const examResponse = await fetch(`get_exam_id.php?subject=${encodeURIComponent(subject)}`);
            const examData = await examResponse.json();
            if (examData.status === 'success') {
                exam_id = examData.exam_id;
            } else {
                console.error('Error fetching exam ID:', examData.message);
            }
        } catch (e) {
            console.error('Error fetching exam ID:', e);
        }

        if (!exam_id) {
            document.getElementById('question-container').textContent = 'Unable to determine exam ID for the subject.';
            return;
        }

        globalExamId = exam_id;

        // Check if cheating was detected for this exam
        try {
            const cheatingResponse = await fetch('check_cheating.php?exam_id=' + encodeURIComponent(exam_id));
            const cheatingData = await cheatingResponse.json();
            if (cheatingData.status === 'cheated') {
                const container = document.getElementById('question-container');
                container.innerHTML = '<div class="alert alert-danger">Cheating detected previously. You cannot access this exam again.</div>';
                setTimeout(() => {
                    window.location.href = 'userdashboard.html';
                }, 3000);
                return;
            }
        } catch (e) {
            console.error('Error checking cheating:', e);
        }

        // Check if student already took the exam
        try {
            const resultsResponse = await fetch('get_student_results.php');
            const resultsData = await resultsResponse.json();
            if (resultsData.status === 'success') {
                const takenExam = resultsData.results.find(r => r.exam_id === exam_id);
if (takenExam) {
    // Show message and redirect after short delay
    const container = document.getElementById('question-container');
    container.innerHTML = '<div class="alert alert-warning">You have already taken this exam. Redirecting to dashboard...</div>';
    setTimeout(() => {
        window.location.href = 'userdashboard.html';
    }, 3000);
    return;
}
            } else {
                console.error('Failed to get student results:', resultsData.message);
            }
        } catch (e) {
            console.error('Error fetching student results:', e);
        }

        // Load questions as usual
        const response = await fetch(`get_exam.php?exam_id=${encodeURIComponent(exam_id)}`);
        const data = await response.json();

        const container = document.getElementById('question-container');
        container.innerHTML = '';

        if (data.status !== 'success' || !data.questions || data.questions.length === 0) {
            container.textContent = 'No questions available.';
            return;
        }

        data.questions.forEach((question, index) => {
            const div = document.createElement('div');
            div.className = 'mb-3';

            const label = document.createElement('label');
            label.className = 'form-label fw-bold';
            label.textContent = `Q${index + 1}: ${question.question_text}`;
            label.setAttribute('for', `answer-${question.id}`);

            div.appendChild(label);

        if (question.question_type === 'multiple-choice' && question.options) {
            question.options.forEach((option, optIndex) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'form-check';

                const input = document.createElement('input');
                input.className = 'form-check-input';
                input.type = 'radio';
                input.name = `answer-${question.id}`;
                input.id = `answer-${question.id}-option-${optIndex}`;
                input.value = option;
                input.required = true;

                const optionLabel = document.createElement('label');
                optionLabel.className = 'form-check-label';
                optionLabel.setAttribute('for', input.id);
                optionLabel.textContent = option;

                optionDiv.appendChild(input);
                optionDiv.appendChild(optionLabel);
                div.appendChild(optionDiv);
            });
        } else if (question.question_type === 'identification') {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = `answer-${question.id}`;
            input.name = `answer-${question.id}`;
            input.placeholder = 'Enter identification answer';
            input.required = true;
            div.appendChild(input);
        } else {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = `answer-${question.id}`;
            input.name = `answer-${question.id}`;
            input.required = true;
            div.appendChild(input);
        }

            container.appendChild(div);
        });
    } catch (error) {
        console.error('Error fetching questions:', error);
        document.getElementById('feedback').textContent = 'Error fetching questions: ' + error.message;
    }
}

        document.getElementById('exam-form').addEventListener('submit', async function(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const answers = {};
            for (const [key, value] of formData.entries()) {
                answers[key] = value;
            }

            const urlParams = new URLSearchParams(window.location.search);
            const subject = urlParams.get('subject') || '';

            let exam_id = null;
            if (subject) {
                try {
                    const examResponse = await fetch(`list_exams.php`);
                    const examText = await examResponse.text();
                    const examIdMatch = examText.match(new RegExp(`Exam ID:\\s*(\\d+)\\s*Subject:\\s*${subject}`, 'i'));
                    if (examIdMatch) {
                        exam_id = parseInt(examIdMatch[1], 10);
                    }
                } catch (e) {
                    console.error('Error fetching exam ID:', e);
                }
            }

            if (!exam_id) {
                document.getElementById('feedback').textContent = 'Unable to determine exam ID for the subject.';
                return;
            }

            try {
                const response = await fetch('save_student_answers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ answers, studentNo, exam_id, section }),
                });
                const result = await response.json();
                const feedbackDiv = document.getElementById('feedback');
if (result.status === 'success') {
    // Redirect to user dashboard after successful submission
    window.location.href = 'userdashboard.html';
} else {
    feedbackDiv.textContent = 'Failed to submit answers: ' + result.message;
}
            } catch (error) {
                document.getElementById('feedback').textContent = 'Error submitting answers: ' + error.message;
            }
        });

        window.onload = loadQuestions;

        async function markCheating(reason) {
            // Capture screenshot immediately at the moment of detection
            const video = document.getElementById('webcam');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            const imageData = canvas.toDataURL('image/png');

            if (globalExamId) {
                // Mark cheating
                fetch('mark_cheating.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'exam_id=' + encodeURIComponent(globalExamId)
                }).catch(e => console.error('Error marking cheating:', e));

                // Save screenshot
                fetch('save_screenshot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'exam_id=' + encodeURIComponent(globalExamId) + '&image=' + encodeURIComponent(imageData)
                }).catch(e => console.error('Error saving screenshot:', e));
            }

            // Now show the message and disable form
            const feedbackDiv = document.getElementById('feedback');
            feedbackDiv.textContent = 'Cheating detected: ' + reason + '. Capturing evidence and redirecting to dashboard in 5 seconds...';
            document.getElementById('exam-form').style.pointerEvents = 'none';
            document.getElementById('exam-form').style.opacity = '0.5';

            setTimeout(() => {
                window.location.href = 'userdashboard.html';
            }, 5000);
        }

        // Detect when student navigates away (switches tabs or minimizes window)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                markCheating('You navigated away from the exam');
            }
        });

        // Webcam detection for suspicious behavior
        const video = document.getElementById('webcam');
        let lastFaceDetected = Date.now();
        navigator.mediaDevices.getUserMedia({video: true})
        .then(stream => {
            video.srcObject = stream;
            return video.play();
        })
        .then(() => {
            return faceapi.nets.tinyFaceDetector.loadFromUri('./weights/tiny_face_detector_model-weights_manifest.json');
        })
        .then(() => {
            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                console.log('Face detections:', detections.length); // Debug log for detection count
                if (detections.length === 0) {
                    // Immediately mark cheating on no face detected (not waiting 60s)
                    markCheating('No face detected');
                    if (Date.now() - lastFaceDetected > 20000) { // 20 seconds
                        markCheating('Prolonged absence from camera');
                    }
                } else {
                    lastFaceDetected = Date.now();
                    if (detections.length > 1) {
                        markCheating('Multiple faces detected');
                    }
                    // Additional suspicious behavior: detect if face is looking away
                    const landmarks = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
                    if (landmarks) {
                        const nose = landmarks.landmarks.getNose();
                        const leftEye = landmarks.landmarks.getLeftEye();
                        const rightEye = landmarks.landmarks.getRightEye();
                        // Simple heuristic: if nose tip is far from midpoint of eyes, user might be looking away
                        const eyeMidX = (leftEye[0].x + rightEye[3].x) / 2;
                        const noseTipX = nose[3].x;
                        const diffX = Math.abs(noseTipX - eyeMidX);
                        if (diffX > 15) { // threshold in pixels, adjust as needed
                            markCheating('Frequent looking away detected');
                        }
                    }
                }
            }, 5000);
        })
        .catch(err => {
            console.error('Camera error:', err);
            // On localhost, Chrome may throw 'NotAllowedError' even if permission granted due to insecure origin
            // So, check if running on localhost and ignore this error
            const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            if ((err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') && !isLocalhost) {
                markCheating('Camera access denied');
            } else {
                // Log other errors but do not mark cheating immediately
                console.warn('Camera error (not permission denied or localhost):', err);
            }
        });
        video.onended = () => markCheating('Camera disabled');
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
