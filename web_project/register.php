
<?php
ini_set("display_errors", "0");

require "db_mysqli.php";

function registration_response($message)
{
    header("Location: signup.html?" . http_build_query([
        "error" => $message
    ]));
    exit;
}

// Accept only form submissions
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.html");
    exit;
}

// Collect form values
$name = trim($_POST["name"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$city = trim($_POST["city"] ?? "");
$state = trim($_POST["state"] ?? "");
$pincode = trim($_POST["pincode"] ?? "");
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";

// Validate required fields
if (
    $name === "" || $username === "" || $email === "" ||
    $phone === "" || $address === "" || $city === "" ||
    $state === "" || $pincode === "" || $course === "" ||
    $year === "" || $gender === "" || $password === "" ||
    $confirmPassword === ""
) {
    registration_response("Please fill in all required fields.");
}

// Validate terms
if (!isset($_POST["terms"])) {
    registration_response("Please accept the Terms & Conditions.");
}

// Validate input formats and field lengths against the users table.
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    registration_response("Please enter a valid email address.");
}

if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
    registration_response("Username must be 3-30 characters and contain only letters, numbers or underscores.");
}

if (!preg_match('/^[A-Za-z ]{3,100}$/', $name)) {
    registration_response("Please enter a valid name.");
}

if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
    registration_response("Phone number must contain a valid 10-digit mobile number.");
}

if (strlen($address) > 255 || strlen($city) > 50 || strlen($state) > 50) {
    registration_response("Please check the length of your address and location details.");
}

if (!preg_match('/^[0-9]{6}$/', $pincode)) {
    registration_response("Pincode must contain exactly 6 digits.");
}

$allowedCities = [
    "Surat", "Pune", "Bengaluru", "Chennai", "Hyderabad",
    "Delhi", "Ahmedabad", "Jaipur", "Lucknow", "Kolkata"
];
$allowedStates = [
    "Maharashtra", "Karnataka", "Tamil Nadu", "Telangana",
    "Delhi", "Gujarat", "Rajasthan", "Uttar Pradesh",
    "West Bengal", "Andhra Pradesh"
];
$allowedCourses = [
    "C Programming", "C++ Programming",
    "Data Structures & Algorithms", "Web Development",
    "Python", "Artificial Intelligence"
];

if (
    !in_array($city, $allowedCities, true) ||
    !in_array($state, $allowedStates, true) ||
    !in_array($course, $allowedCourses, true) ||
    !in_array($year, ["1st Year", "2nd Year", "3rd Year", "4th Year"], true) ||
    !in_array($gender, ["Male", "Female", "Other"], true)
) {
    registration_response("Please choose valid course, location, year, and gender options.");
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/', $password)) {
    registration_response("Password needs 8 characters, uppercase, lowercase, number and special character.");
}

if ($password !== $confirmPassword) {
    registration_response("Passwords do not match.");
}

// Check for an existing username or email
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE username = ? OR email = ?"
);

if (!$stmt) {
    error_log("Omega Learn registration lookup prepare failed: " . $conn->error);
    registration_response("Registration is temporarily unavailable. Please try again later.");
}

$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    registration_response("Username or email is already registered.");
}

$stmt->close();

// Hash password before saving
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the registration details
$stmt = $conn->prepare(
    "INSERT INTO users
    (name, username, email, password, phone, address, city, state, pincode, course, year, gender)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    error_log("Omega Learn registration insert prepare failed: " . $conn->error);
    registration_response("Registration is temporarily unavailable. Please try again later.");
}

$stmt->bind_param(
    "ssssssssssss",
    $name,
    $username,
    $email,
    $hashedPassword,
    $phone,
    $address,
    $city,
    $state,
    $pincode,
    $course,
    $year,
    $gender
);

try {
    $created = $stmt->execute();
    $errorNumber = $stmt->errno;
} catch (mysqli_sql_exception $e) {
    error_log("Omega Learn registration insert failed: " . $e->getMessage());
    $created = false;
    $errorNumber = $e->getCode();
}

$stmt->close();
$conn->close();

if (!$created) {
    if ($errorNumber === 1062) {
        registration_response("Username or email is already registered.");
    }

    error_log("Omega Learn registration insert failed with database error " . $errorNumber);
    registration_response("Registration is temporarily unavailable. Please try again later.");
}

header("Location: signup.html?success=" . urlencode("Registration successful. You can now log in."));
exit;
?>
