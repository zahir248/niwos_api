<?php

include('db_connect.php');

// Retrieve form data
$problemDescription = $_POST['problemDescription'];
$featureArea = $_POST['featureArea'];
$severity = $_POST['severity'];
$problemDetails = $_POST['problemDetails'];
$impact = $_POST['impact'];
$frequency = $_POST['frequency'];
$additionalComments = $_POST['additionalComments'];
$username = $_POST['username']; // Retrieve the username from the form data

// Get Malaysia's current date and time
date_default_timezone_set('Asia/Kuala_Lumpur');
$creationTimeDate = date('Y-m-d H:i:s');

// Define mappings for Frequency_ID, Severity_ID, and Feature_ID
$frequencyMappings = ['Rarely' => 1, 'Occasionally' => 2, 'Frequently' => 3];
$severityMappings = ['Low' => 1, 'Medium' => 2, 'High' => 3];
$featureMappings = ['User management' => 1, 'Attendance monitoring' => 2, 'Access control management' => 3, 'Payment monitoring' => 4];

// Get the corresponding IDs
$frequencyID = $frequencyMappings[$frequency];
$severityID = $severityMappings[$severity];
$featureID = $featureMappings[$featureArea];

// Query to fetch the user ID based on the username
$userQuery = "SELECT id FROM users WHERE username = '$username'";
$userResult = $conn->query($userQuery);

if ($userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $userID = $userRow['id'];

    // Insert data into database including the user ID
    $sql = "INSERT INTO feedback (CreationTimeDate, Description, Detail, Suggestion, Impact, Frequency_ID, Severity_ID, Feature_ID, id)
            VALUES ('$creationTimeDate', '$problemDescription', '$problemDetails', '$additionalComments', '$impact', '$frequencyID', '$severityID', '$featureID', '$userID')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "Error: User not found";
}

// Close connection
$conn->close();

?>
