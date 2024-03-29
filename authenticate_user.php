<?php

// Include your database connection configuration file
include 'db_connect.php';

// Get username and password from POST request
$username = $_POST['username'];
$password = $_POST['password'];

// Sanitize input
$username = mysqli_real_escape_string($conn, $username);
$password = mysqli_real_escape_string($conn, $password);

// Query to check if user exists with provided username and password
$query = "SELECT * FROM user WHERE UserName = '$username' AND PassWord = '$password'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // User found
    $response = array("found" => true);
} else {
    // User not found
    $response = array("found" => false);
}

// Convert response to JSON format
echo json_encode($response);

// Close database connection
mysqli_close($conn);

?>
