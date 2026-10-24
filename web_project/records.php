<?php
    require "admin_access.php";
    require_admin_access();

    $file = "data.json";
    $data = [];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            $data = [];
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Records - Omega Learn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <header>
        <div class="navbar">
            <div class="logo">
                <div class="logo-symbol">Ω</div>
                <div class="logo-text">
                    <span>OMEGA</span><br>
                    <span>LEARN</span>
                </div>
            </div>
            <nav>
                <a href="index.html">Home</a>
                <a href="courses.html">Courses</a>
                <a href="notes.html">Notes</a>
                <a href="project.html">Projects</a>
                <a href="store.html">Store</a>
                <a href="notices.html">Notices</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="notes">
            <h2>Submitted Records</h2>
            <p>Registration and contact details stored in JSON.</p>

            <?php if (empty($data)): ?>
                <p>No records found.</p>
            <?php else: ?>
                <?php foreach ($data as $record): ?>
                    <div class="note">
                        <div style="padding: 22px 30px;">
                            <h3><?php echo htmlspecialchars($record["name"]); ?></h3>
                            <p>Email: <?php echo htmlspecialchars($record["email"]); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($record["phone"]); ?></p>
                            <p>Course: <?php echo htmlspecialchars($record["course"]); ?></p>
                            <p>Message: <?php echo htmlspecialchars($record["message"]); ?></p>
                            <p>Date: <?php echo htmlspecialchars($record["date"]); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-logo">
            Ω Omega Learn
        </div>
        <p class="copyright">
            © 2026 Omega Learn. All rights reserved.
        </p>
    </footer>
    </body>
    </html>