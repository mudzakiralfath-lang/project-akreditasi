<?php
// C:\xampp\htdocs\e-akreditasi-app\koneksi.php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'e-akreditasi';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser($conn) {
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}
?>