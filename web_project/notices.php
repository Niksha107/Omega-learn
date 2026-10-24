<?php

require "db.php";

$stmt = $pdo->prepare("SELECT * FROM notices ORDER BY date DESC");
$stmt->execute();

$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Omega Learn - Notices</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        h1 {
            text-align: center;
        }

        .notice {
            background: white;
            padding: 20px;
            margin: 20px auto;
            max-width: 700px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .notice h2 {
            margin-top: 0;
        }

        .category {
            font-weight: bold;
        }

        .date {
            color: gray;
        }
    </style>
</head>

<body>

<h1>Omega Learn Notices</h1>

<?php if (count($notices) > 0): ?>

    <?php foreach ($notices as $notice): ?>

        <div class="notice">

            <h2>
                <?= htmlspecialchars($notice['title']) ?>
            </h2>

            <p class="category">
                Category: <?= htmlspecialchars($notice['category']) ?>
            </p>

            <p class="date">
                Date: <?= htmlspecialchars($notice['date']) ?>
            </p>

            <p>
                <?= htmlspecialchars($notice['description']) ?>
            </p>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>No notices available.</p>

<?php endif; ?>

</body>
</html>