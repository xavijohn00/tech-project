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

$success = $errors = [];

// Student sets a new target temperature — PHP calls Flask, not MySQL directly
if ($_SERVER["REQUEST_METHOD"] === "POST" && $brooder) {
    $target_temp = floatval($_POST["target_temp"]);

    if ($target_temp <= 0) {
        $errors[] = "Please enter a valid temperature";
    } else {
        $response = flask_call('POST', '/api/settings', [
            'target_temp' => $target_temp,
            'student_id'  => $current_user_id
        ]);

        if ($response['status'] === 201) {
            $success[] = "Target temperature set to " . $target_temp . "°C";
        } else {
            $errors[] = "Could not save temperature. API error.";
        }
    }
}

// Get latest data from Flask
$latest = $target = null;
if ($brooder) {
    $reading = flask_call('GET', '/api/readings');
    if ($reading['status'] === 200) $latest = $reading['data'];

    $setting = flask_call('GET', '/api/settings');
    if ($setting['status'] === 200) $target = $setting['data']['target_temp'];
}
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
    <h1>Climate Control</h1>

    <?php foreach ($errors  as $e): ?><div class="notice error"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <?php foreach ($success as $s): ?><div class="notice success"><?php echo htmlspecialchars($s); ?></div><?php endforeach; ?>

    <?php if (!$brooder): ?>
        <div class="notice">No brooder assigned yet.</div>
    <?php else: ?>
    <div class="cards">
        <!-- Set target temp — posts to Flask via PHP -->
        <div class="card">
            <h3>Set Target Temperature</h3>
            <strong><?php echo $target ? $target . "°C" : "Not set"; ?></strong>
            <div class="control-panel">
                <form method="post">
                    <input type="number" name="target_temp" step="0.1" min="0" max="60"
                           placeholder="Target °C" class="input-box" required>
                    <button type="submit" class="btn">Set Temperature</button>
                </form>
            </div>
        </div>

        <!-- Live reading from Flask -->
        <div class="card">
            <h3>Current Reading</h3>
            <strong><?php echo $latest ? $latest["temperature"] . "°C" : "No data"; ?></strong>
            <p style="color:var(--teal); font-size:0.85rem; margin:6px 0;">
                Humidity: <?php echo $latest ? $latest["humidity"] . "%" : "—"; ?><br>
                <?php echo $latest ? "Last updated: " . $latest["recorded_at"] : "Awaiting sensor data from RPi"; ?>
            </p>
        </div>

        <!-- Fans — UI mock only -->
        <div class="card">
            <h3>Fans</h3>
            <strong id="fan-status">AUTO: 40%</strong>
            <div class="control-panel">
                <label style="color:var(--teal);">Auto Mode:</label>
                <button id="fan-mode-btn" data-mode="auto" onclick="toggleFanMode()" class="btn" style="width:auto; padding:8px 20px; margin-top:8px;">ON</button>
                <div id="fan-manual" style="display:none; margin-top:10px;">
                    <input type="range" min="0" max="100" value="40" oninput="setFanSpeed(this.value)">
                </div>
            </div>
        </div>

        <!-- Heating — UI mock only -->
        <div class="card">
            <h3>Heating</h3>
            <strong id="heat-status">AUTO: ON</strong>
            <div class="control-panel">
                <label style="color:var(--teal);">Auto Mode:</label>
                <button id="heat-mode-btn" data-mode="auto" onclick="toggleHeatMode()" class="btn" style="width:auto; padding:8px 20px; margin-top:8px;">ON</button>
                <div id="heat-manual" style="display:none; margin-top:10px;">
                    <button id="heat-power-btn" data-state="on" onclick="toggleHeatPower()" class="btn" style="width:auto; padding:8px 20px;">POWER ON</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
