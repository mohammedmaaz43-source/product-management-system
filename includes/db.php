<?php

$host = "sql113.infinityfree.com";
$username = "if0_42800588";
$password = "4mu5fBRthiv";
$database = "if0_42800588_product_management_system";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>