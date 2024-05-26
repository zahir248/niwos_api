<?php
include 'db_connect.php'; 

$response = array();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON body of the request
    $data = json_decode(file_get_contents('php://input'), true);

    // Extract the values from the JSON body
    $username = $data['username'];
    $oldPassword = $data['old_password'];
    $newPassword = $data['new_password'];

    // Validate the inputs
    if (empty($username) || empty($oldPassword) || empty($newPassword)) {
        $response['status'] = 'error';
        $response['message'] = 'All fields are required';
    } else {
        // Prepare SQL statement to fetch the user's current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // Check if the user exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            // Verify the old password
            if (password_verify($oldPassword, $hashedPassword)) {
                // Hash the new password
                $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // Update the user's password
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $updateStmt->bind_param("ss", $newHashedPassword, $username);

                if ($updateStmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Password changed successfully';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Failed to update password';
                }

                $updateStmt->close();
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Old password is incorrect';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'User not found';
        }

        $stmt->close();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
