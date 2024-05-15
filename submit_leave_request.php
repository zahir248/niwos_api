<?php

// Include database configuration file
include 'db_connect.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the submitted form data
    $typeOfLeave = $_POST["type_of_leave"];
    $startDate = $_POST["start_date"];
    $endDate = $_POST["end_date"];
    $reason = isset($_POST["reason"]) ? $_POST["reason"] : null;
    $username = $_POST["username"];

    // Calculate duration
    $startDateTime = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);
    $duration = $startDateTime->diff($endDateTime)->days;

    // Function to generate NW_LeaveRequest_ID
    function generateLRLeaveRequestID($conn) {
        $sql = "SELECT MAX(NW_LeaveRequest_ID) AS max_id FROM leave_request";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $maxID = $row['max_id'];
        if ($maxID === null) {
            $nwRequestId = 'LR0000001'; // Initial value
        } else {
            $maxIDNumeric = intval(substr($maxID, 2)); // Extract numeric part
            $nextIDNumeric = $maxIDNumeric + 1; // Increment
            $nwRequestId = 'LR' . str_pad($nextIDNumeric, 7, '0', STR_PAD_LEFT); // Format
        }
        return $nwRequestId;
    }

    // Generate NW_Request_ID
    $nwRequestId = generateLRLeaveRequestID($conn);

    // Set SubmissionTimeDate
    date_default_timezone_set('Asia/Kuala_Lumpur'); // Set the time zone to Kuala Lumpur, Malaysia
    $submissionTimeDate = date("Y-m-d H:i:s");

    // Set LeaveRequestStatus_ID
    $leaveRequestStatusId = 2;

    // Set LeaveType_ID based on typeOfLeave value
    switch ($typeOfLeave) {
        case "Sick":
            $leaveTypeId = 1;
            break;
        case "Vacation":
            $leaveTypeId = 2;
            break;
        case "Personal":
            $leaveTypeId = 3;
            break;
        case "Maternity":
            $leaveTypeId = 4;
            break;
        default:
            $leaveTypeId = null;
    }

    // Fetch User_ID based on username
    $stmt = $conn->prepare("SELECT id FROM users WHERE UserName = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $userId = $row["id"];

    // Insert leave request into database
    $sql = "INSERT INTO leave_request (NW_LeaveRequest_ID, StartDate, EndDate, Duration, Reason, SubmissionTimeDate, LeaveRequestStatus_ID, LeaveType_ID, id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisssii", $nwRequestId, $startDate, $endDate, $duration, $reason, $submissionTimeDate, $leaveRequestStatusId, $leaveTypeId, $userId);

    // Execute the query
    if ($stmt->execute() === TRUE) {
        //echo "Reason: " . $reason;
        echo "Leave request submitted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close connection
    $stmt->close();
    $conn->close();
} else {
    // Invalid request method
    echo "Invalid request method";
}
?>