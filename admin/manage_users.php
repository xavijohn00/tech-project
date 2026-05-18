<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'admin') { header("Location: /index.php"); exit(); }

$success = $errors = [];

// Create user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "create") {
    $full_name = trim($_POST["full_name"]);
    $email     = trim($_POST["email"]);
    $role      = $_POST["role"];
    $password  = password_hash("SALCC1234", PASSWORD_DEFAULT); // default password

    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (!in_array($role, ["admin","lecturer","student"])) $errors[] = "Invalid role";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $full_name, $email, $password, $role);
        if ($stmt->execute()) {
            $success[] = "User created. Default password: SALCC1234";
        } else {
            $errors[] = "Email already exists";
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
            <p style="font-size:0.85rem; color:var(--teal); margin:6px 0;">Default password will be: <strong>SALCC1234</strong></p>
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
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
