<?php
session_start();
require_once '../config/config.php';
require_once 'auth_middleware.php'; // Ensure user is logged in

$message = '';
$error = '';
$is_first_login = isset($_GET['first_login']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $user_id = $_SESSION['user_id'];

            // Update password and set first_login to false
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, first_login = FALSE WHERE id = ?');
            $stmt->execute([$new_password_hash, $user_id]);

            if ($is_first_login) {
                header('Location: dashboard.php?success=Password changed successfully!');
                exit;
            } else {
                $message = "Password changed successfully!";
            }

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f0f0; }
        .change-password-box { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 400px; }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 0.75rem; background-color: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background-color: #0056b3; }
        .error { color: red; text-align: center; margin-bottom: 1rem; }
        .success { color: green; text-align: center; margin-bottom: 1rem; }
        .info { color: #0056b3; text-align: center; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="change-password-box">
        <h1>Change Password</h1>
        <?php if ($is_first_login): ?>
            <p class="info">This is your first login. Please set a new password to continue.</p>
        <?php endif; ?>
        <?php if ($message): ?>
            <p class="success"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Change Password</button>
        </form>
        <?php if (!$is_first_login): ?>
            <p style="text-align: center; margin-top: 1rem;"><a href="dashboard.php">Back to Dashboard</a></p>
        <?php endif; ?>
    </div>
</body>
</html>