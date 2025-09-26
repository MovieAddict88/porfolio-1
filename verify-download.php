<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['password_code'])) {
    header('Location: download.php');
    exit;
}

$code = $_POST['password_code'];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('
        SELECT d.file_path, p.expiration_date, p.is_active
        FROM passwords p
        JOIN documents d ON p.document_id = d.id
        WHERE p.code = ?
    ');
    $stmt->execute([$code]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        header('Location: download.php?error=Invalid password code.');
        exit;
    }

    if (!$result['is_active']) {
        header('Location: download.php?error=This download code has been deactivated.');
        exit;
    }

    $expiration = new DateTime($result['expiration_date']);
    $now = new DateTime();

    if ($now > $expiration) {
        header('Location: download.php?error=This download code has expired.');
        exit;
    }

    $file_path = $result['file_path'];
    $full_file_path = __DIR__ . '/' . $file_path;

    if (file_exists($full_file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($full_file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($full_file_path));
        readfile($full_file_path);
        exit;
    } else {
        header('Location: download.php?error=File not found on server.');
        exit;
    }

} catch (PDOException $e) {
    header('Location: download.php?error=' . urlencode('Database error.'));
    exit;
}
?>