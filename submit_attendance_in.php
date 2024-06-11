<?php
include 'db_connect.php';

// Retrieve data sent via POST request and sanitize it
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
$time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);

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
$sqlCheck = "SELECT PunchInTime FROM attendance WHERE AttendanceDate = ? AND id = ? AND ShiftSession_ID = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("sii", $formattedDate, $userID, $shiftSessionID);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    // Attendance record already exists for the given date, user ID, and ShiftSession_ID
    $row = $resultCheck->fetch_assoc();
    $existingPunchInTime = $row['PunchInTime'];
    echo "Attendance record already exists for today. Punch-in time: $existingPunchInTime.";
} else {
    // Prepare SQL statement to insert data into the database
    $sql = "INSERT INTO attendance (NW_Attendance_ID, PunchInTime, PunchOutTime, AttendanceDate, ShiftSession_ID, AttendanceStatus_ID, id) VALUES (?, ?, NULL, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $nwAttendanceID, $time, $formattedDate, $shiftSessionID, $attendanceStatusID, $userID);

    // Execute SQL statement
    if ($stmt->execute()) {
        // Data inserted successfully
        echo "Data inserted successfully";
    } else {
        // Error occurred
        echo "Error: " . $stmt->error;
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
    $sql = "SELECT id FROM users WHERE UserName = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } else {
        return null; // User not found
    }
}
?>
