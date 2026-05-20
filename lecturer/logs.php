<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

$result = null;
$query_error = "";

// Lecturer only sees logs from their assigned students
$logs = $conn->prepare("
    SELECT sl.log_id, sl.event_type, sl.description, sl.logged_at,
           u.full_name, b.name AS brooder_name
    FROM system_logs sl
    LEFT JOIN users u ON u.user_id = sl.user_id
    LEFT JOIN brooders b ON b.brooder_id = sl.brooder_id
    WHERE sl.user_id IN (
        SELECT student_id FROM lecturer_student WHERE lecturer_id = ?
    )
    ORDER BY sl.logged_at DESC
    LIMIT 200
");

if (!$logs) {
    $query_error = "Could not load logs: " . $conn->error;
} else {
    $logs->bind_param("i", $current_user_id);
    $logs->execute();
    $result = $logs->get_result();
    $logs->close();
}
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Logs | SALCC</title>
    <link rel="icon" type="image/png" href="../images/salcc-logo-30.png">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Student Activity Logs</h1>
    <div class="notice">Showing logs from your assigned students only.</div>
    <?php if ($query_error): ?><div class="notice error"><?php echo htmlspecialchars($query_error); ?></div><?php endif; ?>
    <?php if (!$query_error): ?>
    <div class="card">
        <table>
            <tr><th>Time</th><th>Student</th><th>Brooder</th><th>Event</th><th>Description</th></tr>
            <?php while ($l = $result->fetch_assoc()): ?>
            <tr>
                <td style="font-size:0.8rem; white-space:nowrap;"><?php echo $l["logged_at"]; ?></td>
                <td><?php echo htmlspecialchars($l["full_name"] ?? "-"); ?></td>
                <td><?php echo htmlspecialchars($l["brooder_name"] ?? "-"); ?></td>
                <td style="font-family:monospace; font-size:0.8rem;"><?php echo htmlspecialchars($l["event_type"]); ?></td>
                <td style="font-size:0.85rem;"><?php echo htmlspecialchars($l["description"] ?? ""); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script src="../script.js"></script>
</body>
</html>
