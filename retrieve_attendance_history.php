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

    // Query to fetch attendance data based on User_ID and join with related tables
    $query = "SELECT a.PunchInTime, a.PunchOutTime, a.AttendanceDate, s.Session, st.Status, u.User_ID
              FROM attendance AS a
              JOIN shift_session AS s ON a.ShiftSession_ID = s.ShiftSession_ID
              JOIN attendance_status AS st ON a.AttendanceStatus_ID = st.AttendanceStatus_ID
              JOIN user AS u ON u.User_ID = '$userID'
              WHERE a.User_ID = '$userID'";
    $result = $conn->query($query);

    // Check if there are any results
    if ($result->num_rows > 0) {
        // Fetch data from each row and store it in an array
        $attendanceData = array();
        while ($row = $result->fetch_assoc()) {
            $attendanceData[] = $row;
        }

        // Send the data as JSON response
        header('Content-Type: application/json');
        echo json_encode($attendanceData);
    } else {
        // No attendance data found for the user
        echo "No attendance data found for the user";
    }
} else {
    // Username not found
    echo "Username not found";
}

// Close the database connection
$conn->close();
?>
