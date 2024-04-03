<?php
include("connect.php");

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from the request
$data = json_decode(file_get_contents('php://input'), true);
$recipientId = $data['recipientId'];
$message = $data['message'];

//  SQL statement to insert data into the notifications table
$stmt = $conn->prepare("INSERT INTO notification_table (recipient_id, message, notification_time) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $recipientId, $message);

// Execute the statement
if ($stmt->execute() === TRUE) {
    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false));
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
