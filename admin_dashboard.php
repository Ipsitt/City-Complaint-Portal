<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 3) {
    header('Location: login.php');
    exit;
}

/* ---------- DB ---------- */
$host = 'localhost'; $db = 'complain_portal';
$user = 'root';      $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

/* ---------- DELETE ---------- */
if (isset($_POST['delete_user'])) {
    $stmt = $conn->prepare("DELETE FROM user WHERE user_email = ? AND type = 2");
    $stmt->bind_param("s", $_POST['delete_user']);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php"); exit;
}

/* ---------- UPDATE ---------- */
$updateErrors = [];
if (isset($_POST['update_user'])) {
    $name  = trim($_POST['u_name']);
    $phone = trim($_POST['u_contact']);
    $sector= $_POST['u_sector'];
    $email = $_POST['u_email'];

    if (!preg_match('/^[A-Za-z ]{4,100}$/', $name))
        $updateErrors['name'] = 'Name needs 1–2 spaces, 4–100 letters.';
    if (!preg_match('/^(9\d{9}|01\d{7})$/', $phone))
        $updateErrors['phone'] = 'Phone: 9XXXXXXXXX or 01XXXXXXX';

    if (!$updateErrors) {
        $stmt = $conn->prepare("UPDATE user SET name=?, contact=?, sector=? WHERE user_email=? AND type=2");
        $stmt->bind_param("ssss", $name, $phone, $sector, $email);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php"); exit;
    }
}

