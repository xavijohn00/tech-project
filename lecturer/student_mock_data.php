<?php
require_once __DIR__ . "/../auth/connectdb.php";
require_once __DIR__ . "/../auth/flask.php";

function lecturer_students_with_mock_data($conn, $lecturer_id) {
    $students = array();
    $query_error = "";

    $stmt = $conn->prepare("
        SELECT u.user_id, u.full_name,
               b.brooder_id, b.name AS brooder_name, b.location
        FROM lecturer_student ls
        JOIN users u ON u.user_id = ls.student_id
        LEFT JOIN student_brooder sb ON sb.student_id = u.user_id
        LEFT JOIN brooders b ON b.brooder_id = sb.brooder_id
        WHERE ls.lecturer_id = ?
        ORDER BY u.full_name
    ");

    if (!$stmt) {
        return array(array(), "Could not load students: " . $conn->error);
    }

    $stmt->bind_param("i", $lecturer_id);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($students as &$student) {
        $seed = intval($student["brooder_id"] ?: $student["user_id"]);
        $student["target_temp"] = 32.5;
        $student["temperature"] = null;
        $student["humidity"] = null;
        $student["recorded_at"] = null;
        $student["water_level"] = 55 + (($seed * 13 + intval(time() / 300)) % 35);
        $student["feed_level"] = 40 + (($seed * 17 + intval(time() / 420)) % 45);
        $student["activity"] = 65 + (($seed * 11 + intval(time() / 180)) % 30);
        $student["light_level"] = 45 + (($seed * 7 + intval(time() / 240)) % 45);
        $student["status"] = $student["brooder_id"] ? "Online" : "No brooder";
        $student["last_seen"] = date("Y-m-d H:i:s", time() - (($seed * 23) % 240));

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

    return array($students, $query_error);
}
?>
