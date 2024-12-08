<?php
include 'db.php'; 

// Start the session
session_start();

if (isset($_POST['add'])) {
    // Get the form data
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    
    // Insert into the database
    $sql = "INSERT INTO products (title, price, description) VALUES ('$title', '$price', '$description')";
    if ($conn->query($sql) === TRUE) {
        echo "New product added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
 else {
    $_SESSION['error'] = 'Please fill out the form first';
}

// Redirect to the page (replace .php with the correct page name, e.g., index.php)
header('Location: try.php');
exit();
?>
