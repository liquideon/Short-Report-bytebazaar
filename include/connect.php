<?php

// Database credentials
$host = "localhost";
$user = "webuser";
$password = "@Thepassword12345";
$database = "bytebazaar";

// Create connection
$con = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$con) {
    die("Database connection failed");
}

// 🔐 Security improvement
mysqli_set_charset($con, "utf8mb4");

?>
