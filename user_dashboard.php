<?php
session_start();

// Allow only normal users (type=1)
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 1) {
    header('Location: index.php');
    exit;
}

/* ---------- DB ---------- */
$host = 'localhost'; $db = 'complain_portal';
$user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$updateSuccess = false;
$updateErrors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['contact'] ?? '');
    $email = $_SESSION['user_email'];

    // Validate inputs
    if (!preg_match('/^[A-Za-z ]{4,100}$/', $name)) {
        $updateErrors['name'] = 'Name needs 4–100 letters and spaces.';
    }
    if (!preg_match('/^(9\d{9}|01\d{7})$/', $phone)) {
        $updateErrors['phone'] = 'Phone must be 9XXXXXXXXX or 01XXXXXXX.';
    }

    if (!$updateErrors) {
        $stmt = $conn->prepare("UPDATE user SET name=?, contact=? WHERE user_email=? AND type=1");
        $stmt->bind_param("sss", $name, $phone, $email);
        if ($stmt->execute()) {
            // Check if row was updated
            if ($stmt->affected_rows > 0) {
                $updateSuccess = true;
            } else {
                $updateErrors['generic'] = "No changes were made or user not found.";
            }
        } else {
            $updateErrors['generic'] = "Database error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

// Fetch user data to display current info (or updated info if form submitted)
$stmt = $conn->prepare("SELECT user_email, name, contact FROM user WHERE user_email=? AND type=1");
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
<title>User Dashboard – Your Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
    :root {
        --bg:#0a0a0a; --card:#1a1a1a; --nav:#111;
        --accent:#a78bfa; --hover:#8b5cf6;
        --btn:#8b5cf6; --btn-hover:#7c3aed;
        --text:#e0e0e0; --sec:#ccc;
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
    input, button {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 4px;
    }
    input {
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
        color: #4CAF50;
        font-size: 1rem;
        margin-top: 0.5rem;
        font-weight: 600;
    }
</style>
</head>
<body>
<nav>
    <h1>Your Profile</h1>
</nav>

<main>
    <div class="card">
        <?php if ($updateSuccess): ?>
            <div class="success">Profile has been successfully edited.</div>
        <?php endif; ?>

        <form action="user_dashboard.php" method="POST" novalidate>
            <input type="hidden" name="update_profile" value="1">

            <label>Email (cannot change)</label>
            <input type="email" name="email" value="<?=htmlspecialchars($user['user_email'])?>" readonly>

            <label>Full Name</label>
            <input type="text" name="name" value="<?=htmlspecialchars($_POST['name'] ?? $user['name'])?>" required>
            <?php if (!empty($updateErrors['name'])): ?>
                <div class="error"><?= $updateErrors['name'] ?></div>
            <?php endif; ?>

            <label>Phone Number</label>
            <input type="text" name="contact" value="<?=htmlspecialchars($_POST['contact'] ?? $user['contact'])?>" required>
            <?php if (!empty($updateErrors['phone'])): ?>
                <div class="error"><?= $updateErrors['phone'] ?></div>
            <?php endif; ?>

            <?php if (!empty($updateErrors['generic'])): ?>
                <div class="error"><?= $updateErrors['generic'] ?></div>
            <?php endif; ?>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</main>

<?php if ($updateSuccess): ?>
<script>
  setTimeout(() => {
    window.location.href = 'home.php';
  }, 2000); // Redirect after 2 seconds
</script>
<?php endif; ?>

</body>
</html>
