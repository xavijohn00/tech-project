<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
require_once "../auth/flask.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

$stmt = $conn->prepare("
    SELECT u.full_name, u.user_id,
           b.brooder_id, b.api_key, b.name AS brooder_name, b.location,
           ts.target_temp
    FROM lecturer_student ls
    JOIN users u ON u.user_id = ls.student_id
    LEFT JOIN student_brooder sb ON sb.student_id = u.user_id
    LEFT JOIN brooders b ON b.brooder_id = sb.brooder_id
    LEFT JOIN brooder_settings ts ON ts.brooder_id = b.brooder_id
        AND ts.setting_id = (SELECT MAX(setting_id) FROM brooder_settings WHERE brooder_id = b.brooder_id)
    WHERE ls.lecturer_id = ?
    ORDER BY u.full_name
");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($students as &$student) {
    /*
    $reading = flask_call('GET', '/api/readings', $student["api_key"]);
    if ($reading['status'] === 200) {
        $student["temperature"] = $reading['data']["temperature"];
        $student["humidity"] = $reading['data']["humidity"];
        $student["recorded_at"] = $reading['data']["recorded_at"];
    }
    */

    if ($student["brooder_id"]) {
        $reading = mock_live_reading($student["brooder_id"], $student["target_temp"]);
        $student["temperature"] = $reading["temperature"];
        $student["humidity"] = $reading["humidity"];
        $student["recorded_at"] = $reading["recorded_at"];
    }
}
unset($student);
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
    <div class="notice">Read-only view with mock live readings until the Raspberry Pi is connected.</div>

    <?php if (empty($students)): ?>
        <div class="notice">No students have been assigned to you yet.</div>
    <?php else: ?>
    <div class="cards">
        <?php foreach ($students as $s): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($s["full_name"]); ?></h3>
            <p style="color:var(--teal); margin:4px 0;">Brooder: <strong><?php echo $s["brooder_name"] ? htmlspecialchars($s["brooder_name"]) : "Not assigned"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Location: <?php echo htmlspecialchars($s["location"] ?? "-"); ?></p>
            <p style="color:var(--teal); margin:4px 0;">Current Temp: <strong><?php echo isset($s["temperature"]) ? $s["temperature"] . "&deg;C" : "No data"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Target Temp: <strong><?php echo $s["target_temp"] ? $s["target_temp"] . "&deg;C" : "Not set"; ?></strong></p>
            <p style="color:var(--teal); margin:4px 0;">Humidity: <strong><?php echo isset($s["humidity"]) ? $s["humidity"] . "%" : "No data"; ?></strong></p>
            <?php if (isset($s["recorded_at"])): ?>
            <p style="font-size:0.8rem; color:#999; margin:4px 0;">Mock reading: <?php echo htmlspecialchars($s["recorded_at"]); ?></p>
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
