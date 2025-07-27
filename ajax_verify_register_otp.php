<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['reg_email'], $_SESSION['reg_name'], $_SESSION['reg_contact'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired or invalid. Please register again.']);
    exit;
}

$email = $_POST['email'] ?? '';
$entered_otp = $_POST['otp'] ?? '';

if (!$email || !$entered_otp) {
    echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
    exit;
}

if ($email !== $_SESSION['reg_email']) {
    echo json_encode(['success' => false, 'message' => 'Email mismatch.']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Check OTP from DB
$stmt = $conn->prepare("SELECT otp FROM otp WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'No OTP found for this email.']);
    $conn->close();
    exit;
}

if ($entered_otp != $row['otp']) {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
    $conn->close();
    exit;
}

// OTP correct - insert user
// Check again if user exists to be safe
$stmtCheck = $conn->prepare("SELECT user_email FROM user WHERE user_email = ?");
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    $stmtCheck->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'User already registered. Please login.']);
    exit;
}
$stmtCheck->close();

$name = $_SESSION['reg_name'];
$contact = $_SESSION['reg_contact'];
$type = 1; // default user type
$sector = 'User'; // default sector

$stmtInsert = $conn->prepare("INSERT INTO user (user_email, name, contact, type, sector) VALUES (?, ?, ?, ?, ?)");
$stmtInsert->bind_param("sssis", $email, $name, $contact, $type, $sector);

if ($stmtInsert->execute()) {
    // Delete OTP after successful registration
    $del = $conn->prepare("DELETE FROM otp WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();
    $del->close();

    // Clear session registration data
    unset($_SESSION['reg_email'], $_SESSION['reg_name'], $_SESSION['reg_contact']);

    // Set session for logged in user
    $_SESSION['user_email'] = $email;
    $_SESSION['user_type'] = $type;

    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to register user.']);
}

$stmtInsert->close();
$conn->close();
