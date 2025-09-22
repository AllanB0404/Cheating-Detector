<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $studentNo = $conn->real_escape_string($_POST['studentNo']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $confirmPassword = $_POST['confirmPassword'];
    $name = $conn->real_escape_string($_POST['name']);
    $age = (int)$_POST['age'];
    $email = $conn->real_escape_string($_POST['email']);
    $homeAddress = $conn->real_escape_string($_POST['homeAddress']);
    $birthdate = $conn->real_escape_string($_POST['birthdate']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $role = $conn->real_escape_string($_POST['role']);
    $section = $conn->real_escape_string($_POST['section']);

    // Basic validation
    if ($_POST['password'] !== $confirmPassword) {
        // Redirect back to register.html with error message
        header("Location: register_form.php?error=" . urlencode("Password and Confirm Password do not match."));
        exit();
    }

    if ($role === 'proctor') {
        // Check if email already exists in proctors table
        $checkSql = "SELECT * FROM proctors WHERE email = '$email'";
        $checkResult = $conn->query($checkSql);
        if ($checkResult->num_rows > 0) {
            header("Location: register_form.php?error=" . urlencode("Email already registered as proctor."));
            exit();
        }

        // Insert into proctors table
        $sql = "INSERT INTO proctors (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            header("Location: register_form.php?error=" . urlencode("Prepare failed: " . $conn->error));
            exit();
        }
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header("Location: Login.html");
            exit();
        } else {
            header("Location: register_form.php?error=" . urlencode("Error: " . $stmt->error));
            exit();
        }

        $stmt->close();
        $conn->close();
        exit();
    } elseif ($role === 'pproctor') {
        // Accept 'pproctor' as alias for 'proctor' temporarily
        // Check if email already exists in proctors table
        $checkSql = "SELECT * FROM proctors WHERE email = '$email'";
        $checkResult = $conn->query($checkSql);
        if ($checkResult->num_rows > 0) {
            header("Location: register_form.php?error=" . urlencode("Email already registered as proctor."));
            exit();
        }

        // Insert into proctors table
        $sql = "INSERT INTO proctors (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            header("Location: register_form.php?error=" . urlencode("Prepare failed: " . $conn->error));
            exit();
        }
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header("Location: Login.html");
            exit();
        } else {
            header("Location: register_form.php?error=" . urlencode("Error: " . $stmt->error));
            exit();
        }

        $stmt->close();
        $conn->close();
        exit();
    } elseif ($role === 'student') {
        // Check if studentNo or email already exists in users table
        $checkSql = "SELECT * FROM users WHERE studentNo = '$studentNo' OR email = '$email'";
        $checkResult = $conn->query($checkSql);
        if ($checkResult->num_rows > 0) {
            header("Location: register_form.php?error=" . urlencode("Student Number or Email already registered."));
            exit();
        }

        // Insert into users table
        $sql = "INSERT INTO users (studentNo, password, name, age, email, homeAddress, birthdate, sex, role, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            header("Location: register_form.php?error=" . urlencode("Prepare failed: " . $conn->error));
            exit();
        }
        $stmt->bind_param("sssissssss", $studentNo, $password, $name, $age, $email, $homeAddress, $birthdate, $sex, $role, $section);

        if ($stmt->execute()) {
            // Start session and set session variables
            session_start();
            $_SESSION['studentNo'] = $studentNo;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            // Redirect to login page after successful registration
            header("Location: Login.html");
            exit();
        } else {
            header("Location: register_form.php?error=" . urlencode("Error: " . $stmt->error));
            exit();
        }

        $stmt->close();
        $conn->close();
        exit();
    } else {
        if (empty($role)) {
            header("Location: register_form.php?error=" . urlencode("Role must be selected."));
            exit();
        } else {
            header("Location: register_form.php?error=" . urlencode("Invalid role selected: " . htmlspecialchars($role)));
            exit();
        }
    }
} else {
    header("Location: register_form.php?error=" . urlencode("Invalid request method."));
    exit();
}
?>
