<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>City Portal - Home</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background-color: #0a0a0a;
    color: #e0e0e0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  a {
    color: #a78bfa;
    text-decoration: none;
    transition: color 0.3s ease;
  }
  a:hover { color: #8b5cf6; }

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
  .navbar .left { display: flex; align-items: center; gap: 0.8rem; }
  .navbar .logo {
    width: 45px; height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #a78bfa;
    box-shadow: 0 0 10px #a78bfa88;
  }
  .navbar h1 { color: #a78bfa; font-size: 1.5rem; }
  .navbar nav a { margin-left: 1.5rem; font-weight: 600; font-size: 1.1rem; }

  header {
    background: url('images/backdrop.jpeg') no-repeat center center/cover;
    padding: 8rem 2rem 3rem 2rem;
    text-align: left;
    margin-top: 70px;
  }
  header h2 { font-size: 2rem; color: #fff; margin-bottom: 0.5rem; }
  header p { color: #ddd; max-width: 600px; margin-bottom: 1rem; }
  .buttons { margin-top: 1rem; }
  .btn {
    background: #8b5cf6;
    color: #0a0a0a;
    padding: 12px 28px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    box-shadow: 0 0 15px #8b5cf688;
    margin-right: 1rem;
    transition: box-shadow 0.3s ease, background 0.3s ease;
    cursor: pointer;
  }
  .btn:hover { background: #7c3aed; box-shadow: 0 0 30px #7c3aedcc; }

  .header-bottom-stats {
    margin-top: 2rem;
    display: flex;
    gap: 2rem;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
  }

  main { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }

  .how-it-works { margin-bottom: 3rem; }
  .how-it-works h2 { color: #fff; margin-bottom: 1rem; }

  .features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 2rem;
    padding-bottom: 3rem;
  }
  .feature {
    background: #111;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 0 20px #8b5cf666;
    transition: box-shadow 0.3s ease;
  }
  .feature:hover { box-shadow: 0 0 35px #a78bfaaa; }
  .feature-icon { font-size: 2.5rem; color: #a78bfa; }
  .feature h4 { margin: 1rem 0 0.5rem; color: #a78bfa; }

  .complaints-section { margin-top: 3rem; }
  .complaints-section h2 { margin-bottom: 1.5rem; color: #fff; }
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
    transition: box-shadow 0.3s ease, transform 0.6s ease, opacity 0.6s ease;
    position: relative;
    display: block;
    opacity: 0;
    transform: translateY(30px);
  }
  .complaint-card.show {
    opacity: 1;
    transform: translateY(0);
  }
  .complaint-card:hover { box-shadow: 0 0 35px #a78bfaaa; }
  .complaint-left { display: flex; flex-direction: column; gap: 0.3rem; }
  .title-votes-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 1rem;
  }
  .complaint-title { color: #a78bfa; font-size: 1.2rem; font-weight: 600; }
  .complaint-votes { font-size: 0.85rem; color: #ccc; white-space: nowrap; }
  .complaint-status {
    padding: 4px 12px;
    font-size: 0.8rem;
    border-radius: 6px;
    font-weight: bold;
    color: #000;
    min-width: max-content;
  }
  .complaint-status.resolved { background: #0f0; box-shadow: 0 0 12px 2px #00ff00aa; }
  .complaint-status.being-resolved { background: #ff0; box-shadow: 0 0 12px 2px #ffff00aa; }
  .complaint-status.unresolved { background: #f00; box-shadow: 0 0 12px 2px #ff0000aa; }
  .complaint-card p { margin-top: 0.5rem; color: #ddd; }
  .complaint-location { color: #aaa; font-size: 0.9rem; margin-top: 0.1rem; }
  .action-container {
    position: absolute;
    bottom: 10px;
    right: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .upvote-btn {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #a78bfa;
    padding: 0;
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

<div class="navbar">
  <div class="left">
    <img src="images/logo.png" class="logo" />
    <h1>City Portal</h1>
  </div>
  <nav>
    <a href="home.php">Home</a>
    <a href="issues.php">Recent Complaints</a>
    <a href="account.php">Account</a>
  </nav>
</div>

<header>
  <h2>Online Complaint Portal</h2>
  <p>Building Better Nepal<br>One Report at a Time</p>
  <p>Help local authorities address urban problems faster. Report infrastructure issues, safety hazards, and community concerns to create safer, cleaner, and more sustainable cities.</p>
  <div class="buttons">
    <button class="btn" onclick="location.href='report_issue.php'">Report an Issue</button>
    <button class="btn" onclick="location.href='track_complaint.php'">Track Your Complaint</button>
  </div>
  <div class="header-bottom-stats">
    <div>📈 <span id="resolved-percentage">0</span>% Issues Resolved</div>
    <div>🌍 <span>UN SDGs Aligned</span></div>
  </div>
</header>

<main>
  <section class="how-it-works">
    <h2>How It Works</h2>
    <div class="features">
      <div class="feature"><div class="feature-icon">🛣️</div><h4>Report Issues</h4><p>Submit complaints about roads, infrastructure, and safety hazards with details.</p></div>
      <div class="feature"><div class="feature-icon">📸</div><h4>Photo Evidence</h4><p>Upload photos to show the problem and help authorities understand severity.</p></div>
      <div class="feature"><div class="feature-icon">📍</div><h4>Location Tracking</h4><p>GPS helps locate and address issues faster.</p></div>
      <div class="feature"><div class="feature-icon">🔄</div><h4>Track Progress</h4><p>View real-time updates on complaint status.</p></div>
      <div class="feature"><div class="feature-icon">🌍</div><h4>Community Impact</h4><p>Track collective improvement stats.</p></div>
      <div class="feature"><div class="feature-icon">🔒</div><h4>Secure & Anonymous</h4><p>Choose to report anonymously while ensuring accountability.</p></div>
    </div>
  </section>

  <section class="complaints-section">
    <h2>Recent Complaints</h2>
    <div class="complaints-grid">
      <?php
      if (!$conn->connect_error) {
          $sql = "SELECT title, description, location, votes, status FROM complaints ORDER BY date_reported DESC LIMIT 6";
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  $status_text = strtolower($row['status']);
                  if ($status_text === 'resolved') {
                      $status_class = 'resolved';
                  } elseif ($status_text === 'being resolved') {
                      $status_class = 'being-resolved';
                  } else {
                      $status_class = 'unresolved';
                  }

                  echo "
                  <div class='complaint-card'>
                      <div class='complaint-left'>
                          <div class='title-votes-row'>
                              <div class='complaint-title'>" . htmlspecialchars($row['title']) . "</div>
                              <div class='complaint-votes'>" . intval($row['votes']) . " people have faced this issue</div>
                          </div>
                          <p>" . htmlspecialchars($row['description']) . "</p>
                          <div class='complaint-location'>Location: " . htmlspecialchars($row['location']) . "</div>
                      </div>
                      <div class='action-container'>
                          <div class='complaint-status $status_class'>" . htmlspecialchars($row['status']) . "</div>
                          <button class='upvote-btn'>↑</button>
                      </div>
                  </div>";
              }
          } else {
              echo "<p>No recent complaints found.</p>";
          }
      } else {
          echo "<p style='color:red;'>Failed to connect to database.</p>";
      }
      $conn->close();
      ?>
    </div>
  </section>
</main>

<footer>
  <p>City Complaint Portal • Aligned with UN SDGs</p>
  <p>complain.portal.paradox@gmail.com | +977-986-0906232</p>
  <p>© 2025 City Complaint Portal</p>
</footer>

<script>
const cards = document.querySelectorAll('.complaint-card');
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('show');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.2 });
cards.forEach(card => observer.observe(card));

const target = <?php echo $resolved_percentage; ?>;
let current = 0;
const el = document.getElementById('resolved-percentage');
const speed = 20;

const counter = setInterval(() => {
  current++;
  el.textContent = current;
  if (current >= target) clearInterval(counter);
}, speed);
</script>

</body>
</html>
