<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

$stmt = $conn->prepare("
    SELECT u.user_id, u.full_name,
           b.brooder_id, b.name AS brooder_name, b.location
    FROM lecturer_student ls
    JOIN users u ON u.user_id = ls.student_id
    LEFT JOIN student_brooder sb ON sb.student_id = u.user_id
    LEFT JOIN brooders b ON b.brooder_id = sb.brooder_id
    WHERE ls.lecturer_id = ?
    ORDER BY u.full_name
");
$students = [];
$query_error = "";

if (!$stmt) {
    $query_error = "Could not load monitoring overview: " . $conn->error;
} else {
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

foreach ($students as &$student) {
    $seed = intval($student["brooder_id"] ?: $student["user_id"]);
    $student["activity"] = 65 + (($seed * 11 + intval(time() / 180)) % 30);
    $student["light_level"] = 45 + (($seed * 7 + intval(time() / 240)) % 45);
    $student["status"] = $student["brooder_id"] ? "Online" : "No brooder assigned";
    $student["last_seen"] = date("Y-m-d H:i:s", time() - (($seed * 23) % 240));
}
unset($student);
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Monitoring | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Monitoring Overview</h1>
    <div class="notice">Read-only mock monitoring data until the Raspberry Pi is connected.</div>
    <?php if ($query_error): ?><div class="notice error"><?php echo htmlspecialchars($query_error); ?></div><?php endif; ?>

    <?php if (!$query_error && empty($students)): ?>
        <div class="notice">No students have been assigned to you yet.</div>
    <?php elseif (!$query_error): ?>
    <div class="cards">
        <?php foreach ($students as $s): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($s["full_name"]); ?></h3>
            <p style="color:var(--teal); margin:4px 0;">Brooder: <strong><?php echo $s["brooder_name"] ? htmlspecialchars($s["brooder_name"]) : "Not assigned"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Location: <?php echo htmlspecialchars($s["location"] ?? "-"); ?></p>
            <p style="color:var(--teal); margin:4px 0;">Status: <strong><?php echo htmlspecialchars($s["status"]); ?></strong></p>
            <p style="color:var(--teal); margin:8px 0 4px;">Activity: <strong><?php echo $s["activity"]; ?>%</strong></p>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?php echo $s["activity"]; ?>%;"></div></div>
            <p style="color:var(--teal); margin:8px 0 4px;">Light Level: <strong><?php echo $s["light_level"]; ?>%</strong></p>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?php echo $s["light_level"]; ?>%;"></div></div>
            <p style="font-size:0.8rem; color:#999; margin:8px 0 0;">Mock update: <?php echo htmlspecialchars($s["last_seen"]); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
