<?php

include 'db_connect.php'; // Include the database connection file

// Set timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Get current datetime
$current_datetime = date('Y-m-d H:i:s');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the required fields are present in the POST data
    if (isset($_POST["username"]) && isset($_POST["location"]) && isset($_POST["amount"])) {
        // Retrieve the data from the POST request
        $username = $_POST["username"];
        $location = $_POST["location"];
        $amount = $_POST["amount"];

        // Get User_ID from the user table based on username
        $user_query = "SELECT User_ID FROM user WHERE Username = '$username'";
        $user_result = $conn->query($user_query);
        if ($user_result->num_rows > 0) {
            $user_row = $user_result->fetch_assoc();
            $user_id = $user_row["User_ID"];

            // Check the balance in the wallet table
            $balance_query = "SELECT Balance FROM wallet WHERE User_ID = '$user_id'";
            $balance_result = $conn->query($balance_query);
            if ($balance_result->num_rows > 0) {
                $balance_row = $balance_result->fetch_assoc();
                $balance = $balance_row["Balance"];

                // Proceed if the amount is less than or equal to the balance
                if ($amount <= $balance) {
                    // Set Currency to MYR
                    $currency = "MYR";

                    // Set Location_ID based on location value
                    $location_id = ($location == "Cafeteria") ? 1 : 2;

                    // Set Method_ID to 2
                    $method_id = 2;

                    // Decrement the balance by the payment amount
                    $new_balance = $balance - $amount;

                    // Update the balance and LastTransactionTimeDate in the wallet table
                    $update_balance_sql = "UPDATE wallet SET Balance = '$new_balance', LastTransactionTimeDate = '$current_datetime' WHERE User_ID = '$user_id'";
                    if ($conn->query($update_balance_sql) === TRUE) {
                        // Generate NW_Payment_ID for MP
                        $payment_query = "SELECT COUNT(*) AS count FROM payment WHERE NW_Payment_ID LIKE 'MP%'";
                        $payment_result = $conn->query($payment_query);
                        if ($payment_result->num_rows > 0) {
                            $payment_row = $payment_result->fetch_assoc();
                            $count = $payment_row["count"] + 1;
                            $nw_payment_id = "MP" . str_pad($count, 7, "0", STR_PAD_LEFT);
                            
                            // Prepare the SQL statement to insert the data into the payment table
                            $sql = "INSERT INTO payment (NW_Payment_ID, Amount, PaymentTimeDate, Currency, Method_ID, Location_ID, User_ID)
                            VALUES ('$nw_payment_id', '$amount', '$current_datetime', '$currency', '$method_id', '$location_id', '$user_id')";

                            // Execute the SQL statement
                            if ($conn->query($sql) === TRUE) {
                                echo "Data inserted successfully";
                            } else {
                                echo "Error: " . $sql . "<br>" . $conn->error;
                            }
                        } else {
                            echo "Error: Failed to generate NW_Payment_ID";
                        }
                    } else {
                        echo "Error: Failed to update balance";
                    }
                } else {
                    // Insufficient balance
                    echo "Error: Insufficient Balance. Please reload.";
                }
            } else {
                echo "Error: Wallet not found";
            }
        } else {
            echo "Error: User not found";
        }
    } else {
        // Required fields are missing
        echo "Error: Required fields are missing";
    }
} else {
    // Invalid request method
    echo "Error: Invalid request method";
}

// Close the database connection
$conn->close();

?>