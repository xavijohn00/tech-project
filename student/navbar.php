<?php $page = basename($_SERVER['PHP_SELF']); ?>
<div class="navbar">
    <a href="/student/dashboard.php"><img src="/images/salcc_black.png" class="logo-header"></a>
    <div class="nav-links">
        <a href="/student/dashboard.php"   class="<?php echo $page==='dashboard.php'  ?'active':''; ?>">Dashboard</a>
        <a href="/student/temperature.php" class="<?php echo $page==='temperature.php'?'active':''; ?>">Temperature</a>
        <a href="/student/nutrition.php"   class="<?php echo $page==='nutrition.php'  ?'active':''; ?>">Nutrition</a>
        <a href="/student/monitoring.php"  class="<?php echo $page==='monitoring.php' ?'active':''; ?>">Monitoring</a>
    </div>
    <div class="nav-right">
        <img src="/images/Notifications_Bell_Outline.png" class="alert-icon" onclick="alert('No new alerts')">
        <div class="profile-circle" onclick="toggleMenu()"><?php echo $current_initial; ?></div>
    </div>
    <div id="dropdown" class="dropdown">
        <p style="margin:0 0 4px; font-size:0.85rem; color:#999;"><?php echo htmlspecialchars($current_full_name); ?></p>
        <p style="margin:0 0 10px; font-size:0.8rem; color:var(--teal);">Student</p>
        <a href="/auth/logout.php" style="color:var(--green); font-weight:bold; text-decoration:none;">Sign Out</a>
    </div>
</div>
