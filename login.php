<?php
session_start();
require_once 'db_connection.php';
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT password FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION["username"] = $username;
            header("Location: home.php");
            exit();
        } else {
            $errorMsg = "Invalid username or password. Please try again.";
        }
    } else {
        $errorMsg = "User not found. Please register or try a different username.";
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Decryptoid</title>
  <link rel="stylesheet" href="CSS/style.css">
</head>
<body>    
  <div class="login-form">    
    <h1>Login to Decryptoid</h1>  
    <?php    
    if (!empty($errorMsg)) {
        echo '<p style="color: red;">' . $errorMsg . '</p>';
    }
    ?>
    <form action="login.php" method="POST">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" placeholder="Enter your username" required> 
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required> 
      <button type="submit">Login</button>
    </form>
    <div class="signup-link">
      <br>
      <span>Don't have an account? <a href="signup.php">Signup</a></span>
    </div>
  </div>
</body>
</html>
