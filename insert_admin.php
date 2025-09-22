<?php
include 'db_connect.php';

$name = 'admin1';
$email = 'admin1@gmail.com';
$password_plain = 'ssap123$$';

// Hash the password
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// Prepare and execute the insert statement
$sql = "INSERT INTO admins (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sss", $name, $email, $password_hashed);

if ($stmt->execute()) {
    echo "Admin account inserted successfully.";
} else {
    echo "Error inserting admin account: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
