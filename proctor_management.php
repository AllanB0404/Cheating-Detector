<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Function to get all proctors
function getAllProctors() {
    global $conn;

    $stmt = $conn->prepare("
        SELECT u.studentNo, u.name, u.email, u.status,
               COUNT(ep.exam_id) as assigned_exams
        FROM users u
        LEFT JOIN exam_proctors ep ON u.studentNo = ep.proctor_id
        WHERE u.role = 'proctor'
        GROUP BY u.studentNo, u.name, u.email, u.status
        ORDER BY u.name
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $proctors = [];
    while ($row = $result->fetch_assoc()) {
        $proctors[] = $row;
    }
    $stmt->close();

    return [
        'status' => 'success',
        'proctors' => $proctors
    ];
}

// Function to get proctor details with assigned exams
function getProctorDetails($proctorId) {
    global $conn;

    // Get proctor info
    $stmt = $conn->prepare("
        SELECT studentNo, name, email, status
        FROM users
        WHERE studentNo = ? AND role = 'proctor'
    ");
    $stmt->bind_param("s", $proctorId);
    $stmt->execute();
    $proctor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$proctor) {
        return ['status' => 'error', 'message' => 'Proctor not found'];
    }

    // Get assigned exams
    $stmt = $conn->prepare("
        SELECT e.exam_id, e.title, e.subject, e.status
        FROM exams e
        INNER JOIN exam_proctors ep ON e.exam_id = ep.exam_id
        WHERE ep.proctor_id = ?
        ORDER BY e.title
    ");
    $stmt->bind_param("s", $proctorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignedExams = [];
    while ($row = $result->fetch_assoc()) {
        $assignedExams[] = $row;
    }
    $stmt->close();

    // Get available exams (not assigned to this proctor)
    $stmt = $conn->prepare("
        SELECT exam_id, title, subject, status
        FROM exams
        WHERE exam_id NOT IN (
            SELECT exam_id FROM exam_proctors WHERE proctor_id = ?
        )
        ORDER BY title
    ");
    $stmt->bind_param("s", $proctorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $availableExams = [];
    while ($row = $result->fetch_assoc()) {
        $availableExams[] = $row;
    }
    $stmt->close();

    return [
        'status' => 'success',
        'proctor' => $proctor,
        'assigned_exams' => $assignedExams,
        'available_exams' => $availableExams
    ];
}

// Function to assign exam to proctor
function assignExamToProctor($proctorId, $examId) {
    global $conn;

    try {
        // Check if assignment already exists
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM exam_proctors
            WHERE proctor_id = ? AND exam_id = ?
        ");
        $stmt->bind_param("ss", $proctorId, $examId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result['count'] > 0) {
            return ['status' => 'error', 'message' => 'Exam already assigned to this proctor'];
        }

        // Insert assignment
        $stmt = $conn->prepare("
            INSERT INTO exam_proctors (proctor_id, exam_id, assigned_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->bind_param("ss", $proctorId, $examId);
        $stmt->execute();
        $stmt->close();

        // Log the assignment
        logAudit('EXAM_ASSIGNED', "Exam $examId assigned to proctor $proctorId");

        return [
            'status' => 'success',
            'message' => 'Exam assigned successfully'
        ];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to assign exam: ' . $e->getMessage()
        ];
    }
}

// Function to unassign exam from proctor
function unassignExamFromProctor($proctorId, $examId) {
    global $conn;

    try {
        $stmt = $conn->prepare("
            DELETE FROM exam_proctors
            WHERE proctor_id = ? AND exam_id = ?
        ");
        $stmt->bind_param("ss", $proctorId, $examId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        if ($affectedRows > 0) {
            // Log the unassignment
            logAudit('EXAM_UNASSIGNED', "Exam $examId unassigned from proctor $proctorId");

            return [
                'status' => 'success',
                'message' => 'Exam unassigned successfully'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Assignment not found'
            ];
        }

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to unassign exam: ' . $e->getMessage()
        ];
    }
}

// Function to get proctor statistics
function getProctorStatistics() {
    global $conn;

    $stmt = $conn->prepare("
        SELECT
            COUNT(DISTINCT u.studentNo) as total_proctors,
            COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.studentNo END) as active_proctors,
            COUNT(DISTINCT ep.exam_id) as total_assignments
        FROM users u
        LEFT JOIN exam_proctors ep ON u.studentNo = ep.proctor_id
        WHERE u.role = 'proctor'
    ");
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return [
        'status' => 'success',
        'statistics' => $stats
    ];
}

// Function to log audit events
function logAudit($action, $details) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $userId = $_SESSION['user_id'] ?? 'SYSTEM';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt->bind_param("ssss", $userId, $action, $details, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

// Handle requests
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_all':
        echo json_encode(getAllProctors());
        break;

    case 'get_details':
        $proctorId = $_GET['proctor_id'] ?? '';
        if (empty($proctorId)) {
            echo json_encode(['status' => 'error', 'message' => 'Proctor ID is required']);
        } else {
            echo json_encode(getProctorDetails($proctorId));
        }
        break;

    case 'assign_exam':
        $proctorId = $_POST['proctor_id'] ?? '';
        $examId = $_POST['exam_id'] ?? '';
        if (empty($proctorId) || empty($examId)) {
            echo json_encode(['status' => 'error', 'message' => 'Proctor ID and Exam ID are required']);
        } else {
            echo json_encode(assignExamToProctor($proctorId, $examId));
        }
        break;

    case 'unassign_exam':
        $proctorId = $_POST['proctor_id'] ?? '';
        $examId = $_POST['exam_id'] ?? '';
        if (empty($proctorId) || empty($examId)) {
            echo json_encode(['status' => 'error', 'message' => 'Proctor ID and Exam ID are required']);
        } else {
            echo json_encode(unassignExamFromProctor($proctorId, $examId));
        }
        break;

    case 'statistics':
        echo json_encode(getProctorStatistics());
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>
