<?php
require_once 'auth_middleware.php'; // Protect this page
require_once '../config/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password_hash'])) {
            $error = "Your current password is not correct.";
        } elseif ($new_password !== $confirm_password) {
            $error = "The new passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error = "New password must be at least 8 characters long.";
        } else {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$new_password_hash, $_SESSION['user_id']]);
            $message = "Password updated successfully!";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
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
        .form-container { max-width: 600px; margin-top: 20px; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
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
        <h1>Settings</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <hr>

    <div class="form-container">
        <h3>Change Password</h3>
        <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <form action="settings.php" method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Update Password</button>
        </form>
    </div>
</div>

</body>
</html>