<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'admin') { header("Location: /index.php"); exit(); }

$students  = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()["c"];
$lecturers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='lecturer'")->fetch_assoc()["c"];
$brooders  = $conn->query("SELECT COUNT(*) AS c FROM brooders")->fetch_assoc()["c"];
$assigned  = $conn->query("SELECT COUNT(*) AS c FROM student_brooder")->fetch_assoc()["c"];
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Admin Dashboard | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Admin Dashboard</h1>
    <div class="cards">
        <div class="card">
            <h3>Students</h3>
            <strong><?php echo $students; ?></strong>
            <a href="manage_users.php" class="btn secondary" style="display:inline-block; width:auto; padding:8px 16px; text-decoration:none; margin-top:10px;">Manage →</a>
        </div>
        <div class="card">
            <h3>Lecturers</h3>
            <strong><?php echo $lecturers; ?></strong>
        </div>
        <div class="card">
            <h3>Brooders</h3>
            <strong><?php echo $brooders; ?></strong>
            <a href="manage_brooders.php" class="btn secondary" style="display:inline-block; width:auto; padding:8px 16px; text-decoration:none; margin-top:10px;">Manage →</a>
        </div>
        <div class="card">
            <h3>Assigned Brooders</h3>
            <strong><?php echo $assigned; ?> / <?php echo $brooders; ?></strong>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" style="width:<?php echo $brooders > 0 ? ($assigned/$brooders*100) : 0; ?>%;"></div>
            </div>
            <a href="assign.php" class="btn secondary" style="display:inline-block; width:auto; padding:8px 16px; text-decoration:none; margin-top:10px;">Assign →</a>
        </div>
    </div>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
