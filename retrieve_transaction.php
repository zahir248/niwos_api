<?php
// Include your database connection file
include 'db_connect.php';

// Retrieve username from request parameters
$username = $_GET['username']; // Assuming it's sent as a GET parameter

// Query to fetch User_ID based on username
$userQuery = "SELECT id FROM users WHERE UserName = '$username'";
$userResult = $conn->query($userQuery);

// Check if username exists and get the User_ID
if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $userID = $userData['id'];

        // Query to fetch transaction history data based on User_ID and join with related tables
        $query = "SELECT 
        p.Amount, 
        p.PaymentTimeDate, 
        p.Currency, 
        m.MethodType, 
        COALESCE(l.LocationName, 'None') AS LocationName
    FROM 
        payment AS p
        JOIN method AS m ON p.Method_ID = m.Method_ID
        LEFT JOIN location AS l ON p.Location_ID = l.Location_ID
    WHERE 
        p.id = '$userID'";
    $result = $conn->query($query);

    // Check if there are any results
    if ($result->num_rows > 0) {
        // Fetch data from each row and store it in an array
        $transactionData = array();
        while ($row = $result->fetch_assoc()) {
            $transactionData[] = $row;
        }

        // Send the data as JSON response
        header('Content-Type: application/json');
        echo json_encode($transactionData);
    } else {
        // No transaction history data found for the user
        echo "No transaction history data found for the user";
    }
} else {
    // Username not found
    echo "Username not found";
}

// Close the database connection
$conn->close();
?>