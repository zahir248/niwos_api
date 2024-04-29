<?php
// Include the db_connect.php file
include 'db_connect.php';

// Retrieve username from POST request
$username = $_POST['username'];

// Prepare SQL query to retrieve User_ID from user table based on UserName
$sql_user = "SELECT User_ID FROM user WHERE UserName = '$username'";

// Execute SQL query
$result_user = $conn->query($sql_user);

// Check if any rows were returned
if ($result_user->num_rows > 0) {
  // Fetch User_ID from the result
  $row_user = $result_user->fetch_assoc();
  $user_id = $row_user['User_ID'];

  // Prepare SQL query to retrieve Balance and LastTransactionTimeDate from wallet table based on User_ID
  $sql_wallet = "SELECT Balance, LastTransactionTimeDate FROM wallet WHERE User_ID = '$user_id'";

  // Execute SQL query
  $result_wallet = $conn->query($sql_wallet);

  // Check if any rows were returned
  if ($result_wallet->num_rows > 0) {
    // Output data of the first row
    $row_wallet = $result_wallet->fetch_assoc();
    $balance = $row_wallet['Balance'];
    $lastTransaction = $row_wallet['LastTransactionTimeDate'];

    // Return wallet information as JSON
    echo json_encode(array('balance' => $balance, 'lastTransaction' => $lastTransaction));
  } else {
    // If no rows were returned from wallet table, return an error message
    echo "No wallet information found for username: $username";
  }
} else {
  // If no rows were returned from user table, return an error message
  echo "User not found with username: $username";
}

// Close database connection
$conn->close();
?>