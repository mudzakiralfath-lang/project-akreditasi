<?php
// C:\xampp\htdocs\e-akreditasi-app\koneksi.php

$host = 'localhost';
$user = 'root';        // user MySQL Anda
$password = '';        // password MySQL Anda
$database = 'e_akreditasi';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Fungsi helper untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser($conn) {
    if (!isset($_SESSION['user_id'])) return null;
    
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    return $result->fetch_assoc();
}

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>