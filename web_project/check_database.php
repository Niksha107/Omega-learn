<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require "db_mysqli.php";

echo "Connected database: ";

$result = $conn->query("SELECT DATABASE() AS db");
$row = $result->fetch_assoc();

echo htmlspecialchars($row["db"]) . "<br>";

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
$row = $result->fetch_assoc();

echo "Users in this database: " . $row["total"] . "<br>";

echo "Connection file: db_mysqli.php";
?>