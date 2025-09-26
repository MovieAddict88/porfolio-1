<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin (for development)

require_once '../config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SELECT name, photo_url, tagline, bio, education, philosophy, email, linkedin_url, phone FROM about_me LIMIT 1');
    $about_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($about_data) {
        echo json_encode($about_data);
    } else {
        echo json_encode(['error' => 'No "About Me" data found.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>