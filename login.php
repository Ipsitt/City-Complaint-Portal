<?php
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // DB connection
    $conn = new mysqli("localhost", "root", "", "complain_portal");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE user_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: otp.php?email=" . urlencode($email));
        exit;
    } else {
        $error = "Email not registered. Please register first.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Complaint Portal</title>
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: #000;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      text-align: center;
    }

    input[type="email"] {
      width: 90%;
      max-width: 400px;
      padding: 14px;
      margin-bottom: 25px;
      border: none;
      border-radius: 8px;
      background: #1c1c1c;
      color: #fff;
      font-size: 16px;
      outline: none;
      text-align: center;
    }

    .btn {
      width: 90%;
      max-width: 400px;
      padding: 14px;
      border: none;
      border-radius: 8px;
      margin-bottom: 25px;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: bold;
      color: white;
    }

    .login-btn {
      background: #00cc66;
      box-shadow: 0 0 15px #00cc66, 0 0 30px #00cc66;
    }

    .login-btn:hover {
      background: #00b359;
      box-shadow: 0 0 25px #00ff88, 0 0 50px #00ff88;
    }

    .register-btn {
      background: #7e3ff2;
      box-shadow: 0 0 15px #7e3ff2, 0 0 30px #7e3ff2;
    }

    .register-btn:hover {
      background: #6a2ed9;
      box-shadow: 0 0 25px #b07af7, 0 0 50px #b07af7;
    }

    .error {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <form method="POST">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit" class="btn login-btn">Login</button>
    <button type="button" class="btn register-btn" onclick="window.location.href='register.php'">Register</button>
  </form>

  <?php if (!empty($error)): ?>
    <p class="error"><?= $error ?></p>
  <?php endif; ?>
</body>
</html>
