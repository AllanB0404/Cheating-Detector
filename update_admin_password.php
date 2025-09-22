<?php
include 'db_connect.php';

$email = 'admin1@gmail.com';
$new_password_plain = 'ssap123$$';

// Hash the new password
$new_password_hashed = password_hash($new_password_plain, PASSWORD_DEFAULT);

// Prepare and execute the update statement
$sql = "UPDATE admins SET password = ? WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ss", $new_password_hashed, $email);

if ($stmt->execute()) {
    echo "Admin password updated successfully.";
} else {
    echo "Error updating admin password: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
