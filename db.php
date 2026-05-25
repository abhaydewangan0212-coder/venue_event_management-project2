<?php
// db.php
$host = "localhost";
$user = "root";
$pass = "Abhay dewangan@123";
$dbname = "eventdb";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>