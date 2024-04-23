<?php
// Include your database connection file
include 'db_connect.php';

// Retrieve username from request parameters
$username = $_GET['username']; // Assuming it's sent as a GET parameter

// Query to fetch User_ID based on username
$userQuery = "SELECT User_ID FROM user WHERE UserName = '$username'";
$userResult = $conn->query($userQuery);

// Check if username exists and get the User_ID
if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $userID = $userData['User_ID'];

    // Query to fetch access request history data based on User_ID and join with related tables
    $query = "SELECT ar.AccessRequest_ID, ar.StartTimeDate, ar.EndTimeDate, ar.Duration, ar.Reason, ar.SubmissionTimeDate, ars.Status, ara.AreaName
              FROM access_request AS ar
              JOIN access_request_status AS ars ON ar.AccessRequestStatus_ID = ars.AccessRequestStatus_ID
              JOIN access_request_area AS ara ON ar.AccessRequestArea_ID = ara.AccessRequestArea_ID
              WHERE ar.User_ID = '$userID' AND ar.AccessRequestStatus_ID = 2"; // Adjusted query to include condition for AccessRequestStatus_ID
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
        // No access request history data found for the user with AccessRequestStatus_ID = 2
        echo "No access request history data found for the user with AccessRequestStatus_ID = 2";
    }
} else {
    // Username not found
    echo "Username not found";
}

// Close the database connection
$conn->close();
?>