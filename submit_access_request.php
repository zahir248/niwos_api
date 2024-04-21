<?php

// Include database configuration file
include 'db_connect.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the submitted form data
    $nameOfArea = $_POST["name_of_area"];
    $startTimeDate = $_POST["start_date_time"];
    $endTimeDate = $_POST["end_date_time"];
    $reason = isset($_POST["reason"]) ? $_POST["reason"] : null;
    $username = $_POST["username"];

    // Calculate duration
    $startDateTime = new DateTime($startTimeDate);
    $endDateTime = new DateTime($endTimeDate);

    // If end time is before start time or same date with endTimeDate and time value for endTimeDate is equal or less than time value for startTimeDate, set duration to 0
    if ($endDateTime < $startDateTime || ($startDateTime->format('Y-m-d') === $endDateTime->format('Y-m-d') && $endDateTime->format('H:i:s') <= $startDateTime->format('H:i:s'))) {
        $duration = 0;
    } else {
        // Otherwise, calculate duration in minutes
        $duration = $startDateTime->diff($endDateTime)->days * 24 * 60; // Convert days to minutes
        $duration += $startDateTime->diff($endDateTime)->h * 60; // Add hours converted to minutes
        $duration += $startDateTime->diff($endDateTime)->i; // Add remaining minutes
    }

    // Function to generate NW_AccessRequest_ID
    function generateARAccessRequestID($conn) {
        $sql = "SELECT MAX(NW_AccessRequest_ID) AS max_id FROM access_request";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $maxID = $row['max_id'];
        if ($maxID === null) {
            $nwRequestId = 'AR0000001'; // Initial value
        } else {
            $maxIDNumeric = intval(substr($maxID, 2)); // Extract numeric part
            $nextIDNumeric = $maxIDNumeric + 1; // Increment
            $nwRequestId = 'AR' . str_pad($nextIDNumeric, 7, '0', STR_PAD_LEFT); // Format
        }
        return $nwRequestId;
    }

    // Generate NW_Request_ID
    $nwRequestId = generateARAccessRequestID($conn);

    // Set SubmissionTimeDate
    date_default_timezone_set('Asia/Kuala_Lumpur'); // Set the time zone to Kuala Lumpur, Malaysia
    $submissionTimeDate = date("Y-m-d H:i:s");

    // Set AccessRequestStatus_ID
    $accessRequestStatusId = 2;

    // Set AccessType_ID based on nameOfArea value
    switch ($nameOfArea) {
        case "Meeting rooms":
            $accessRequestAreaId = 1;
            break;
        case "Training rooms":
            $accessRequestAreaId = 2;
            break;
        default:
            $accessRequestAreaId = null;
    }

    // Fetch User_ID based on username
    $stmt = $conn->prepare("SELECT User_ID FROM user WHERE UserName = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $userId = $row["User_ID"];

    // Insert access request into database
    $sql = "INSERT INTO access_request (NW_AccessRequest_ID, StartTimeDate, EndTimeDate, Duration, Reason, SubmissionTimeDate, AccessRequestStatus_ID, AccessRequestArea_ID, User_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisssii", $nwRequestId, $startTimeDate, $endTimeDate, $duration, $reason, $submissionTimeDate, $accessRequestStatusId, $accessRequestAreaId, $userId);

    // Execute the query
    if ($stmt->execute() === TRUE) {
        //echo "Reason: " . $reason;
        echo "Access request submitted successfully";
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