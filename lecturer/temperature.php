<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

$stmt = $conn->prepare("
    SELECT u.full_name, b.name AS brooder_name,
           ts.target_temp, tr.temperature, tr.humidity, tr.recorded_at
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
    <title>Temperature | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Temperature Overview</h1>
    <div class="notice">Read-only — you cannot change student temperature settings.</div>
    <div class="cards">
        <?php foreach ($students as $s): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($s["full_name"]); ?></h3>
            <p style="color:var(--teal); margin:4px 0;">Brooder: <strong><?php echo $s["brooder_name"] ?? "Not assigned"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Current: <strong><?php echo $s["temperature"] ? $s["temperature"] . "°C" : "No data"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Target: <strong><?php echo $s["target_temp"] ? $s["target_temp"] . "°C" : "Not set"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Humidity: <strong><?php echo $s["humidity"] ? $s["humidity"] . "%" : "—"; ?></strong></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
