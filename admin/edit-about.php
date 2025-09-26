<?php
require_once 'auth_middleware.php';
require_once '../config/config.php';

$message = '';
$error = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle file upload
        $photo_url = $_POST['current_photo_url']; // Keep old photo if new one isn't uploaded
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = "../assets/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $target_file = $target_dir . basename($_FILES["photo"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                    $photo_url = "assets/" . basename($_FILES["photo"]["name"]);
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error = "File is not an image.";
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare('UPDATE about_me SET name = ?, photo_url = ?, tagline = ?, bio = ?, education = ?, philosophy = ?, email = ?, linkedin_url = ?, phone = ? WHERE id = 1');
            $stmt->execute([
                $_POST['name'],
                $photo_url,
                $_POST['tagline'],
                $_POST['bio'],
                $_POST['education'],
                $_POST['philosophy'],
                $_POST['email'],
                $_POST['linkedin_url'],
                $_POST['phone']
            ]);
            $message = "About Me section updated successfully!";
        }
    }

    // Fetch current data
    $stmt = $pdo->query('SELECT * FROM about_me WHERE id = 1');
    $about = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Me</title>
    <style>
        body { font-family: sans-serif; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; position: fixed; height: 100%; padding-top: 20px; }
        .sidebar h2 { text-align: center; }
        .sidebar ul { list-style-type: none; padding: 0; }
        .sidebar ul li a { display: block; color: white; padding: 15px 20px; text-decoration: none; }
        .sidebar ul li a:hover { background: #555; }
        .main-content { margin-left: 250px; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .logout-btn { background: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; text-decoration: none; }
        .form-container { max-width: 800px; margin-top: 20px; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type="text"], textarea { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        textarea { min-height: 100px; }
        button { padding: 0.75rem 1.5rem; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="edit-about.php">About Me</a></li>
        <li><a href="edit-experience.php">Experience</a></li>
        <li><a href="edit-skills.php">Skills</a></li>
        <li><a href="edit-projects.php">Projects</a></li>
        <li><a href="manage-documents.php">Documents</a></li>
        <li><a href="manage-passwords.php">Download Passwords</a></li>
        <li><a href="settings.php">Settings</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Edit About Me</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <hr>

    <div class="form-container">
        <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

        <form action="edit-about.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($about['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="tagline">Tagline</label>
                <input type="text" id="tagline" name="tagline" value="<?php echo htmlspecialchars($about['tagline']); ?>">
            </div>
            <div class="form-group">
                <label for="photo">Photo</label>
                <input type="file" id="photo" name="photo">
                <input type="hidden" name="current_photo_url" value="<?php echo htmlspecialchars($about['photo_url']); ?>">
                <p>Current photo: <img src="../<?php echo htmlspecialchars($about['photo_url']); ?>" alt="Current Photo" width="100"></p>
            </div>
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio"><?php echo htmlspecialchars($about['bio']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="education">Education</label>
                <textarea id="education" name="education"><?php echo htmlspecialchars($about['education']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="philosophy">Teaching Philosophy</label>
                <textarea id="philosophy" name="philosophy"><?php echo htmlspecialchars($about['philosophy']); ?></textarea>
            </div>
            <hr>
            <h3>Contact Information</h3>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($about['email']); ?>">
            </div>
            <div class="form-group">
                <label for="linkedin_url">LinkedIn URL</label>
                <input type="text" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($about['linkedin_url']); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($about['phone']); ?>">
            </div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>