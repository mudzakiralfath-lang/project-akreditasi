<?php
// C:\xampp\htdocs\e-akreditasi-app\views\dashboard.php
require_once __DIR__ . '/../koneksi.php';

// ============== AUTHENTICATION CHECK ==============
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$current_user = getCurrentUser($conn);
$avatar_dir = __DIR__ . '/../uploads/avatars/';
$user_id = $current_user['id'];
$nama_user = $current_user['full_name'] ?: $current_user['username'];

// ============== HITUNG SEMUA DATA ==============

// Total semua dokumen
$result_total = $conn->query("SELECT COUNT(*) as total FROM borang");
$total_borang = $result_total ? $result_total->fetch_assoc()['total'] : 0;

// Total dokumen user ini
$cek_column = $conn->query("SHOW COLUMNS FROM borang LIKE 'created_by'");
$column_exists = ($cek_column && $cek_column->num_rows > 0);
$user_dokumen = 0;
if ($column_exists) {
    $user_dokumen_result = $conn->query("SELECT COUNT(*) as total FROM borang WHERE created_by = $user_id");
    $user_dokumen = $user_dokumen_result ? $user_dokumen_result->fetch_assoc()['total'] : 0;
}

// Total dokumen (file yang terupload)
$result_dokumen = $conn->query("SELECT COUNT(*) as total FROM borang WHERE file IS NOT NULL AND file != ''");
$total_dokumen = $result_dokumen ? $result_dokumen->fetch_assoc()['total'] : 0;

// Status dokumen semua user
$result_selesai = $conn->query("SELECT COUNT(*) as total FROM borang WHERE status = 'selesai'");
$selesai = $result_selesai ? $result_selesai->fetch_assoc()['total'] : 0;

$result_proses = $conn->query("SELECT COUNT(*) as total FROM borang WHERE status = 'proses'");
$proses = $result_proses ? $result_proses->fetch_assoc()['total'] : 0;

$result_belum = $conn->query("SELECT COUNT(*) as total FROM borang WHERE status = 'belum'");
$belum = $result_belum ? $result_belum->fetch_assoc()['total'] : 0;

// Hitung borang aktif (status proses + selesai)
$borang_aktif = $proses + $selesai;

// Hitung progress semua user
$progress = $total_borang > 0 ? round(($selesai / $total_borang) * 100) : 0;

// Hitung circumference untuk circle progress
$circumference = 364.4;
$stroke_dashoffset = $circumference - (($progress / 100) * $circumference);

