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
        if (isset($_POST['upload_document'])) {
            if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
                $target_dir = "../documents/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $file_name = basename($_FILES["document"]["name"]);
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
                    $stmt = $pdo->prepare('INSERT INTO documents (file_name, file_path) VALUES (?, ?)');
                    $stmt->execute([$file_name, 'documents/' . $file_name]);
                    $message = "Document uploaded successfully!";
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error = "No file was uploaded or an error occurred.";
            }
        } elseif (isset($_POST['delete_document'])) {
            $id = $_POST['id'];

            // First, get the file path to delete the file from the server
            $stmt = $pdo->prepare('SELECT file_path FROM documents WHERE id = ?');
            $stmt->execute([$id]);
            $doc = $stmt->fetch();

            if ($doc && file_exists('../' . $doc['file_path'])) {
                unlink('../' . $doc['file_path']);
            }

            // Also delete any passwords associated with this document
            $stmt = $pdo->prepare('DELETE FROM passwords WHERE document_id = ?');
            $stmt->execute([$id]);

            // Delete the document record from the database
            $stmt = $pdo->prepare('DELETE FROM documents WHERE id = ?');
            $stmt->execute([$id]);

            $message = "Document and associated passwords deleted successfully!";
        }
    }

    // Fetch all documents
    $docs_stmt = $pdo->query('SELECT * FROM documents ORDER BY uploaded_at DESC');
    $documents = $docs_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Documents</title>
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
        <h1>Manage Documents</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <hr>

    <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

    <div class="form-container">
        <h3>Upload New Document</h3>
        <form action="manage-documents.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="document" required>
            <button type="submit" name="upload_document">Upload</button>
        </form>
    </div>

    <div class="table-container">
        <h3>Uploaded Documents</h3>
        <table>
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Path</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doc['file_name']); ?></td>
                    <td><a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($doc['file_path']); ?></a></td>
                    <td><?php echo $doc['uploaded_at']; ?></td>
                    <td>
                        <form action="manage-documents.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                            <button type="submit" name="delete_document" class="delete-btn" onclick="return confirm('Are you sure? This will also delete any passwords for this file.')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>