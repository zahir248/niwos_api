<?php
// Include the database connection file
include 'db_connect.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $data = json_decode(file_get_contents("php://input"));
    
    // Check if data is valid
    if(isset($data->username) && isset($data->newPassword)){
        // Escape user inputs for security
        $username = mysqli_real_escape_string($conn, $data->username);
        $newPassword = mysqli_real_escape_string($conn, $data->newPassword);
        
        // Hash the password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password in the database
        $sql = "UPDATE users SET password='$hashedPassword' WHERE username='$username'";
        
        if ($conn->query($sql) === TRUE) {
            // Password updated successfully
            echo json_encode(array("message" => "Password updated successfully"));
        } else {
            // Error updating password
            echo json_encode(array("error" => "Error updating password: " . $conn->error));
        }
    } else {
        // Data is missing
        echo json_encode(array("error" => "Missing data"));
    }
} else {
    // Invalid request method
    echo json_encode(array("error" => "Invalid request method"));
}

// Close connection
$conn->close();
?>
