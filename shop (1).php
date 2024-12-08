<?php
// Start session to check if the user is logged in
session_start();

// Database Configuration
$host = "localhost"; // Database host
$username = "root";  // Database username
$password = "";      // Database password
$database = "webforgedb"; // Database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: register.php");
    exit();
}

// PayPal API credentials
$client_id = "AYgvUfT1GEgxnwxO0VC8RU4xkAzHaC1IiDLs_Xv2QC79_eaVlPUGoH70ELkaumrqiHYIfiFjaiXVSG6P";
$secret = "EJ4QF9rZo98fWyubkY5PvooktX3N-6a1k1C33sSQ2GhMneNztuH2o1Ej9d2ShdxewEBulOKJ7-6OJzLt";

// Initialize message
$message = "";

// Handle PayPal Payment Process
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $amount = $product['price']; // Product price
        $description = $product['description'];

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
                        "total" => $amount,
                        "currency" => "USD"
                    ],
                    "description" => $description
                ]
            ],
            "redirect_urls" => [
                "return_url" => "http://localhost/sia/register.php?paymentId=$payment_id&product_id=$product_id",
                "cancel_url" => "http://localhost/sia/shop.php"
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

        // Step 3: Redirect user to PayPal for approval
        if (isset($response_data->links)) {
            foreach ($response_data->links as $link) {
                if ($link->rel == "approval_url") {
                    header("Location: " . $link->href);
                    exit;
                }
            }
        } else {
            $message = "Error: No approval URL found in the PayPal response.";
        }
    } else {
        $message = "Product not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - WebForge</title>
    <link rel="stylesheet" href="style.css">
</head> 
<body>
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
          <li><a href="register.php">Logout</a></li>
          <li><a href="register.php" onclick="return confirmLogout()">Logout</a></li>
        </ul>
      </nav>
    </header>

    <header class="wrapper_white">
        <div class="container">
            <h1 class="Title">Shop Our Templates</h1>
            <p style="text-align: center;">Explore our range of professionally designed website templates.</p>
        </div>
    </header>

    
   <a href="try.php" class="btn btn-1 btn-1a mb-3" style="
    background-color: #007bff; /* Set background color */
    color: white;              /* Set text color */
    border: none;              /* Remove border */
    padding: 12px 25px;        /* Padding for the button */
    font-size: 18px;           /* Font size */
    font-weight: bold;         /* Make the text bold */
    text-align: center;        /* Center the text */
    cursor: pointer;          /* Change cursor to pointer */
    border-radius: 50px;       /* Rounded corners */
    text-decoration: none;     /* Remove underline */
    transition: all 0.3s ease-in-out; /* Smooth transition for hover effect */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow effect */
    display: inline-block;     /* Make the button inline */
    margin-right: 10px;        /* Space between buttons */
">
    Sell a website
</a>




    <section id="shop" class="wrapper_lightblue">
        <div class="container">
            <div class="columns">
                <?php
                // Query to fetch all products from the database
                $sql = "SELECT * FROM products";
                $result = $conn->query($sql);

                // Check if products exist
                if ($result->num_rows > 0) {
                    // Output each product
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="column">';
                        
                        // Check if image exists for the product
                        if (!empty($row['image'])) {
                            echo '<img src="' . $row['image'] . '" alt="Product Image" style="width: 100%; max-width: 200px; height: auto;">';
                        } else {
                            echo '<p>No image available</p>';
                        }

                        echo '<h3>' . $row['title'] . '</h3>';
                        echo '<p>' . $row['description'] . '</p>';
                        echo '<p><strong>$' . number_format($row['price'], 2) . '</strong></p>';
                        echo '<button class="btn btn-1 btn-1a" onclick="window.location.href=\'shop.php?product_id=' . $row['id'] . '\'">Buy Now</button>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No products available at the moment.</p>";
                }

                // Close the database connection
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; 2024 Webforge. All Rights Reserved.</p>
    </footer>
    <script src="mobile-navbar.js"></script>
</body>
</html>
