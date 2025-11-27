<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "school";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    // You might want to display a user-friendly message in production
    die("Database connection failed. Please try again later.");
}
?>