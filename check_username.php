<?php

// Include the database connection file
include 'db_connect.php';

// Get username from POST request
$username = $_POST["username"];

// Prepare SQL statement to check if username exists
$sql = "SELECT * FROM user WHERE UserName = '$username'"; // Adjust the column name if necessary

// Execute SQL query
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Username exists, return success message
    echo json_encode(array("exists" => true));
} else {
    // Username does not exist, return error message
    echo json_encode(array("exists" => false));
}

// Close database connection (optional)
// $conn->close();

?>
