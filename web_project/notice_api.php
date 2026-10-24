<?php
require "admin_access.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function decode_notices($contents) {
    $notices = json_decode($contents, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($notices)) {
        throw new RuntimeException("The notices data file is invalid.");
    }

    foreach ($notices as $notice) {
        if (!is_array($notice) || !isset(
            $notice["id"],
            $notice["title"],
            $notice["category"],
            $notice["date"],
            $notice["description"]
        )) {
            throw new RuntimeException("The notices data file has an invalid entry.");
        }
    }

    return $notices;
}

function read_notices() {
    $path = __DIR__ . "/notices.json";
    $handle = @fopen($path, "rb");

    if ($handle === false) {
        throw new RuntimeException("The notices data file could not be opened.");
    }

    if (!flock($handle, LOCK_SH)) {
        fclose($handle);
        throw new RuntimeException("The notices data file could not be locked.");
    }

    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    if ($contents === false) {
        throw new RuntimeException("The notices data file could not be read.");
    }

    return decode_notices($contents);
}

function modify_notices($callback) {
    $path = __DIR__ . "/notices.json";
    $handle = @fopen($path, "c+");

    if ($handle === false) {
        throw new RuntimeException("The notices data file could not be opened for updates.");
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        throw new RuntimeException("The notices data file could not be locked.");
    }

    $contents = stream_get_contents($handle);
    if ($contents === false) {
        flock($handle, LOCK_UN);
        fclose($handle);
        throw new RuntimeException("The notices data file could not be read.");
    }

    try {
        $notices = decode_notices($contents);
        $result = $callback($notices);
        $encoded = json_encode(
            $notices,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($encoded === false) {
            throw new RuntimeException("The notices could not be encoded.");
        }

        rewind($handle);
        if (!ftruncate($handle, 0)) {
            throw new RuntimeException("The notices data file could not be updated.");
        }

        $written = fwrite($handle, $encoded . PHP_EOL);
        if ($written !== strlen($encoded . PHP_EOL) || !fflush($handle)) {
            throw new RuntimeException("The notices data file could not be saved completely.");
        }

        return $result;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function validate_notice_input($input) {
    $title = trim($input["title"] ?? "");
    $category = trim($input["category"] ?? "");
    $date = trim($input["date"] ?? "");
    $description = trim($input["description"] ?? "");
    $allowedCategories = [
        "Notes", "Course", "Project", "Exam",
        "Event", "General", "Study"
    ];

    if ($title === "" || strlen($title) > 150 ||
        $description === "" || $category === "" || $date === "") {
        respond(422, [
            "success" => false,
            "message" => "Please provide all fields. Title must be 150 characters or fewer."
        ]);
    }

    if (!in_array($category, $allowedCategories, true)) {
        respond(422, [
            "success" => false,
            "message" => "Invalid category."
        ]);
    }

    $dateObj = DateTime::createFromFormat("!Y-m-d", $date);
    if (!$dateObj || $dateObj->format("Y-m-d") !== $date) {
        respond(422, [
            "success" => false,
            "message" => "Invalid date."
        ]);
    }

    return [
        "title" => $title,
        "category" => $category,
        "date" => $date,
        "description" => $description
    ];
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        $response = [
            "success" => true,
            "data" => read_notices()
        ];

        if (is_current_admin()) {
            $response["csrfToken"] = admin_csrf_token();
        }

        respond(200, $response);
    } catch (RuntimeException $e) {
        error_log($e->getMessage());
        respond(500, [
            "success" => false,
            "message" => "Notices could not be loaded."
        ]);
    }
}

if (!in_array($method, ["POST", "PUT", "DELETE"], true)) {
    header("Allow: GET, POST, PUT, DELETE");
    respond(405, [
        "success" => false,
        "message" => "Method not allowed."
    ]);
}

require_admin_access(true);

if (!validate_admin_csrf_token($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "")) {
    respond(403, [
        "success" => false,
        "message" => "Invalid security token. Reload the page and try again."
    ]);
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    respond(400, [
        "success" => false,
        "message" => "Invalid JSON request."
    ]);
}

try {
    if ($method === "POST") {
        $notice = validate_notice_input($input);
        $id = modify_notices(function (&$notices) use ($notice) {
            $ids = array_map(function ($item) {
                return (int)$item["id"];
            }, $notices);
            $id = $ids ? max($ids) + 1 : 1;
            $notice["id"] = $id;
            $notices[] = $notice;
            return $id;
        });

        respond(201, [
            "success" => true,
            "message" => "Notice added successfully.",
            "id" => $id
        ]);
    }

    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        respond(400, [
            "success" => false,
            "message" => "Valid notice ID is required."
        ]);
    }

    if ($method === "PUT") {
        $notice = validate_notice_input($input);
        $updated = modify_notices(function (&$notices) use ($id, $notice) {
            foreach ($notices as $index => $item) {
                if ((int)$item["id"] === $id) {
                    $notices[$index] = array_merge($item, $notice, ["id" => $id]);
                    return true;
                }
            }
            return false;
        });

        if (!$updated) {
            respond(404, [
                "success" => false,
                "message" => "Notice not found."
            ]);
        }

        respond(200, [
            "success" => true,
            "message" => "Notice updated successfully."
        ]);
    }

    $deleted = modify_notices(function (&$notices) use ($id) {
        foreach ($notices as $index => $item) {
            if ((int)$item["id"] === $id) {
                array_splice($notices, $index, 1);
                return true;
            }
        }
        return false;
    });

    if (!$deleted) {
        respond(404, [
            "success" => false,
            "message" => "Notice not found."
        ]);
    }

    respond(200, [
        "success" => true,
        "message" => "Notice deleted successfully."
    ]);
} catch (RuntimeException $e) {
    error_log($e->getMessage());
    respond(500, [
        "success" => false,
        "message" => "The notices could not be saved."
    ]);
}
