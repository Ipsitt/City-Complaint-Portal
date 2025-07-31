<?php
session_start();
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] != 2) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$gov_email = $_SESSION['user_email'];
$sector = '';
$stmt = $conn->prepare("SELECT sector FROM user WHERE user_email = ?");
$stmt->bind_param("s", $gov_email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $sector = $row['sector'];
}
$stmt->close();

$complaints = [];
if ($sector) {
    $sql = "SELECT c.*, u.name AS complainer_name, u.contact AS complainer_contact 
            FROM complaints c
            JOIN user u ON c.user_email = u.user_email
            WHERE c.sector = ? 
            ORDER BY c.date_reported DESC";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("s", $sector);
    $stmt2->execute();
    $complaints = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Government Dashboard - City Portal</title>
<style>
body {
  background-color: #0a0a0a;
  color: #e0e0e0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0;
}
.navbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  background: #111;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.8rem 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.8);
  z-index: 1000;
}
.navbar .left { display: flex; align-items: center; gap: 0.8rem; }
.logo {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  border: 2px solid #a78bfa;
  box-shadow: 0 0 10px #a78bfa88;
}
.navbar h1 { color: #a78bfa; font-size: 1.5rem; }
.navbar nav a {
  margin-left: 1.5rem;
  color: #a78bfa;
  text-decoration: none;
  font-weight: 600;
}
.navbar nav a:hover { color: #8b5cf6; }
header {
  background: url('images/backdrop.jpeg') no-repeat center center/cover;
  padding: 8rem 2rem 3rem;
  margin-top: 70px;
}
header h2 { color: #fff; font-size: 2rem; margin-bottom: 0.5rem; }
header h3 { color: #a78bfa; font-size: 1.2rem; }
main { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
.complaints-grid { display: flex; flex-direction: column; gap: 2rem; }

.complaint-card {
  background: #1a1a1a;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 0 15px #8b5cf644;
  cursor: pointer;
  transition: box-shadow 0.3s ease;
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
}
.complaint-card:hover { box-shadow: 0 0 35px #a78bfaaa; }
.complaint-card img {
  width: 250px; height: auto;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
}
.complaint-info { flex: 1; display: flex; flex-direction: column; }
.title-votes-row { display: flex; justify-content: space-between; align-items: baseline; }
.complaint-title { color: #a78bfa; font-size: 1.2rem; font-weight: 600; }
.complaint-votes { font-size: 0.85rem; color: #ccc; }
.complaint-location { color: #aaa; font-size: 0.9rem; margin-top: 0.5rem; }
.status-dropdown {
  margin-top: 10px; background: #111; color: #fff;
  border: 1px solid #a78bfa; border-radius: 5px;
  padding: 5px; font-weight: 600;
}

.modal {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.8);
  display: none; justify-content: center; align-items: center;
  z-index: 2000; padding: 1rem;
}
.modal-content {
  background: #1a1a1a;
  padding: 2rem;
  border-radius: 15px;
  max-width: 900px;
  width: 90%;
  color: #fff;
  box-shadow: 0 0 20px #8b5cf6;
  position: relative;
  display: flex;
  gap: 2rem;
  flex-wrap: nowrap;
  transform-origin: center;
  transform: scale(0.5);
  opacity: 0;
  transition: all 0.3s ease;
}
.modal.show .modal-content {
  transform: scale(1);
  opacity: 1;
}
.modal-close {
  cursor: pointer;
  position: absolute; top: 15px; right: 15px;
  font-size: 24px; color: #fff;
}
.modal-content img {
  width: 350px; height: auto;
  border-radius: 10px; object-fit: cover;
  flex-shrink: 0;
}
.modal-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}
.modal-info h2 { margin-top: 0; color: #a78bfa; }
.modal-info p { margin: 0; line-height: 1.3; }
.modal-info hr { border-color: #555; margin: 10px 0; }

.status-glow-resolved { border-color: #22c55e !important; box-shadow: 0 0 10px 2px #22c55e99; color: #22c55e !important; }
.status-glow-being-resolved { border-color: #eab308 !important; box-shadow: 0 0 10px 2px #eab30899; color: #eab308 !important; }
.status-glow-unresolved { border-color: #ef4444 !important; box-shadow: 0 0 10px 2px #ef444499; color: #ef4444 !important; }

footer { text-align: center; font-size: 0.9rem; color: #aaa; padding: 2rem 1rem; background: #111; margin-top: 3rem; }
</style>
</head>
<body>
<div class="navbar">
  <div class="left">
    <img src="images/logo.png" class="logo" />
    <h1>City Portal</h1>
  </div>
  <nav>
    <a href="gov_dashboard.php">Dashboard</a>
    <a href="account_gov.php">Account</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>

<header>
  <h2>Online Complaint Portal</h2>
  <h3>Sector: <?= htmlspecialchars($sector) ?></h3>
</header>

<main>
  <section class="complaints-section">
    <h2>Complaints for your Sector</h2>
    <div class="complaints-grid">
      <?php
      if ($complaints) {
        foreach ($complaints as $row) {
          $imagePath = $row['image'] ? "complaint_images/" . htmlspecialchars($row['image']) : "images/no-image.png";
          $statusClass = "";
          $status = $row['status'];
          if (strtolower($status) === "resolved") $statusClass = "status-glow-resolved";
          else if (strtolower($status) === "being resolved") $statusClass = "status-glow-being-resolved";
          else if (strtolower($status) === "unresolved") $statusClass = "status-glow-unresolved";

          echo "<div class='complaint-card' 
                    data-id='{$row['complaint_id']}' 
                    data-title='" . htmlspecialchars($row['title'], ENT_QUOTES) . "' 
                    data-description='" . htmlspecialchars($row['description'], ENT_QUOTES) . "' 
                    data-location='" . htmlspecialchars($row['location'], ENT_QUOTES) . "' 
                    data-votes='{$row['votes']}' 
                    data-status='" . htmlspecialchars($row['status'], ENT_QUOTES) . "'
                    data-image='{$imagePath}'
                    data-email='" . htmlspecialchars($row['user_email'], ENT_QUOTES) . "'
                    data-name='" . htmlspecialchars($row['complainer_name'], ENT_QUOTES) . "'
                    data-contact='" . htmlspecialchars($row['complainer_contact'], ENT_QUOTES) . "'>
                    <img src='{$imagePath}' alt='Complaint Image' loading='lazy'>
                    <div class='complaint-info'>
                      <div class='title-votes-row'>
                        <div class='complaint-title'>" . htmlspecialchars($row['title']) . "</div>
                        <div class='complaint-votes'>{$row['votes']} people faced this issue</div>
                      </div>
                      <div class='complaint-location'>Location: " . htmlspecialchars($row['location']) . "</div>
                      <select class='status-dropdown {$statusClass}'>
                        <option " . ($status=='Resolved'?'selected':'') . ">Resolved</option>
                        <option " . ($status=='Being Resolved'?'selected':'') . ">Being Resolved</option>
                        <option " . ($status=='Unresolved'?'selected':'') . ">Unresolved</option>
                      </select>
                    </div>
                  </div>";
        }
      } else {
        echo "<p>No complaints found for your sector.</p>";
      }
      ?>
    </div>
  </section>
</main>

<div class="modal" id="complaintModal">
  <div class="modal-content">
    <span class="modal-close" id="modalClose">&times;</span>
    <img id="modalImage" src="" alt="Complaint Image" />
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
  <p>© 2025 City Complaint Portal</p>
</footer>

<script>
function updateStatusGlow(select) {
  select.classList.remove('status-glow-resolved', 'status-glow-being-resolved', 'status-glow-unresolved');
  const val = select.value.toLowerCase();
  if(val === 'resolved') select.classList.add('status-glow-resolved');
  else if(val === 'being resolved') select.classList.add('status-glow-being-resolved');
  else if(val === 'unresolved') select.classList.add('status-glow-unresolved');
}

document.querySelectorAll('.status-dropdown').forEach(select => {
  updateStatusGlow(select);
  select.addEventListener('change', async function() {
    updateStatusGlow(this);
    const complaintId = this.closest('.complaint-card').dataset.id;
    const newStatus = this.value;
    const response = await fetch('update_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${complaintId}&status=${encodeURIComponent(newStatus)}`
    });
    const result = await response.json();
    if (!result.success) alert('Failed to update status.');
  });
});

const modal = document.getElementById('complaintModal');
const modalContent = modal.querySelector('.modal-content');
const closeModal = document.getElementById('modalClose');

document.querySelectorAll('.complaint-card').forEach(card => {
  card.addEventListener('click', function(e) {
    if (e.target.classList.contains('status-dropdown')) return;
    const imgSrc = this.dataset.image;
    document.getElementById('modalImage').src = imgSrc;
    document.getElementById('modalTitle').textContent = this.dataset.title;
    document.getElementById('modalDesc').textContent = this.dataset.description;
    document.getElementById('modalLoc').textContent = this.dataset.location;
    document.getElementById('modalVotes').textContent = this.dataset.votes + ' people faced this issue';
    document.getElementById('modalStatus').textContent = this.dataset.status;
    const statusLower = this.dataset.status.toLowerCase();
    document.getElementById('modalStatus').style.color = statusLower === 'resolved' ? '#22c55e' :
      statusLower === 'being resolved' ? '#eab308' : '#ef4444';
    document.getElementById('modalEmail').textContent = this.dataset.email;
    document.getElementById('modalName').textContent = this.dataset.name;
    document.getElementById('modalContact').textContent = this.dataset.contact;

    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
  });
});

function closeModalFn() {
  modal.classList.remove('show');
  setTimeout(() => { modal.style.display = 'none'; }, 300);
}
closeModal.addEventListener('click', closeModalFn);
modal.addEventListener('click', e => { if (e.target === modal) closeModalFn(); });
</script>
</body>
</html>
