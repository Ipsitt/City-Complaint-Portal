<?php
// gov_dashboard.php
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 2) {
    header('Location: index.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

/* 1.  READ CURRENT SECTOR */
$stmt = $conn->prepare("SELECT sector FROM user WHERE user_email = ? AND type = 2");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$stmt->bind_result($sector);
$stmt->fetch();
$stmt->close();

/* 2.  FILTER & LIST COMPLAINTS */
$filter = $_GET['status'] ?? 'all';
$sql = "SELECT c.*, u.name AS complainer_name, u.contact AS complainer_contact 
        FROM complaints c
        JOIN user u ON c.user_email = u.user_email
        WHERE c.sector = ?";
$params = [$sector];
$types  = 's';

if ($filter !== 'all') {
    $sql   .= " AND LOWER(c.status) = ?";
    $params[] = strtolower($filter);
    $types   .= 's';
}
$sql .= " ORDER BY c.date_reported DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Government Dashboard - City Portal</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root{
  --bg:#0a0a0a; --card:#1a1a1a; --nav:#111;
  --accent:#a78bfa; --hover:#8b5cf6;
  --btn:#8b5cf6; --btn-hover:#7c3aed;
  --text:#e0e0e0; --sec:#ccc;
  --success:#22c55e; --warning:#eab308; --danger:#ef4444;
}
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;margin:0;padding:0}
html,body{height:100%}
body{display:flex;flex-direction:column;background:var(--bg);color:var(--text)}

/* NAVBAR */
.navbar{position:fixed;top:0;left:0;right:0;background:var(--nav);display:flex;align-items:center;justify-content:space-between;padding:.8rem 2rem;box-shadow:0 2px 8px rgba(0,0,0,.8);z-index:1000}
.navbar .left{display:flex;align-items:center;gap:.8rem}
.navbar .logo{width:45px;height:45px;border-radius:50%;border:2px solid var(--accent);box-shadow:0 0 10px var(--accent)88}
.navbar h1{color:var(--accent);font-size:1.5rem}
.navbar nav{display:flex;align-items:center;gap:1rem}
.navbar nav a{color:var(--accent);text-decoration:none;font-weight:600}
.navbar nav a:hover{color:var(--hover)}

