<?php
session_start();

// Allow only government users (type=2)
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 2) {
    header('Location: index.php');
    exit;
}

/* ---------- DB ---------- */
$host = 'localhost'; $db = 'complain_portal';
$user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

/* ---------- UPDATE PROFILE ---------- */
$updateErrors = [];
$updateValues = [];
$updateSuccess = false;

if (isset($_POST['update_profile'])) {
    $updateValues = [
        'email'  => $_SESSION['user_email'],
        'name'   => trim($_POST['name']),
        'phone'  => trim($_POST['contact']),
    ];

    if (!preg_match('/^[A-Za-z ]{4,100}$/', $updateValues['name'])) {
        $updateErrors['name'] = 'Name needs 4–100 letters and spaces.';
    }
    if (!preg_match('/^(9\d{9}|01\d{7})$/', $updateValues['phone'])) {
        $updateErrors['phone'] = 'Phone must be 9XXXXXXXXX or 01XXXXXXX.';
    }

    if (!$updateErrors) {
        $stmt = $conn->prepare("UPDATE user SET name=?, contact=? WHERE user_email=? AND type=2");
        $stmt->bind_param("sss", $updateValues['name'], $updateValues['phone'], $updateValues['email']);
        if ($stmt->execute()) {
            $updateSuccess = true;
        } else {
            $updateErrors['generic'] = 'Error: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

/* ---------- READ CURRENT USER ---------- */
$stmt = $conn->prepare("SELECT user_email, name, contact, sector FROM user WHERE user_email=? AND type=2");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Government Account – Your Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
    :root {
        --bg:#0a0a0a; --card:#1a1a1a; --nav:#111;
        --accent:#a78bfa; --hover:#8b5cf6;
        --btn:#8b5cf6; --btn-hover:#7c3aed;
        --text:#e0e0e0; --sec:#ccc;
        --success: #4BB543;
    }
    * {
        box-sizing: border-box;
        font-family: system-ui, Arial, sans-serif;
    }
    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
    }
    nav {
        background: var(--nav);
        padding: 1rem;
    }
    nav h1 {
        margin: 0;
        color: var(--accent);
    }
    main {
        max-width: 480px;
        margin: 3rem auto;
        padding: 0 1rem;
    }
    .card {
        background: var(--card);
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 0 10px #8b5cf666;
        margin-bottom: 2rem;
    }
    label {
        display: block;
        margin: 0.75rem 0 0.25rem;
        font-size: 0.9rem;
        color: var(--sec);
    }
    input, select, button {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 4px;
    }
    input, select {
        background: #222;
        color: var(--text);
    }
    input[readonly] {
        background: #333;
        color: #aaa;
        cursor: not-allowed;
    }
    button {
        background: var(--btn);
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 1.5rem;
    }
    button:hover {
        background: var(--btn-hover);
        box-shadow: 0 0 8px var(--btn-hover);
    }
    .error {
        color: #ff0;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
    .success {
        color: var(--success);
        font-size: 0.9rem;
        margin-top: 1rem;
        font-weight: 600;
        text-align: center;
    }
</style>
</head>
<body>
<nav>
    <h1>Government Profile</h1>
</nav>

<main>
    <div class="card">
        <form action="account_gov.php" method="POST" novalidate>
            <input type="hidden" name="update_profile" value="1">

            <label>Email</label>
            <input type="email" name="email" value="<?=htmlspecialchars($user['user_email'])?>" readonly>

            <label>Sector</label>
            <input type="text" name="sector" value="<?=htmlspecialchars($user['sector'])?>" readonly>

            <label>Full Name</label>
            <input type="text" name="name" value="<?=htmlspecialchars($_POST['name'] ?? $user['name'])?>" required>
            <?php if (!empty($updateErrors['name'])) echo '<div class="error">' . $updateErrors['name'] . '</div>'; ?>

            <label>Phone Number</label>
            <input type="text" name="contact" value="<?=htmlspecialchars($_POST['contact'] ?? $user['contact'])?>" required>
            <?php if (!empty($updateErrors['phone'])) echo '<div class="error">' . $updateErrors['phone'] . '</div>'; ?>

            <?php if (!empty($updateErrors['generic'])) echo '<div class="error">' . $updateErrors['generic'] . '</div>'; ?>

            <button type="submit">Update Profile</button>

            <?php if ($updateSuccess): ?>
                <div class="success">Profile updated successfully.</div>
            <?php endif; ?>
        </form>
    </div>
</main>
</body>
</html>
