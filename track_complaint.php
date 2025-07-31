<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");

// For resolved percentage (from second file)
$resolved_percentage = 0;
if (!$conn->connect_error) {
    $total_sql = "SELECT COUNT(*) AS total FROM complaints";
    $resolved_sql = "SELECT COUNT(*) AS resolved FROM complaints WHERE status='resolved'";

    $total_result = $conn->query($total_sql);
    $resolved_result = $conn->query($resolved_sql);

    if ($total_result && $resolved_result) {
        $total = $total_result->fetch_assoc()['total'];
        $resolved = $resolved_result->fetch_assoc()['resolved'];
        if ($total > 0) {
            $resolved_percentage = round(($resolved / $total) * 100);
        }
    }
}

// Fetch complaints for the logged-in user
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
a {
  color: #a78bfa;
  text-decoration: none;
  transition: color 0.3s ease;
}
a:hover {
  color: #8b5cf6;
}

/* NAVBAR (copied from first file) */
.navbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  background-color: #111;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.8rem 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
  z-index: 1000;
}
.navbar .left {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}
.navbar .logo {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #a78bfa;
  box-shadow: 0 0 10px #a78bfa88;
}
.navbar h1 {
  color: #a78bfa;
  font-size: 1.5rem;
}
.navbar nav a {
  margin-left: 1.5rem;
  font-weight: 600;
  font-size: 1.1rem;
}

/* HEADER */
header {
  background: url('images/backdrop.jpeg') no-repeat center center/cover;
  padding: 8rem 2rem 3rem;
  margin-top: 70px;
}
header h2 {
  font-size: 2rem;
  color: #fff;
  margin-bottom: 0.5rem;
}

/* MAIN COMPLAINTS GRID */
main {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 1rem;
}
.complaints-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}
.complaint-card {
  background: #1a1a1a;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 0 15px #8b5cf644;
  transition: box-shadow 0.3s ease;
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
  cursor: default;
}
.complaint-card:hover {
  box-shadow: 0 0 35px #a78bfaaa;
}
.complaint-card img {
  width: 250px;
  height: auto;
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
.complaint-title {
  color: #a78bfa;
  font-size: 1.2rem;
  font-weight: 600;
}
.complaint-location {
  color: #aaa;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}
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

footer {
  text-align: center;
  font-size: 0.9rem;
  color: #aaa;
  padding: 2rem 1rem;
  background-color: #111;
  margin-top: 3rem;
}
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
  <div class="left">
    <img src="images/logo.png" class="logo" />
    <h1>City Portal</h1>
  </div>
  <nav>
    <a href="home.php">Home</a>
    <a href="issues.php">Recent Complaints</a>
    <a href="account.php">Account</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>

<!-- HEADER -->
<header>
  <h2>Your Registered Complaints</h2>
</header>

<!-- MAIN SECTION -->
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

              echo "<div class='complaint-card'>
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

<footer>
  &copy; <?php echo date("Y"); ?> City Portal
</footer>

</body>
</html>
