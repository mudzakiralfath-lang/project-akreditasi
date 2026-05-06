<?php
// init_db.php - Jalankan file ini sekali untuk setup database
$host = "localhost";
$user = "root";
$password = "";

// Buat koneksi tanpa database
$conn = new mysqli($host, $user, $password);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buat database jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS akreditasi";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database 'akreditasi' berhasil dibuat atau sudah ada.<br>";
} else {
    echo "❌ Error membuat database: " . $conn->error . "<br>";
}

// Pilih database
$conn->select_db("akreditasi");

// Buat tabel borang (TANPA created_at dan updated_at)
$sql_table = "CREATE TABLE IF NOT EXISTS borang (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    standar VARCHAR(100) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    file VARCHAR(255) NOT NULL,
    tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_table) === TRUE) {
    echo "✅ Tabel 'borang' berhasil dibuat atau sudah ada.<br>";
} else {
    echo "❌ Error membuat tabel: " . $conn->error . "<br>";
}

// Tambahkan data contoh (opsional)
$sql_check = "SELECT COUNT(*) as total FROM borang";
$result = $conn->query($sql_check);
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    $sql_insert = "INSERT INTO borang (standar, judul, deskripsi, file) VALUES
        ('Standar 1', 'Borang Standar 1 - Visi Misi', 'Dokumen borang untuk standar 1 tentang visi, misi dan tujuan program studi', 'contoh_standar1.pdf'),
        ('Standar 2', 'Borang Standar 2 - Tata Pamong', 'Dokumen borang untuk standar 2 tentang tata pamong dan kerjasama', 'contoh_standar2.pdf'),
        ('Standar 3', 'Borang Standar 3 - Mahasiswa', 'Dokumen borang untuk standar 3 tentang manajemen mahasiswa', 'contoh_standar3.pdf')";
    
    if ($conn->query($sql_insert) === TRUE) {
        echo "✅ Data contoh berhasil ditambahkan.<br>";
    }
}

echo "<br><hr>";
echo "<h3>✅ Setup Database Selesai!</h3>";
echo "<p>Silakan akses:</p>";
echo "<ul>";
echo "<li><a href='views/dashboard.php'>📊 Dashboard Monitoring</a></li>";
echo "<li><a href='views/borang.php'>📁 Manajemen Borang</a></li>";
echo "</ul>";

$conn->close();
?>