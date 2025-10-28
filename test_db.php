<?php
// test_db.php - debug only, remove after use
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost"; // ← Change this
$username = "u376937047_gamuchirai";
$password = "qD57E?S&";
$dbname = "u376937047_gamuchirai_db";

// Add connection timeout and better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
    
    echo 'DB connected OK!<br>';
    echo 'Server: ' . htmlspecialchars($servername) . '<br>';
    echo 'Database: ' . htmlspecialchars($dbname) . '<br>';
    echo 'MySQL version: ' . htmlspecialchars($conn->server_info);
    
    $conn->close();
} catch (mysqli_sql_exception $e) {
    echo '<strong>Connection Failed!</strong><br>';
    echo 'Error: ' . htmlspecialchars($e->getMessage()) . '<br>';
    echo 'Error Code: ' . $e->getCode() . '<br>';
    error_log('MySQL Connection Error: ' . $e->getMessage());
}
?>