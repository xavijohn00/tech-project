<?php
// connectdb.php

// Stop mysqli from throwing fatal exceptions
mysqli_report(MYSQLI_REPORT_OFF);

// Get database details from Render Environment Variables
$host   = getenv("DB_HOST");
$user   = getenv("DB_USER");
$pass   = getenv("DB_PASSWORD");
$dbname = getenv("DB_NAME");
$port   = getenv("DB_PORT") ?: 4000;

$conn = mysqli_init();

if (!$conn) {
    die("Database initialization failed.");
}

// TiDB Cloud needs SSL
$success = mysqli_real_connect(
    $conn,
    $host,
    $user,
    $pass,
    $dbname,
    (int)$port,
    null,
    MYSQLI_CLIENT_SSL
);

if (!$success) {
    die("Database connection failed: " . mysqli_connect_error());
}

$conn->set_charset("utf8mb4");
?>
