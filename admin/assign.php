<?php
require_once "../auth/session_check.php";
require_once "../auth/connectdb.php";
if ($current_role !== 'admin') { header("Location: /index.php"); exit(); }

$success = $errors = [];

// Assign brooder to student
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["action"] === "assign_brooder") {
    $student_id = intval($_POST["student_id"]);
    $brooder_id = intval($_POST["brooder_id"]);
    $stmt = $conn->prepare("INSERT INTO student_brooder (student_id, brooder_id) VALUES (?,?) ON DUPLICATE KEY UPDATE brooder_id=?");
    $stmt->bind_param("iii", $student_id, $brooder_id, $brooder_id);
    $stmt->execute() ? $success[] = "Brooder assigned" : $errors[] = "Already assigned";
    $stmt->close();
}

// Assign student to lecturer
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["action"] === "assign_lecturer") {
    $lecturer_id = intval($_POST["lecturer_id"]);
    $student_id  = intval($_POST["student_id"]);
    $stmt = $conn->prepare("INSERT IGNORE INTO lecturer_student (lecturer_id, student_id) VALUES (?,?)");
    $stmt->bind_param("ii", $lecturer_id, $student_id);
    $stmt->execute() ? $success[] = "Student assigned to lecturer" : $errors[] = "Error assigning";
    $stmt->close();
}

$students  = $conn->query("SELECT user_id, full_name FROM users WHERE role='student' ORDER BY full_name");
$lecturers = $conn->query("SELECT user_id, full_name FROM users WHERE role='lecturer' ORDER BY full_name");
$brooders  = $conn->query("SELECT brooder_id, name, location FROM brooders WHERE status='active' ORDER BY name");

// Current assignments
$assignments = $conn->query("
    SELECT u.full_name AS student, b.name AS brooder, b.location
    FROM student_brooder sb
    JOIN users u ON u.user_id = sb.student_id
    JOIN brooders b ON b.brooder_id = sb.brooder_id
    ORDER BY u.full_name
");

$lec_assignments = $conn->query("
    SELECT l.full_name AS lecturer, s.full_name AS student
    FROM lecturer_student ls
    JOIN users l ON l.user_id = ls.lecturer_id
    JOIN users s ON s.user_id = ls.student_id
    ORDER BY l.full_name, s.full_name
");
?>
<!DOCTYPE html>
<html id="top">
<head>
    <title>Assignments | SALCC</title>
    <link rel="icon" type="image/png" href="/images/salcc-logo-30.png">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <h1>Assignments</h1>

    <?php foreach ($errors  as $e): ?><div class="notice error"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <?php foreach ($success as $s): ?><div class="notice success"><?php echo htmlspecialchars($s); ?></div><?php endforeach; ?>

    <div class="cards">
        <!-- Assign brooder to student -->
        <div class="card">
            <h3>Assign Brooder to Student</h3>
            <form method="post">
                <input type="hidden" name="action" value="assign_brooder">
                <select name="student_id" class="input-box" required>
                    <option value="">Select Student</option>
                    <?php
                    $students->data_seek(0);
                    while ($s = $students->fetch_assoc()):
                    ?>
                        <option value="<?php echo $s["user_id"]; ?>"><?php echo htmlspecialchars($s["full_name"]); ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="brooder_id" class="input-box" required>
                    <option value="">Select Brooder</option>
                    <?php
                    $brooders->data_seek(0);
                    while ($b = $brooders->fetch_assoc()):
                    ?>
                        <option value="<?php echo $b["brooder_id"]; ?>"><?php echo htmlspecialchars($b["name"]); ?> — <?php echo htmlspecialchars($b["location"]); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn">Assign Brooder</button>
            </form>
        </div>

        <!-- Assign student to lecturer -->
        <div class="card">
            <h3>Assign Student to Lecturer</h3>
            <form method="post">
                <input type="hidden" name="action" value="assign_lecturer">
                <select name="lecturer_id" class="input-box" required>
                    <option value="">Select Lecturer</option>
                    <?php
                    $lecturers->data_seek(0);
                    while ($l = $lecturers->fetch_assoc()):
                    ?>
                        <option value="<?php echo $l["user_id"]; ?>"><?php echo htmlspecialchars($l["full_name"]); ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="student_id" class="input-box" required>
                    <option value="">Select Student</option>
                    <?php
                    $students->data_seek(0);
                    while ($s = $students->fetch_assoc()):
                    ?>
                        <option value="<?php echo $s["user_id"]; ?>"><?php echo htmlspecialchars($s["full_name"]); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn">Assign Student</button>
            </form>
        </div>
    </div>

    <!-- Current brooder assignments -->
    <div class="card" style="margin-top:30px;">
        <h3>Current Brooder Assignments</h3>
        <table>
            <tr><th>Student</th><th>Brooder</th><th>Location</th></tr>
            <?php while ($a = $assignments->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($a["student"]); ?></td>
                <td><?php echo htmlspecialchars($a["brooder"]); ?></td>
                <td><?php echo htmlspecialchars($a["location"]); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Current lecturer assignments -->
    <div class="card" style="margin-top:20px;">
        <h3>Lecturer Oversight Assignments</h3>
        <table>
            <tr><th>Lecturer</th><th>Student</th></tr>
            <?php while ($a = $lec_assignments->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($a["lecturer"]); ?></td>
                <td><?php echo htmlspecialchars($a["student"]); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<?php include "../auth/footer.php"; ?>
<script src="/script.js"></script>
</body>
</html>
