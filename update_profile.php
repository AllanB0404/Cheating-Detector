<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$studentNo = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $age = $_POST['age'] ?? '';
    $homeAddress = $_POST['homeAddress'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $role = $_POST['role'] ?? '';
    $section = $_POST['section'] ?? '';

    // Validate inputs (basic example, can be extended)
    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        // Handle profile picture upload if any
        $profilePicturePath = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
            $fileName = $_FILES['profile_picture']['name'];
            $fileSize = $_FILES['profile_picture']['size'];
            $fileType = $_FILES['profile_picture']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Directory for uploads
                $uploadFileDir = 'uploads/profile_pictures/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $newFileName = $studentNo . '_' . time() . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profilePicturePath = $dest_path;
                } else {
                    $error = "There was an error moving the uploaded file.";
                }
            } else {
                $error = "Upload failed. Allowed file types: " . implode(',', $allowedfileExtensions);
            }
        }

        if (!isset($error)) {
            // Update user info in database
            if ($profilePicturePath) {
                $sql = "UPDATE users SET name=?, email=?, age=?, homeAddress=?, birthdate=?, sex=?, role=?, section=?, profile_picture=? WHERE studentNo=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssisssssss", $name, $email, $age, $homeAddress, $birthdate, $sex, $role, $section, $profilePicturePath, $studentNo);
            } else {
                $sql = "UPDATE users SET name=?, email=?, age=?, homeAddress=?, birthdate=?, sex=?, role=?, section=? WHERE studentNo=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssissssss", $name, $email, $age, $homeAddress, $birthdate, $sex, $role, $section, $studentNo);
            }

            if ($stmt->execute()) {
                header("Location: profile.php");
                exit();
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

// Fetch current user data for form pre-fill
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Update Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        input[type="file"] {
            margin-top: 5px;
        }
        .profile-picture-preview {
            display: block;
            margin: 15px auto;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
        }
        .submit-btn {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            margin-top: 15px;
            text-align: center;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #007bff;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Update Profile</h1>
    <?php if (isset($error)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="update_profile.php" method="post" enctype="multipart/form-data">
        <img src="<?php echo htmlspecialchars(!empty($user['profile_picture']) && file_exists($user['profile_picture']) ? $user['profile_picture'] : 'uploads/profile_pictures/default.jpg'); ?>" alt="Profile Picture" class="profile-picture-preview" />
        <label for="profile_picture">Change Profile Picture:</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" />
        
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required />
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />
        
        <label for="age">Age:</label>
        <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($user['age']); ?>" />
        
        <label for="homeAddress">Home Address:</label>
        <input type="text" name="homeAddress" id="homeAddress" value="<?php echo htmlspecialchars($user['homeAddress']); ?>" />
        
        <label for="birthdate">Birthdate:</label>
        <input type="date" name="birthdate" id="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>" />
        
        <label for="sex">Sex:</label>
        <select name="sex" id="sex">
            <option value="Male" <?php if ($user['sex'] === 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if ($user['sex'] === 'Female') echo 'selected'; ?>>Female</option>
            <option value="Other" <?php if ($user['sex'] === 'Other') echo 'selected'; ?>>Other</option>
        </select>
        
        <label for="role">Role:</label>
        <input type="text" name="role" id="role" value="<?php echo htmlspecialchars($user['role']); ?>" />
        
        <label for="section">Section:</label>
        <input type="text" name="section" id="section" value="<?php echo htmlspecialchars($user['section']); ?>" />
        
        <button type="submit" class="submit-btn">Save Changes</button>
    </form>
    <a href="profile.php" class="back-link">Back to Profile</a>
</body>
</html>
