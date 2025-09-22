<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$studentNo = $_SESSION['user_id'];

// Fetch user data from database
$sql = "SELECT * FROM users WHERE studentNo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();

// Set profile picture path or default
$profilePicture = "uploads/profile_pictures/default.jpg";
if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
    $profilePicture = $user['profile_picture'];
}
?>

<div class="profile-container p-4 bg-white rounded shadow-sm">
    <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" class="profile-picture rounded-circle mx-auto d-block mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #007bff;" />
    <div class="profile-info text-center">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Student No:</strong> <?php echo htmlspecialchars($user['studentNo']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
        <p><strong>Home Address:</strong> <?php echo htmlspecialchars($user['homeAddress']); ?></p>
        <p><strong>Birthdate:</strong> <?php echo htmlspecialchars($user['birthdate']); ?></p>
        <p><strong>Sex:</strong> <?php echo htmlspecialchars($user['sex']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        <p><strong>Section:</strong> <?php echo htmlspecialchars($user['section']); ?></p>
    </div>
    <a href="update_profile.php" class="btn btn-primary d-block mx-auto mt-3" style="width: 180px;">Update Profile</a>
</div>
</create_file>
