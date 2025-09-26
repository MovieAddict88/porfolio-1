<?php
// Prevent direct access to this script
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// --- Database Configuration ---
$db_host = $_POST['db_host'];
$db_user = $_POST['db_user'];
$db_pass = $_POST['db_pass'];
$db_name = $_POST['db_name'];

// --- Create config.php ---
$config_content = "<?php
define('DB_HOST', '{$db_host}');
define('DB_USER', '{$db_user}');
define('DB_PASS', '{$db_pass}');
define('DB_NAME', '{$db_name}');
?>";

// Create the config directory if it doesn't exist
if (!is_dir('../config')) {
    mkdir('../config');
}

// Write the config file
if (!file_put_contents('../config/config.php', $config_content)) {
    header('Location: index.php?error=Failed to create config file.');
    exit;
}

// --- Database Connection & Setup ---
try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");

    // --- SQL Schema ---
    $sql = "
    CREATE TABLE IF NOT EXISTS `users` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `username` VARCHAR(50) NOT NULL UNIQUE,
      `password_hash` VARCHAR(255) NOT NULL,
      `first_login` BOOLEAN NOT NULL DEFAULT TRUE
    );

    CREATE TABLE IF NOT EXISTS `about_me` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `photo_url` VARCHAR(255),
      `tagline` VARCHAR(255),
      `bio` TEXT,
      `education` TEXT,
      `philosophy` TEXT,
      `email` VARCHAR(100),
      `linkedin_url` VARCHAR(255),
      `phone` VARCHAR(50)
    );

    CREATE TABLE IF NOT EXISTS `skills` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `skill_name` VARCHAR(100) NOT NULL,
      `level` INT NOT NULL,
      `category` VARCHAR(100)
    );

    CREATE TABLE IF NOT EXISTS `experience` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `title` VARCHAR(100) NOT NULL,
      `institution` VARCHAR(100) NOT NULL,
      `start_year` VARCHAR(4),
      `end_year` VARCHAR(10),
      `description` TEXT
    );

    CREATE TABLE IF NOT EXISTS `projects` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `title` VARCHAR(100) NOT NULL,
      `description` TEXT,
      `media_url` VARCHAR(255)
    );

    CREATE TABLE IF NOT EXISTS `documents` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `file_name` VARCHAR(255) NOT NULL,
      `file_path` VARCHAR(255) NOT NULL,
      `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS `passwords` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `code` VARCHAR(255) NOT NULL UNIQUE,
      `document_id` INT,
      `expiration_date` DATETIME,
      `is_active` BOOLEAN DEFAULT TRUE,
      FOREIGN KEY (document_id) REFERENCES documents(id)
    );
    ";

    // Execute the SQL schema
    $pdo->exec($sql);

    // --- Preload Placeholder Data ---

    // 1. Admin User
    $admin_user = 'admin';
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, first_login) VALUES (?, ?, ?)');
    $stmt->execute([$admin_user, $admin_pass, 1]);

    // 2. About Me
    $stmt = $pdo->prepare('INSERT INTO about_me (name, photo_url, tagline, bio, education, philosophy, email, linkedin_url, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        'Ms. Jane Doe',
        'assets/placeholder-teacher.jpg',
        'Inspiring young minds through creativity and patience',
        'I am a passionate educator with a love for early childhood learning. My goal is to create engaging, inclusive, and supportive environments where students thrive.',
        'Bachelor of Elementary Education – University of Example (2022)',
        'Every child learns differently, and it is my duty to adapt to their unique strengths and challenges.',
        'janedoe@email.com',
        'https://linkedin.com/in/janedoe',
        '+63 912 345 6789'
    ]);

    // 3. Skills
    $skills = [
        ['Classroom Management', 4, 'Teaching'],
        ['Lesson Planning', 5, 'Teaching'],
        ['Communication', 5, 'Interpersonal'],
        ['Technology Integration', 4, 'Technical'],
        ['Creativity', 5, 'Teaching']
    ];
    $stmt = $pdo->prepare('INSERT INTO skills (skill_name, level, category) VALUES (?, ?, ?)');
    foreach ($skills as $skill) {
        $stmt->execute($skill);
    }

    // 4. Experience
    $experience = [
        ['Student Teacher', 'Example Elementary School', '2022', 'Present', 'Assisted in classrooms, prepared activities, and supported learners.'],
        ['Volunteer Tutor', 'Community Learning Center', '2021', '2022', 'Provided after-school tutoring for children aged 6–10.']
    ];
    $stmt = $pdo->prepare('INSERT INTO experience (title, institution, start_year, end_year, description) VALUES (?, ?, ?, ?, ?)');
    foreach ($experience as $exp) {
        $stmt->execute($exp);
    }

    // 5. Projects
    $projects = [
        ['Interactive Storytelling Project', 'Designed a storytelling activity integrating puppetry and digital slides.', null],
        ['Math Games for Grades 2–3', 'Developed interactive math activities to strengthen problem-solving skills.', null]
    ];
    $stmt = $pdo->prepare('INSERT INTO projects (title, description, media_url) VALUES (?, ?, ?)');
    foreach ($projects as $project) {
        $stmt->execute($project);
    }

    // 6. Documents (placeholder filenames)
    $documents = [
        ['Resume.pdf', 'documents/Resume.pdf'],
        ['Teacher_ID.pdf', 'documents/Teacher_ID.pdf'],
        ['Clearance_Certificate.pdf', 'documents/Clearance_Certificate.pdf']
    ];
    if (!is_dir('../documents')) {
        mkdir('../documents');
    }
    $stmt = $pdo->prepare('INSERT INTO documents (file_name, file_path) VALUES (?, ?)');
    foreach ($documents as $doc) {
        $stmt->execute($doc);
    }

    // --- Disable Installer ---
    // Rename the install directory to prevent re-running
    rename(__DIR__, __DIR__ . '_installed');


    // --- Redirect to Login ---
    header('Location: ../admin/login.php?success=Installation complete. Please login.');
    exit;

} catch (PDOException $e) {
    // On failure, delete the config file and show an error
    unlink('../config/config.php');
    header('Location: index.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}
?>