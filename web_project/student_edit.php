<?php
require "admin_access.php";
require_admin_access();
require "db_mysqli.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid student ID.");
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    exit("Student not found.");
}

$error = "";
$csrfToken = admin_csrf_token();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_admin_csrf_token($_POST["csrf_token"] ?? "")) {
        http_response_code(403);
        exit("Invalid request. Reload the form and try again.");
    }

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || $course === "" || $year === "") {
        $error = "Please enter valid details in every field.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, email = ?, course = ?, year = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ssssi", $name, $email, $course, $year, $id);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: students.php?msg=updated");
            exit;
        } else {
            $error = $stmt->errno === 1062
                ? "This email belongs to another student."
                : "Unable to update student. Please try again.";
        }

        $stmt->close();
    }

    $student["name"] = $name;
    $student["email"] = $email;
    $student["course"] = $course;
    $student["year"] = $year;
}

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Omega Learn</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 550px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0002;
        }

        .form-container label {
            display: block;
            margin-top: 15px;
        }

        .form-container input,
        .form-container select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        .form-container button {
            margin-top: 20px;
            padding: 11px 18px;
            background: #176b55;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            color: #c62828;
        }
    </style>
</head>
<body>
    <main class="form-container">
        <h1>Edit Student</h1>

        <?php if ($error !== ""): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST"
              action="student_edit.php?id=<?= (int) $id ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name"
                   value="<?= e($student["name"]) ?>"
                   maxlength="100" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= e($student["email"]) ?>"
                   maxlength="100" required>

            <label for="course">Course</label>
            <select id="course" name="course" required>
                <?php foreach (["CSE", "IT", "AI & ML"] as $c): ?>
                    <option value="<?= e($c) ?>"
                        <?= $student["course"] === $c ? "selected" : "" ?>>
                        <?= e($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="year">Year</label>
            <select id="year" name="year" required>
                <?php foreach (["1st Year", "2nd Year", "3rd Year", "4th Year"] as $y): ?>
                    <option value="<?= e($y) ?>"
                        <?= $student["year"] === $y ? "selected" : "" ?>>
                        <?= e($y) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Update Student</button>
        </form>

        <p><a href="students.php">Back to Student Management</a></p>
    </main>
</body>
</html>