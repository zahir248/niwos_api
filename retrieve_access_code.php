<?php

// Include db_connect.php file
include 'db_connect.php';

// Initialize an array to store the access codes
$accessCodes = array();

// Check if username is provided in the POST request
if(isset($_POST['username'])) {
    // Sanitize and prepare the username for database query
    $username = $conn->real_escape_string($_POST['username']);
    
    // SQL query to retrieve the UserID based on the username
    $userQuery = "SELECT id FROM users WHERE UserName = '$username'";
    
    // Execute the query to fetch UserID
    $userResult = $conn->query($userQuery);
    
    // Check if any rows are returned
    if ($userResult->num_rows > 0) {
        // Fetch the UserID from the result
        $userRow = $userResult->fetch_assoc();
        $userID = $userRow["id"];
        
        // SQL query to select rows to be deleted and insert into deleted_access_log table
        $selectDeleteQuery = "SELECT * FROM user_access_area WHERE id = '$userID' AND EndTimeDate < NOW()";
        $deleteResult = $conn->query($selectDeleteQuery);

        // Check if any rows are returned for deletion
        if ($deleteResult->num_rows > 0) {
            // Initialize an array to store the rows for insertion into deleted_access_log
            $rowsToDelete = array();

            // Fetch rows to be deleted and store them
            while ($row = $deleteResult->fetch_assoc()) {
                $rowsToDelete[] = $row;
            }

            // Insert rows into deleted_access_log table and delete them from user_access_area table
            foreach ($rowsToDelete as $row) {
                // Insert into deleted_access_log
                $insertQuery = "INSERT INTO deleted_access_log (id, AccessArea_ID, StartTimeDate, EndTimeDate, Reason) 
                                VALUES ('{$row['id']}', '{$row['AccessArea_ID']}', '{$row['StartTimeDate']}', '{$row['EndTimeDate']}', 'Terminated by system')";
                $conn->query($insertQuery);

                // Delete from user_access_area
                $deleteQuery = "DELETE FROM user_access_area WHERE id = '{$row['id']}' AND AccessArea_ID = '{$row['AccessArea_ID']}'";
                $conn->query($deleteQuery);
            }
        }

        // SQL query to retrieve all AccessCodes based on the UserID
        $accessQuery = "SELECT aa.AccessCode 
                        FROM user_access_area uaa
                        JOIN access_area aa ON uaa.AccessArea_ID = aa.AccessArea_ID
                        WHERE uaa.id = '$userID'";
        
        // Execute the query to fetch AccessCodes
        $accessResult = $conn->query($accessQuery);
        
        // Check if any rows are returned
        if ($accessResult->num_rows > 0) {
            // Fetch all AccessCodes from the result
            while ($row = $accessResult->fetch_assoc()) {
                $accessCodes[] = $row["AccessCode"];
            }
        }
    }
}

// Close the database connection
$conn->close();

// Send access codes as JSON
echo json_encode($accessCodes);

?>