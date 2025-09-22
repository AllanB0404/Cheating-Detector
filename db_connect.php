<?php
// Database connection script for db_cheating_detection

$host = 'localhost'; // Change if your MySQL host is different
$username = 'root';  // Change to your MySQL username
$password = '';      // Change to your MySQL password
$database = 'db_cheating_detection';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful
// You can now use $conn to perform database queries

/*
Example usage:
include 'db_connect.php';

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Name: " . $row["name"] . "<br>";
    }
} else {
    echo "0 results";
}
*/
?>
