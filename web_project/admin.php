<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

$timeout = 900;

if (
    isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.html?error=timeout");
    exit;
}

require "db.php";

$stmt = $pdo->prepare(
    "SELECT id, name, role FROM users WHERE id = ?"
);
$stmt->execute([$_SESSION["user_id"]]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.html");
    exit;
}

if ($currentUser["role"] !== "admin") {
    http_response_code(403);
    exit("Access Denied: Admin only.");
}

$_SESSION["role"] = $currentUser["role"];
$_SESSION["name"] = $currentUser["name"];
$_SESSION["last_activity"] = time();

$userId = (int)$currentUser["id"];

$pdo->prepare(
    "INSERT INTO audit_logs (user_id, action, details)
     VALUES (?, ?, ?)"
)->execute([
    $userId,
    "Dashboard Access",
    "Admin opened the dashboard"
]);

$totalUsers = (int)$pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

$totalStudents = (int)$pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

$totalNotices = (int)$pdo->query(
    "SELECT COUNT(*) FROM notices"
)->fetchColumn();

$registrations = $pdo->query(
    "SELECT id, name, email, role, created_at
     FROM users
     ORDER BY created_at DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

$auditLogs = $pdo->query(
    "SELECT a.action, a.details, a.created_at, u.name
     FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.id DESC
     LIMIT 10"
)->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Omega Learn</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            color: #27223a;
        }

        header {
            background: #6c3cff;
            color: white;
            padding: 22px 5%;
        }

        header h1 {
            margin: 0 0 8px;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 18px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(
                auto-fit, minmax(200px, 1fr)
            );
            gap: 18px;
            margin: 25px 0;
        }

        .card, section {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0001;
        }

        .card h3 {
            margin-top: 0;
            color: #625879;
            font-size: 16px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
            color: #6c3cff;
        }

        section {
            margin-bottom: 25px;
            overflow-x: auto;
        }

        h2 {
            color: #6c3cff;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f0ebff;
        }

        .empty {
            color: #777;
        }

        @media (max-width: 600px) {
            header {
                padding: 20px;
            }

            main {
                padding: 0 12px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Omega Learn Admin Dashboard</h1>

    <p>
        Welcome, <?= e($_SESSION["name"]) ?>!
        Role: <?= e($_SESSION["role"]) ?>
    </p>

    <nav>
        <a href="admin.php">Overview</a>
        <a href="students.php">Manage Students</a>
        <a href="notice_admin.php">Manage Notices</a>
        <a href="student_add.php">Add Student</a>
        <a href="#registrations">Latest Registrations</a>
        <a href="#audit">Audit Logs</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>

    <h2>Dashboard Overview</h2>
    <p>Monitor users, student records, notices and admin activity.</p>

    <div class="stats">
        <div class="card">
            <h3>Total Users</h3>
            <div class="number"><?= $totalUsers ?></div>
        </div>

        <div class="card">
            <h3>Student Records</h3>
            <div class="number"><?= $totalStudents ?></div>
        </div>

        <div class="card">
            <h3>Notices / Event Content</h3>
            <div class="number"><?= $totalNotices ?></div>
        </div>
    </div>

    <section id="registrations">
        <h2>Latest Registrations</h2>

        <?php if (!$registrations): ?>
            <p class="empty">No registrations found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered At</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($registrations as $user): ?>
                        <tr>
                            <td><?= e($user["id"]) ?></td>
                            <td><?= e($user["name"]) ?></td>
                            <td><?= e($user["email"]) ?></td>
                            <td><?= e($user["role"]) ?></td>
                            <td><?= e($user["created_at"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section id="audit">
        <h2>Recent Audit Logs</h2>

        <?php if (!$auditLogs): ?>
            <p class="empty">No audit logs found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date and Time</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($auditLogs as $log): ?>
                        <tr>
                            <td><?= e($log["name"] ?? "Unknown") ?></td>
                            <td><?= e($log["action"]) ?></td>
                            <td><?= e($log["details"]) ?></td>
                            <td><?= e($log["created_at"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</main>

</body>
</html>