<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'admin') { header("Location: ../index.php"); exit(); }

// Admin sees ALL logs
$logs = $conn->query("
    SELECT sl.log_id, sl.event_type, sl.description, sl.logged_at,
           u.full_name, u.role,
           b.name AS brooder_name
    FROM system_logs sl
    LEFT JOIN users u ON u.user_id = sl.user_id
    LEFT JOIN brooders b ON b.brooder_id = sl.brooder_id
    ORDER BY sl.logged_at DESC
    LIMIT 200
");
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>System Logs | SALCC</title>
    <link rel="icon" type="image/png" href="../images/salcc-logo-30.png">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>System Logs</h1>
    <div class="card">
        <table>
            <tr><th>Time</th><th>User</th><th>Role</th><th>Brooder</th><th>Event</th><th>Description</th></tr>
            <?php while ($l = $logs->fetch_assoc()): ?>
            <tr>
                <td style="font-size:0.8rem; white-space:nowrap;"><?php echo $l["logged_at"]; ?></td>
                <td><?php echo htmlspecialchars($l["full_name"] ?? "System"); ?></td>
                <td style="text-transform:capitalize;"><?php echo htmlspecialchars($l["role"] ?? "-"); ?></td>
                <td><?php echo htmlspecialchars($l["brooder_name"] ?? "-"); ?></td>
                <td style="font-family:monospace; font-size:0.8rem;"><?php echo htmlspecialchars($l["event_type"]); ?></td>
                <td style="font-size:0.85rem;"><?php echo htmlspecialchars($l["description"] ?? ""); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<?php include "../auth/footer.php"; ?>
<script src="../script.js"></script>
</body>
</html>
