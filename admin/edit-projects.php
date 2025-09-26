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
        $media_urls = [];
        $current_media_url = $_POST['current_media_url'] ?? '';

        // Handle file uploads
        if (isset($_FILES['media']['name']) && is_array($_FILES['media']['name'])) {
            $target_dir = "../assets/projects/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            foreach ($_FILES['media']['name'] as $key => $name) {
                if ($_FILES['media']['error'][$key] == 0) {
                    $target_file = $target_dir . basename($name);
                    if (move_uploaded_file($_FILES['media']['tmp_name'][$key], $target_file)) {
                        $media_urls[] = "assets/projects/" . basename($name);
                    } else {
                        $error = "Sorry, there was an error uploading one of your files.";
                    }
                }
            }
        }

        $new_media_urls = implode(',', $media_urls);
        $final_media_url = $current_media_url;

        if (!empty($new_media_urls)) {
            if (!empty($final_media_url)) {
                $final_media_url .= ',' . $new_media_urls;
            } else {
                $final_media_url = $new_media_urls;
            }
        }

        if (empty($error)) {
            if (isset($_POST['add_project'])) {
                $stmt = $pdo->prepare('INSERT INTO projects (title, description, media_url) VALUES (?, ?, ?)');
                $stmt->execute([$_POST['title'], $_POST['description'], $final_media_url]);
                $message = "Project added successfully!";
            } elseif (isset($_POST['update_project'])) {
                $stmt = $pdo->prepare('UPDATE projects SET title = ?, description = ?, media_url = ? WHERE id = ?');
                $stmt->execute([$_POST['title'], $_POST['description'], $final_media_url, $_POST['id']]);
                $message = "Project updated successfully!";
            } elseif (isset($_POST['delete_project'])) {
                // Optionally, delete the associated media file from the server
                $stmt = $pdo->prepare('SELECT media_url FROM projects WHERE id = ?');
                $stmt->execute([$_POST['id']]);
                $project = $stmt->fetch();
                if ($project && $project['media_url'] && file_exists('../' . $project['media_url'])) {
                    unlink('../' . $project['media_url']);
                }

                $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
                $stmt->execute([$_POST['id']]);
                $message = "Project deleted successfully!";
            }
        }
    }

    // Fetch all projects
    $projects_stmt = $pdo->query('SELECT * FROM projects');
    $projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Projects</title>
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
        <h1>Edit Projects</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <hr>

    <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

    <div class="form-container">
        <h3>Add New Project</h3>
        <form action="edit-projects.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            <div class="form-group">
                <label>Media (Image or Video)</label>
                <input type="file" name="media[]" multiple>
            </div>
            <button type="submit" name="add_project">Add Project</button>
        </form>
    </div>

    <div class="table-container">
        <h3>Existing Projects</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Media</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <form action="edit-projects.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                        <input type="hidden" name="current_media_url" value="<?php echo htmlspecialchars($project['media_url']); ?>">
                        <td><input type="text" name="title" value="<?php echo htmlspecialchars($project['title']); ?>"></td>
                        <td><textarea name="description"><?php echo htmlspecialchars($project['description']); ?></textarea></td>
                        <td>
                            <?php if ($project['media_url']): ?>
                                <a href="../<?php echo htmlspecialchars($project['media_url']); ?>" target="_blank">View Media</a><br>
                            <?php endif; ?>
                            <input type="file" name="media[]" multiple>
                        </td>
                        <td>
                            <button type="submit" name="update_project">Update</button>
                            <button type="submit" name="delete_project" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>