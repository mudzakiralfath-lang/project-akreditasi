<?php
// C:\xampp\htdocs\e-akreditasi-app\views\dokumen.php
require_once __DIR__ . '/../koneksi.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$current_user = getCurrentUser($conn);
$avatar_dir = __DIR__ . '/../uploads/avatars/';
$user_id = $current_user['id'];
$nama_user = $current_user['full_name'] ?: $current_user['username'];
$upload_dir = __DIR__ . '/../uploads/';

if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0777, true);

$selected_folder = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;
$folder_info = null;
if ($selected_folder > 0) {
    $result = $conn->query("SELECT * FROM folders WHERE id = $selected_folder");
    $folder_info = $result->fetch_assoc();
}

// Cek kolom created_by
$cek_column = $conn->query("SHOW COLUMNS FROM borang LIKE 'created_by'");
$column_exists = ($cek_column && $cek_column->num_rows > 0);

// Buat Folder
if (isset($_POST['create_folder'])) {
    $nama_folder = $conn->real_escape_string($_POST['nama_folder']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $created_by = $column_exists ? $user_id : 0;
    $conn->query("INSERT INTO folders (nama_folder, deskripsi, created_by) VALUES ('$nama_folder', '$deskripsi', $created_by)");
    header("Location: dokumen.php");
    exit();
}

// Hapus Folder
if (isset($_GET['delete_folder'])) {
    $id = intval($_GET['delete_folder']);
    $borang = $conn->query("SELECT file FROM borang WHERE folder_id = $id");
    while($row = $borang->fetch_assoc()) {
        $file_path = $upload_dir . $row['file'];
        if (file_exists($file_path)) unlink($file_path);
    }
    $conn->query("DELETE FROM borang WHERE folder_id = $id");
    $conn->query("DELETE FROM folders WHERE id = $id");
    header("Location: dokumen.php");
    exit();
}

// Hapus Dokumen
if (isset($_GET['hapus_dokumen'])) {
    $id = intval($_GET['hapus_dokumen']);
    $get = $conn->query("SELECT file FROM borang WHERE id = $id");
    if ($get && $data = $get->fetch_assoc()) {
        $file_path = $upload_dir . $data['file'];
        if ($data['file'] && file_exists($file_path)) unlink($file_path);
    }
    $conn->query("DELETE FROM borang WHERE id = $id");
    $redirect = $selected_folder ? "dokumen.php?folder_id=$selected_folder" : "dokumen.php";
    header("Location: $redirect");
    exit();
}

// Update Status
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE borang SET status = '$status' WHERE id = $id");
    $redirect = $selected_folder ? "dokumen.php?folder_id=$selected_folder" : "dokumen.php";
    header("Location: $redirect");
    exit();
}

// Edit Dokumen
if (isset($_POST['edit_dokumen'])) {
    $id = intval($_POST['id']);
    $judul = $conn->real_escape_string($_POST['judul']);
    $standar = $conn->real_escape_string($_POST['standar']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $conn->query("UPDATE borang SET judul = '$judul', standar = '$standar', deskripsi = '$deskripsi' WHERE id = $id");
    $redirect = $selected_folder ? "dokumen.php?folder_id=$selected_folder" : "dokumen.php";
    header("Location: $redirect");
    exit();
}

// Ganti File
if (isset($_POST['ganti_file'])) {
    $id = intval($_POST['id']);
    $get = $conn->query("SELECT file FROM borang WHERE id = $id");
    $old_file = $get->fetch_assoc()['file'];
    $old_path = $upload_dir . $old_file;
    if (file_exists($old_path)) unlink($old_path);
    
    $file = $_FILES['file_baru']['name'];
    $tmp = $_FILES['file_baru']['tmp_name'];
    $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file);
    if (move_uploaded_file($tmp, $upload_dir . $newFileName)) {
        $conn->query("UPDATE borang SET file = '$newFileName' WHERE id = $id");
    }
    $redirect = $selected_folder ? "dokumen.php?folder_id=$selected_folder" : "dokumen.php";
    header("Location: $redirect");
    exit();
}

