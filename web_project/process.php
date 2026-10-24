<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$course = trim($_POST["course"] ?? "");
$message = trim($_POST["message"] ?? "");

$name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$email = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
$phone = htmlspecialchars($phone, ENT_QUOTES, "UTF-8");
$course = htmlspecialchars($course, ENT_QUOTES, "UTF-8");
$message = htmlspecialchars($message, ENT_QUOTES, "UTF-8");

$errors = [];

if ($name === "" || !preg_match("/^[A-Za-z ]{3,}$/", $name)) {
    $errors[] = "Please enter a valid name.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email.";
}

if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
    $errors[] = "Please enter a valid 10-digit phone number.";
}

if ($course === "") {
    $errors[] = "Please select a course.";
}

if ($message === "" || strlen($message) < 5) {
    $errors[] = "Message must contain at least 5 characters.";
}

if (!empty($errors)) {
    echo "<h2>Form Submission Failed</h2>";
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo '<a href="contact.html">Go Back</a>';
    exit;
}

$file = "data.json";

if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) {
        $data = [];
    }
} else {
    $data = [];
}

$newRecord = [
    "id" => count($data) + 1,
    "name" => $name,
    "email" => $email,
    "phone" => $phone,
    "course" => $course,
    "message" => $message,
    "date" => date("Y-m-d H:i:s")
];

$data[] = $newRecord;

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

echo "<h2>Registration Successful!</h2>";
echo "<p>Your information has been saved successfully.</p>";
echo '<a href="contact.html">Go Back</a>';
?>