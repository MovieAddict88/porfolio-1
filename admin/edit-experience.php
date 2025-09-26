<?php
require_once 'auth_middleware.php';
require_once '../config/config.php';

$message = '';
$error = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_experience'])) {
            $stmt = $pdo->prepare('INSERT INTO experience (title, institution, start_year, end_year, description) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$_POST['title'], $_POST['institution'], $_POST['start_year'], $_POST['end_year'], $_POST['description']]);
            $message = "Experience added successfully!";
        } elseif (isset($_POST['update_experience'])) {
            $stmt = $pdo->prepare('UPDATE experience SET title = ?, institution = ?, start_year = ?, end_year = ?, description = ? WHERE id = ?');
            $stmt->execute([$_POST['title'], $_POST['institution'], $_POST['start_year'], $_POST['end_year'], $_POST['description'], $_POST['id']]);
            $message = "Experience updated successfully!";
        } elseif (isset($_POST['delete_experience'])) {
            $stmt = $pdo->prepare('DELETE FROM experience WHERE id = ?');
            $stmt->execute([$_POST['id']]);
            $message = "Experience deleted successfully!";
        }
    }

    // Fetch all experience
    $exp_stmt = $pdo->query('SELECT * FROM experience ORDER BY start_year DESC');
    $experiences = $exp_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Experience</title>
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
        .form-container, .table-container { max-width: 800px; margin-top: 20px; background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type="text"], textarea { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 0.75rem 1.5rem; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .delete-btn { background-color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
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
        <h1>Edit Experience</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <hr>

    <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

    <div class="form-container">
        <h3>Add New Experience</h3>
        <form action="edit-experience.php" method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Institution</label>
                <input type="text" name="institution" required>
            </div>
            <div class="form-group">
                <label>Start Year</label>
                <input type="text" name="start_year" required>
            </div>
            <div class="form-group">
                <label>End Year</label>
                <input type="text" name="end_year" placeholder="e.g., Present" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            <button type="submit" name="add_experience">Add Experience</button>
        </form>
    </div>

    <div class="table-container">
        <h3>Existing Experience</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Institution</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($experiences as $exp): ?>
                <tr>
                    <form action="edit-experience.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $exp['id']; ?>">
                        <td><input type="text" name="title" value="<?php echo htmlspecialchars($exp['title']); ?>"></td>
                        <td><input type="text" name="institution" value="<?php echo htmlspecialchars($exp['institution']); ?>"></td>
                        <td>
                            <input type="text" name="start_year" value="<?php echo htmlspecialchars($exp['start_year']); ?>" style="width: 60px;"> -
                            <input type="text" name="end_year" value="<?php echo htmlspecialchars($exp['end_year']); ?>" style="width: 60px;">
                        </td>
                        <td>
                            <button type="submit" name="update_experience">Update</button>
                            <button type="submit" name="delete_experience" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                        </td>
                         <!-- Hidden description for update -->
                        <input type="hidden" name="description" value="<?php echo htmlspecialchars($exp['description']); ?>">
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>