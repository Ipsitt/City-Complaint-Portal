<?php
session_start();
if (!isset($_GET['email']) || !filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    die("Invalid or missing email.");
}
$email = $_GET['email'];

// Generate and save OTP to DB, and send email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust path if needed

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$otp = rand(100000, 999999);

$stmt = $conn->prepare("REPLACE INTO otp (email, otp) VALUES (?, ?)");
$stmt->bind_param("si", $email, $otp);
$stmt->execute();
$stmt->close();

$mail = new PHPMailer(true);
$mail_sent = false;
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'complain.portal.paradox@gmail.com';
    $mail->Password   = 'dzzqjdrsqklgxdqp'; // app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('complain.portal.paradox@gmail.com', 'City Complaint Portal');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';
    $mail->Body = '
        <div style="
            font-family: Arial, sans-serif; 
            text-align: center; 
            background-color: #f0f0f0; 
            padding: 30px; 
            border-radius: 10px;
            color: #333;
        ">
            <h2 style="color: #007BFF;">Your OTP for City Complaint Portal</h2>
            <p style="font-size: 18px;">Please use the following One-Time Password to complete your registration:</p>
            <p style="
                font-size: 48px; 
                font-weight: bold; 
                margin: 30px 0; 
                color: #28a745;
                letter-spacing: 10px;
            ">
                ' . $otp . '
            </p>
            <p style="font-size: 14px; color: #555;">
                If you did not request this, please ignore this email.
            </p>
        </div>
    ';
    $mail->AltBody = "Your OTP for City Complaint Portal is: $otp";
    $mail->send();
    $mail_sent = true;
} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
    $mail_sent = false;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verify OTP</title>
  <style>
    body {
      background: #0a0a0a;
      color: #eee;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      text-align: center;
    }
    input[type="text"] {
      padding: 14px;
      font-size: 18px;
      border-radius: 8px;
      border: none;
      margin-bottom: 25px;
      width: 250px;
      text-align: center;
      background: #1c1c1c;
      color: #fff;
      outline: none;
      box-shadow: 0 0 8px #00ff88;
    }
    button {
      padding: 14px 40px;
      font-size: 18px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      color: white;
      background: #8b5cf6;
      box-shadow: 0 0 15px #8b5cf6cc, 0 0 30px #8b5cf6cc;
      font-weight: bold;
      transition: 0.3s ease;
    }
    button:hover {
      background: #7c3aed;
      box-shadow: 0 0 25px #a78bfaaa, 0 0 50px #a78bfaaa;
    }
    .message {
      margin-bottom: 30px;
      font-size: 18px;
    }
    .error {
      color: #ff4444;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <div class="message">
    <?php 
      if ($mail_sent) {
        echo "An OTP has been sent to <strong>" . htmlspecialchars($email) . "</strong>. Please enter it below.";
      } else {
        echo '<span class="error">Failed to send OTP email. Please try again later.</span>';
      }
    ?>
  </div>

  <form id="otpForm">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>" />
    <input type="text" name="otp" placeholder="Enter OTP" required minlength="6" maxlength="6" />
    <br />
    <button type="submit">Verify OTP</button>
  </form>

  <div id="msg" style="margin-top:20px;"></div>

  <script>
  document.getElementById('otpForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const response = await fetch('ajax_verify_register_otp.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    const msgDiv = document.getElementById('msg');
    if (result.success) {
      msgDiv.style.color = 'lightgreen';
      msgDiv.textContent = result.message + ', redirecting...';
      setTimeout(() => {
        window.location.href = 'home.php';
      }, 1500);
    } else {
      msgDiv.style.color = 'red';
      msgDiv.textContent = result.message;
    }
  });
  </script>

</body>
</html>
