<?php
require_once 'config/config.php';

// --- Fetch all portfolio data ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch About Me data (which includes contact info)
    $about_stmt = $pdo->query('SELECT * FROM about_me LIMIT 1');
    $about = $about_stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch Experience data
    $exp_stmt = $pdo->query('SELECT * FROM experience ORDER BY start_year DESC');
    $experiences = $exp_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Skills data
    $skills_stmt = $pdo->query('SELECT * FROM skills ORDER BY category, level DESC');
    $skills = $skills_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Projects data
    $projects_stmt = $pdo->query('SELECT * FROM projects');
    $projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // A simple error message if the database connection fails.
    die("Error: Could not connect to the database. Please ensure the installer has run and the config file is correct. " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($about['name'] ?? 'Portfolio'); ?>'s Portfolio</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

    <!-- Header / Home Section -->
    <header id="home">
        <div class="container">
            <h1><?php echo htmlspecialchars($about['name'] ?? 'Jane Doe'); ?></h1>
            <p><?php echo htmlspecialchars($about['tagline'] ?? 'Inspiring the next generation.'); ?></p>
        </div>
    </header>

    <div class="container">
        <!-- About Me Section -->
        <section id="about" class="section">
            <h2>About Me</h2>
            <?php if (!empty($about['photo_url'])): ?>
                <img src="<?php echo htmlspecialchars($about['photo_url']); ?>" alt="Photo of <?php echo htmlspecialchars($about['name']); ?>">
            <?php endif; ?>
            <p><?php echo nl2br(htmlspecialchars($about['bio'] ?? '')); ?></p>
            <h3>Education</h3>
            <?php
            if (!empty($about['education'])) {
                $education_items = explode("\n", trim($about['education']));
                if (!empty($education_items)) {
                    echo '<ul class="education-list">';
                    foreach ($education_items as $item) {
                        if (!empty(trim($item))) {
                            echo '<li>' . htmlspecialchars(trim($item)) . '</li>';
                        }
                    }
                    echo '</ul>';
                }
            }
            ?>
            <h3>My Philosophy</h3>
            <p><?php echo htmlspecialchars($about['philosophy'] ?? ''); ?></p>
        </section>

        <!-- Experience Section -->
        <section id="experience" class="section">
            <h2>Experience</h2>
            <ul class="timeline">
                <?php foreach ($experiences as $exp): ?>
                <li class="timeline-item">
                    <div class="year"><?php echo htmlspecialchars($exp['start_year']); ?> - <?php echo htmlspecialchars($exp['end_year']); ?></div>
                    <div class="timeline-content">
                        <h3><?php echo htmlspecialchars($exp['title']); ?></h3>
                        <h4><?php echo htmlspecialchars($exp['institution']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Skills Section -->
        <section id="skills" class="section">
            <h2>Skills</h2>
            <ul class="skills-list">
                <?php foreach ($skills as $skill): ?>
                    <li class="skill-item"><?php echo htmlspecialchars($skill['skill_name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Projects Section -->
        <section id="projects" class="section">
            <h2>Projects</h2>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                <div class="project">
                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                    <?php if (!empty($project['media_url'])):
                        $media_files = explode(',', $project['media_url']);
                        $first_image = $media_files[0];
                    ?>
                        <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="project-thumbnail" data-media='<?php echo htmlspecialchars(json_encode($media_files), ENT_QUOTES, 'UTF-8'); ?>'>
                    <?php endif; ?>
                    <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Lightbox Modal -->
        <div id="lightbox-modal" class="lightbox">
            <span class="close-btn">&times;</span>
            <div class="lightbox-content">
                <img id="lightbox-img" src="">
                <a class="prev-btn">&#10094;</a>
                <a class="next-btn">&#10095;</a>
            </div>
        </div>

        <!-- Contact Section -->
        <section id="contact" class="section">
            <h2>Contact Me</h2>
            <div class="contact-info">
                <a href="mailto:<?php echo htmlspecialchars($about['email'] ?? ''); ?>">Email</a>
                <?php if (!empty($about['linkedin_url'])): ?>
                <a href="<?php echo htmlspecialchars($about['linkedin_url']); ?>" target="_blank">LinkedIn</a>
                <?php endif; ?>
                <a href="tel:<?php echo htmlspecialchars($about['phone'] ?? ''); ?>">Phone</a>
            </div>
        </section>

        <!-- Download Center Section -->
        <section id="download-center" class="section">
            <h2>Download Center</h2>
            <p>Access protected documents, such as my resume, by entering a password.</p>
            <a href="download.php" class="download-link">Go to Download Page</a>
        </section>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($about['name'] ?? 'Portfolio'); ?>. All Rights Reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('lightbox-modal');
            const modalImg = document.getElementById('lightbox-img');
            const closeBtn = modal.querySelector('.close-btn');
            const prevBtn = modal.querySelector('.prev-btn');
            const nextBtn = modal.querySelector('.next-btn');

            let currentMedia = [];
            let currentIndex = 0;

            document.querySelectorAll('.project-thumbnail').forEach(item => {
                item.addEventListener('click', event => {
                    currentMedia = JSON.parse(event.target.dataset.media);
                    currentIndex = 0;
                    updateLightbox();
                    modal.style.display = 'block';
                });
            });

            function updateLightbox() {
                modalImg.src = currentMedia[currentIndex];
                prevBtn.style.display = currentMedia.length > 1 ? 'block' : 'none';
                nextBtn.style.display = currentMedia.length > 1 ? 'block' : 'none';
            }

            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            prevBtn.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + currentMedia.length) % currentMedia.length;
                updateLightbox();
            });

            nextBtn.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % currentMedia.length;
                updateLightbox();
            });

            window.addEventListener('click', (event) => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>