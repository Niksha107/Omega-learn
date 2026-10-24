<?php
require "admin_access.php";
require_admin_access();
require "db_mysqli.php";

$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");
$csrfToken = admin_csrf_token();

$allowedCourses = ["CSE", "IT", "AI & ML"];

if (!in_array($course, $allowedCourses, true)) {
    $course = "";
}

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $types .= "ss";
}

if ($course !== "") {
    $sql .= " AND course = ?";
    $params[] = $course;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | Omega Learn</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .student-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .student-table th,
        .student-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .student-table th {
            background: #f1f1f1;
        }

        .student-table a {
            margin-right: 8px;
        }

        .add-btn {
            display: inline-block;
            padding: 10px 16px;
            background: #176b55;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .table-wrapper {
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <main class="student-container">
        <h1>Student Management</h1>
        <?php
        $messages = [
            "added" => "Student added successfully!",
            "updated" => "Student updated successfully!",
            "deleted" => "Student deleted successfully!"
        ];

        $msg = $_GET["msg"] ?? "";

        if (isset($messages[$msg])) {
            echo '<p style="color:green;">'
                . htmlspecialchars($messages[$msg])
                . '</p>';
        }
        ?>
        <p>Manage Omega Learn student records.</p>

        <a class="add-btn" href="student_add.php">+ Add Student</a>
        <a href="admin.php">Back to Dashboard</a>

        <form method="GET" action="students.php" style="margin-top:20px;">
            <input type="text" name="search" placeholder="Search by name or email" value="<?= e($search) ?>">

            <select name="course">
                <option value="">All Courses</option>
                <option value="CSE" <?= $course === "CSE" ? "selected" : "" ?>>CSE</option>
                <option value="IT" <?= $course === "IT" ? "selected" : "" ?>>IT</option>
                <option value="AI & ML" <?= $course === "AI & ML" ? "selected" : "" ?>>AI & ML</option>
            </select>

            <button type="submit">Search / Filter</button>
            <a href="students.php">Reset</a>
        </form>
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int) $student["id"] ?></td>
                                <td><?= htmlspecialchars($student["name"]) ?></td>
                                <td><?= htmlspecialchars($student["email"]) ?></td>
                                <td><?= htmlspecialchars($student["course"]) ?></td>
                                <td><?= htmlspecialchars($student["year"]) ?></td>
                                <td>
                                    <a href="student_edit.php?id=<?= (int) $student["id"] ?>">Edit</a>
                                    <form action="student_delete.php" method="POST" style="display:inline"
                                        onsubmit="return confirm('Are you sure you want to delete this student?')">

                                        <input type="hidden" name="id" value="<?= (int) $student["id"] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No student records found.</p>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </main>
</body>

</html>