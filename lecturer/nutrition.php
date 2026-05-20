<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

$stmt = $conn->prepare("
    SELECT u.user_id, u.full_name,
           b.brooder_id, b.name AS brooder_name
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
    $query_error = "Could not load nutrition overview: " . $conn->error;
} else {
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

foreach ($students as &$student) {
    $seed = intval($student["brooder_id"] ?: $student["user_id"]);
    $student["water_level"] = 55 + (($seed * 13 + intval(time() / 300)) % 35);
    $student["feed_level"] = 40 + (($seed * 17 + intval(time() / 420)) % 45);
}
unset($student);
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Nutrition | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Nutrition Overview</h1>
    <div class="notice">Read-only mock nutrition levels until the Raspberry Pi is connected.</div>
    <?php if ($query_error): ?><div class="notice error"><?php echo htmlspecialchars($query_error); ?></div><?php endif; ?>

    <?php if (!$query_error && empty($students)): ?>
        <div class="notice">No students have been assigned to you yet.</div>
    <?php elseif (!$query_error): ?>
    <div class="cards">
        <?php foreach ($students as $s): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($s["full_name"]); ?></h3>
            <p style="color:var(--teal); margin:4px 0;">Brooder: <strong><?php echo $s["brooder_name"] ? htmlspecialchars($s["brooder_name"]) : "Not assigned"; ?></strong></p>
            <p style="color:var(--teal); margin:8px 0 4px;">Water Tank: <strong><?php echo $s["water_level"]; ?>%</strong></p>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?php echo $s["water_level"]; ?>%;"></div></div>
            <p style="color:var(--teal); margin:8px 0 4px;">Feed Storage: <strong><?php echo $s["feed_level"]; ?>%</strong></p>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?php echo $s["feed_level"]; ?>%;"></div></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