// Total folder
$total_folder_result = $conn->query("SELECT COUNT(*) as total FROM folders");
$total_folder = $total_folder_result ? $total_folder_result->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Akreditasi | Akreditasia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; overflow-x: hidden; }
        
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
        
        /* Profile Card di Sidebar */
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
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 32px;
        }
        .header h1 { font-size: 1.8rem; font-weight: 700; color: #0f2b3d; letter-spacing: -0.5px; }
        .header h1 span { background: linear-gradient(135deg, #2563eb, #6366f1); -webkit-background-clip: text; background-clip: text; color: transparent; }
        
        .header-right { display: flex; gap: 12px; align-items: center; }
        
        .btn {
            padding: 10px 20px;
            border-radius: 40px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary { background: linear-gradient(95deg, #2563eb, #1e40af); color: white; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35); }
        .btn-refresh { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
        .btn-refresh:hover { background: #e2e8f0; transform: translateY(-2px); }
        
        .profile-dropdown { position: relative; display: inline-block; }
        .avatar {
            width: 46px;
            height: 46px;
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        .card {
            background: white;
            padding: 22px 24px;
            border-radius: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            transition: all 0.3s;
            border: 1px solid #eef2f8;
        }
        .card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08); border-color: #cbdff2; }
        .card-info h3 { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 8px; }
        .card-info h2 { font-size: 2.2rem; font-weight: 800; color: #0f2b3d; margin: 0; }
        .card-icon { width: 52px; height: 52px; background: #eef3ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #2563eb; }
        
        .folder-stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .folder-stats-card .card-info h3 { color: rgba(255,255,255,0.8); }
        .folder-stats-card .card-info h2 { color: white; }
        .folder-stats-card .card-icon { background: rgba(255,255,255,0.2); color: white; }
        
        .user-stats-card { background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: white; }
        .user-stats-card .card-info h3 { color: rgba(255,255,255,0.8); }
        .user-stats-card .card-info h2 { color: white; }
        .user-stats-card .card-icon { background: rgba(255,255,255,0.2); color: white; }
        
        .progress-box {
            background: white;
            padding: 28px 32px;
            border-radius: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f8;
        }
        .progress-box h3 { font-size: 1.2rem; font-weight: 700; color: #0f2b3d; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
        .progress-content { display: flex; flex-wrap: wrap; align-items: center; gap: 48px; }
        
        .circle { position: relative; width: 140px; height: 140px; }
        .circle svg { transform: rotate(-90deg); }
        .circle-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
        .circle-text h2 { font-size: 1.8rem; font-weight: 800; color: #2563eb; }
        .circle-text small { font-size: 0.7rem; color: #64748b; }
        
        .progress-stats { display: flex; gap: 40px; flex-wrap: wrap; }
        .progress-item {
            text-align: center;
            min-width: 100px;
            padding: 12px 20px;
            background: #f8fafc;
            border-radius: 20px;
            transition: all 0.2s;
        }
        .progress-item:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        .progress-item h2 { font-size: 1.8rem; font-weight: 800; color: #0f2b3d; }
        .progress-item p { font-size: 0.8rem; color: #64748b; margin-top: 6px; }
        .progress-item .badge { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
        
        .last-update { margin-top: 20px; padding-top: 16px; border-top: 1px solid #eef2f8; font-size: 0.7rem; color: #94a3b8; text-align: right; }
        
        @media (max-width: 768px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; position: relative; height: auto; }
            .main { padding: 20px; }
            .stats { grid-template-columns: 1fr; }
            .progress-content { flex-direction: column; text-align: center; gap: 24px; }
            .progress-stats { justify-content: center; }
            .header h1 { font-size: 1.4rem; }
            .dropdown-menu { position: fixed; top: auto; right: 20px; left: 20px; width: auto; }
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
        
        <!-- Profile Card di Sidebar -->
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
            <a href="dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="personal_dashboard.php">
                <i class="fas fa-user-chart"></i> <?= htmlspecialchars($nama_user) ?>
            </a>
            <a href="dokumen.php">
                <i class="fas fa-folder-open"></i> Dokumen
            </a>
            <a href="profile.php">
                <i class="fas fa-user-circle"></i> Profil
            </a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Dashboard <span>Monitoring</span></h1>
            <div class="header-right">
                <a href="dokumen.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Kelola Dokumen
                </a>
                <a href="dashboard.php" class="btn btn-refresh">
                    <i class="fas fa-sync-alt"></i> Refresh
                </a>
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
        </div>

        <!-- CARDS STATISTIK -->
        <div class="stats">
            <div class="card">
                <div class="card-info">
                    <h3>Total Dokumen</h3>
                    <h2><?= $total_borang ?></h2>
                </div>
                <div class="card-icon"><i class="fas fa-file-alt"></i></div>
            </div>
            <div class="card user-stats-card">
                <div class="card-info">
                    <h3>Dokumen Saya</h3>
                    <h2><?= $user_dokumen ?></h2>
                </div>
                <div class="card-icon"><i class="fas fa-user"></i></div>
            </div>
            <div class="card folder-stats-card">
                <div class="card-info">
                    <h3>Total Folder</h3>
                    <h2><?= $total_folder ?></h2>
                </div>
                <div class="card-icon"><i class="fas fa-folder"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Progress Global</h3>
                    <h2><?= $progress ?>%</h2>
                </div>
                <div class="card-icon"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>

        <!-- PROGRESS OVERVIEW -->
        <div class="progress-box">
            <h3><i class="fas fa-chart-line" style="color: #2563eb;"></i> Overview Progress Akreditasi</h3>
            <div class="progress-content">
                <div class="circle">
                    <svg width="140" height="140">
                        <circle cx="70" cy="70" r="58" stroke="#e2e8f0" stroke-width="8" fill="none"/>
                        <circle cx="70" cy="70" r="58" stroke="#2563eb" stroke-width="8" fill="none" 
                            stroke-dasharray="<?= $circumference ?>" stroke-dashoffset="<?= $stroke_dashoffset ?>" stroke-linecap="round"/>
                    </svg>
                    <div class="circle-text">
                        <h2><?= $progress ?>%</h2>
                        <small>Keseluruhan</small>
                    </div>
                </div>
                <div class="progress-stats">
                    <div class="progress-item">
                        <h2><?= $selesai ?></h2>
                        <p><span class="badge" style="background: #10b981;"></span> ✅ Selesai</p>
                    </div>
                    <div class="progress-item">
                        <h2><?= $proses ?></h2>
                        <p><span class="badge" style="background: #f59e0b;"></span> 🔄 Proses</p>
                    </div>
                    <div class="progress-item">
                        <h2><?= $belum ?></h2>
                        <p><span class="badge" style="background: #ef4444;"></span> ⏳ Belum</p>
                    </div>
                </div>
            </div>
            <div class="last-update">
                <i class="fas fa-clock"></i> Terakhir diperbarui: <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        document.getElementById('profileDropdown').classList.toggle('active');
    }
    
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const avatar = document.querySelector('.avatar');
        if (dropdown && !dropdown.contains(event.target) && event.target !== avatar && !avatar?.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) dropdown.classList.remove('active');
        }
    });
</script>

<?php $conn->close(); ?>
</body>
</html>