/* LAYOUT */
.wrapper{display:flex;flex:1;margin-top:70px}
.sidebar{position:fixed;left:0;top:70px;bottom:0;width:200px;background:var(--card);padding:1rem;border-right:1px solid #333;overflow-y:auto}
.sidebar h3{color:var(--accent);margin-bottom:1rem}
.sidebar a{display:block;padding:.5rem .75rem;margin:.25rem 0;color:var(--sec);text-decoration:none;border-radius:4px;transition:.2s}
.sidebar a.active{background:var(--accent);color:var(--bg)}
.sidebar a:hover{background:var(--hover);color:var(--bg)}
.content{margin-left:200px;padding:2rem;flex:1}

/* COMPLAINT CARD */
.complaints-grid{display:flex;flex-direction:column;gap:1.5rem}
.complaint-card{background:var(--card);padding:1.5rem;border-radius:10px;box-shadow:0 0 15px #8b5cf644;cursor:pointer;transition:box-shadow .3s}
.complaint-card:hover{box-shadow:0 0 35px var(--hover)}
.complaint-card img{width:250px;height:auto;border-radius:10px;object-fit:cover;flex-shrink:0}
.complaint-info{flex:1;display:flex;flex-direction:column}
.title-votes-row{display:flex;justify-content:space-between;align-items:baseline}
.complaint-title{color:var(--accent);font-size:1.2rem;font-weight:600}
.complaint-votes{font-size:.85rem;color:var(--sec)}
.complaint-location{color:#aaa;font-size:.9rem;margin:.5rem 0}
.status-dropdown{background:#111;color:#fff;border:1px solid var(--accent);border-radius:5px;padding:5px;font-weight:600}
.status-dropdown.resolved{border-color:var(--success);box-shadow:0 0 10px 2px var(--success)99;color:var(--success)}
.status-dropdown.being-resolved{border-color:var(--warning);box-shadow:0 0 10px 2px var(--warning)99;color:var(--warning)}
.status-dropdown.unresolved{border-color:var(--danger);box-shadow:0 0 10px 2px var(--danger)99;color:var(--danger)}

/* MODAL & FOOTER */
.modal{display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.7);align-items:center;justify-content:center}
.modal-content{background:var(--card);border-radius:15px;max-width:900px;width:90%;padding:2rem;color:var(--text);box-shadow:0 0 20px var(--accent);position:relative;display:flex;gap:2rem;flex-wrap:wrap}
.modal-close{position:absolute;top:15px;right:15px;font-size:24px;color:var(--text);cursor:pointer}
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
        <a href="gov_dashboard.php">Dashboard</a>
        <a href="account_gov.php">Account</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<div class="wrapper">
    <!-- SIDE NAV -->
    <aside class="sidebar">
        <h3>Status</h3>
        <a href="?status=all"   class="<?= $filter==='all'   ? 'active' : '' ?>">All</a>
        <a href="?status=unresolved"   class="<?= $filter==='unresolved'   ? 'active' : '' ?>">Unresolved</a>
        <a href="?status=being resolved" class="<?= $filter==='being resolved' ? 'active' : '' ?>">Being Resolved</a>
        <a href="?status=resolved"   class="<?= $filter==='resolved'   ? 'active' : '' ?>">Resolved</a>
    </aside>

    <!-- CONTENT -->
    <main class="content">
        <section class="complaints-section">
            <h2>Complaints <?= $filter!=='all' ? '(' . ucfirst($filter) . ')' : '' ?></h2>
            <div class="complaints-grid">
                <?php
                if ($complaints) {
                    foreach ($complaints as $row) {
                        $imagePath = $row['image'] ? "complaint_images/" . htmlspecialchars($row['image']) : "images/no-image.png";
                        $status = strtolower($row['status']);
                        $glow = match($status){
                            'resolved'        => 'resolved',
                            'being resolved'  => 'being-resolved',
                            default           => 'unresolved'
                        };
                        echo "
                        <div class='complaint-card' data-id='{$row['complaint_id']}'>
                            <img src='{$imagePath}' alt='Complaint Image' loading='lazy'>
                            <div class='complaint-info'>
                                <div class='title-votes-row'>
                                    <div class='complaint-title'>" . htmlspecialchars($row['title']) . "</div>
                                    <div class='complaint-votes'>{$row['votes']} people faced this issue</div>
                                </div>
                                <div class='complaint-location'>Location: " . htmlspecialchars($row['location']) . "</div>
                                <select class='status-dropdown {$glow}' data-id='{$row['complaint_id']}'>
                                    <option value='Unresolved'   " . ($status==='unresolved'   ? 'selected' : '') . ">Unresolved</option>
                                    <option value='Being Resolved' " . ($status==='being resolved' ? 'selected' : '') . ">Being Resolved</option>
                                    <option value='Resolved'     " . ($status==='resolved'     ? 'selected' : '') . ">Resolved</option>
                                </select>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<p>No complaints found.</p>";
                }
                ?>
            </div>
        </section>
    </main>
</div>

<!-- MODAL -->
<div class="modal" id="modal">
    <div class="modal-content">
        <span class="modal-close" id="modalClose">&times;</span>
        <img id="modalImage" src="" alt="Complaint Image">
        <div class="modal-info">
            <h2 id="modalTitle"></h2>
            <p id="modalDesc"></p>
            <p><strong>Location:</strong> <span id="modalLoc"></span></p>
            <p><strong>Votes:</strong> <span id="modalVotes"></span></p>
            <p><strong>Status:</strong> <span id="modalStatus"></span></p>
            <hr>
            <p><strong>Complainer Email:</strong> <span id="modalEmail"></span></p>
            <p><strong>Complainer Name:</strong> <span id="modalName"></span></p>
            <p><strong>Contact:</strong> <span id="modalContact"></span></p>
        </div>
    </div>
</div>

<footer>
    <p>City Complaint Portal • Aligned with UN SDGs</p>
    <p>complain.portal.paradox@gmail.com | +977-986-0906232</p>
    <p>&copy; <?= date('Y') ?> City Complaint Portal</p>
</footer>

<script>
/* update status */
document.querySelectorAll('.status-dropdown').forEach(sel => {
    sel.addEventListener('change', async () => {
        const id = sel.dataset.id;
        const newStatus = sel.value;
        const res = await fetch('update_status.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `id=${id}&status=${encodeURIComponent(newStatus)}`
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Update failed');
    });
});

/* modal */
const modal = document.getElementById('modal');
document.querySelectorAll('.complaint-card').forEach(card => {
    card.addEventListener('click', e => {
        if (e.target.classList.contains('status-dropdown')) return;
        const d = card.dataset;
        document.getElementById('modalImage').src = d.image;
        document.getElementById('modalTitle').textContent = d.title;
        document.getElementById('modalDesc').textContent = d.description;
        document.getElementById('modalLoc').textContent = d.location;
        document.getElementById('modalVotes').textContent = d.votes + ' people';
        document.getElementById('modalStatus').textContent = d.status;
        document.getElementById('modalEmail').textContent = d.email;
        document.getElementById('modalName').textContent = d.name;
        document.getElementById('modalContact').textContent = d.contact;
        modal.style.display = 'flex';
    });
});
document.getElementById('modalClose').addEventListener('click', () => modal.style.display = 'none');
</script>
</body>
</html>