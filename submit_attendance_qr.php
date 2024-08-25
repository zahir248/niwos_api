<?php
// Include database connection
include 'db_connect.php';

// Get POST data
$username = $_POST['username'] ?? '';
$punchType = $_POST['punchType'] ?? '';
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';

// Validate data
if (empty($username) || empty($punchType) || empty($date) || empty($time)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Retrieve the user's ID based on the username
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($userId);
$stmt->fetch();
$stmt->close();

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Format the date to YYYY-MM-DD
$date = date('Y-m-d', strtotime($date));

// Check if the Punch In or Punch Out record exists
if ($punchType == 'Punch In') {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM attendance 
        WHERE id = ? AND AttendanceDate = ? AND PunchInTime IS NOT NULL
    ");
    $stmt->bind_param("ss", $userId, $date);
    $stmt->execute();
    $stmt->bind_result($punchInCount);
    $stmt->fetch();
    $stmt->close();

    if ($punchInCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Punch In already recorded for today']);
        exit;
    }

    // Determine AttendanceStatus_ID based on time
    $punchInTime = DateTime::createFromFormat('H:i:s', $time);
    $cutOffTime = DateTime::createFromFormat('H:i:s', '09:00:00');
    $attendanceStatusId = ($punchInTime <= $cutOffTime) ? 1 : 2;
} else if ($punchType == 'Punch Out') {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM attendance 
        WHERE id = ? AND AttendanceDate = ? AND PunchOutTime IS NOT NULL
    ");
    $stmt->bind_param("ss", $userId, $date);
    $stmt->execute();
    $stmt->bind_result($punchOutCount);
    $stmt->fetch();
    $stmt->close();

    if ($punchOutCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Punch Out already recorded for today']);
        exit;
    }

    // Determine AttendanceStatus_ID based on the time of Punch Out
    $punchOutTime = DateTime::createFromFormat('H:i:s', $time);
    $cutOffTime = DateTime::createFromFormat('H:i:s', '18:00:00');

    if ($punchOutTime < $cutOffTime) {
        $attendanceStatusId = 3;
    } else if ($punchOutTime == $cutOffTime) {
        $attendanceStatusId = 1;
    } else {
        $attendanceStatusId = 2;
    }
}

// Get the current maximum NW_Attendance_ID
$stmt = $conn->prepare("SELECT MAX(NW_Attendance_ID) FROM attendance");
$stmt->execute();
$stmt->bind_result($maxId);
$stmt->fetch();
$stmt->close();

// Generate the new NW_Attendance_ID
$newIdNumber = 1; // Default value if there are no existing records
if ($maxId) {
    // Extract the numeric part and increment it
    $currentNumber = intval(substr($maxId, 2));
    $newIdNumber = $currentNumber + 1;
}
$newId = 'AT' . str_pad($newIdNumber, 7, '0', STR_PAD_LEFT);

// Prepare SQL query based on Punch Type
if ($punchType == 'Punch In') {
    $stmt = $conn->prepare("
        INSERT INTO attendance (NW_Attendance_ID, id, PunchInTime, PunchOutTime, AttendanceDate, ShiftSession_ID, AttendanceStatus_ID) 
        VALUES (?, ?, ?, NULL, ?, ?, ?)
    ");
    $shiftSessionId = 1; // Example ShiftSession_ID, replace with your logic
    $stmt->bind_param("sissii", $newId, $userId, $time, $date, $shiftSessionId, $attendanceStatusId);
} else if ($punchType == 'Punch Out') {
    $stmt = $conn->prepare("
        INSERT INTO attendance (NW_Attendance_ID, id, PunchInTime, PunchOutTime, AttendanceDate, ShiftSession_ID, AttendanceStatus_ID) 
        VALUES (?, ?, NULL, ?, ?, ?, ?)
    ");
    $shiftSessionId = 2; // Example ShiftSession_ID, replace with your logic
    $stmt->bind_param("sissii", $newId, $userId, $time, $date, $shiftSessionId, $attendanceStatusId);
}

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
