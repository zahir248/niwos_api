<?php
include 'db_connect.php'; // Include the database connection file

// Check if username is provided in the request
if (isset($_GET['username'])) {
    // Sanitize the input to prevent SQL injection
    $username = $conn->real_escape_string($_GET['username']);

    // Prepare SQL statement with a prepared statement to retrieve user data with department name
    $sql = "SELECT u.name, u.Niwos_ID, d.DepartmentName 
            FROM users u 
            LEFT JOIN department d ON u.Department_ID = d.Department_ID
            WHERE u.UserName = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind parameters and execute the statement
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();

        if ($result) {
            if ($result->num_rows > 0) {
                // Fetch user data as an associative array
                $row = $result->fetch_assoc();
                
                // Convert user data to JSON format
                $user_data = json_encode($row);

                // Output user data
                echo $user_data;
            } else {
                // No user found with the provided username
                echo "User not found";
            }
        } else {
            // Error occurred during query execution
            echo "Error: " . $conn->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Error occurred in preparing the statement
        echo "Error: " . $conn->error;
    }
} else {
    // Username not provided in the request
    echo "Username not provided";
}

// Close database connection
$conn->close();
?>
