<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "omega_learn";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    error_log("Omega Learn MySQLi connection failed: " . $conn->connect_error);
    http_response_code(500);
    die("Database connection failed. Check the local server configuration.");
}

$conn->set_charset("utf8mb4");

?>