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
        if (isset($_POST['generate_password'])) {
            $document_id = $_POST['document_id'];
            $expiration_date = $_POST['expiration_date'];
            // Generate a random, unique code
            $code = substr(bin2hex(random_bytes(8)), 0, 12);

            $stmt = $pdo->prepare('INSERT INTO passwords (code, document_id, expiration_date) VALUES (?, ?, ?)');
            $stmt->execute([$code, $document_id, $expiration_date]);
            $message = "New password generated successfully! Code: <strong>$code</strong>";

        } elseif (isset($_POST['toggle_active'])) {
            $stmt = $pdo->prepare('UPDATE passwords SET is_active = NOT is_active WHERE id = ?');
            $stmt->execute([$_POST['id']]);
            $message = "Password status updated.";

        } elseif (isset($_POST['delete_password'])) {
            $stmt = $pdo->prepare('DELETE FROM passwords WHERE id = ?');
            $stmt->execute([$_POST['id']]);
            $message = "Password deleted successfully.";
        }
    }

    // Fetch all documents for the dropdown
    $docs_stmt = $pdo->query('SELECT id, file_name FROM documents');
    $documents = $docs_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all passwords with their linked document names
    $pass_stmt = $pdo->query('
        SELECT p.id, p.code, p.expiration_date, p.is_active, d.file_name
        FROM passwords p
        JOIN documents d ON p.document_id = d.id
        ORDER BY p.expiration_date DESC
    ');
    $passwords = $pass_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Download Passwords</title>
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
        .toggle-btn { background-color: #ffc107; }
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
        <h1>Manage Download Passwords</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <hr>

    <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

    <div class="form-container">
        <h3>Generate New Password</h3>
        <form action="manage-passwords.php" method="POST">
            <div class="form-group">
                <label for="document_id">Select Document</label>
                <select name="document_id" required>
                    <option value="">-- Choose a Document --</option>
                    <?php foreach ($documents as $doc): ?>
                        <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['file_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="expiration_date">Expiration Date</label>
                <input type="datetime-local" name="expiration_date" required>
            </div>
            <button type="submit" name="generate_password">Generate Password</button>
        </form>
    </div>

    <div class="table-container">
        <h3>Generated Passwords</h3>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Document</th>
                    <th>Expires At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passwords as $pass): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pass['code']); ?></td>
                    <td><?php echo htmlspecialchars($pass['file_name']); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($pass['expiration_date'])); ?></td>
                    <td>
                        <?php if (new DateTime() > new DateTime($pass['expiration_date'])): ?>
                            <span style="color:red;">Expired</span>
                        <?php elseif ($pass['is_active']): ?>
                            <span style="color:green;">Active</span>
                        <?php else: ?>
                            <span style="color:grey;">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="manage-passwords.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $pass['id']; ?>">
                            <button type="submit" name="toggle_active" class="toggle-btn">
                                <?php echo $pass['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </button>
                            <button type="submit" name="delete_password" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
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