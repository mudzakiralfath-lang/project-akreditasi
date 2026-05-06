<?php
require_once 'koneksi.php';

echo "<h2>Test Database</h2>";

// Cek koneksi
if ($conn) {
    echo "✅ Koneksi berhasil<br>";
}

// Cek tabel users
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "✅ Tabel users ada<br>";
} else {
    echo "❌ Tabel users TIDAK ada<br>";
}

// Cek data users
$result = $conn->query("SELECT * FROM users");
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✅ User ditemukan: " . $user['username'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    
    // Test password
    $test_password = 'admin123';
    if (password_verify($test_password, $user['password'])) {
        echo "✅ Password 'admin123' COCOK!<br>";
    } else {
        echo "❌ Password 'admin123' TIDAK COCOK<br>";
    }
} else {
    echo "❌ Tidak ada user di database<br>";
}

// Test buat session
$_SESSION['test'] = 'OK';
echo "<br>Session: " . ($_SESSION['test'] ?? 'GAGAL');
?>