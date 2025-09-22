o<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Try to find user in admins table
    $sql = "SELECT * FROM admins WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    error_log("Admin user fetched: " . print_r($user, true));
    error_log("Password hash from DB: " . $user['password']);
    if (password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['email'];
        $_SESSION['role'] = 'admin';
        header("Location: admindashboard.html");
        exit();
        } else {
            error_log("Password verification failed for admin user: " . $user['email']);
            header("Location: Login.html?error=Invalid+password");
            exit();
        }
}
    $stmt->close();

    // Try to find user in proctors table
    $sql = "SELECT * FROM proctors WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    error_log("Proctor login query executed for email: " . $username);
    error_log("Proctor query result rows: " . $result->num_rows);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['role'] = 'proctor';
            header("Location: proctordashboard.html");
            exit();
        } else {
            header("Location: Login.html?error=Invalid+password");
            exit();
        }
    }
    $stmt->close();

    // Try to find user in users table (students)
    $sql = "SELECT * FROM users WHERE studentNo = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['studentNo'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            if ($_SESSION['role'] === 'proctor') {
                header("Location: proctordashboard.html");
                exit();
            } else {
                header("Location: userdashboard.html");
                exit();
            }
        } else {
            header("Location: Login.html?error=Invalid+password");
            exit();
        }
    } else {
        header("Location: Login.html?error=User+not+found");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect to login page without error message on non-POST access
    header("Location: Login.html");
    exit();
}
?>
