<?php
session_start();

require "db_mysqli.php";

// Accept only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

// Get form values
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

// Validate inputs
if ($email === "" || $password === "") {
    header("Location: login.html?error=empty");
    exit;
}

// The login form accepts email or phone; reject ambiguous phone matches.
$stmt = $conn->prepare(
    "SELECT id, name, email, password, role
     FROM users
     WHERE email = ? OR phone = ?
     LIMIT 2"
);

$stmt->bind_param("ss", $email, $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->num_rows === 1 ? $result->fetch_assoc() : null;
$stmt->close();

// Verify password
if (!$user || !password_verify($password, $user["password"])) {
    header("Location: login.html?error=invalid");
    exit;
}

// Regenerate session ID after successful login
session_regenerate_id(true);

// Store session data
$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];
$_SESSION["last_activity"] = time();

// Redirect according to role
if ($user["role"] === "admin") {
    header("Location: admin.php");
} else {
    header("Location: student.php");
}

exit;
?>