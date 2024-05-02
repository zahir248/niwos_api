<?php
// Include db_connect.php file
include "db_connect.php";

// Retrieve username and amount from POST request
$username = $_POST['username'];
$amount = $_POST['amount'];

// Get User_ID from user table based on UserName
$userQuery = "SELECT User_ID FROM user WHERE UserName = '$username'";
$userResult = $conn->query($userQuery);

if ($userResult->num_rows > 0) {
  $row = $userResult->fetch_assoc();
  $userID = $row['User_ID'];

  // Generate NW_Payment_ID
  $paymentID = generatePaymentID($conn);

  // Insert payment details into payment table
  $insertPaymentQuery = "INSERT INTO payment (NW_Payment_ID, Amount, PaymentTimeDate, Currency, Method_ID, Location_ID, User_ID) VALUES ('$paymentID', $amount, NOW(), 'MYR', 1, NULL, $userID)";
  
  if ($conn->query($insertPaymentQuery) === TRUE) {
    // Update wallet balance
    $updateQuery = "UPDATE wallet SET Balance = Balance + $amount, LastTransactionTimeDate = NOW(), User_ID = $userID";
  
    if ($conn->query($updateQuery) === TRUE) {
      // Transaction successful
      echo "Transaction successful";
    } else {
      // Transaction failed
      echo "Error updating wallet balance: " . $conn->error;
    }
  } else {
    // Payment insertion failed
    echo "Error inserting payment details: " . $conn->error;
  }
} else {
  // User not found
  echo "User not found";
}

// Close connection
$conn->close();

// Function to generate NW_Payment_ID
function generatePaymentID($conn) {
    // Retrieve the current maximum RW_Payment_ID
    $maxQuery = "SELECT MAX(NW_Payment_ID) AS maxID FROM payment WHERE NW_Payment_ID LIKE 'RW%'";
    $maxResult = $conn->query($maxQuery);
    
    if ($maxResult->num_rows > 0) {
      $row = $maxResult->fetch_assoc();
      $maxID = $row['maxID'];
      
      // Check if maxID is null (no previous RW entries)
      if ($maxID === null) {
        return 'RW0000001'; // Start from RW0000001
      } else {
        // Extract the numeric part of the maximum payment ID
        $numericPart = (int)substr($maxID, 2);
        
        // Increment the numeric part
        $newNumericPart = $numericPart + 1;
        
        // Format the new payment ID
        $newPaymentID = 'RW' . str_pad($newNumericPart, 7, '0', STR_PAD_LEFT);
        
        return $newPaymentID;
      }
    } else {
      // If no previous RW payment IDs found, start from RW0000001
      return 'RW0000001';
    }
  }  
?>