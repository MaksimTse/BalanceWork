<?php
$servername = "localhost";
$username = "Balancer";
$password = "1";
$dbname = "balance_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
