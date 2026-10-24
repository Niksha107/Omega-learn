<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require "db_mysqli.php";

$email = getenv("OMEGA_ADMIN_EMAIL");

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Set OMEGA_ADMIN_EMAIL to the email address of an existing account before running this CLI script.\n");
    exit(1);
}

$stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE email = ? AND role = 'student'");
$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    fwrite(STDERR, "Admin account update failed.\n");
    exit(1);
}

if ($stmt->affected_rows === 0) {
    $stmt->close();
    $stmt = $conn->prepare("SELECT role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($role);

    if (!$stmt->fetch()) {
        fwrite(STDERR, "No account matches that email.\n");
        exit(1);
    }

    if ($role !== "admin") {
        fwrite(STDERR, "The account could not be promoted.\n");
        exit(1);
    }

    fwrite(STDOUT, "Account already has the admin role.\n");
    $stmt->close();
    $conn->close();
    exit;
}

fwrite(STDOUT, "Existing account promoted to admin. Its password was not changed.\n");
$stmt->close();
$conn->close();
?>