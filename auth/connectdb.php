<?php
// Prevent mysqli from throwing unhandled fatal exceptions and breaking the layout
mysqli_report(MYSQLI_REPORT_OFF);

$host   = "gateway01.us-east-1.prod.aws.tidbcloud.com";
$user   = "49r5VHQULjxi3Up.root";
$pass   = "pNrrdzsMgW5uYz3J";
$dbname = "brooder_system";
$port   = 4000;

// 1. Initialize the connection object
$conn = mysqli_init();

if (!$conn) {
    die("Database initialization failed.");
}

// 2. Establish the connection while explicitly forcing standard TLS/SSL encryption
$success = @mysqli_real_connect(
    $conn, 
    $host, 
    $user, 
    $pass, 
    $dbname, 
    $port, 
    NULL, 
    MYSQLI_CLIENT_SSL
);

if (!$success) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
