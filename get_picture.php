<?php
// Connect to your MySQL database
include 'db_connect.php';

// Assuming you have a table named 'user' with columns 'UserName' and 'SecurityImage'
$username = isset($_GET['username']) ? $_GET['username'] : null;

// Check if the username parameter is provided
if ($username) {
    try {
        // Prepare the SQL query
        $sql = "SELECT SecurityImage FROM users WHERE UserName = ?";
        $stmt = $conn->prepare($sql);

        // Bind parameters and execute the query
        $stmt->bind_param("s", $username);
        $stmt->execute();

        // Bind result variables
        $stmt->bind_result($securityImage);

        // Fetch the result
        if ($stmt->fetch()) {
            // Encode the image data as base64
            $encodedImage = base64_encode($securityImage);

            // Prepare the response array
            $response = array(
                'username' => $username,
                'security_image' => $encodedImage
            );

            // Set the appropriate content type header
            header('Content-Type: application/json');

            // Output the JSON response
            echo json_encode($response);
        } else {
            // Return a 404 response if no matching record found
            http_response_code(404);
        }
    } catch (Exception $e) {
        // Return a 500 response if an error occurs
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {
    // Return a 400 response if username parameter is missing or invalid
    http_response_code(400);
    echo "Invalid username";
}
?>
