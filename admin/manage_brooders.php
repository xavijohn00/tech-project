<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'admin') { header("Location: /index.php"); exit(); }

$success = $errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["action"] === "create") {
    $name     = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $api_key  = bin2hex(random_bytes(16)); // generates a random API key

    if (empty($name)) $errors[] = "Brooder name is required";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO brooders (name, location, api_key, status) VALUES (?,?,?,'active')");
        $stmt->bind_param("sss", $name, $location, $api_key);
        $stmt->execute()
            ? $success[] = "Brooder created. API Key: " . $api_key . " — give this to Sherdai for the RPi."
            : $errors[]  = "Error creating brooder";
        $stmt->close();
    }
}

$brooders = $conn->query("SELECT * FROM brooders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Manage Brooders | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Manage Brooders</h1>

    <?php foreach ($errors  as $e): ?><div class="notice error"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <?php foreach ($success as $s): ?><div class="notice success"><?php echo htmlspecialchars($s); ?></div><?php endforeach; ?>

    <div class="card" style="margin-bottom:30px;">
        <h3>Add New Brooder</h3>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <input type="text" name="name"     placeholder="Brooder Name (e.g. Brooder A)" class="input-box" required>
            <input type="text" name="location" placeholder="Location (e.g. Lab Room 1)"    class="input-box">
            <p style="font-size:0.85rem; color:var(--teal); margin:6px 0;">An API key will be auto-generated — give it to Sherdai for the RPi.</p>
            <button type="submit" class="btn">Add Brooder</button>
        </form>
    </div>

    <div class="card">
        <h3>All Brooders</h3>
        <table>
            <tr><th>Name</th><th>Location</th><th>Status</th><th>API Key</th></tr>
            <?php while ($b = $brooders->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($b["name"]); ?></td>
                <td><?php echo htmlspecialchars($b["location"] ?? "—"); ?></td>
                <td style="text-transform:capitalize;"><?php echo htmlspecialchars($b["status"]); ?></td>
                <td style="font-family:monospace; font-size:0.8rem;"><?php echo htmlspecialchars($b["api_key"] ?? "—"); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
