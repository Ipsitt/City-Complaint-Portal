<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "complain_portal");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $sector = trim($_POST['sector'] ?? '');
    $user_email = $_SESSION['user_email'];
    
    // Validate required fields
    if (!$title || !$description || !$location || !$sector) {
        $message = "All fields are required.";
    } elseif (strlen($title) < 5) {
        $message = "Title must be at least 5 characters long.";
    } elseif (strlen($description) < 10) {
        $message = "Description must be at least 10 characters long.";
    } else {
        // Handle image upload
        $image_name = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $message = "Only JPG, PNG, and GIF images are allowed.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                $message = "Image size must be less than 5MB.";
            } else {
                $upload_dir = 'complaint_images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $image_name;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $message = "Failed to upload image. Please try again.";
                }
            }
        }
        
        if (!$message) {
            // Check for duplicate submission (same user, same title, same location within 5 minutes)
            $check_duplicate = $conn->prepare("SELECT complaint_id FROM complaints WHERE user_email = ? AND title = ? AND location = ? AND date_reported > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
            $check_duplicate->bind_param("sss", $user_email, $title, $location);
            $check_duplicate->execute();
            $duplicate_result = $check_duplicate->get_result();
            
            if ($duplicate_result->num_rows > 0) {
                $message = "This complaint has already been submitted recently. Please wait a few minutes before submitting again.";
            } else {
                // Insert complaint into database
                $stmt = $conn->prepare("INSERT INTO complaints (title, description, location, sector, user_email, image, date_reported, status, votes) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Unresolved', 0)");
                $stmt->bind_param("ssssss", $title, $description, $location, $sector, $user_email, $image_name);
                
                if ($stmt->execute()) {
                    $success = true;
                    $message = "Complaint submitted successfully! Your issue has been reported.";
                    // Clear form data
                    $_POST = array();
                } else {
                    $message = "Failed to submit complaint. Please try again.";
                }
                $stmt->close();
            }
            $check_duplicate->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Issue - City Complaint Portal</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #0a0a0a;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
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
            color: #a78bfa;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }
        
        .navbar nav a:hover {
            color: #8b5cf6;
        }
        
        .container {
            max-width: 800px;
            margin: 100px auto 2rem;
            padding: 0 1rem;
        }
        
        .form-container {
            background: #111;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 30px #8b5cf6cc;
            border: 1px solid #333;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-header h2 {
            color: #a78bfa;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .form-header p {
            color: #ccc;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #a78bfa;
            font-weight: 600;
            font-size: 1rem;
        }
        
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #333;
            border-radius: 8px;
            background: #1c1c1c;
            color: #eee;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #a78bfa;
            box-shadow: 0 0 10px #a78bfa44;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-upload-label {
            display: block;
            padding: 15px;
            border: 2px dashed #a78bfa;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #1c1c1c;
        }
        
        .file-upload-label:hover {
            border-color: #8b5cf6;
            background: #1a1a1a;
            box-shadow: 0 0 15px #a78bfa44;
        }
        
        .file-upload-label i {
            font-size: 2rem;
            color: #a78bfa;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .location-group {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .btn {
            background: #8b5cf6;
            color: #0a0a0a;
            padding: 14px 28px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 0 15px #8b5cf688;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            background: #7c3aed;
            box-shadow: 0 0 30px #7c3aedcc;
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            background: #666;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        
        .btn-location {
            background: #333;
            color: #a78bfa;
            padding: 12px 20px;
            font-size: 1rem;
            white-space: nowrap;
        }
        
        .btn-location:hover {
            background: #444;
            box-shadow: 0 0 15px #a78bfa44;
        }
        
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-align: center;
        }
        
        .message.success {
            background: #1a472a;
            color: #4ade80;
            border: 1px solid #4ade80;
        }
        
        .message.error {
            background: #4c1d1d;
            color: #f87171;
            border: 1px solid #f87171;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #333;
            color: #aaa;
        }
        
        .form-footer a {
            color: #a78bfa;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
        }
        
        .character-count {
            font-size: 0.85rem;
            color: #888;
            text-align: right;
            margin-top: 0.25rem;
        }
        
        .required {
            color: #f87171;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 80px auto 1rem;
                padding: 0 0.5rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .location-group {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                padding: 0.8rem 1rem;
            }
            
            .navbar nav a {
                margin-left: 1rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="left">
            <img src="images/logo.png" class="logo" alt="City Portal Logo">
            <h1>City Portal</h1>
        </div>
        <nav>
            <a href="home.php">Home</a>
            <a href="gov_dashboard.php">Dashboard</a>
        </nav>
    </div>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2>Report an Issue</h2>
                <p>Help us make our city better by reporting infrastructure problems, safety hazards, and community concerns.</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?= $success ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="report_issue.php" enctype="multipart/form-data" id="reportForm">
                <div class="form-group">
                    <label for="title">Issue Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                           placeholder="Brief description of the issue (e.g., Pothole on Main Street)"
                           maxlength="100">
                    <div class="character-count">
                        <span id="titleCount">0</span>/100 characters
                    </div>
                </div>

                <div class="form-group">
                    <label for="sector">Issue Category <span class="required">*</span></label>
                    <select id="sector" name="sector" required>
                        <option value="">Select a category</option>
                        <option value="Roads" <?= ($_POST['sector'] ?? '') === 'Roads' ? 'selected' : '' ?>>Roads & Infrastructure</option>
                        <option value="Water" <?= ($_POST['sector'] ?? '') === 'Water' ? 'selected' : '' ?>>Water & Sanitation</option>
                        <option value="Electricity" <?= ($_POST['sector'] ?? '') === 'Electricity' ? 'selected' : '' ?>>Electricity & Street Lights</option>
                        <option value="Waste" <?= ($_POST['sector'] ?? '') === 'Waste' ? 'selected' : '' ?>>Waste Management</option>
                        <option value="Transport" <?= ($_POST['sector'] ?? '') === 'Transport' ? 'selected' : '' ?>>Public Transport</option>
                        <option value="Other" <?= ($_POST['sector'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Detailed Description <span class="required">*</span></label>
                    <textarea id="description" name="description" required 
                              placeholder="Please provide a detailed description of the issue, including when you first noticed it, its severity, and any potential safety concerns..."
                              maxlength="1000"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <div class="character-count">
                        <span id="descCount">0</span>/1000 characters
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location <span class="required">*</span></label>
                    <div class="location-group">
                        <input type="text" id="location" name="location" required 
                               value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                               placeholder="Street address, landmark, or area description">
                        <button type="button" class="btn btn-location" onclick="getCurrentLocation()">
                            📍 Use My Location
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Photo Evidence (Optional)</label>
                    <div class="file-upload">
                        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                        <label for="image" class="file-upload-label">
                            <i>📸</i>
                            <span>Click to upload an image or drag and drop</span>
                            <br>
                            <small>JPG, PNG, GIF up to 5MB</small>
                        </label>
                    </div>
                    <img id="imagePreview" class="preview-image" alt="Preview">
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    Submit Report
                </button>
            </form>

            <div class="form-footer">
                <p>Your report will be reviewed by local authorities and assigned to the appropriate department.</p>
                <p>Track your complaint status on the <a href="home.php">home page</a>.</p>
            </div>
        </div>
    </div>

    <script>
        // Character count functionality
        document.getElementById('title').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('titleCount').textContent = count;
        });

        document.getElementById('description').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('descCount').textContent = count;
        });

        // Initialize character counts
        document.getElementById('titleCount').textContent = document.getElementById('title').value.length;
        document.getElementById('descCount').textContent = document.getElementById('description').value.length;

        // Image preview functionality
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // Location functionality
        function getCurrentLocation() {
            const locationInput = document.getElementById('location');
            const locationBtn = document.querySelector('.btn-location');
            
            if (navigator.geolocation) {
                locationBtn.textContent = '📍 Getting Location...';
                locationBtn.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        
                        // Use reverse geocoding to get address (simplified)
                        // In a real application, you'd use a geocoding service
                        locationInput.value = `GPS: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                        locationBtn.textContent = '📍 Location Set';
                        
                        setTimeout(() => {
                            locationBtn.textContent = '📍 Use My Location';
                            locationBtn.disabled = false;
                        }, 2000);
                    },
                    function(error) {
                        locationBtn.textContent = '📍 Location Failed';
                        alert('Unable to get your location. Please enter it manually.');
                        
                        setTimeout(() => {
                            locationBtn.textContent = '📍 Use My Location';
                            locationBtn.disabled = false;
                        }, 2000);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            } else {
                alert('Geolocation is not supported by this browser. Please enter location manually.');
            }
        }

        // Form validation
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const location = document.getElementById('location').value.trim();
            const sector = document.getElementById('sector').value;
            const submitBtn = document.getElementById('submitBtn');
            
            if (!title || !description || !location || !sector) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Title must be at least 5 characters long.');
                return false;
            }
            
            if (description.length < 10) {
                e.preventDefault();
                alert('Description must be at least 10 characters long.');
                return false;
            }
            
            // Prevent double submission
            if (submitBtn.disabled) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            submitBtn.textContent = 'Submitting...';
            submitBtn.disabled = true;
            
            // Re-enable button after 10 seconds in case of error
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.textContent = 'Submit Report';
                    submitBtn.disabled = false;
                }
            }, 10000);
        });

        // Drag and drop functionality
        const fileUpload = document.querySelector('.file-upload');
        const fileInput = document.getElementById('image');
        const fileLabel = document.querySelector('.file-upload-label');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUpload.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            fileLabel.style.borderColor = '#8b5cf6';
            fileLabel.style.background = '#1a1a1a';
        }

        function unhighlight(e) {
            fileLabel.style.borderColor = '#a78bfa';
            fileLabel.style.background = '#1c1c1c';
        }

        fileUpload.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            previewImage(fileInput);
        }
    </script>
</body>
</html>
