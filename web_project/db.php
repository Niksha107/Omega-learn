<?php

$host = "localhost";
$dbname = "omega_learn";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log("Omega Learn PDO connection failed: " . $e->getMessage());
    http_response_code(500);
    die("Database connection failed. Check the local server configuration.");
}

?>