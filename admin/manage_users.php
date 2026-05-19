<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'admin') { header("Location: /index.php"); exit(); }

$success = $errors = [];
$default_password = "SALCC1234";

// Create user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "create") {
    $full_name = trim($_POST["full_name"]);
    $email     = trim($_POST["email"]);
    $role      = $_POST["role"];
    $password  = password_hash($default_password, PASSWORD_DEFAULT); // default password

    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (!in_array($role, ["admin","lecturer","student"])) $errors[] = "Invalid role";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $full_name, $email, $password, $role);
        if ($stmt->execute()) {
            $success[] = "User created. Default password: " . $default_password;
        } else {
            $errors[] = "Email already exists";
        }
        $stmt->close();
    }
}

// Reset user password
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "reset_password") {
    $uid = intval($_POST["user_id"]);
    $password = password_hash($default_password, PASSWORD_DEFAULT);
    $target_name = "";

    $user_stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ? AND role != 'admin' LIMIT 1");
    $user_stmt->bind_param("i", $uid);
    $user_stmt->execute();
    $target_user = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();

    if (!$target_user) {
        $errors[] = "Password could not be reset for this user";
    } else {
        $target_name = $target_user["full_name"];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ? AND role != 'admin'");
        $stmt->bind_param("si", $password, $uid);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $success[] = "Password reset for " . $target_name . ". Default password: " . $default_password;

            $event_type = "password_reset";
            $description = "Admin reset password for " . $target_name . " to the default password.";
            $log_stmt = $conn->prepare("INSERT INTO system_logs (user_id, event_type, description) VALUES (?,?,?)");
            $log_stmt->bind_param("iss", $current_user_id, $event_type, $description);
            $log_stmt->execute();
            $log_stmt->close();
        } else {
            $errors[] = "Password could not be reset for this user";
        }

        $stmt->close();
    }
}

// Delete user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
    $uid = intval($_POST["user_id"]);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();
    $success[] = "User deleted";
}

$users = $conn->query("SELECT user_id, full_name, email, role, created_at FROM users ORDER BY role, full_name");
$logs = $conn->query("
    SELECT sl.log_id, sl.event_type, sl.description, sl.logged_at,
           u.full_name, u.role,
           b.name AS brooder_name
    FROM system_logs sl
    LEFT JOIN users u ON u.user_id = sl.user_id
    LEFT JOIN brooders b ON b.brooder_id = sl.brooder_id
    ORDER BY sl.logged_at DESC
    LIMIT 25
");
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Manage Users | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Manage Users</h1>

    <?php foreach ($errors  as $e): ?><div class="notice error"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <?php foreach ($success as $s): ?><div class="notice success"><?php echo htmlspecialchars($s); ?></div><?php endforeach; ?>

    <!-- Create user form -->
    <div class="card" style="margin-bottom:30px;">
        <h3>Create New User</h3>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <input type="text"  name="full_name" placeholder="Full Name"     class="input-box" required>
            <input type="email" name="email"     placeholder="Email Address"  class="input-box" required>
            <select name="role" class="input-box" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="lecturer">Lecturer</option>
                <option value="admin">Admin</option>
            </select>
            <p style="font-size:0.85rem; color:var(--teal); margin:6px 0;">Default password will be: <strong><?php echo htmlspecialchars($default_password); ?></strong></p>
            <button type="submit" class="btn">Create User</button>
        </form>
    </div>

    <!-- Users table -->
    <div class="card">
        <h3>All Users</h3>
        <table>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Action</th></tr>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($u["full_name"]); ?></td>
                <td><?php echo htmlspecialchars($u["email"]); ?></td>
                <td style="text-transform:capitalize;"><?php echo htmlspecialchars($u["role"]); ?></td>
                <td><?php echo date("d M Y", strtotime($u["created_at"])); ?></td>
                <td>
                    <?php if ($u["role"] !== "admin"): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Reset this user password to the default?')">
                        <input type="hidden" name="action"  value="reset_password">
                        <input type="hidden" name="user_id" value="<?php echo $u["user_id"]; ?>">
                        <button type="submit" class="btn secondary" style="width:auto; padding:6px 12px; font-size:0.8rem;">Reset Password</button>
                    </form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                        <input type="hidden" name="action"  value="delete">
                        <input type="hidden" name="user_id" value="<?php echo $u["user_id"]; ?>">
                        <button type="submit" class="btn danger" style="width:auto; padding:6px 12px; font-size:0.8rem;">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Recent logs -->
    <div class="card" style="margin-top:30px;">
        <h3>Recent System Logs</h3>
        <p style="font-size:0.85rem; color:var(--teal); margin:0 0 12px;">Showing the 25 most recent logs.</p>
        <table>
            <tr><th>Time</th><th>User</th><th>Role</th><th>Brooder</th><th>Event</th><th>Description</th></tr>
            <?php while ($l = $logs->fetch_assoc()): ?>
            <tr>
                <td style="font-size:0.8rem; white-space:nowrap;"><?php echo htmlspecialchars($l["logged_at"]); ?></td>
                <td><?php echo htmlspecialchars($l["full_name"] ?? "System"); ?></td>
                <td style="text-transform:capitalize;"><?php echo htmlspecialchars($l["role"] ?? "-"); ?></td>
                <td><?php echo htmlspecialchars($l["brooder_name"] ?? "-"); ?></td>
                <td style="font-family:monospace; font-size:0.8rem;"><?php echo htmlspecialchars($l["event_type"]); ?></td>
                <td style="font-size:0.85rem;"><?php echo htmlspecialchars($l["description"] ?? ""); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <a href="/admin/logs.php" class="btn secondary" style="display:inline-block; width:auto; padding:8px 16px; text-decoration:none; margin-top:12px;">View All Logs</a>
    </div>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
