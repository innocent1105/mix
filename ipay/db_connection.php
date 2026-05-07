<?php
$host = "localhost";
$db   = "payper";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>