<?php
$host = 'localhost';
$user = 'root';      // change if needed
$password = '';      // change if needed
$database = 'company_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>