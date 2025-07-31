<?php
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 2) {
    header('Location: index.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$gov_email = $_SESSION['user_email'];

/* ---------- UPDATE PROFILE ---------- */
$updateErrors = [];
$updateSuccess = false;

if (isset($_POST['update_profile'])) {
    $name   = trim($_POST['name']);
    $phone  = trim($_POST['contact']);
    $sector = trim($_POST['sector']);

    if (!preg_match('/^[A-Za-z ]{4,100}$/', $name))
        $updateErrors['name'] = 'Name needs 4–100 letters and spaces.';
    if (!preg_match('/^(9\d{9}|01\d{7})$/', $phone))
        $updateErrors['phone'] = 'Phone must be 9XXXXXXXXX or 01XXXXXXX.';
    if (!in_array($sector, ['water','electricity','roads and infrastructures','waste management','public safety','public transportation']))
        $updateErrors['sector'] = 'Invalid sector.';

    if (!$updateErrors) {
        $stmt = $conn->prepare("UPDATE user SET name = ?, contact = ?, sector = ? WHERE user_email = ? AND type = 2");
        $stmt->bind_param("ssss", $name, $phone, $sector, $gov_email);
        $updateSuccess = $stmt->execute();
        $stmt->close();
    }
}

/* ---------- READ CURRENT USER ---------- */
$stmt = $conn->prepare("SELECT user_email, name, contact, sector FROM user WHERE user_email = ? AND type = 2");
$stmt->bind_param("s", $gov_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Account – City Portal</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root{
  --bg:#0a0a0a; --card:#1a1a1a; --nav:#111;
  --accent:#a78bfa; --hover:#8b5cf6;
  --btn:#8b5cf6; --btn-hover:#7c3aed;
  --text:#e0e0e0; --sec:#ccc;
  --success:#4BB543;
}
*{box-sizing:border-box;font-family:system-ui,Arial,sans-serif;margin:0;padding:0}
html,body{height:100%}
body{display:flex;flex-direction:column;background:var(--bg);color:var(--text)}
nav{background:var(--nav);padding:1rem}
nav h1{margin:0;color:var(--accent)}
main{flex:1;display:flex;align-items:center;justify-content:center;padding:100px 1rem 3rem}
.card{background:var(--card);border-radius:8px;padding:2rem;box-shadow:0 0 10px #8b5cf666;max-width:480px;width:100%}
h2{margin:0 0 1rem;color:var(--accent);font-size:1.4rem;text-align:center}
label{display:block;margin:.75rem 0 .25rem;font-size:.9rem;color:var(--sec)}
input,select,button{width:100%;padding:.75rem;border:none;border-radius:4px}
input,select{background:#222;color:var(--text)}
input[readonly]{background:#333;color:#aaa}
button{background:var(--btn);color:#fff;font-weight:600;cursor:pointer;margin-top:1.5rem}
button:hover{background:var(--btn-hover)}
.error{color:#ff0;font-size:.8rem;margin-top:.25rem}
.success{color:var(--success);font-size:.9rem;margin-top:1rem;text-align:center}
footer{margin-top:auto;text-align:center;font-size:.9rem;color:#aaa;padding:2rem 1rem;background:var(--nav)}
</style>
</head>
<body>
<nav>
    <h1>Government Profile</h1>
</nav>

<main>
    <div class="card">
        <h2>Account Details</h2>

        <form action="account_gov.php" method="POST" novalidate>
            <input type="hidden" name="update_profile" value="1">

            <label>Email</label>
            <input type="email" value="<?=htmlspecialchars($user['user_email'])?>" readonly>

            <label>Full Name</label>
            <input type="text" name="name" value="<?=htmlspecialchars($_POST['name'] ?? $user['name'])?>" required>
            <?php if (!empty($updateErrors['name'])) echo '<div class="error">'.$updateErrors['name'].'</div>'; ?>

            <label>Phone Number</label>
            <input type="text" name="contact" value="<?=htmlspecialchars($_POST['contact'] ?? $user['contact'])?>" required>
            <?php if (!empty($updateErrors['phone'])) echo '<div class="error">'.$updateErrors['phone'].'</div>'; ?>

            <label>Sector</label>
            <select name="sector" required>
                <?php
                $sectors = ['water','electricity','roads and infrastructures','waste management','public safety','public transportation'];
                foreach ($sectors as $s) {
                    $sel = ($s === $user['sector']) ? 'selected' : '';
                    echo "<option value=\"$s\" $sel>".ucfirst($s)."</option>";
                }
                ?>
            </select>
            <?php if (!empty($updateErrors['sector'])) echo '<div class="error">'.$updateErrors['sector'].'</div>'; ?>

            <button type="submit">Save Changes</button>
            <?php if ($updateSuccess): ?>
                <div class="success">Profile updated successfully.</div>
            <?php endif; ?>
        </form>
    </div>
</main>

<footer>
    <p>City Complaint Portal • Aligned with UN SDGs</p>
    <p>complain.portal.paradox@gmail.com | +977-986-0906232</p>
    <p>&copy; <?= date('Y') ?> City Complaint Portal</p>
</footer>

</body>
</html>