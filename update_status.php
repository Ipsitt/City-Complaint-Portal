<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    echo json_encode(['success' => false]);
    exit;
}

$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';

if ($id && $status) {
    $stmt = $conn->prepare("UPDATE complaints SET status = ?, last_updated = NOW() WHERE complaint_id = ?");
    $stmt->bind_param("si", $status, $id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}
$conn->close();
?>
