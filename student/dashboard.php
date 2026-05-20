<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
require_once "../auth/flask.php";
if ($current_role !== 'student') { header("Location: /index.php"); exit(); }

$stmt = $conn->prepare("
    SELECT b.* FROM brooders b
    JOIN student_brooder sb ON sb.brooder_id = b.brooder_id
    WHERE sb.student_id = ?
");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$brooder = $stmt->get_result()->fetch_assoc();
$stmt->close();

$latest = $target = null;
if ($brooder) {
    $stmt = $conn->prepare("
        SELECT target_temp
        FROM brooder_settings
        WHERE brooder_id = ?
        ORDER BY set_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $brooder["brooder_id"]);
    $stmt->execute();
    $setting = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($setting) $target = $setting["target_temp"];

    /*
    $reading = flask_call('GET', '/api/readings', $brooder["api_key"]);
    if ($reading['status'] === 200) $latest = $reading['data'];

    $setting = flask_call('GET', '/api/settings', $brooder["api_key"]);
    if ($setting['status'] === 200) $target = $setting['data']['target_temp'];
    */

    $latest = mock_live_reading($brooder["brooder_id"], $target);
}
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Dashboard | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>My Brooder</h1>

    <?php if (!$brooder): ?>
        <div class="notice">No brooder has been assigned to you yet. Contact your lecturer or admin.</div>
    <?php else: ?>
    <div class="notice">Showing mock live readings until the Raspberry Pi is connected.</div>
    <div class="cards">
        <a href="temperature.php" style="text-decoration:none;">
            <div class="card">
                <h3>Target Temperature</h3>
                <strong><?php echo $target ? $target . "&deg;C" : "Not set"; ?></strong>
            </div>
        </a>
        <a href="temperature.php" style="text-decoration:none;">
            <div class="card">
                <h3>Current Temperature</h3>
                <strong><?php echo $latest ? $latest["temperature"] . "&deg;C" : "No data"; ?></strong>
            </div>
        </a>
        <a href="temperature.php" style="text-decoration:none;">
            <div class="card">
                <h3>Humidity</h3>
                <strong><?php echo $latest ? $latest["humidity"] . "%" : "No data"; ?></strong>
            </div>
        </a>
        <div class="card">
            <h3>Brooder</h3>
            <strong style="font-size:1.2rem;"><?php echo htmlspecialchars($brooder["name"]); ?></strong>
            <p style="color:var(--teal); margin:0; font-size:0.85rem;"><?php echo htmlspecialchars($brooder["location"] ?? ""); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
