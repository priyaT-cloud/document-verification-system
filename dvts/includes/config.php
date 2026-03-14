<?php
// Database Configuration
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dvts');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("<div style='color:red; padding:20px; font-family:Arial;'>
        <h3>Database Connection Failed</h3>
        <p>Error: " . mysqli_connect_error() . "</p>
        <p>Please make sure XAMPP MySQL is running and you have imported <strong>database.sql</strong></p>
    </div>");
}

mysqli_set_charset($conn, "utf8");

// Upload directory
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/dvts/uploads/');
define('UPLOAD_URL', 'http://localhost:8080/dvts/uploads/');

// Create upload directory if not exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

session_start();
?>
