<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$email = $_POST['email'] ?? '';
$entered_otp = $_POST['otp'] ?? '';

if (!$email || !$entered_otp) {
    echo json_encode(['success' => false, 'message' => 'Email and OTP required']);
    exit;
}

// Check OTP
$stmt = $conn->prepare("SELECT otp FROM otp WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'No OTP found for this email']);
    exit;
}

if ($entered_otp == $row['otp']) {
    // OTP correct, now check user type
    $stmt2 = $conn->prepare("SELECT type FROM user WHERE user_email = ?");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $user = $result2->fetch_assoc();
    $stmt2->close();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($user['type'] == 1) {
        // valid user type, proceed
        // Invalidate old OTP by generating new random OTP
        $new_otp = rand(100000, 999999);
        $update = $conn->prepare("UPDATE otp SET otp = ? WHERE email = ?");
        $update->bind_param("is", $new_otp, $email);
        $update->execute();
        $update->close();

        $_SESSION['user_email'] = $email;
        $_SESSION['user_type'] = $user['type'];

        echo json_encode(['success' => true, 'message' => 'OTP verified']);
    } else {
        // User type not allowed for home.php redirect
        echo json_encode(['success' => false, 'message' => 'Unauthorized user type']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
}

$conn->close();
