<?php
// Replace with your actual database credentials
$host = 'localhost'; 
$username = 'root';  
$password = '';      
$database = 'webforgedb'; // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>