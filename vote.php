<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_email'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $user_email = $_SESSION['user_email'];

    $conn = new mysqli("localhost", "root", "", "complain_portal");
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Check if user already voted
    $check_sql = "SELECT 1 FROM votes WHERE user_email = ? AND complaint_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("si", $user_email, $complaint_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();

        // Insert vote
        $insert_sql = "INSERT INTO votes (user_email, complaint_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("si", $user_email, $complaint_id);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to save vote']);
            exit;
        }
        $stmt->close();

        // Increment votes count in complaints table
        $update_sql = "UPDATE complaints SET votes = votes + 1 WHERE complaint_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $stmt->close();

        $conn->close();

        echo json_encode(['success' => true]);
    } else {
        $stmt->close();
        $conn->close();

        echo json_encode(['success' => false, 'message' => 'Already voted']);
    }

    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
