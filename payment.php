<?php
include("connect.php");
// Function to insert payment details into the database
function insertPaymentDetails($transaction_id, $order_id, $payment_method, $amount, $meal_id, $customer_id, $meal_name, $price) {
    // Assuming you have a database connection available through $conn
    global $conn;

    // Insert transaction details into 'transactions' table
    $transaction_time = date('Y-m-d H:i:s'); // Assuming transaction time is current time
    $sql_transaction = "INSERT INTO transactions (transaction_id, order_id, payment_method, amount, transaction_time)
                        VALUES ('$transaction_id', '$order_id', '$payment_method', $amount, '$transaction_time')";
    $result_transaction = mysqli_query($conn, $sql_transaction);

    // Insert paid meal details into 'paid_meals' table
    $payment_date = date('Y-m-d'); // Assuming payment date is current date
    $sql_paid_meal = "INSERT INTO paid_meals (meal_id, customer_id, meal_name, price, payment_date)
                      VALUES ($meal_id, $customer_id, '$meal_name', $price, '$payment_date')";
    $result_paid_meal = mysqli_query($conn, $sql_paid_meal);

    // Check for successful insertion into both tables
    if ($result_transaction && $result_paid_meal) {
        return true; // Successfully inserted into both tables
    } else {
        return false; // Failed to insert into one or both tables
    }
}


// Handle M-Pesa payment
if (isset($_POST['mpesa_payment'])) {
    // Process M-Pesa payment 
    
    $consumer_key = 'MEbupSu6xSJHYfsABPIYBlTVidSGifr3O6gJpwI1fQY5DPWB';
    $consumer_secret = 'XBMQMeSdU9DU6rc5XpzADugdEVVLYZSslPp1BCK4c0zGygrStjBys4KdpUs7e4oR';
    
    // Create the authorization header
    $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
    $authorization_header = 'Authorization: Basic ' . $credentials;
    
    // Initialize cURL session
    $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$authorization_header]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
    
    // Execute the request
    $response = curl_exec($ch);
    
    // Handle the response (assuming it's in JSON format)
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];
        echo "Access Token: $access_token";
    } else {
        echo "Error fetching access token.";
    }
    
    // Close the cURL session
    curl_close($ch);


    echo 'M-Pesa payment processed successfully!';
    exit;

 // After processing payment successfully
 $transaction_id = uniqid(); // Generate a unique transaction ID
 $order_id = ''; // Add your order ID here
 $payment_method = 'Mpesa'; // Payment method
 $amount = 100.00; // Example amount, you should replace this with the actual amount
 $meal_id = ''; // Add your meal ID here
 $customer_id = ''; // Add your customer ID here
 $meal_name = ''; // Add your meal name here
 $price = ''; // Add your meal price here

 // Insert payment details into the database
 if (insertPaymentDetails($transaction_id, $order_id, $payment_method, $amount, $meal_id, $customer_id, $meal_name, $price)) {
     echo 'Mpesa payment processed successfully!';
 } else {
     echo 'Error processing PayPal payment. Please try again.';
 }

 exit;
}

// Handle PayPal payment
if (isset($_POST['paypal_payment'])) {
    // Process PayPal payment 
    
    // PayPal credentials
    $clientId = 'AQgGk67xmSY4hMBKy3_4jeYF-eXiOa4XIbpRXL62U7JqWhPl9aG0DYjeP9ki4C8hMNUqOlBdOpUXbVph';
    $clientSecret = 'EGAJJ5JXSl7kCKIgpKKxXC7PG59MqvhR6Letuvh62Zcspyf_gADOUBLTB0PqlLR2SVzFPfH6dqjeQW9a';
    
    // Create the authorization header
    $credentials = base64_encode($clientId . ':' . $clientSecret);
    $authorization_header = 'Authorization: Basic ' . $credentials;

    // Initialize cURL session
    $ch = curl_init('https://api.sandbox.paypal.com/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Accept-Language: en_US',
        'Authorization: Basic ' . $credentials
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Handle the response (assuming it's in JSON format)
    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        $accessToken = $data['access_token'];
        echo "Access Token: $accessToken\n";

        // Create an order (you'll need to define your order details)
        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '100.00', // Replace with your order amount
                    ],
                ],
            ],
        ];

        $ch = curl_init('https://api.sandbox.paypal.com/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);
        $order = json_decode($response, true);
        print_r($order); // Display the order details

        // Close the cURL session
        curl_close($ch);
    } else {
        echo "Error fetching access token.\n";
    }

    echo 'PayPal payment processed successfully!';
    exit;

 // After processing payment successfully
 $transaction_id = uniqid(); // Generate a unique transaction ID
 $order_id = ''; // Add your order ID here
 $payment_method = 'PayPal'; // Payment method
 $amount = 100.00; // Example amount, you should replace this with the actual amount
 $meal_id = ''; // Add your meal ID here
 $customer_id = ''; // Add your customer ID here
 $meal_name = ''; // Add your meal name here
 $price = ''; // Add your meal price here

 // Insert payment details into the database
 if (insertPaymentDetails($transaction_id, $order_id, $payment_method, $amount, $meal_id, $customer_id, $meal_name, $price)) {
     echo 'PayPal payment processed successfully!';
 } else {
     echo 'Error processing PayPal payment. Please try again.';
 }

 exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>
    <h1>Choose Payment Method</h1>
    <form method="post">
        <!-- M-Pesa payment button -->
        <button type="submit" name="mpesa_payment">Pay with M-Pesa</button>

        <!-- PayPal payment button -->
        <button type="submit" name="paypal_payment">Pay with PayPal</button>
    </form>
</body>
</html> 