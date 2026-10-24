<?php

function start_admin_session()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function is_current_admin()
{
    start_admin_session();

    if (
        !isset($_SESSION["user_id"]) ||
        ($_SESSION["role"] ?? "") !== "admin"
    ) {
        return false;
    }

    if (
        !isset($_SESSION["last_activity"]) ||
        time() - $_SESSION["last_activity"] > 900
    ) {
        $_SESSION = [];
        session_destroy();
        return false;
    }

    $_SESSION["last_activity"] = time();
    return true;
}

function require_admin_access($jsonResponse = false)
{
    start_admin_session();

    $authenticated = isset($_SESSION["user_id"]);
    $expired = $authenticated && (
        !isset($_SESSION["last_activity"]) ||
        time() - $_SESSION["last_activity"] > 900
    );

    if ($expired) {
        $_SESSION = [];
        session_destroy();
    }

    if (!$authenticated || $expired) {
        if ($jsonResponse) {
            http_response_code(401);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode([
                "success" => false,
                "message" => "Please log in as an administrator."
            ]);
            exit;
        }

        header($expired
            ? "Location: login.html?error=timeout"
            : "Location: login.html");
        exit;
    }

    if (($_SESSION["role"] ?? "") !== "admin") {
        if ($jsonResponse) {
            http_response_code(403);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode([
                "success" => false,
                "message" => "Administrator access is required."
            ]);
            exit;
        }

        http_response_code(403);
        exit("Access Denied: Admin only.");
    }

    if (!$expired) {
        $_SESSION["last_activity"] = time();
    }
}

function admin_csrf_token()
{
    start_admin_session();

    if (!isset($_SESSION["admin_csrf_token"])) {
        $_SESSION["admin_csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["admin_csrf_token"];
}

function validate_admin_csrf_token($token)
{
    start_admin_session();

    return isset($_SESSION["admin_csrf_token"]) &&
        is_string($token) &&
        hash_equals($_SESSION["admin_csrf_token"], $token);
}
