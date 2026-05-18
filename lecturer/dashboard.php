<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

$stmt = $conn->prepare("
    SELECT u.full_name, u.user_id,
           b.name AS brooder_name, b.location,
           ts.target_temp,
           tr.temperature, tr.humidity, tr.recorded_at
    FROM lecturer_student ls
    JOIN users u ON u.user_id = ls.student_id
    LEFT JOIN student_brooder sb ON sb.student_id = u.user_id
    LEFT JOIN brooders b ON b.brooder_id = sb.brooder_id
    LEFT JOIN temperature_settings ts ON ts.brooder_id = b.brooder_id
        AND ts.setting_id = (SELECT MAX(setting_id) FROM temperature_settings WHERE brooder_id = b.brooder_id)
    LEFT JOIN temperature_readings tr ON tr.brooder_id = b.brooder_id
        AND tr.reading_id = (SELECT MAX(reading_id) FROM temperature_readings WHERE brooder_id = b.brooder_id)
    WHERE ls.lecturer_id = ?
    ORDER BY u.full_name
");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Lecturer Dashboard | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>My Students</h1>
    <div class="notice">Read-only view — you can monitor your students' brooders but cannot change their settings.</div>

    <?php if (empty($students)): ?>
        <div class="notice">No students have been assigned to you yet.</div>
    <?php else: ?>
    <div class="cards">
        <?php foreach ($students as $s): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($s["full_name"]); ?></h3>
            <p style="color:var(--teal); margin:4px 0;">Brooder: <strong><?php echo $s["brooder_name"] ? htmlspecialchars($s["brooder_name"]) : "Not assigned"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Location: <?php echo htmlspecialchars($s["location"] ?? "—"); ?></p>
            <p style="color:var(--teal); margin:4px 0;">Current Temp: <strong><?php echo $s["temperature"] ? $s["temperature"] . "°C" : "No data"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Target Temp: <strong><?php echo $s["target_temp"] ? $s["target_temp"] . "°C" : "Not set"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Humidity: <strong><?php echo $s["humidity"] ? $s["humidity"] . "%" : "No data"; ?></strong></p>
            <?php if ($s["recorded_at"]): ?>
            <p style="font-size:0.8rem; color:#999; margin:4px 0;">Last reading: <?php echo $s["recorded_at"]; ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
