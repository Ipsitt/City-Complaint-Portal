<?php
session_start();

/* ---------- DB ---------- */
$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) die("DB connection failed: ".$conn->connect_error);

/* ---------- SORT ORDER (public feed) ---------- */
$sql = "SELECT c.*, u.name AS complainer_name, u.contact AS complainer_contact
        FROM complaints c
        JOIN user u ON c.user_email = u.user_email
        ORDER BY c.date_reported DESC,
                 FIELD(c.status,'Unresolved','Being Resolved','Resolved'),
                 c.votes DESC";
$complaints = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$conn->close();

/* ---------- FLAIR DATA ---------- */
$flair = [
    'water'                => ['color' => '#0099ff', 'emoji' => '💧'],
    'electricity'          => ['color' => '#ffcc00', 'emoji' => '⚡'],
    'waste management'     => ['color' => '#33cc33', 'emoji' => '🗑️'],
    'roads and infrastructures' => ['color' => '#000',   'emoji' => '🏙️'],
    'public safety'        => ['color' => '#0066ff', 'emoji' => '🚔'],
    'transportation'       => ['color' => '#ff3333', 'emoji' => '🚌'],
];
$statusColor = [
    'Resolved'      => '#0f0',
    'In Progress'   => '#ff0',
    'Being Resolved'=> '#ff0', 
    'Unresolved'    => '#ff3333',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>City Issues</title>
<style>
:root{
    --bg:#0a0a0a; --card:#1a1a1a; --nav:#111111;
    --accent:#a78bfa; --hover:#8b5cf6; --text:#e0e0e0; --sec:#ccc;
}
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
body{margin:0;background:var(--bg);color:var(--text)}
.navbar{position:fixed;top:0;left:0;right:0;background:var(--nav);display:flex;justify-content:space-between;align-items:center;padding:.8rem 2rem;z-index:1000}
.navbar .left{display:flex;align-items:center;gap:.8rem}
.logo{width:45px;height:45px;border-radius:50%;border:2px solid var(--accent);box-shadow:0 0 10px #a78bfa88}
.navbar h1{color:var(--accent);font-size:1.5rem}
.navbar nav a{margin-left:1.5rem;color:var(--accent);text-decoration:none;font-weight:600}
.navbar nav a:hover{color:var(--hover)}
header{background:url('images/backdrop.jpeg') no-repeat center/cover;padding:8rem 2rem 3rem;margin-top:70px}
header h2{color:#fff;font-size:2rem;margin-bottom:.5rem}
main{max-width:1200px;margin:2rem auto;padding:0 1rem}
.complaints-grid{display:flex;flex-direction:column;gap:2rem}
.complaint-card{
    background:var(--card);padding:1.5rem;border-radius:10px;
    box-shadow:0 0 15px #8b5cf644;cursor:pointer;transition:box-shadow .3s;
    display:flex;gap:1.5rem;align-items:flex-start;position:relative;
}
.complaint-card:hover{box-shadow:0 0 35px var(--hover)}
.complaint-card img{width:250px;height:auto;border-radius:10px;object-fit:cover;flex-shrink:0}
.complaint-info{flex:1;display:flex;flex-direction:column}
.complaint-title{color:var(--accent);font-size:1.2rem;font-weight:600;margin-bottom:.2rem}
.complaint-votes{font-size:.85rem;color:var(--sec)}
.complaint-location{color:#aaa;font-size:.9rem;margin:.2rem 0}

/* hollow flairs */
.flair-container{
    position:absolute;top:.75rem;right:.75rem;display:flex;flex-direction:column;align-items:flex-end;gap:.4rem
}
.flair{
    padding:.35rem .8rem;border-radius:12px;border:1px solid;
    font-size:.9rem;font-weight:600;background:transparent;min-width:5.8rem;text-align:center
}

.modal{
    position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.8);
    display:none;justify-content:center;align-items:center;z-index:2000;padding:1rem;
}
.modal-content{
    background:var(--card);padding:2rem;border-radius:15px;max-width:900px;width:90%;
    color:var(--text);box-shadow:0 0 20px var(--accent);display:flex;gap:2rem;flex-wrap:wrap;
}
.modal-content img{max-width:350px;border-radius:10px;flex-shrink:0}
.modal-info{flex:1;display:flex;flex-direction:column;gap:.4rem}
.modal-info h2{margin:.2rem 0;color:var(--accent)}
.modal-close{position:absolute;top:15px;right:20px;font-size:24px;color:var(--text);cursor:pointer}
footer{text-align:center;font-size:.9rem;color:#aaa;padding:2rem 1rem;background:var(--nav);margin-top:3rem}
.modal-close{
    position:absolute;
    top:15px;
    right:20px;
    width:34px;
    height:34px;
    border-radius:50%;      /* neat white circle */
    background:#fff;        /* white background */
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:900;
    color:#000;             /* black bold × */
    cursor:pointer;
    border:none;
    line-height:1;
    z-index:1;
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
    <a href="logout.php">Logout</a>
  </nav>
</div>

<header>
  <h2>City Issues</h2>
</header>

<main>
    <div class="complaints-grid">
    <?php
    foreach ($complaints as $row) {
        $imagePath = $row['image'] ? "complaint_images/".htmlspecialchars($row['image']) : "images/no-image.png";
        $sec = strtolower($row['sector']);
        $fc = $flair[$sec] ?? ['color'=>'#888','emoji'=>'❓'];
        $sc = $statusColor[$row['status']] ?? '#888';
    ?>
        <div class="complaint-card"
             data-title="<?= htmlspecialchars($row['title']) ?>"
             data-description="<?= htmlspecialchars($row['description']) ?>"
             data-location="<?= htmlspecialchars($row['location']) ?>"
             data-votes="<?= $row['votes'] ?>"
             data-status="<?= htmlspecialchars($row['status']) ?>"
             data-image="<?= $imagePath ?>">
            <img src="<?= $imagePath ?>" alt="Complaint Image" loading="lazy">
            <div class="complaint-info">
                <div class="complaint-title"><?= htmlspecialchars($row['title']) ?></div>
                <div class="complaint-votes"><?= $row['votes'] ?> people faced this issue</div>
                <div class="complaint-location">Location: <?= htmlspecialchars($row['location']) ?></div>
            </div>
            <div class="flair-container">
                <div class="flair" style="color:<?= $fc['color'] ?>;border-color:<?= $fc['color'] ?>"><?= $fc['emoji'] ?> <?= htmlspecialchars(ucfirst($row['sector'])) ?></div>
                <div class="flair" style="color:<?= $sc ?>;border-color:<?= $sc ?>"><?= htmlspecialchars($row['status']) ?></div>
            </div>
        </div>
    <?php } ?>
    </div>
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
const modalCloseBtn = document.querySelector('.modal-close');

document.querySelectorAll('.complaint-card').forEach(card => {
  card.addEventListener('click', () => {
    modalImage.src = card.dataset.image;
    modalTitle.textContent = card.dataset.title;
    modalDescription.textContent = card.dataset.description;
    modalLocation.textContent = card.dataset.location;
    modalVotes.textContent = card.dataset.votes + ' people';
    modalStatus.textContent = card.dataset.status;
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