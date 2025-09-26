<?php
require_once 'auth_middleware.php'; // Protect this page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Basic styles for layout */
        body { font-family: sans-serif; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; position: fixed; height: 100%; padding-top: 20px; }
        .sidebar h2 { text-align: center; }
        .sidebar ul { list-style-type: none; padding: 0; }
        .sidebar ul li a { display: block; color: white; padding: 15px 20px; text-decoration: none; }
        .sidebar ul li a:hover { background: #555; }
        .main-content { margin-left: 250px; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .logout-btn { background: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; text-decoration: none; }
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
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <hr>

    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>

    <h2>Portfolio Overview</h2>
    <p>Welcome to the admin panel. From here, you can manage all the content on your portfolio website.</p>
    <p>Use the navigation on the left to edit different sections of your portfolio.</p>

</div>

</body>
</html>