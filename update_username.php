<?php
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentUsername = $_POST['current_username'];
    $newUsername = $_POST['new_username'];

    if (empty($currentUsername) || empty($newUsername)) {
        echo json_encode(['status' => 'error', 'message' => 'Username fields cannot be empty']);
        exit();
    }

    // Check if the new username already exists
    $checkQuery = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $checkQuery->bind_param("s", $newUsername);
    $checkQuery->execute();
    $checkQuery->store_result();

    if ($checkQuery->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
        $checkQuery->close();
        $conn->close();
        exit();
    }
    $checkQuery->close();

    // Update username query
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE username = ?");
    $stmt->bind_param("ss", $newUsername, $currentUsername);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Username updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update username']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
