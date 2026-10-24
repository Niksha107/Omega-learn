<?php
require "admin_access.php";
require_admin_access();
require "db_mysqli.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: students.php");
    exit;
}

if (!validate_admin_csrf_token($_POST["csrf_token"] ?? "")) {
    http_response_code(403);
    exit("Invalid request. Return to the student list and try again.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: students.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: students.php?msg=deleted");
    exit;
}

$stmt->close();
$conn->close();

exit("Unable to delete student. Please try again.");