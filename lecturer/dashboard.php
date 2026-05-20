<?php
require_once "../auth/session_check.php";
require_once __DIR__ . "/student_mock_data.php";
if ($current_role !== 'lecturer') { header("Location: /index.php"); exit(); }

list($students, $query_error) = lecturer_students_with_mock_data($conn, $current_user_id);
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
    <div class="notice">Search, filter, and expand each student to review mock brooder data.</div>
    <?php if ($query_error): ?><div class="notice error"><?php echo htmlspecialchars($query_error); ?></div><?php endif; ?>

    <?php if (!$query_error && empty($students)): ?>
        <div class="notice">No students have been assigned to you yet.</div>
    <?php elseif (!$query_error): ?>
    <div class="card">
        <h3>Student Overview</h3>
        <div style="display:grid; grid-template-columns: 1fr 220px; gap:12px; margin-bottom:14px;">
            <input type="text" id="student-search" class="input-box" placeholder="Search student or brooder">
            <select id="student-filter" class="input-box">
                <option value="all">All Students</option>
                <option value="assigned">Assigned Brooder</option>
                <option value="unassigned">No Brooder</option>
                <option value="attention">Needs Attention</option>
            </select>
        </div>

        <table id="student-table">
            <tr><th>Student</th><th>Brooder</th><th>Status</th><th>Temp</th><th>Water</th><th>Feed</th><th>Action</th></tr>
            <?php foreach ($students as $s): ?>
            <?php
                $has_brooder = !empty($s["brooder_id"]);
                $needs_attention = !$has_brooder || $s["water_level"] < 60 || $s["feed_level"] < 50;
                $row_filter = ($has_brooder ? "assigned " : "unassigned ") . ($needs_attention ? "attention" : "ok");
                $search_text = strtolower($s["full_name"] . " " . ($s["brooder_name"] ?? ""));
            ?>
            <tr class="student-row" data-filter="<?php echo $row_filter; ?>" data-search="<?php echo htmlspecialchars($search_text); ?>">
                <td><?php echo htmlspecialchars($s["full_name"]); ?></td>
                <td><?php echo $has_brooder ? htmlspecialchars($s["brooder_name"]) : "Not assigned"; ?></td>
                <td><?php echo htmlspecialchars($s["status"]); ?></td>
                <td><?php echo isset($s["temperature"]) ? $s["temperature"] . "&deg;C" : "No data"; ?></td>
                <td><?php echo $s["water_level"]; ?>%</td>
                <td><?php echo $s["feed_level"]; ?>%</td>
                <td><button type="button" class="btn secondary detail-toggle" style="width:auto; padding:6px 12px; font-size:0.8rem;">View</button></td>
            </tr>
            <tr class="student-detail" style="display:none;">
                <td colspan="7">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:14px;">
                        <div><strong style="font-size:1rem;">Location</strong><?php echo htmlspecialchars($s["location"] ?? "-"); ?></div>
                        <div><strong style="font-size:1rem;">Target</strong><?php echo $s["target_temp"]; ?>&deg;C</div>
                        <div><strong style="font-size:1rem;">Humidity</strong><?php echo isset($s["humidity"]) ? $s["humidity"] . "%" : "-"; ?></div>
                        <div><strong style="font-size:1rem;">Activity</strong><?php echo $s["activity"]; ?>%</div>
                        <div><strong style="font-size:1rem;">Light</strong><?php echo $s["light_level"]; ?>%</div>
                        <div><strong style="font-size:1rem;">Updated</strong><?php echo htmlspecialchars($s["recorded_at"] ?? $s["last_seen"]); ?></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include "../auth/footer.php"; ?>
<script>
const searchInput = document.getElementById('student-search');
const filterInput = document.getElementById('student-filter');
function filterStudents() {
    const query = (searchInput.value || '').toLowerCase();
    const filter = filterInput.value;
    document.querySelectorAll('.student-row').forEach(row => {
        const detail = row.nextElementSibling;
        const matchesSearch = row.dataset.search.includes(query);
        const matchesFilter = filter === 'all' || row.dataset.filter.includes(filter);
        const show = matchesSearch && matchesFilter;
        row.style.display = show ? '' : 'none';
        if (!show && detail) detail.style.display = 'none';
    });
}
document.querySelectorAll('.detail-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const detail = button.closest('tr').nextElementSibling;
        const open = detail.style.display !== 'none';
        detail.style.display = open ? 'none' : '';
        button.textContent = open ? 'View' : 'Hide';
    });
});
searchInput.addEventListener('input', filterStudents);
filterInput.addEventListener('change', filterStudents);
</script>
<script src="/script.js"></script>
</body>
</html>
