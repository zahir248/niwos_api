<?php
// Include your database connection file
include 'db_connect.php';

// Retrieve AccessRequest_ID from the request body
$requestData = json_decode(file_get_contents('php://input'), true);
$accessRequestID = $requestData['AccessRequest_ID'];

// Update the AccessRequestStatus_ID to 4 for the specified AccessRequest_ID
$updateQuery = "UPDATE access_request SET AccessRequestStatus_ID = 4 WHERE AccessRequest_ID = '$accessRequestID'";

if ($conn->query($updateQuery) === TRUE) {
    echo "Access request status updated successfully";
} else {
    echo "Error updating access request status: " . $conn->error;
}

// Close the database connection
$conn->close();
?>