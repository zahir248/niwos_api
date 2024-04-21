<?php

// Include db_connect.php file
include 'db_connect.php';

// Check if username is provided in the POST request
if(isset($_POST['username'])) {
    // Sanitize and prepare the username for database query
    $username = $conn->real_escape_string($_POST['username']);
    
    // SQL query to retrieve the UserID based on the username
    $userQuery = "SELECT User_ID FROM user WHERE UserName = '$username'";
    
    // Execute the query to fetch UserID
    $userResult = $conn->query($userQuery);
    
    // Check if any rows are returned
    if ($userResult->num_rows > 0) {
        // Fetch the UserID from the result
        $userRow = $userResult->fetch_assoc();
        $userID = $userRow["User_ID"];
        
        // SQL query to retrieve all AccessCodes based on the UserID
        $accessQuery = "SELECT aa.AccessCode 
                        FROM user_access_area uaa
                        JOIN access_area aa ON uaa.AccessArea_ID = aa.AccessArea_ID
                        WHERE uaa.User_ID = '$userID'";
        
        // Execute the query to fetch AccessCodes
        $accessResult = $conn->query($accessQuery);
        
        // Initialize an array to store the retrieved access codes
        $accessCodes = array();
        
        // Check if any rows are returned
        if ($accessResult->num_rows > 0) {
            // Fetch all AccessCodes from the result
            while ($row = $accessResult->fetch_assoc()) {
                $accessCodes[] = $row["AccessCode"];
            }
            
            // Return the AccessCodes as the response
            echo json_encode($accessCodes);
        } else {
            // If no rows are returned, echo an empty array
            echo json_encode($accessCodes);
        }
    } else {
        // If no UserID is found for the provided username, echo an empty array
        echo json_encode($accessCodes);
    }
} else {
    // If username is not provided in the POST request, return an error message
    echo "Error: Username not provided.";
}

// Close the database connection
$conn->close();

?>