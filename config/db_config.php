<?php
$servername = "sql109.infinityfree.com";
$username = "if0_40150657";
$password = "rXMjLqilyaOeVQ";
$dbname = "if0_40150657_gamulms";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>