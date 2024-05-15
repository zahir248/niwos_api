<?php
// Include your database connection file
include 'db_connect.php';

// Retrieve username from request parameters
$username = $_GET['username']; // Assuming it's sent as a GET parameter

// Query to fetch User_ID based on username
$userQuery = "SELECT id FROM users WHERE UserName = '$username'";
$userResult = $conn->query($userQuery);

// Check if username exists and get the User_ID
if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $userID = $userData['id'];

    // Query to fetch access request history data based on User_ID and join with related tables
    $query = "SELECT ar.StartTimeDate, ar.EndTimeDate, ar.Duration, ar.Reason, ar.SubmissionTimeDate, ars.Status, aa.AreaName
              FROM access_request AS ar
              JOIN access_request_status AS ars ON ar.AccessRequestStatus_ID = ars.AccessRequestStatus_ID
              JOIN access_request_area AS aa ON ar.AccessRequestArea_ID = aa.AccessRequestArea_ID
              WHERE ar.id = '$userID'";
    $result = $conn->query($query);

    // Check if there are any results
    if ($result->num_rows > 0) {
        // Fetch data from each row and store it in an array
        $accessData = array();
        while ($row = $result->fetch_assoc()) {
            $accessData[] = $row;
        }

        // Send the data as JSON response
        header('Content-Type: application/json');
        echo json_encode($accessData);
    } else {
        // No access request history data found for the user
        echo "No access request history data found for the user";
    }
} else {
    // Username not found
    echo "Username not found";
}

// Close the database connection
$conn->close();
?>