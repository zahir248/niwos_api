<?php
include 'db_connect.php';

// Retrieve data sent via POST request
$username = $_POST['username'];
$date = $_POST['date'];
$time = $_POST['time'];

// Convert date to MySQL date format (YYYY-MM-DD)
$formattedDate = date('Y-m-d', strtotime($date));

// Generate NW_Attendance_ID
$nwAttendanceID = generateNWAttendanceID($conn);

// Set ShiftSession_ID to 1
$shiftSessionID = 1;

// Get User_ID based on username
$userID = getUserID($conn, $username);

// Set default value for AttendanceStatus_ID
$attendanceStatusID = 1;

// Check if the time is before or equal to 09:00:00 AM
if (strtotime($time) <= strtotime('09:00:00')) {
    // Time is before or equal to 09:00:00 AM, set AttendanceStatus_ID to 1
    $attendanceStatusID = 1;
} else {
    // Time is after 09:00:00 AM, set AttendanceStatus_ID to 2
    $attendanceStatusID = 2;
}

// Check if there is already a record for the given date and user ID
$sqlCheck = "SELECT * FROM attendance WHERE AttendanceDate = '$formattedDate' AND id = '$userID' AND ShiftSession_ID = '$shiftSessionID'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows > 0) {
    // Attendance record already exists for the given date, user ID, and ShiftSession_ID
    echo "Attendance record already exists for today.";
} else {
    // Prepare SQL statement to insert data into the database
    $sql = "INSERT INTO attendance (NW_Attendance_ID, PunchInTime, PunchOutTime, AttendanceDate, ShiftSession_ID, AttendanceStatus_ID, id) VALUES ('$nwAttendanceID', '$time', NULL, '$formattedDate', '$shiftSessionID', '$attendanceStatusID', '$userID')";

    // Execute SQL statement
    if ($conn->query($sql) === TRUE) {
        // Data inserted successfully
        echo "Data inserted successfully";
    } else {
        // Error occurred
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close database connection
$conn->close();

// Function to generate NW_Attendance_ID
function generateNWAttendanceID($conn) {
    $sql = "SELECT MAX(NW_Attendance_ID) AS max_id FROM attendance";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $maxID = $row['max_id'];
    if ($maxID === null) {
        $nwAttendanceID = 'AT0000001'; // Initial value
    } else {
        $maxIDNumeric = intval(substr($maxID, 2)); // Extract numeric part
        $nextIDNumeric = $maxIDNumeric + 1; // Increment
        $nwAttendanceID = 'AT' . str_pad($nextIDNumeric, 7, '0', STR_PAD_LEFT); // Format
    }
    return $nwAttendanceID;
}

// Function to get User_ID based on username
function getUserID($conn, $username) {
    $sql = "SELECT id FROM users WHERE UserName = '$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } else {
        return null; // User not found
    }
}
