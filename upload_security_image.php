<?php
// Include the db_connect.php file
include 'db_connect.php'; 

// Function to handle image upload
function uploadImage($conn) {
    // Check if a POST request with 'username' and 'security_image' parameters is received
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['security_image'])) {
        // Get username and image data from the POST request
        $username = $_POST['username'];
        $securityImageData = $_POST['security_image'];

        // Decode base64-encoded image data
        $securityImageData = base64_decode($securityImageData);

        // Update the SecurityImage attribute for the specified username
        $stmt = $conn->prepare("UPDATE users SET SecurityImage = ? WHERE UserName = ?");
        $stmt->bind_param("ss", $securityImageData, $username);

        // Execute the SQL statement
        if ($stmt->execute() === TRUE) {
            // Image uploaded successfully
            echo "Security image uploaded successfully.";
        } else {
            // Error occurred while updating image
            echo "Error: " . $conn->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // No valid POST request received
        echo "Invalid request.";
    }
}

// Call the function to handle image upload
uploadImage($conn);
?>
