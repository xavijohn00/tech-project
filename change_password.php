<?php
require_once "auth/session_check.php";
require_once "auth/connectdb.php";

$errors  = [];
$success = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST["current_password"] ?? "";
    $new_password     = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Fetch current hashed password from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $errors[] = "User not found.";
    } elseif (!password_verify($current_password, $row["password"])) {
        $errors[] = "Current password is incorrect.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match.";
    } elseif ($new_password === $current_password) {
        $errors[] = "New password must be different from your current password.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $current_user_id);
        if ($stmt->execute()) {
            $success[] = "Password changed successfully.";
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

// Include the correct navbar based on role
$navbar = match($current_role) {
    "admin"    => "admin/navbar.php",
    "lecturer" => "lecturer/navbar.php",
    default    => "student/navbar.php",
};
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Change Password | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include $navbar; ?>
<div class="container">
    <h1>Change Password</h1>

    <?php foreach ($errors  as $e): ?>
        <div class="notice error"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
    <?php foreach ($success as $s): ?>
        <div class="notice success"><?php echo htmlspecialchars($s); ?></div>
    <?php endforeach; ?>

    <div class="card" style="max-width:480px;">
        <form method="post">
            <label style="font-size:0.9rem; color:var(--teal);">Current Password</label>
            <input type="password" name="current_password" class="input-box" required>

            <label style="font-size:0.9rem; color:var(--teal);">New Password</label>
            <input type="password" name="new_password" class="input-box" required>

            <label style="font-size:0.9rem; color:var(--teal);">Confirm New Password</label>
            <input type="password" name="confirm_password" class="input-box" required>

            <button type="submit" class="btn" style="margin-top:16px;">Update Password</button>
        </form>
    </div>
</div>
<?php include "auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
