<?php
require_once "../auth/session_check.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title><?php echo ucfirst("nutrition"); ?> | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1><?php echo ucfirst("nutrition"); ?></h1>
    <div class="notice"><strong>MOCK SYSTEM:</strong> This module is a visual prototype.</div>
    <div class="cards">
        <div class="card"><h3>Water Tank</h3><strong>60%</strong>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:60%;"></div></div>
        </div>
        <div class="card"><h3>Feed Storage</h3><strong>45%</strong>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:45%;"></div></div>
        </div>
    </div>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
