<?php
// Include your database connection file
include 'db_connect.php';

// Retrieve LeaveRequest_ID from the request body
$requestData = json_decode(file_get_contents('php://input'), true);
$leaveRequestID = $requestData['LeaveRequest_ID'];

// Update the LeaveRequestStatus_ID to 4 for the specified LeaveRequest_ID
$updateQuery = "UPDATE leave_request SET LeaveRequestStatus_ID = 4 WHERE LeaveRequest_ID = '$leaveRequestID'";

if ($conn->query($updateQuery) === TRUE) {
    echo "Leave request status updated successfully";
} else {
    echo "Error updating leave request status: " . $conn->error;
}

// Close the database connection
$conn->close();
?>