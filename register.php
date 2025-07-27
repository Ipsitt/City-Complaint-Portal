<?php
session_start();
$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if (!$email || !$name || !$contact) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_email FROM user WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Email already registered. Please login or use a different email.";
        } else {
            // Save form data in session to use on OTP page
            $_SESSION['reg_email'] = $email;
            $_SESSION['reg_name'] = $name;
            $_SESSION['reg_contact'] = $contact;
            $stmt->close();
            $conn->close();
            // Redirect to otp_register.php with email query param
            header("Location: otp_register.php?email=" . urlencode($email));
            exit;
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
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register - City Complaint Portal</title>
<style>
  body {
    background-color: #0a0a0a;
    color: #e0e0e0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
  }
  .register-container {
    background: #111;
    padding: 2rem 3rem;
    border-radius: 10px;
    box-shadow: 0 0 20px #8b5cf6cc;
    width: 100%;
    max-width: 400px;
  }
  h2 {
    color: #a78bfa;
    margin-bottom: 1.5rem;
    text-align: center;
  }
  label {
    display: block;
    margin-top: 1rem;
    margin-bottom: 0.3rem;
    font-weight: 600;
  }
  input[type="text"], input[type="email"], input[type="tel"] {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    border: none;
    background: #1c1c1c;
    color: #eee;
    font-size: 1rem;
  }
  input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus {
    outline: none;
    box-shadow: 0 0 8px #a78bfa;
  }
  .btn {
    margin-top: 2rem;
    width: 100%;
    padding: 14px 0;
    font-size: 1.1rem;
    background: #8b5cf6;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    color: #0a0a0a;
    cursor: pointer;
    box-shadow: 0 0 15px #8b5cf688;
    transition: box-shadow 0.3s ease, background 0.3s ease;
  }
  .btn:hover {
    background: #7c3aed;
    box-shadow: 0 0 30px #7c3aedcc;
  }
  .message {
    margin-top: 1rem;
    color: #ff6666;
    font-weight: 600;
    text-align: center;
  }
</style>
</head>
<body>
  <div class="register-container">
    <h2>Create an Account</h2>
    <form method="POST" action="register.php" novalidate>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
      
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
      
      <label for="contact">Contact Number</label>
      <input type="tel" id="contact" name="contact" required pattern="[0-9+ -]{7,15}" value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>" />
      
      <button type="submit" class="btn">Register</button>
    </form>
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
