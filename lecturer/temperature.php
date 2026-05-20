<?php
require_once "../auth/session_check.php";
require_once __DIR__ . "/student_mock_data.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

list($students, $query_error) = lecturer_students_with_mock_data($conn, $current_user_id);
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
    <div class="notice">Read-only mock readings until the Raspberry Pi is connected.</div>
    <?php if ($query_error): ?><div class="notice error"><?php echo htmlspecialchars($query_error); ?></div><?php endif; ?>

    <?php if (!$query_error): ?>
    <div class="card">
        <h3>Student Temperatures</h3>
        <div style="display:grid; grid-template-columns: 1fr 220px; gap:12px; margin-bottom:14px;">
            <input type="text" id="student-search" class="input-box" placeholder="Search student or brooder">
            <select id="student-filter" class="input-box">
                <option value="all">All Students</option>
                <option value="assigned">Assigned Brooder</option>
                <option value="unassigned">No Brooder</option>
                <option value="attention">Needs Attention</option>
            </select>
        </div>
        <table>
            <tr><th>Student</th><th>Brooder</th><th>Current</th><th>Target</th><th>Humidity</th><th>Action</th></tr>
            <?php foreach ($students as $s): ?>
            <?php
                $has_brooder = !empty($s["brooder_id"]);
                $needs_attention = !$has_brooder || !isset($s["temperature"]);
                $row_filter = ($has_brooder ? "assigned " : "unassigned ") . ($needs_attention ? "attention" : "ok");
                $search_text = strtolower($s["full_name"] . " " . ($s["brooder_name"] ?? ""));
            ?>
            <tr class="student-row" data-filter="<?php echo $row_filter; ?>" data-search="<?php echo htmlspecialchars($search_text); ?>">
                <td><?php echo htmlspecialchars($s["full_name"]); ?></td>
                <td><?php echo $has_brooder ? htmlspecialchars($s["brooder_name"]) : "Not assigned"; ?></td>
                <td><?php echo isset($s["temperature"]) ? $s["temperature"] . "&deg;C" : "No data"; ?></td>
                <td><?php echo $s["target_temp"]; ?>&deg;C</td>
                <td><?php echo isset($s["humidity"]) ? $s["humidity"] . "%" : "-"; ?></td>
                <td><button type="button" class="btn secondary detail-toggle" style="width:auto; padding:6px 12px; font-size:0.8rem;">View</button></td>
            </tr>
            <tr class="student-detail" style="display:none;"><td colspan="6">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:14px;">
                    <div><strong style="font-size:1rem;">Status</strong><?php echo htmlspecialchars($s["status"]); ?></div>
                    <div><strong style="font-size:1rem;">Location</strong><?php echo htmlspecialchars($s["location"] ?? "-"); ?></div>
                    <div><strong style="font-size:1rem;">Mock Update</strong><?php echo htmlspecialchars($s["recorded_at"] ?? "-"); ?></div>
                </div>
            </td></tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script>
const searchInput = document.getElementById('student-search');
const filterInput = document.getElementById('student-filter');
function filterStudents(){const q=(searchInput.value||'').toLowerCase();const f=filterInput.value;document.querySelectorAll('.student-row').forEach(r=>{const d=r.nextElementSibling;const show=r.dataset.search.includes(q)&&(f==='all'||r.dataset.filter.includes(f));r.style.display=show?'':'none';if(!show&&d)d.style.display='none';});}
document.querySelectorAll('.detail-toggle').forEach(b=>b.addEventListener('click',()=>{const d=b.closest('tr').nextElementSibling;const o=d.style.display!=='none';d.style.display=o?'none':'';b.textContent=o?'View':'Hide';}));
searchInput.addEventListener('input',filterStudents);filterInput.addEventListener('change',filterStudents);
</script>
<script src="/script.js"></script>
</body>
</html>
