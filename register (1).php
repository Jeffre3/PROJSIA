<?php
session_start();
include 'db.php'; 


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === "signup") {
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password']; 

        
        $checkQuery = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email or Username already exists.";
        } else {
            $insertQuery = "INSERT INTO users (email, username, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sss", $email, $username, $password);

            if ($stmt->execute()) {
                // Prepare email
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'webforgesia@gmail.com';
                    $mail->Password = 'tzoo rigb joav hciq'; 
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('webforgesia@gmail.com', 'WebForge');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Account Created';
                    $mail->Body = "Thank you for creating an account with WebForge.<br><br>
                                   Here are the details of your Account:<br><br>
                                   <b>Email:</b> $email<br>
                                   <b>Username:</b> $username<br>
                                   <b>Password:</b> $password<br><br>
                                   Best regards,<br>Web Forge.";

                    $mail->send();
                    echo '<script>alert("Email sent successfully.");</script>';
                } catch (Exception $e) {
                    echo '<script>alert("Email sending failed. Error: ' . $mail->ErrorInfo . '");</script>';
                }

                $_SESSION['notification'] = 'Account created successfully';
                header("Location: register.php");
                exit();
            } else {
                $message = "Error: " . $conn->error;
            }
        }

        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] === "login") {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Webforge - Register & Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="register.css" />
</head>
<body style="display: flex; justify-content:center; align-items: center; height: 100vh; overflow: hidden;">
    <div class="form-wrap">
        <div class="tabs">
            <h3 class="signup-tab"><a class="active" href="#signup-tab-content" style="border-top-left-radius: 7px;">Sign Up</a></h3>
            <h3 class="login-tab"><a href="#login-tab-content" style="border-top-right-radius: 7px;">Login</a></h3>
        </div>
        <div class="tabs-content">
            <!-- Display Message -->
            <?php if (!empty($message)) { echo "<p style='color:red;'>$message</p>"; } ?>

            <div id="signup-tab-content" class="active">
                <form class="signup-form" action="" method="post">
                    <input type="hidden" name="action" value="signup">
                    <input type="email" class="input" name="email" autocomplete="off" placeholder="Email" required>
                    <input type="text" class="input" name="username" autocomplete="off" placeholder="Username" required>
                    <input type="password" class="input" name="password" autocomplete="off" placeholder="Password" required>
                    <input type="submit" class="button" value="Sign Up">
                </form>
            </div>

            <div id="login-tab-content">
                <form class="login-form" action="" method="post">
                    <input type="hidden" name="action" value="login">
                    <input type="text" class="input" name="login" autocomplete="off" placeholder="Email or Username" required>
                    <input type="password" class="input" name="password" autocomplete="off" placeholder="Password" required>
                    <input type="checkbox" class="checkbox" id="remember_me">
                    <label for="remember_me">Remember me</label>
                    <input type="submit" class="button" value="Login">
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');

        const tab = $('.tabs h3 a');
        if (tabParam === "login") {
            tab.removeClass('active');
            $('a[href="#login-tab-content"]').addClass('active');
            $('div[id$="tab-content"]').removeClass('active');
            $('#login-tab-content').addClass('active');
        }

        tab.on('click', function(event) {
            event.preventDefault();
            tab.removeClass('active');
            $(this).addClass('active');
            const tabContent = $(this).attr('href');
            $('div[id$="tab-content"]').removeClass('active');
            $(tabContent).addClass('active');
        });
    });
    </script>
</body>
</html>