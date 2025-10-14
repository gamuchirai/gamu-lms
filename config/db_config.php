<?php
$servername = "localhost";
$username = "admin";
$password = "Tuk03187";
$dbname = "u376937047_gamuchirai_db";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>