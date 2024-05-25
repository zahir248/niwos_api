<?php

// Include your database connection configuration file
include 'db_connect.php';

// Get username and password from POST request
$username = $_POST['username'];
$password = $_POST['password'];

// Sanitize input
$username = mysqli_real_escape_string($conn, $username);

// Query to retrieve the hashed password and account status for the provided username
$query = "SELECT password, AccountStatus_ID FROM users WHERE UserName = '$username'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // User found, retrieve the hashed password and account status
    $row = mysqli_fetch_assoc($result);
    $hashed_password_from_db = $row['password'];
    $account_status_id = $row['AccountStatus_ID'];

    // Verify the password
    if (password_verify($password, $hashed_password_from_db)) {
        if ($account_status_id == 1) {
            // Account is active and password is correct
            $response = array("found" => true, "status" => "active");
        } else {
            // Account is not active
            $response = array("found" => true, "status" => "inactive");
        }
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