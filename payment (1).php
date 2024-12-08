<?php
// PayPal API credentials
$client_id = "AYgvUfT1GEgxnwxO0VC8RU4xkAzHaC1IiDLs_Xv2QC79_eaVlPUGoH70ELkaumrqiHYIfiFjaiXVSG6P"; // Replace with your own credentials
$secret = "EJ4QF9rZo98fWyubkY5PvooktX3N-6a1k1C33sSQ2GhMneNztuH2o1Ej9d2ShdxewEBulOKJ7-6OJzLt"; // Replace with your own credentials

// Initialize message
$message = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount']; // Amount from the form
    
    // Step 1: Get Access Token from PayPal
    $api_url = "https://api.sandbox.paypal.com/v1/oauth2/token";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic " . base64_encode("$client_id:$secret"),
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        exit;
    }
    curl_close($ch);
    
    $response_data = json_decode($response);
    $access_token = $response_data->access_token;

    // Step 2: Create PayPal Payment Request
    $payment_url = "https://api.sandbox.paypal.com/v1/payments/payment";
    
    // Set payment details
    $payment_data = [
        "intent" => "sale",
        "payer" => [
            "payment_method" => "paypal"
        ],
        "transactions" => [
            [
                "amount" => [
                    "total" => $amount, // The amount to charge
                    "currency" => "USD"
                ],
                "description" => "Test payment"
            ]
        ],
        "redirect_urls" => [
            "return_url" => "http://localhost/PROJSASIA/success.php", // Replace with your success URL
            "cancel_url" => "http://localhost/PROJSASIA/shop.php" // Replace with your cancel URL
        ]
    ];

    $payment_json = json_encode($payment_data);
    
    // Make API request to create payment
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $payment_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payment_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $access_token"
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        exit;
    }
    curl_close($ch);
    
    $response_data = json_decode($response);

    // Debugging: Check the response data
    echo "<pre>";
    print_r($response_data);
    echo "</pre>";

    // Step 3: Redirect user to PayPal for approval
    if (isset($response_data->links)) {
        $approval_url = "";
        foreach ($response_data->links as $link) {
            if ($link->rel == "approval_url") {
                $approval_url = $link->href;
                break;
            }
        }
        if ($approval_url) {
            header("Location: $approval_url");
            exit;
        } else {
            $message = "Error: No approval URL found in the PayPal response.";
        }
    } else {
        $message = "Error creating PayPal payment.";
    }
}

// Handle Success or Cancel responses from PayPal
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success' && isset($_GET['paymentId']) && isset($_GET['PayerID'])) {
        // Capture the payment
        $payment_id = $_GET['paymentId'];
        $payer_id = $_GET['PayerID'];

        $api_url = "https://api.sandbox.paypal.com/v1/payments/payment/$payment_id/execute";
        $payment_data = [
            "payer_id" => $payer_id
        ];

        $payment_json = json_encode($payment_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payment_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $access_token"
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            exit;
        }
        curl_close($ch);

        $response_data = json_decode($response);

        // Check if payment was successful
        if ($response_data->state == "approved") {
            $message = "Payment Successful!";
        } else {
            $message = "Payment Failed.";
        }
    } elseif ($_GET['status'] == 'cancel') {
        $message = "Payment was canceled.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <style>
     /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Payment Container */
.payment-container {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 300px;
}

/* Heading */
.payment-container h1 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

/* Message */
.message {
    color: #e74c3c;
    font-size: 16px;
    margin-bottom: 20px;
}

/* Payment Form */
.payment-form label {
    display: block;
    font-size: 14px;
    color: #333;
    margin-bottom: 10px;
}

.payment-form input[type="number"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.payment-form .pay-button {
    background-color: #0070ba;
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
}

.payment-form .pay-button:hover {
    background-color: #005f8d;
}

    </style>
<header>
      <nav>
        <a class="logo" href="index.php">WebForge</a>
        <div class="mobile-menu">
          <div class="line1"></div>
          <div class="line2"></div>
          <div class="line3"></div>
        </div>
        <ul class="nav-list">
          <li><a href="index.php">Home</a></li>
          <li><a href="shop.php">Shop</a></li>
          <li><a href="register.php">Login</a></li>
          <li><a href="index.php">Logout</a></li>
        </ul>
      </nav>
    </header>
    <div class="payment-container">
        <h1>Pay with PayPal</h1>
        
        <!-- Display message -->
        <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>

        <!-- Payment Form -->
        <form action="payment.php" method="POST" class="payment-form">
            <label for="amount">Amount to Pay:</label>
            <input type="number" name="amount" id="amount" min="1" value="10.00" required>
            <br><br>
            <button type="submit" class="pay-button">Pay Now</button>
            
        </form>
    </div>
</body>
</html>