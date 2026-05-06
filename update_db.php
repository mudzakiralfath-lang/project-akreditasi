<?php
// update_db.php - Jalankan file ini untuk menambah kolom status
require_once 'koneksi.php';

// Cek apakah kolom status sudah ada
$result = $conn->query("SHOW COLUMNS FROM borang LIKE 'status'");
$exists = $result->num_rows > 0;

if (!$exists) {
    // Tambah kolom status
    $sql = "ALTER TABLE borang ADD COLUMN status ENUM('selesai', 'proses', 'belum') DEFAULT 'proses'";
    if ($conn->query($sql)) {
        echo "✅ Kolom 'status' berhasil ditambahkan ke tabel borang<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
} else {
    echo "✅ Kolom 'status' sudah ada di tabel borang<br>";
}

// Update beberapa data contoh dengan status berbeda
$conn->query("UPDATE borang SET status = 'selesai' WHERE id = 1");
$conn->query("UPDATE borang SET status = 'proses' WHERE id = 2");
$conn->query("UPDATE borang SET status = 'belum' WHERE id = 3");

echo "<br>✅ Update database selesai!<br>";
echo "<a href='views/dashboard.php'>Kembali ke Dashboard</a>";

$conn->close();
?>