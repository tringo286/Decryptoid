<?php
session_start();
require_once 'db_connection.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$errorMsg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errorMsg = "Username can only contain English letters (capitalized or not), digits, '_', and '-' characters.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format. Please enter a valid email address.";
    } else if (strlen($password) < 4) {
        $errorMsg = "Password must be at least 4 characters long.";
    } else {
        $check_query = "SELECT * FROM users WHERE username='$username'";
        $check_result = $conn->query($check_query);
        $checkEmailQuery = "SELECT * FROM users WHERE email='$email'";
        $checkEmailResult = $conn->query($checkEmailQuery);
        if ($check_result->num_rows > 0) {
            $errorMsg = "Username already exists. Please choose a different username.";
        } else if ($checkEmailResult->num_rows > 0) {
            $errorMsg = "Email has already been used. Please use a different email address.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
            if ($conn->query($sql) === TRUE) {
                header("Location: login.php");
                exit();
            } else {
                $errorMsg = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup - Decryptoid</title>
  <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
  <div class="login-form">
    <h1>Signup to Decryptoid</h1>
    <?php    
    if (!empty($errorMsg)) {
        echo '<p style="color: red;">' . $errorMsg . '</p>';
    }
    ?>
    <form action="signup.php" method="POST">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" placeholder="Enter your username" required> 
      <label for="email">Email</label> 
      <input type="email" id="email" name="email" placeholder="Enter your email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Enter a valid email address" required> 
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required> 
      <button type="submit">Signup</button>
    </form>
    <div class="signup-link">
      <br>
      <span>Already have an account? <a href="login.php">Login</a></span>
    </div>
  </div>
</body>
</html>
    

