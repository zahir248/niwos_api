<?php
header('Content-Type: application/json');

// Include the database connection file
include 'db_connect.php';

// Retrieve the POST data
$niwosId = $_POST['Niwos_ID'] ?? '';
$phoneNumber = $_POST['PhoneNumber'] ?? '';

// Prepare and execute the SQL query
$query = "SELECT UserName FROM users WHERE Niwos_ID = ? AND PhoneNumber = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $niwosId, $phoneNumber);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    // Fetch the username
    $row = $result->fetch_assoc();
    $username = $row['UserName']; // Use 'UserName' instead of 'username'

    // Prepare the response
    $response['success'] = true;
    $response['username'] = $username;
} else {
    // User not found
    $response['success'] = false;
    $response['error'] = 'Invalid credentials';
}

// Close the database connection
$stmt->close();
$conn->close();

// Send the response
echo json_encode($response);
?>
