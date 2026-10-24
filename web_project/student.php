<?php
session_start();

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

// Check session timeout
$timeout = 900;

if (!isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout) {

    $_SESSION = [];
    session_destroy();

    header("Location: login.html?error=timeout");
    exit;
}

// Check student role
if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Access Denied: Students only.");
}

$_SESSION["last_activity"] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Omega Learn</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            margin: 0;
            padding: 40px;
        }

        .dashboard {
            max-width: 700px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0002;
        }

        h1 {
            color: #6c3cff;
        }

        .logout {
            display: inline-block;
            padding: 10px 20px;
            background: #6c3cff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

    <div class="dashboard">
        <h1>Omega Learn Student Dashboard</h1>

        <p>Welcome,
            <?php echo htmlspecialchars(
                $_SESSION["name"],
                ENT_QUOTES,
                "UTF-8"
            ); ?>!
        </p>

        <p>You are logged in as a student.</p>

        <a class="logout" href="logout.php">Logout</a>
    </div>

</body>
</html>