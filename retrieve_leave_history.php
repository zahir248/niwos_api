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

    // Query to fetch leave request history data based on User_ID and join with related tables
    $query = "SELECT lr.StartDate, lr.EndDate, lr.Duration, lr.Reason, lr.SubmissionTimeDate, ls.Status, lt.Type
              FROM leave_request AS lr
              JOIN leave_request_status AS ls ON lr.LeaveRequestStatus_ID = ls.LeaveRequestStatus_ID
              JOIN leave_type AS lt ON lr.LeaveType_ID = lt.LeaveType_ID
              WHERE lr.id = '$userID'";
    $result = $conn->query($query);

    // Check if there are any results
    if ($result->num_rows > 0) {
        // Fetch data from each row and store it in an array
        $leaveData = array();
        while ($row = $result->fetch_assoc()) {
            $leaveData[] = $row;
        }

        // Send the data as JSON response
        header('Content-Type: application/json');
        echo json_encode($leaveData);
    } else {
        // No leave request history data found for the user
        echo "No leave request history data found for the user";
    }
} else {
    // Username not found
    echo "Username not found";
}

// Close the database connection
$conn->close();
?>