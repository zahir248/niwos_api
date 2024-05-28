<?php
include 'db_connect.php'; // Include the database connection file

// Get the raw POST data
$postData = file_get_contents('php://input');
$request = json_decode($postData, true);
$username = $request['username'] ?? '';

if (empty($username)) {
    echo json_encode(["error" => "Username is required"]);
    exit;
}

// Get the user ID from the username
$userSql = "SELECT id FROM users WHERE username = ?";
$stmtUser = $conn->prepare($userSql);
$stmtUser->bind_param('s', $username);
$stmtUser->execute();
$userResult = $stmtUser->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

$user = $userResult->fetch_assoc();
$userId = $user['id'];

// Prepare SQL queries to fetch notifications
$accessRequestsSql = "
    SELECT 
        CASE 
            WHEN AccessRequestStatus_ID = 1 THEN 'Your Access Request Has Been Approved'
            WHEN AccessRequestStatus_ID = 3 THEN 'Your Access Request Has Been Rejected'
        END as title, 
        CONCAT('Submitted on: ', DATE_FORMAT(SubmissionTimeDate, '%Y-%m-%d %h:%i %p')) as message, 
        DATE_FORMAT(ResponseTimeDate, '%Y-%m-%d %h:%i %p') as timestamp
    FROM access_request 
    WHERE AccessRequestStatus_ID IN (1, 3) AND id = ?
";

$leaveRequestsSql = "
    SELECT 
        CASE 
            WHEN LeaveRequestStatus_ID = 1 THEN 'Your Leave Request Has Been Approved'
            WHEN LeaveRequestStatus_ID = 3 THEN 'Your Leave Request Has Been Rejected'
        END as title, 
        CONCAT('Submitted on: ', DATE_FORMAT(SubmissionTimeDate, '%Y-%m-%d %h:%i %p')) as message, 
        DATE_FORMAT(ResponseTimeDate, '%Y-%m-%d %h:%i %p') as timestamp
    FROM leave_request 
    WHERE LeaveRequestStatus_ID IN (1, 3) AND id = ?
";

$stmtAccess = $conn->prepare($accessRequestsSql);
$stmtAccess->bind_param('i', $userId);
$stmtAccess->execute();
$accessRequests = $stmtAccess->get_result();

$stmtLeave = $conn->prepare($leaveRequestsSql);
$stmtLeave->bind_param('i', $userId);
$stmtLeave->execute();
$leaveRequests = $stmtLeave->get_result();

$notifications = array();

while ($row = $accessRequests->fetch_assoc()) {
    $notifications[] = $row;
}

while ($row = $leaveRequests->fetch_assoc()) {
    $notifications[] = $row;
}

$conn->close();

echo json_encode($notifications);
?>
