<?php
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 1) {
    header('Location: login.php');
    exit;
}

/* ---------- DB ---------- */
$host = 'localhost'; $db = 'complain_portal';
$user = 'root';      $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

/* ---------- UPDATE PROFILE ---------- */
$updateErrors = [];
$updateSuccess = false;

if (isset($_POST['update_profile'])) {
    $name  = trim($_POST['name']);
    $phone = trim($_POST['contact']);

    if (!preg_match('/^[A-Za-z ]{4,100}$/', $name))
        $updateErrors['name'] = 'Name needs 4–100 letters and spaces.';
    if (!preg_match('/^(9\d{9}|01\d{7})$/', $phone))
        $updateErrors['phone'] = 'Phone must be 9XXXXXXXXX or 01XXXXXXX.';

    if (!$updateErrors) {
        $stmt = $conn->prepare("UPDATE user SET name=?, contact=? WHERE user_email=? AND type=1");
        $stmt->bind_param("sss", $name, $phone, $_SESSION['user_email']);
        $updateSuccess = $stmt->execute();
        $stmt->close();
    }
}

/* ---------- READ CURRENT USER ---------- */
$stmt = $conn->prepare("SELECT user_email, name, contact FROM user WHERE user_email=? AND type=1");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Account Details – City Portal</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    :root {
        --bg:#0a0a0a; --card:#1a1a1a; --nav:#111;
        --accent:#a78bfa; --hover:#8b5cf6;
        --btn:#8b5cf6; --btn-hover:#7c3aed;
        --text:#e0e0e0; --sec:#ccc;
        --success:#4BB543;
    }
    *{box-sizing:border-box;font-family:system-ui,Arial,sans-serif;margin:0;padding:0}
    html,body{height:100%}
    body{display:flex;flex-direction:column;background:var(--bg);color:var(--text)}
    /* NAVBAR (home style) */
    .navbar{
        position:fixed;top:0;left:0;right:0;
        background:var(--nav);display:flex;align-items:center;justify-content:space-between;
        padding:.8rem 2rem;box-shadow:0 2px 8px rgba(0,0,0,.8);z-index:1000
    }
    .navbar .left{display:flex;align-items:center;gap:.8rem}
    .navbar .logo{width:45px;height:45px;border-radius:50%;border:2px solid var(--accent);box-shadow:0 0 10px var(--accent)88}
    .navbar h1{color:var(--accent);font-size:1.5rem}
    .navbar nav a{margin-left:1.5rem;color:var(--accent);text-decoration:none;font-weight:600;font-size:1.1rem}
    .navbar nav a:hover{color:var(--hover)}
    /* MAIN */
    main{flex:1;max-width:480px;margin:100px auto 3rem;padding:0 1rem}
    .card{background:var(--card);border-radius:8px;padding:2rem;box-shadow:0 0 10px #8b5cf666}
    h2{margin:0 0 1rem;color:var(--accent);font-size:1.4rem;text-align:center}
    label{display:block;margin:.75rem 0 .25rem;font-size:.9rem;color:var(--sec)}
    input,button{width:100%;padding:.75rem;border:none;border-radius:4px}
    input{background:#222;color:var(--text)}
    input[readonly]{background:#333;color:#aaa;cursor:not-allowed}
    button{background:var(--btn);color:#fff;font-weight:600;cursor:pointer;margin-top:1.5rem}
    button:hover{background:var(--btn-hover)}
    .error{color:#ff0;font-size:.8rem;margin-top:.25rem}
    .success{color:var(--success);font-size:.9rem;margin-top:1rem;text-align:center}
    /* STICKY FOOTER */
    footer{margin-top:auto;text-align:center;font-size:.9rem;color:#aaa;padding:2rem 1rem;background:var(--nav)}
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="left">
        <img src="images/logo.png" class="logo" alt="City Portal">
        <h1>City Portal</h1>
    </div>
    <nav>
        <a href="home.php">Home</a>
        <a href="issues.php">Recent Complaints</a>
        <a href="account.php">Account</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<main>
    <div class="card">
        <h2>Account Details</h2>

        <form action="user_dashboard.php" method="POST" novalidate>
            <input type="hidden" name="update_profile" value="1">

            <label>Email</label>
            <input type="email" value="<?=htmlspecialchars($user['user_email'])?>" readonly>

            <label>Full Name</label>
            <input type="text" name="name" value="<?=htmlspecialchars($_POST['name'] ?? $user['name'])?>" required>
            <?php if (!empty($updateErrors['name'])) echo '<div class="error">'.$updateErrors['name'].'</div>'; ?>

            <label>Phone Number</label>
            <input type="text" name="contact" value="<?=htmlspecialchars($_POST['contact'] ?? $user['contact'])?>" required>
            <?php if (!empty($updateErrors['phone'])) echo '<div class="error">'.$updateErrors['phone'].'</div>'; ?>

            <?php if (!empty($updateErrors['generic'])) echo '<div class="error">'.$updateErrors['generic'].'</div>'; ?>

            <button type="submit">Update Profile</button>
            <?php if ($updateSuccess): ?>
                <div class="success">Profile updated successfully.</div>
            <?php endif; ?>
        </form>
    </div>
</main>

<!-- STICKY FOOTER -->
<footer>
    <p>City Complaint Portal • Aligned with UN SDGs</p>
    <p>complain.portal.paradox@gmail.com | +977-986-0906232</p>
    <p>&copy; <?= date('Y') ?> City Complaint Portal</p>
</footer>

</body>
</html>