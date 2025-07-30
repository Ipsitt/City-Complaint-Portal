<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$user_email = $_SESSION['user_email'];

$sql = "SELECT c.*, u.name AS complainer_name, u.contact AS complainer_contact 
        FROM complaints c
        JOIN user u ON c.user_email = u.user_email
        WHERE c.user_email = ? 
        ORDER BY c.date_reported DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

function getStatusClass($status) {
    $status = strtolower($status);
    if ($status === "resolved") return "resolved";
    if ($status === "being resolved") return "being-resolved";
    if ($status === "unresolved") return "unresolved";
    return "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Your Complaints - City Portal</title>
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
.complaint-info {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
}
.details-section {
  display: flex;
  flex-direction: column;
}
.complaint-title { color: #a78bfa; font-size: 1.2rem; font-weight: 600; }
.complaint-location { color: #aaa; font-size: 0.9rem; margin-top: 0.5rem; }
.complaint-description {
  color: #ccc;
  font-size: 0.9rem;
  margin-top: 0.5rem;
  line-height: 1.4;
}

.status-votes {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
.complaint-votes {
  font-size: 0.85rem;
  color: #ccc;
  margin-bottom: 0.4rem;
}

.status-btn {
  padding: 6px 16px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 10px;
  border: none;
  cursor: default;
  color: #000;
  min-width: 130px;
  text-align: center;
}
.status-btn.resolved {
  background-color: #22c55e;
  box-shadow: 0 0 10px 2px #22c55e88;
}
.status-btn.being-resolved {
  background-color: #eab308;
  box-shadow: 0 0 10px 2px #eab30888;
}
.status-btn.unresolved {
  background-color: #ef4444;
  box-shadow: 0 0 10px 2px #ef444488;
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

footer {
  text-align: center;
  font-size: 0.9rem;
  color: #aaa;
  padding: 2rem 1rem;
  background: #111;
  margin-top: 3rem;
}
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
    <a href="account.php">Account</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>

<header>
  <h2>Your Registered Complaints</h2>
</header>

<main>
  <section class="complaints-section">
    <h2>Your Complaints</h2>
    <div class="complaints-grid">
      <?php
      if ($complaints) {
          foreach ($complaints as $row) {
              $imagePath = $row['image'] ? "complaint_images/" . htmlspecialchars($row['image']) : "images/no-image.png";
              $statusClass = getStatusClass($row['status']);
              $statusText = htmlspecialchars($row['status']);

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
                      <img src='$imagePath' alt='Complaint Image'>
                      <div class='complaint-info'>
                        <div class='details-section'>
                          <span class='complaint-title'>" . htmlspecialchars($row['title']) . "</span>
                          <span class='complaint-description'>Description: " . htmlspecialchars($row['description']) . "</span>
                          <span class='complaint-location'>Location: " . htmlspecialchars($row['location']) . "</span>
                        </div>
                        <div class='status-votes'>
                          <span class='complaint-votes'>Votes: {$row['votes']}</span>
                          <button class='status-btn $statusClass' disabled>$statusText</button>
                        </div>
                      </div>
                    </div>";
          }
      } else {
          echo "<p>You haven't registered any complaints yet.</p>";
      }
      ?>
    </div>
  </section>
</main>

<div id="modal" class="modal" aria-hidden="true">
  <div class="modal-content" role="document">
    <button class="modal-close" aria-label="Close modal">&times;</button>
    <img src="" alt="Complaint Image" id="modal-image" />
    <div class="modal-info">
      <h2 id="modal-title"></h2>
      <p><strong>Description:</strong> <span id="modal-description"></span></p>
      <p><strong>Location:</strong> <span id="modal-location"></span></p>
      <p><strong>Votes:</strong> <span id="modal-votes"></span></p>
      <p><strong>Status:</strong> <span id="modal-status"></span></p>
      <hr />
      <p><strong>Reported By:</strong> <span id="modal-name"></span></p>
      <p><strong>Contact:</strong> <span id="modal-contact"></span></p>
      <p><strong>Email:</strong> <span id="modal-email"></span></p>
    </div>
  </div>
</div>

<script>
const modal = document.getElementById('modal');
const modalImage = document.getElementById('modal-image');
const modalTitle = document.getElementById('modal-title');
const modalDescription = document.getElementById('modal-description');
const modalLocation = document.getElementById('modal-location');
const modalVotes = document.getElementById('modal-votes');
const modalStatus = document.getElementById('modal-status');
const modalName = document.getElementById('modal-name');
const modalContact = document.getElementById('modal-contact');
const modalEmail = document.getElementById('modal-email');
const modalCloseBtn = document.querySelector('.modal-close');

document.querySelectorAll('.complaint-card').forEach(card => {
  card.addEventListener('click', () => {
    modalImage.src = card.dataset.image;
    modalTitle.textContent = card.dataset.title;
    modalDescription.textContent = card.dataset.description;
    modalLocation.textContent = card.dataset.location;
    modalVotes.textContent = card.dataset.votes;
    modalStatus.textContent = card.dataset.status;
    modalName.textContent = card.dataset.name;
    modalContact.textContent = card.dataset.contact;
    modalEmail.textContent = card.dataset.email;

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    modalCloseBtn.focus();
  });
});

modalCloseBtn.addEventListener('click', () => {
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden', 'true');
});

window.addEventListener('click', e => {
  if (e.target === modal) {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  }
});

window.addEventListener('keydown', e => {
  if (e.key === "Escape" && modal.style.display === 'flex') {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  }
});
</script>

<footer>
  &copy; <?php echo date("Y"); ?> City Portal
</footer>
</body>
</html>
