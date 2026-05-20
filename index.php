<?php
require_once "auth/session_check.php";

// Redirect to the correct dashboard based on role
switch ($current_role) {
    case 'admin':
        header("Location: /admin/dashboard.php");
        break;
    case 'lecturer':
        header("Location: /lecturer/dashboard.php");
        break;
    case 'student':
        header("Location: /student/dashboard.php");
        break;
    default:
        session_unset();
        session_destroy();
        header("Location: /auth/login.php");
}
exit();
