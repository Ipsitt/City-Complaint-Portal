<?php
header('Content-Type: application/json');
session_start();

// Only logged-in government users (type=2) may update
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 2) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Input
$id     = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';

// Allowed statuses
$allowed = ['Unresolved', 'Being Resolved', 'Resolved'];
if (!$id || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Verify the complaint belongs to the government user’s sector
$gov_email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT sector FROM user WHERE user_email = ?");
$stmt->bind_param("s", $gov_email);
$stmt->execute();
$stmt->bind_result($sector);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("UPDATE complaints SET status = ?, last_updated = NOW() 
                        WHERE complaint_id = ? AND sector = ?");
$stmt->bind_param("sis", $status, $id, $sector);
$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>