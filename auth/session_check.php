<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$timeout = 1800;

if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login.php");
    exit();
}

if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"]) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: /auth/login.php?timeout=1");
    exit();
}

$_SESSION["last_activity"] = time();

$current_user_id   = $_SESSION["user_id"];
$current_role      = $_SESSION["role"];
$current_full_name = $_SESSION["full_name"];
$current_initial   = strtoupper(substr($current_full_name, 0, 1));
