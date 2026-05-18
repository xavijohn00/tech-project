<?php
$host   = "gateway01.us-east-1.prod.aws.tidbcloud.com";
$user   = "49r5VHQULjxi3Up.root";
$pass   = "pNrrdzsMgW5uYz3J";
$dbname = "brooder_system";
$port = 4000;

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