// Upload Dokumen
if (isset($_POST['submit'])) {
    $standar = $conn->real_escape_string($_POST['standar']);
    $judul = $conn->real_escape_string($_POST['judul']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $status = $conn->real_escape_string($_POST['status']);
    $folder_id = intval($_POST['folder_id']);
    $deadline = !empty($_POST['deadline']) ? "'{$_POST['deadline']}'" : "NULL";
    $created_by = $column_exists ? $user_id : 0;
    
    $file = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];
    $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file);
    
    if (move_uploaded_file($tmp, $upload_dir . $newFileName)) {
        $conn->query("INSERT INTO borang (standar, judul, deskripsi, file, status, folder_id, created_by, deadline) 
                      VALUES ('$standar', '$judul', '$deskripsi', '$newFileName', '$status', $folder_id, $created_by, $deadline)");
        
        // Tambah notifikasi jika tabel ada
        $notif_table = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($notif_table && $notif_table->num_rows > 0) {
            $conn->query("INSERT INTO notifications (user_id, title, message, type) 
                          VALUES ($user_id, 'Dokumen Berhasil Diupload', 'Dokumen \"$judul\" berhasil diupload.', 'success')");
        }
    }
    $redirect = $folder_id ? "dokumen.php?folder_id=$folder_id" : "dokumen.php";
    header("Location: $redirect");
    exit();
}

// AMBIL DATA
$dokumen_list = [];
if ($selected_folder > 0) {
    if ($column_exists) {
        $dokumen_list = $conn->query("SELECT * FROM borang WHERE folder_id = $selected_folder AND created_by = $user_id ORDER BY id DESC");
    } else {
        $dokumen_list = $conn->query("SELECT * FROM borang WHERE folder_id = $selected_folder ORDER BY id DESC");
    }
}

$folders = $conn->query("SELECT f.*, (SELECT COUNT(*) FROM borang WHERE folder_id = f.id) as total_dokumen FROM folders f ORDER BY f.created_at DESC");
$total_all = $conn->query("SELECT COUNT(*) as total FROM borang")->fetch_assoc()['total'];
$total_folder_all = $conn->query("SELECT COUNT(*) as total FROM folders")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen | Akreditasia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f0f4f8 0%, #e8edf5 100%); min-height: 100vh; }
        
        .layout { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f2b3d 0%, #0a1a28 100%);
            color: #e8edf2;
            padding: 28px 20px;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .logo h2 {
            font-size: 1.3rem;
            font-weight: 600;
            background: linear-gradient(135deg, #fff, #a5c9ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .sidebar-profile {
            margin-bottom: 24px;
            padding: 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 16px;
        }
        .sidebar-profile-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .sidebar-profile-name {
            font-weight: 700;
            color: white;
            font-size: 0.9rem;
        }
        .sidebar-profile-email {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.7);
        }
        
        .menu { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; }
        .menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration: none;
            color: #cbd5e6;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        .menu a i { width: 22px; font-size: 1.1rem; }
        .menu a:hover { background: rgba(59, 130, 246, 0.2); color: white; transform: translateX(5px); }
        .menu a.active { background: linear-gradient(95deg, #2563eb, #1e40af); color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
        
        .main { flex: 1; padding: 28px 32px; overflow-x: auto; }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header h1 { font-size: 2rem; font-weight: 700; color: #0f2b3d; letter-spacing: -0.5px; }
        .header h1 span { background: linear-gradient(135deg, #2563eb, #6366f1); -webkit-background-clip: text; background-clip: text; color: transparent; }
        
        .profile-dropdown { position: relative; display: inline-block; }
        .avatar {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid white;
            overflow: hidden;
        }
        .avatar:hover { transform: scale(1.08); }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        
        .dropdown-menu {
            position: absolute;
            top: 60px;
            right: 0;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            width: 260px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-15px);
            transition: all 0.3s;
            z-index: 1000;
        }
        .profile-dropdown.active .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-header {
            padding: 20px;
            border-bottom: 1px solid #eef2f8;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .dropdown-avatar {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            overflow: hidden;
        }
        .dropdown-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .dropdown-info h4 { font-size: 0.95rem; font-weight: 700; color: #0f2b3d; }
        .dropdown-info p { font-size: 0.75rem; color: #64748b; }
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            text-decoration: none;
            color: #334155;
            transition: all 0.2s;
        }
        .dropdown-menu a:hover { background: #f1f5f9; padding-left: 24px; }
        .dropdown-divider { height: 1px; background: #eef2f8; margin: 8px 0; }
        .dropdown-menu .logout { color: #ef4444; }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .breadcrumb a { color: #2563eb; text-decoration: none; font-size: 0.85rem; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb span { color: #64748b; font-size: 0.85rem; }
        
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-mini-card {
            background: white;
            padding: 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #eef2f8;
            transition: all 0.3s;
        }
        .stat-mini-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .stat-mini-info h4 { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 6px; }
        .stat-mini-info h3 { font-size: 1.6rem; font-weight: 800; color: #0f2b3d; }
        .stat-mini-icon { width: 48px; height: 48px; background: #eef3ff; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #2563eb; }
        
        .folders-section {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 28px;
            border: 1px solid #eef2f8;
        }
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eef2ff;
        }
        .section-title i { font-size: 1.3rem; color: #2563eb; }
        .section-title h3 { font-size: 1.1rem; font-weight: 700; color: #0f2b3d; }
        
        .folders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .folder-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 16px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            border: 1px solid #eef2f8;
        }
        .folder-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); border-color: #2563eb; background: white; }
        .folder-card.active { border-color: #2563eb; background: linear-gradient(135deg, #eef2ff, white); }
        .folder-icon { width: 50px; height: 50px; background: #fef3c7; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px; color: #d97706; }
        .folder-name { font-weight: 700; color: #0f2b3d; margin-bottom: 4px; }
        .folder-stats {
            font-size: 0.7rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #eef2f8;
        }
        .delete-folder { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.7rem; }
        
        .card {
            background: white;
            border-radius: 24px;
            padding: 28px 32px;
            margin-bottom: 28px;
            border: 1px solid #eef2f8;
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #eef2ff;
        }
        .card-header i { font-size: 1.5rem; color: #2563eb; }
        .card-header h2 { font-size: 1.25rem; font-weight: 700; color: #0f2b3d; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; color: #334155; }
        .form-group label i { margin-right: 8px; color: #2563eb; }
        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            font-family: inherit;
            font-size: 0.9rem;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .btn {
            padding: 12px 28px;
            border-radius: 40px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .btn-primary { background: linear-gradient(95deg, #2563eb, #1e40af); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37,99,235,0.35); }
        .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; }
        
        .dokumen-section {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 28px;
            border: 1px solid #eef2f8;
        }
        .dokumen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .dokumen-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #eef2f8;
            transition: all 0.3s;
        }
        .dokumen-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); border-color: #2563eb; }
        .dokumen-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .dokumen-icon { width: 48px; height: 48px; background: #eef3ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #2563eb; }
        .dokumen-info { flex: 1; }
        .dokumen-judul { font-size: 0.95rem; font-weight: 700; color: #0f2b3d; margin-bottom: 4px; }
        .dokumen-standar { font-size: 0.7rem; color: #64748b; display: inline-block; padding: 2px 8px; background: #eef3ff; border-radius: 20px; }
        .dokumen-deskripsi { font-size: 0.75rem; color: #475569; line-height: 1.4; margin: 12px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .dokumen-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 12px; border-top: 1px solid #eef2f8; flex-wrap: wrap; gap: 10px; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 40px;
            font-size: 0.65rem;
            font-weight: 600;
        }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-proses { background: #fed7aa; color: #92400e; }
        .status-belum { background: #fee2e2; color: #991b1b; }
        
        .action-buttons { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn-icon {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-view { background: #6366f1; color: white; }
        .btn-edit { background: #f59e0b; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .btn-icon:hover { transform: scale(1.02); opacity: 0.9; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eef2ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 { font-size: 1.2rem; color: #0f2b3d; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; }
        .modal-body { margin-bottom: 24px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; }
        
        .empty-state { text-align: center; padding: 60px; color: #94a3b8; }
        .empty-state i { font-size: 4rem; margin-bottom: 16px; color: #cbd5e1; }
        
        @media (max-width: 768px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; }
            .main { padding: 20px; }
            .form-row { grid-template-columns: 1fr; }
            .folders-grid, .dokumen-grid { grid-template-columns: 1fr; }
            .card { padding: 20px; }
            .stats-mini { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="layout">
    <!-- SIDEBAR DENGAN MENU DINAMIS -->
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
            <h2>Akreditasia</h2>
        </div>
        
        <div class="sidebar-profile">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="sidebar-profile-avatar">
                    <?php 
                    if ($current_user && $current_user['avatar'] && file_exists($avatar_dir . $current_user['avatar'])) {
                        echo '<img src="../uploads/avatars/' . $current_user['avatar'] . '" style="width: 100%; height: 100%; object-fit: cover;">';
                    } else {
                        echo '<i class="fas fa-user" style="color: white; font-size: 1.2rem;"></i>';
                    }
                    ?>
                </div>
                <div>
                    <div class="sidebar-profile-name">
                        Halo, <?= htmlspecialchars($nama_user) ?>
                    </div>
                    <div class="sidebar-profile-email">
                        <?= htmlspecialchars($current_user['email']) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="menu">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="personal_dashboard.php">
                <i class="fas fa-user-chart"></i> <?= htmlspecialchars($nama_user) ?>
            </a>
            <a href="dokumen.php" class="active">
                <i class="fas fa-folder-open"></i> Dokumen
            </a>
            <a href="profile.php">
                <i class="fas fa-user-circle"></i> Profil
            </a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Manajemen <span>Dokumen</span></h1>
            <div class="profile-dropdown" id="profileDropdown">
                <div class="avatar" onclick="toggleDropdown()">
                    <?php 
                    if ($current_user && $current_user['avatar'] && file_exists($avatar_dir . $current_user['avatar'])) {
                        echo '<img src="../uploads/avatars/' . $current_user['avatar'] . '" alt="Avatar">';
                    } else {
                        echo strtoupper(substr($nama_user, 0, 2));
                    }
                    ?>
                </div>
                <div class="dropdown-menu">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">
                            <?php 
                            if ($current_user && $current_user['avatar'] && file_exists($avatar_dir . $current_user['avatar'])) {
                                echo '<img src="../uploads/avatars/' . $current_user['avatar'] . '" alt="Avatar">';
                            } else {
                                echo strtoupper(substr($nama_user, 0, 2));
                            }
                            ?>
                        </div>
                        <div class="dropdown-info">
                            <h4><?= htmlspecialchars($nama_user) ?></h4>
                            <p><?= htmlspecialchars($current_user['email']) ?></p>
                        </div>
                    </div>
                    <a href="profile.php"><i class="fas fa-user-circle"></i> Profil Saya</a>
                    <a href="profile.php?tab=password"><i class="fas fa-key"></i> Ubah Password</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-info"><h4>Total Folder</h4><h3><?= $total_folder_all ?></h3></div>
                <div class="stat-mini-icon"><i class="fas fa-folder"></i></div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info"><h4>Total Dokumen</h4><h3><?= $total_all ?></h3></div>
                <div class="stat-mini-icon"><i class="fas fa-file-alt"></i></div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info"><h4>Dokumen Saya</h4><h3><?= $column_exists ? ($conn->query("SELECT COUNT(*) as total FROM borang WHERE created_by = $user_id")->fetch_assoc()['total'] ?? 0) : $total_all ?></h3></div>
                <div class="stat-mini-icon"><i class="fas fa-user"></i></div>
            </div>
        </div>

        <div class="breadcrumb">
            <a href="dokumen.php"><i class="fas fa-home"></i> Semua Folder</a>
            <?php if ($folder_info): ?>
                <i class="fas fa-chevron-right"></i>
                <span><i class="fas fa-folder-open"></i> <?= htmlspecialchars($folder_info['nama_folder']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Form Buat Folder -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-folder-plus"></i>
                <h2>Buat Folder Baru</h2>
            </div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-folder"></i> Nama Folder</label>
                        <input type="text" name="nama_folder" placeholder="Contoh: Akreditasi 2024" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Deskripsi</label>
                        <input type="text" name="deskripsi" placeholder="Deskripsi folder (opsional)">
                    </div>
                </div>
                <button type="submit" name="create_folder" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Folder
                </button>
            </form>
        </div>

        <!-- Daftar Folder -->
        <div class="folders-section">
            <div class="section-title">
                <i class="fas fa-folders"></i>
                <h3>📁 Semua Folder</h3>
            </div>
            <div class="folders-grid">
                <?php if ($folders && $folders->num_rows > 0): ?>
                    <?php while($folder = $folders->fetch_assoc()): ?>
                        <div style="position: relative;">
                            <a href="dokumen.php?folder_id=<?= $folder['id'] ?>" class="folder-card <?= ($selected_folder == $folder['id']) ? 'active' : '' ?>">
                                <div class="folder-icon"><i class="fas fa-folder"></i></div>
                                <div class="folder-name"><?= htmlspecialchars($folder['nama_folder']) ?></div>
                                <div class="folder-stats">
                                    <span><i class="fas fa-file-alt"></i> <?= $folder['total_dokumen'] ?> Dokumen</span>
                                    <?php if($current_user['role'] == 'admin'): ?>
                                        <a href="?delete_folder=<?= $folder['id'] ?>" class="delete-folder" onclick="event.stopPropagation(); return confirm('Yakin hapus folder ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #94a3b8; grid-column: 1/-1;">
                        <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 12px; display: block;"></i>
                        Belum ada folder. Buat folder pertama Anda di atas.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selected_folder > 0 && $folder_info): ?>
        <!-- Form Upload Dokumen -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cloud-upload-alt"></i>
                <h2>Upload Dokumen ke Folder: <?= htmlspecialchars($folder_info['nama_folder']) ?></h2>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="folder_id" value="<?= $selected_folder ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Standar Akreditasi</label>
                        <select name="standar" required>
                            <option value="">Pilih Standar</option>
                            <option value="Standar 1">Standar 1 - Visi, Misi, Tujuan</option>
                            <option value="Standar 2">Standar 2 - Tata Pamong & Kerjasama</option>
                            <option value="Standar 3">Standar 3 - Mahasiswa</option>
                            <option value="Standar 4">Standar 4 - Sumber Daya Manusia</option>
                            <option value="Standar 5">Standar 5 - Kurikulum & Pembelajaran</option>
                            <option value="Standar 6">Standar 6 - Pembiayaan & Sarana Prasarana</option>
                            <option value="Standar 7">Standar 7 - Penelitian & Pengabdian</option>
                            <option value="Standar 8">Standar 8 - Luaran & Capaian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Judul Dokumen</label>
                        <input type="text" name="judul" placeholder="Contoh: Borang Akreditasi Prodi TI" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-chart-line"></i> Status Progress</label>
                        <select name="status" required>
                            <option value="proses">🟡 Proses</option>
                            <option value="selesai">✅ Selesai</option>
                            <option value="belum">🔴 Belum Mulai</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Deadline (Opsional)</label>
                        <input type="date" name="deadline">
                        <small>Batas waktu pengisian dokumen ini</small>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" placeholder="Deskripsikan isi dokumen..."></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-paperclip"></i> File Dokumen</label>
                    <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                    <small style="color: #6c86a3;">📎 Format: PDF, DOC, DOCX, XLS, XLSX</small>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Dokumen
                </button>
            </form>
        </div>

        <!-- Daftar Dokumen dalam Folder -->
        <div class="dokumen-section">
            <div class="section-title">
                <i class="fas fa-file-alt"></i>
                <h3>📄 Dokumen dalam Folder "<?= htmlspecialchars($folder_info['nama_folder']) ?>"</h3>
            </div>
            
            <?php if ($dokumen_list && $dokumen_list->num_rows > 0): ?>
                <div class="dokumen-grid">
                    <?php while($dokumen = $dokumen_list->fetch_assoc()):
                        $statusClass = '';
                        $statusIcon = '';
                        switch($dokumen['status']) {
                            case 'selesai': $statusClass = 'status-selesai'; $statusIcon = '✅'; break;
                            case 'proses': $statusClass = 'status-proses'; $statusIcon = '🟡'; break;
                            default: $statusClass = 'status-belum'; $statusIcon = '🔴';
                        }
                        $fileExt = pathinfo($dokumen['file'], PATHINFO_EXTENSION);
                        $fileIcon = 'fa-file';
                        if ($fileExt == 'pdf') $fileIcon = 'fa-file-pdf';
                        elseif ($fileExt == 'doc' || $fileExt == 'docx') $fileIcon = 'fa-file-word';
                        elseif ($fileExt == 'xls' || $fileExt == 'xlsx') $fileIcon = 'fa-file-excel';
                    ?>
                    <div class="dokumen-card">
                        <div class="dokumen-header">
                            <div class="dokumen-icon"><i class="fas <?= $fileIcon ?>"></i></div>
                            <div class="dokumen-info">
                                <div class="dokumen-judul"><?= htmlspecialchars($dokumen['judul']) ?></div>
                                <span class="dokumen-standar"><?= htmlspecialchars($dokumen['standar']) ?></span>
                                <?php if($dokumen['deadline']): ?>
                                    <span class="dokumen-standar" style="background:#fed7aa;"><i class="fas fa-calendar"></i> Deadline: <?= date('d/m/Y', strtotime($dokumen['deadline'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="dokumen-deskripsi"><?= htmlspecialchars($dokumen['deskripsi'] ?: 'Tidak ada deskripsi') ?></div>
                        <div class="dokumen-footer">
                            <span class="status-badge <?= $statusClass ?>"><?= $statusIcon ?> <?= ucfirst($dokumen['status']) ?></span>
                            <div class="action-buttons">
                                <a href="../uploads/<?= urlencode($dokumen['file']) ?>" target="_blank" class="btn-icon btn-view"><i class="fas fa-eye"></i> Lihat</a>
                                <button class="btn-icon btn-edit" onclick="openEditModal(<?= $dokumen['id'] ?>, '<?= addslashes($dokumen['judul']) ?>', '<?= addslashes($dokumen['standar']) ?>', '<?= addslashes($dokumen['deskripsi']) ?>')"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn-icon btn-edit" onclick="openGantiFileModal(<?= $dokumen['id'] ?>)" style="background:#8b5cf6;"><i class="fas fa-sync-alt"></i> Ganti File</button>
                                <a href="?hapus_dokumen=<?= $dokumen['id'] ?>&folder_id=<?= $selected_folder ?>" class="btn-icon btn-delete" onclick="return confirm('Yakin hapus dokumen ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Belum ada dokumen dalam folder ini.</p>
                    <p style="font-size: 0.8rem; margin-top: 8px;">Upload dokumen pertama Anda menggunakan form di atas.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Dokumen -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Dokumen</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Standar Akreditasi</label>
                    <select name="standar" id="edit_standar" required>
                        <option value="Standar 1">Standar 1 - Visi, Misi, Tujuan</option>
                        <option value="Standar 2">Standar 2 - Tata Pamong & Kerjasama</option>
                        <option value="Standar 3">Standar 3 - Mahasiswa</option>
                        <option value="Standar 4">Standar 4 - Sumber Daya Manusia</option>
                        <option value="Standar 5">Standar 5 - Kurikulum & Pembelajaran</option>
                        <option value="Standar 6">Standar 6 - Pembiayaan & Sarana Prasarana</option>
                        <option value="Standar 7">Standar 7 - Penelitian & Pengabdian</option>
                        <option value="Standar 8">Standar 8 - Luaran & Capaian</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Judul Dokumen</label>
                    <input type="text" name="judul" id="edit_judul" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" name="edit_dokumen" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ganti File -->
<div id="gantiFileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-sync-alt"></i> Ganti File Dokumen</h3>
            <button class="modal-close" onclick="closeGantiFileModal()">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="ganti_id">
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-paperclip"></i> Pilih File Baru</label>
                    <input type="file" name="file_baru" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                    <small>📎 Format: PDF, DOC, DOCX, XLS, XLSX</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeGantiFileModal()">Batal</button>
                <button type="submit" name="ganti_file" class="btn btn-primary">Upload File Baru</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDropdown() {
        document.getElementById('profileDropdown').classList.toggle('active');
    }
    
    function openEditModal(id, judul, standar, deskripsi) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_judul').value = judul;
        document.getElementById('edit_deskripsi').value = deskripsi;
        document.getElementById('edit_standar').value = standar;
        document.getElementById('editModal').style.display = 'flex';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    function openGantiFileModal(id) {
        document.getElementById('ganti_id').value = id;
        document.getElementById('gantiFileModal').style.display = 'flex';
    }
    
    function closeGantiFileModal() {
        document.getElementById('gantiFileModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        const gantiModal = document.getElementById('gantiFileModal');
        const dropdown = document.getElementById('profileDropdown');
        const avatar = document.querySelector('.avatar');
        
        if (event.target == editModal) closeEditModal();
        if (event.target == gantiModal) closeGantiFileModal();
        
        if (dropdown && !dropdown.contains(event.target) && event.target !== avatar) {
            dropdown.classList.remove('active');
        }
    }
</script>

<?php $conn->close(); ?>
</body>
</html>