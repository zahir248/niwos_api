<?php

// Include your database connection code or configuration file
include 'db_connect.php';

// Check if the username is provided in the URL
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Query to get the User_ID based on the provided username
    $query_user_id = "SELECT id FROM users WHERE UserName = '$username'";
    $result_user_id = $conn->query($query_user_id);

    if ($result_user_id->num_rows > 0) {
        // Fetch User_ID from the result
        $row_user_id = $result_user_id->fetch_assoc();
        $user_id = $row_user_id['id'];

        // Query to retrieve access permission data based on the User_ID
        $query_access_permission = "SELECT access_area.AreaName, user_access_area.StartTimeDate, user_access_area.EndTimeDate 
                                    FROM user_access_area 
                                    INNER JOIN access_area 
                                    ON user_access_area.AccessArea_ID = access_area.AccessArea_ID 
                                    WHERE user_access_area.id = '$user_id'";
        
        $result_access_permission = $conn->query($query_access_permission);

        if ($result_access_permission) {
            // Fetch data from the database result set
            $accessPermissions = [];
            while ($row = $result_access_permission->fetch_assoc()) {
                $accessPermissions[] = $row;
            }

            // Return the access permission data as JSON response
            echo json_encode($accessPermissions);
        } else {
            // Error handling if the query fails
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Failed to retrieve access permission data']);
        }
    } else {
        // Error handling if no user found with the provided username
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'User not found']);
    }
} else {
    // Error handling if the username is not provided in the URL
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Username not provided']);
}

// Close the database connection
$conn->close();

?>