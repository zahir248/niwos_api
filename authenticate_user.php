<?php

// Include your database connection configuration file
include 'db_connect.php';

// Get username and password from POST request
$username = $_POST['username'];
$password = $_POST['password'];

// Sanitize input
$username = mysqli_real_escape_string($conn, $username);

// Query to retrieve the hashed password for the provided username
$query = "SELECT password FROM users WHERE UserName = '$username'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // User found, retrieve the hashed password
    $row = mysqli_fetch_assoc($result);
    $hashed_password_from_db = $row['password'];

    // Verify the password using Laravel's password_verify function
    if (password_verify($password, $hashed_password_from_db)) {
        // Password is correct
        $response = array("found" => true);
    } else {
        // Password is incorrect
        $response = array("found" => false);
    }
} else {
    // User not found
    $response = array("found" => false);
}

// Convert response to JSON format
echo json_encode($response);

// Close database connection
mysqli_close($conn);

?>