/* ---------- CREATE ---------- */
$createErrors = [];
$createValues = [];
if (isset($_POST['create_user'])) {
    $createValues = [
        'name'  => trim($_POST['fullname']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['contact']),
        'sector'=> $_POST['sector']
    ];

    // basic validation
    if (!preg_match('/^[A-Za-z ]{4,100}$/', $createValues['name']))
        $createErrors['name'] = 'Name needs 1–2 spaces, 4–100 letters.';
    if (!preg_match('/^\S+@\S+\.\S+$/', $createValues['email']))
        $createErrors['email'] = 'Invalid email format.';
    if (!preg_match('/^(9\d{9}|01\d{7})$/', $createValues['phone']))
        $createErrors['phone'] = 'Phone: 9XXXXXXXXX or 01XXXXXXX';

    // duplicate email check
    if (!$createErrors) {
        $check = $conn->prepare("SELECT 1 FROM user WHERE user_email = ?");
        $check->bind_param("s", $createValues['email']);
        $check->execute();
        $check->store_result();
        if ($check->num_rows) {
            $createErrors['email'] = 'Email already exists.';
        }
        $check->close();
    }

    if (!$createErrors) {
        $stmt = $conn->prepare("INSERT INTO user (user_email, name, contact, type, sector)
                                VALUES (?,?,?,?,?)");
        $type = 2;
        $stmt->bind_param("sssis", $createValues['email'], $createValues['name'],
                          $createValues['phone'], $type, $createValues['sector']);
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php"); exit;
        } else {
            $createErrors['generic'] = 'Error: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

/* ---------- READ ---------- */
$govUsers = $conn->query("SELECT user_email, name, contact, sector FROM user WHERE type = 2 ORDER BY sector, name");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin – Government Accounts</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
    :root{
        --bg:#0a0a0a; --card:#1a1a1a; --nav:#111;
        --accent:#a78bfa; --hover:#8b5cf6;
        --btn:#8b5cf6; --btn-hover:#7c3aed;
        --text:#e0e0e0; --sec:#ccc;
    }
    *{box-sizing:border-box;font-family:system-ui,Arial,sans-serif}
    body{margin:0;background:var(--bg);color:var(--text)}
    
    /* NAVBAR */
    .navbar{position:fixed;top:0;left:0;right:0;background:var(--nav);display:flex;align-items:center;justify-content:space-between;padding:.8rem 2rem;box-shadow:0 2px 8px rgba(0,0,0,.8);z-index:1000}
    .navbar .left{display:flex;align-items:center;gap:.8rem}
    .navbar .logo{width:45px;height:45px;border-radius:50%;border:2px solid var(--accent);box-shadow:0 0 10px var(--accent)88}
    .navbar h1{color:var(--accent);font-size:1.5rem}
    .navbar nav{display:flex;align-items:center;gap:1rem}
    .navbar nav a{color:var(--accent);text-decoration:none;font-weight:600}
    
    main{max-width:960px;margin:3rem auto;padding:0 1rem;margin-top:100px}
    .card{background:var(--card);border-radius:8px;padding:2rem;box-shadow:0 0 10px #8b5cf666;margin-bottom:2rem}
    label{display:block;margin:.75rem 0 .25rem;font-size:.9rem;color:var(--sec)}
    input,select,button{width:100%;padding:.75rem;border:none;border-radius:4px}
    input,select{background:#222;color:var(--text)}
    button{background:var(--btn);color:#fff;font-weight:600;cursor:pointer;transition:.2s}
    button:hover{background:var(--btn-hover);box-shadow:0 0 8px var(--btn-hover)}
    .error{color:#ff0;font-size:.8rem;margin-top:.25rem}
    table{width:100%;border-collapse:collapse;margin-top:1rem}
    th,td{padding:.75rem;text-align:left;font-size:.9rem}
    th{color:var(--accent)} tr:nth-child(even){background:#222}
    .action-btn{display:block;width:100%;margin:.25rem auto;background:transparent;border:none;color:var(--accent);cursor:pointer;font-size:.85rem}
    .action-btn:hover{text-decoration:underline}
    .modal{display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.7);align-items:center;justify-content:center}
    .modal-content{max-width:480px;width:90%;background:var(--card);border-radius:8px;padding:2rem;box-shadow:0 0 20px var(--accent)}
    .close{color:var(--accent);float:right;font-size:1.5rem;cursor:pointer}
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
        <a href="logout.php">Logout</a>
    </nav>
</div>

<main>
    <!-- ---------- CREATE ---------- -->
    <div class="card">
        <h2>Create Government Account</h2>
        <form action="admin_dashboard.php" method="POST">
            <input type="hidden" name="create_user" value="1">

            <label>Full Name</label>
            <input type="text" name="fullname" value="<?=htmlspecialchars($createValues['name'] ?? '')?>" required>
            <?php if (!empty($createErrors['name'])) echo '<div class="error">'.$createErrors['name'].'</div>'; ?>

            <label>Email</label>
            <input type="email" name="email" value="<?=htmlspecialchars($createValues['email'] ?? '')?>" required>
            <?php if (!empty($createErrors['email'])) echo '<div class="error">'.$createErrors['email'].'</div>'; ?>

            <label>Phone Number</label>
            <input type="text" name="contact" value="<?=htmlspecialchars($createValues['phone'] ?? '')?>" required>
            <?php if (!empty($createErrors['phone'])) echo '<div class="error">'.$createErrors['phone'].'</div>'; ?>

            <label>Government Sector</label>
            <select name="sector" required>
                <option value="" disabled <?=!isset($createValues['sector']) ? 'selected' : ''?>>Select Sector</option>
                <option <?=($createValues['sector'] ?? '')==='water' ? 'selected' : ''?>>Water</option>
                <option <?=($createValues['sector'] ?? '')==='electricity' ? 'selected' : ''?>>Electricity</option>
                <option <?=($createValues['sector'] ?? '')==='roads and infrastructures' ? 'selected' : ''?>>Roads & Infrastructures</option>
                <option <?=($createValues['sector'] ?? '')==='waste management' ? 'selected' : ''?>>Waste Management</option>
                <option <?=($createValues['sector'] ?? '')==='public safety' ? 'selected' : ''?>>Public Safety</option>
                <option <?=($createValues['sector'] ?? '')==='public transportation' ? 'selected' : ''?>>Public Transportation</option>
            </select>

            <button type="submit" style="margin-top:1.5rem">Create Account</button>
            <?php if (!empty($createErrors['generic'])) echo '<div class="error">'.$createErrors['generic'].'</div>'; ?>
        </form>
    </div>

    <!-- ---------- READ ---------- -->
    <div class="card">
        <h2>Existing Government Users</h2>
        <?php if ($govUsers->num_rows): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Sector</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($u=$govUsers->fetch_assoc()): ?>
                <tr>
                    <td><?=htmlspecialchars($u['name'])?></td>
                    <td><?=htmlspecialchars($u['user_email'])?></td>
                    <td><?=htmlspecialchars($u['contact'])?></td>
                    <td><?=htmlspecialchars($u['sector'])?></td>
                    <td style="text-align:center;">
                        <button class="action-btn" onclick="openEditModal('<?=htmlspecialchars(addslashes(json_encode($u)))?>')">Edit</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this account?');">
                            <input type="hidden" name="delete_user" value="<?=htmlspecialchars($u['user_email'])?>">
                            <button type="submit" class="action-btn" style="color:#f55">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No government users yet.</p>
        <?php endif; ?>
    </div>
</main>

<!-- ---------- UPDATE MODAL ---------- -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Account</h2>
        <form action="admin_dashboard.php" method="POST">
            <input type="hidden" name="update_user" value="1">
            <input type="hidden" name="u_email" id="u_email">

            <label>Full Name</label>
            <input type="text" name="u_name" id="u_name" required>
            <?php if (!empty($updateErrors['name'])) echo '<div class="error">'.$updateErrors['name'].'</div>'; ?>

            <label>Contact</label>
            <input type="text" name="u_contact" id="u_contact" required>
            <?php if (!empty($updateErrors['phone'])) echo '<div class="error">'.$updateErrors['phone'].'</div>'; ?>

            <label>Sector</label>
            <select name="u_sector" id="u_sector" required>
                <option value="" disabled <?= !isset($_POST['u_sector']) ? 'selected' : '' ?>>Select Sector</option>
                <option value="Water">Water</option>
                <option value="Electricity">Electricity</option>
                <option value="Roads and Infrastructures">Roads & Infrastructures</option>
                <option value="Waste Management">Waste Management</option>
                <option value="Public Safety">Public Safety</option>
                <option value="Public Transportation">Public Transportation</option>
            </select>

            <button type="submit" style="margin-top:1.5rem">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openEditModal(json){
    const u = JSON.parse(json);
    document.getElementById('u_email').value = u.user_email;
    document.getElementById('u_name').value   = u.name;
    document.getElementById('u_contact').value= u.contact;
    document.getElementById('u_sector').value = u.sector;
    document.getElementById('editModal').style.display='flex';
}
function closeEditModal(){
    document.getElementById('editModal').style.display='none';
}
window.onclick = e => {
    if (e.target === document.getElementById('editModal')) closeEditModal();
}
</script>

</body>
</html>