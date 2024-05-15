<?php
// Include the database connection file
include 'db_connect.php';

// Check if the username parameter is provided
if(isset($_GET['username'])) {
    // Retrieve the username from the request
    $username = $conn->real_escape_string($_GET['username']);

    // Fetch user data from the database based on the username, including the profile image data
    $sql = "SELECT u.name, u.Niwos_ID, u.email, u.PhoneNumber, u.StartDate, u.DateOfBirth,
                   p.PositionName, d.DepartmentName, u.ProfileImage
            FROM users u
            LEFT JOIN position p ON u.Position_ID = p.Position_ID
            LEFT JOIN department d ON u.Department_ID = d.Department_ID
            WHERE u.UserName = ?"; // Use placeholder for username

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

                // Encode profile image data as base64
                $row['ProfileImage'] = base64_encode($row['ProfileImage']);
                
                // Output user data
                echo json_encode($row);
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
