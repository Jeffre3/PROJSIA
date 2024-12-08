<?php
session_start();
// Database Configuration
$host = "localhost"; // Change this if your database is on another host
$username = "root";  // Replace with your database username
$password = "";      // Replace with your database password
$database = "webforgedb"; // Replace with your database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === "signup") {
        // Signup Logic
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email or username already exists
        $checkQuery = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email or Username already exists.";
        } else {
            // Insert into the database
            $insertQuery = "INSERT INTO users (email, username, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sss", $email, $username, $hashedPassword);

            if ($stmt->execute()) {
                // Redirect to the homepage after successful signup
                header("Location: /index.html");
                exit();
            } else {
                $message = "Error: " . $conn->error;
            }
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] === "login") {
        // Login Logic
        $login = $_POST['login']; // Username or Email
        $password = $_POST['password'];

        // Check if the user exists
        $query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Redirect to the homepage after successful login
                header("Location: /index.html");
                exit();
            } else {
                $message = "Invalid credentials.";
            }
        } else {
            $message = "User not found.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WebForge</title>
    <link rel="stylesheet" href="style.css" />
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
          <li><a href="register.php" onclick="return confirmLogout()">Logout</a></li>
        </ul>
      </nav>
    </header>
    <div class="wrapper_whiteshadow">         
      <section class="columns">
          <button class="btn btn-1 btn-1a" onclick="window.location.href='shop.php'">Shop Now</button>
          <button class="btn btn-1 btn-1a" onclick="window.location.href='#Benefits'">Benefits</button>
          <button class="btn btn-1 btn-1a" onclick="window.location.href='#FAQs'">FAQs</button>
          <button class="btn btn-1 btn-1a" onclick="window.location.href='#Contact'">Contact Us</button>
      </section>	
    </div>
    <div id="Shop">
    <div class="container">
      <div class="content">
          <div class="text-section">
    <h1>Welcome to WebForge, 
        <?php 
        // Check if the user is logged in before displaying the username
        if (isset($_SESSION['username'])) {
            echo $_SESSION['username']; 
        }
        ?>
    </h1>
    <p>
        Connecting Buyers and Sellers, Building Bridges Between Ideas and Opportunities.
    </p>
    <button class="login-button" onclick="window.location.href='shop.php'">Shop Now</button>
</div>

          <div class="image-section">
              <img src="https://web-forge.co.uk/wp-content/uploads/2023/08/cropped-cropped-Logo-Final-File-02-2.png"/>
              <div class="decorative-elements">
                  <div class="circle yellow"></div>
                  <div class="circle blue"></div>
              </div>
          </div>
      </div>
    </div>

<div class="wrapper_blue">
  
      <h1 class="Title_dont">Don't have an account yet?</h1>
  

<section class="columns">
  <div class="column">
    <button class="btn btn-1 btn-1a" onclick="window.location.href='register.php'">Sign Up Now</button>
    <div id="Benefits">
  </div>
</section>	



</div>

<div class="wrapper_lightblue">

      <h1 class="Title" >Benefits</h1>
  

<section class="columns">
  <div class="column">
      
      <img src="https://cdn-icons-png.flaticon.com/512/9307/9307321.png">
      <h2>Easy Platform for Buying and Selling Websites</h2>
      <p>Webforge makes it simple for both sellers and buyers to connect. 
        Sellers can list their websites, while buyers can browse a curated marketplace for a website that fits their needs.</p>
  </div>
  
  <div class="column">
      
      <img src="https://cdn-icons-png.flaticon.com/256/1584/1584849.png">
      <h2>Time-Saving and Convenient</h2>
      <p>For buyers, finding a website to purchase can be time-consuming. 
        Webforge streamlines the process with a wide range of pre-verified websites, 
        cutting down the time it would take to build one from scratch.</p>
  </div>

<div class="column">
      
      <img src="https://cdn-icons-png.flaticon.com/512/5485/5485853.png">
      <h2>Wide Range of Choices</h2>
      <p>Whether you're looking for an e-commerce site, a blog, or a portfolio website, 
        Webforge offers a variety of website types for you to choose from, catering to diverse business needs.</p>
  </div>
</section>	
</div>


<div id="FAQs">
<div class="wrapper_faq">
  <h1 class="Title_faqs">WebForge FAQs</h1>
  <ul class="list__faq">
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />What is Webforge?</dt>
      <dd>Webforge is a platform that acts as a marketplace where individuals and businesses can buy and sell websites. 
        We connect website buyers with sellers, making it easy to list, browse, and purchase ready-made websites.</dd>
    </li>
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />How do I buy a website on Webforge?</dt>
      <dd>
        To buy a website on Webforge, simply browse through our marketplace, select the website that best suits your needs, 
        and proceed with the purchase through a secure transaction process. We’ll guide you through each step to ensure a smooth experience.
      </dd>
    </li>
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />How do I sell my website on Webforge?</dt>
      <dd>If you want to sell your website on Webforge, you need to sign up as a seller, list your website with a detailed description, 
        and set your price. Webforge will help market your website and connect you with interested buyers.</dd>
    </li>
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />Can I buy a website without technical knowledge?</dt>
      <dd>
        Absolutely! Whether you have technical knowledge or not, Webforge provides a range of websites that are easy to manage, 
        with many including documentation or customer support to assist you. We offer websites that are ready to use, 
        saving you time and effort.
      </dd>
    </li>
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />What kind of websites can I find on Webforge?</dt>
      <dd>
        Webforge offers a variety of websites, including e-commerce stores, blogs, business websites, portfolios, and more. 
        Whether you’re a small business, an entrepreneur, or someone looking to enter the digital space, 
        you can find websites that match your needs.
      </dd>
    </li>
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />How is the price of a website determined?</dt>
      <dd>Website pricing depends on various factors, including the website's design, functionality, content, traffic, and revenue potential. 
        As a seller, you have the flexibility to set your price, but we also offer tools and resources to help you price your website fairly.</dd>
    </li>
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />What happens after I purchase a website?</dt>
      <dd>Once you purchase a website, Webforge ensures a seamless transfer of ownership, 
        providing all the necessary files and access to manage your website. 
        You’ll also receive support to help you get started and take full control of your new site.</dd>
    </li>
    <li>
      <dt><input type="radio" name="rad-selected" /><input type="radio" name="rad-selected" />Can I modify or redesign a website after purchasing it?</dt>
      <dd>MYes! After purchasing a website, it’s yours to modify and redesign as you wish. 
        Whether you want to update the design, add new content, or change the functionality, 
        you have full control to personalize your website.</dd>
    </li>
  </ul>
</div>
<div id="Contact">
<div class="contact-us">
    <h1 class="Title_contact" >Contact Us</h1>
    <div class="contact-container">
      <!-- Corporate Center -->
      <div class="contact-card">
        <div class="icon">
          <img src="https://cdn-icons-png.flaticon.com/512/3466/3466344.png" alt="Corporate Icon" />
        </div>
        <h3>WebForge Corporate Building:</h3>
        <p>Technological Institute of the Philippines - Arlegui Campus, 1338 Arlegui St, Quiapo, Manila, 1001 Metro Manila</p>
      </div>

      <!-- Contact Center -->
      <div class="contact-card">
        <div class="icon">
          <img src="https://upload.wikimedia.org/wikipedia/commons/6/6c/Phone_icon.png" alt="Phone Icon" />
        </div>
        <h3>WebForge Contact Center:</h3>
        <p>Hotline: <a href="tel:+63288880000">(+632) 8888-0000</a></p>
        <p>Outside Metro Manila (PLDT/Globelines): 
          <a href="tel:#88880000">#8888-0000</a>
        </p>
      </div>

      <!-- Email -->
      <div class="contact-card">
        <div class="icon">
          <img src="https://e7.pngegg.com/pngimages/611/356/png-clipart-email-computer-icons-email-miscellaneous-blue-thumbnail.png" alt="Email Icon" />
        </div>
        <h3>Email:</h3>
        <p><a href="mailto:callcenter@bdo.com.ph">webforgesia@gmail.com</a></p>
        <p>This channel is dedicated to handling online banking enrollment and/or updating contact information for overseas clients.</p>
      </div>
    </div>
  </div>

  <footer class="footer">
      <p>&copy; 2024 WebForge. All rights reserved.</p>
  </footer>
  <script src="mobile-navbar.js"></script>
  <script>
    function confirmLogout() {
      return confirm("Are you sure you want to log out?");
    }
  </script>

  </body>
</html>
