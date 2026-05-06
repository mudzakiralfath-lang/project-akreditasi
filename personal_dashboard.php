<?php
// C:\xampp\htdocs\e-akreditasi-app\views\personal_dashboard.php
require_once __DIR__ . '/../koneksi.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$current_user = getCurrentUser($conn);
$avatar_dir = __DIR__ . '/../uploads/avatars/';
$user_id = $current_user['id'];
$nama_user = $current_user['full_name'] ?: $current_user['username'];

// Cek kolom created_by
$cek_column = $conn->query("SHOW COLUMNS FROM borang LIKE 'created_by'");
$column_exists = ($cek_column && $cek_column->num_rows > 0);

// Total dokumen user
if ($column_exists) {
    $result = $conn->query("SELECT COUNT(*) as total FROM borang WHERE created_by = $user_id");
    $user_dokumen = $result ? $result->fetch_assoc()['total'] : 0;
    $selesai = $conn->query("SELECT COUNT(*) as total FROM borang WHERE created_by = $user_id AND status = 'selesai'")->fetch_assoc()['total'] ?? 0;
    $proses = $conn->query("SELECT COUNT(*) as total FROM borang WHERE created_by = $user_id AND status = 'proses'")->fetch_assoc()['total'] ?? 0;
    $belum = $conn->query("SELECT COUNT(*) as total FROM borang WHERE created_by = $user_id AND status = 'belum'")->fetch_assoc()['total'] ?? 0;
    $tugas_list = $conn->query("SELECT * FROM borang WHERE created_by = $user_id AND status = 'belum' ORDER BY deadline ASC LIMIT 5");
    $deadline_list = $conn->query("SELECT * FROM borang WHERE created_by = $user_id AND deadline IS NOT NULL AND status != 'selesai' ORDER BY deadline ASC LIMIT 5");
} else {
    $result = $conn->query("SELECT COUNT(*) as total FROM borang");
    $user_dokumen = $result ? $result->fetch_assoc()['total'] : 0;
    $selesai = $conn->query("SELECT COUNT(*) as total FROM borang WHERE status = 'selesai'")->fetch_assoc()['total'] ?? 0;
    $proses = $conn->query("SELECT COUNT(*) as total FROM borang WHERE status = 'proses'")->fetch_assoc()['total'] ?? 0;
    $belum = $conn->query("SELECT COUNT(*) as total FROM borang WHERE status = 'belum'")->fetch_assoc()['total'] ?? 0;
    $tugas_list = $conn->query("SELECT * FROM borang WHERE status = 'belum' ORDER BY deadline ASC LIMIT 5");
    $deadline_list = $conn->query("SELECT * FROM borang WHERE deadline IS NOT NULL AND status != 'selesai' ORDER BY deadline ASC LIMIT 5");
}

$progress_user = $user_dokumen > 0 ? round(($selesai / $user_dokumen) * 100) : 0;

// Notifikasi
$notif_table = $conn->query("SHOW TABLES LIKE 'notifications'");
$notif_exists = ($notif_table && $notif_table->num_rows > 0);
$notifications = null;
$unread_count = 0;

if ($notif_exists) {
    if (isset($_GET['read_notif'])) {
        $notif_id = intval($_GET['read_notif']);
        $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND user_id = $user_id");
        header("Location: personal_dashboard.php");
        exit();
    }
    if (isset($_GET['read_all'])) {
        $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
        header("Location: personal_dashboard.php");
        exit();
    }
    if (isset($_GET['delete_notif'])) {
        $notif_id = intval($_GET['delete_notif']);
        $conn->query("DELETE FROM notifications WHERE id = $notif_id AND user_id = $user_id");
        header("Location: personal_dashboard.php");
        exit();
    }
    $notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10");
    $unread_count = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['total'];
}

// User Tasks
$task_table = $conn->query("SHOW TABLES LIKE 'user_tasks'");
$task_exists = ($task_table && $task_table->num_rows > 0);
$user_tasks = null;

if ($task_exists) {
    if (isset($_POST['add_task'])) {
        $task_name = $conn->real_escape_string($_POST['task_name']);
        $description = $conn->real_escape_string($_POST['description']);
        $due_date = !empty($_POST['due_date']) ? "'{$_POST['due_date']}'" : "NULL";
        $priority = $conn->real_escape_string($_POST['priority']);
        $conn->query("INSERT INTO user_tasks (user_id, task_name, description, due_date, priority, status) VALUES ($user_id, '$task_name', '$description', $due_date, '$priority', 'pending')");
        header("Location: personal_dashboard.php");
        exit();
    }
    if (isset($_GET['complete_task'])) {
        $task_id = intval($_GET['complete_task']);
        $conn->query("UPDATE user_tasks SET status = 'completed' WHERE id = $task_id AND user_id = $user_id");
        header("Location: personal_dashboard.php");
        exit();
    }
    if (isset($_GET['delete_task'])) {
        $task_id = intval($_GET['delete_task']);
        $conn->query("DELETE FROM user_tasks WHERE id = $task_id AND user_id = $user_id");
        header("Location: personal_dashboard.php");
        exit();
    }
    $user_tasks = $conn->query("SELECT * FROM user_tasks WHERE user_id = $user_id AND status != 'completed' ORDER BY due_date ASC LIMIT 5");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Dashboard | Akreditasia</title>
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
        
        .menu { display: flex; flex-direction: column; gap: 8px; }
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
        }
        .menu a:hover { background: rgba(59, 130, 246, 0.2); color: white; transform: translateX(5px); }
        .menu a.active { background: linear-gradient(95deg, #2563eb, #1e40af); color: white; }
        .menu a i { width: 22px; }
        
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
        
        .main { flex: 1; padding: 28px 32px; overflow-x: auto; }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header h1 { font-size: 1.8rem; font-weight: 700; color: #0f2b3d; }
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
        
        .welcome-banner {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            border-radius: 24px;
            padding: 24px 32px;
            margin-bottom: 32px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .welcome-text h2 { font-size: 1.5rem; margin-bottom: 8px; }
        .welcome-text p { opacity: 0.9; font-size: 0.9rem; }
        .welcome-stats { display: flex; gap: 30px; }
        .welcome-stat { text-align: center; }
        .welcome-stat .number { font-size: 1.8rem; font-weight: 800; }
        .welcome-stat .label { font-size: 0.7rem; opacity: 0.8; }
        
        .progress-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #eef2f8;
        }
        .progress-title {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-weight: 600;
            color: #334155;
        }
        .progress-bar-container {
            background: #e2e8f0;
            border-radius: 30px;
            height: 12px;
            overflow: hidden;
        }
        .progress-bar-fill {
            background: linear-gradient(95deg, #2563eb, #6366f1);
            border-radius: 30px;
            height: 100%;
            width: 0%;
            transition: width 0.5s ease;
        }
        .progress-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #eef2f8;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eef2ff;
        }
        .card-header h3 { font-size: 1rem; font-weight: 700; color: #0f2b3d; }
        .card-header i { color: #2563eb; font-size: 1.2rem; }
        
        .task-item, .deadline-item, .notification-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .task-item:last-child, .deadline-item:last-child, .notification-item:last-child { border-bottom: none; }
        
        .task-info h4 { font-size: 0.85rem; font-weight: 600; color: #0f2b3d; margin-bottom: 4px; }
        .task-info p { font-size: 0.7rem; color: #64748b; }
        .task-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.6rem;
            font-weight: 600;
        }
        .priority-high { background: #fee2e2; color: #991b1b; }
        .priority-medium { background: #fed7aa; color: #92400e; }
        .priority-low { background: #d1fae5; color: #065f46; }
        
        .deadline-date { font-size: 0.7rem; color: #ef4444; }
        .deadline-soon { background: #fee2e2; padding: 2px 8px; border-radius: 20px; font-size: 0.6rem; color: #991b1b; }
        
        .notification-unread { background: #f0f9ff; border-left: 3px solid #2563eb; padding-left: 12px; }
        .notification-title { font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; }
        .notification-message { font-size: 0.7rem; color: #64748b; }
        .notification-time { font-size: 0.6rem; color: #94a3b8; margin-top: 4px; }
        
        .btn-sm {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-outline { background: none; border: 1px solid #e2e8f0; color: #334155; }
        
        .add-task-form {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #eef2f8;
        }
        .form-row { display: flex; gap: 10px; flex-wrap: wrap; }
        .form-row input, .form-row select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.8rem;
            flex: 1;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #94a3b8;
        }
        .empty-state i { font-size: 2rem; margin-bottom: 10px; color: #cbd5e1; }
        
        .badge-warning { background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 20px; font-size: 0.6rem; }
        
        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; }
            .main { padding: 20px; }
            .welcome-banner { flex-direction: column; text-align: center; }
            .welcome-stats { justify-content: center; }
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
            <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="personal_dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'personal_dashboard.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-user-chart"></i> <?= htmlspecialchars($nama_user) ?>
            </a>
            <a href="dokumen.php" <?= basename($_SERVER['PHP_SELF']) == 'dokumen.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-folder-open"></i> Dokumen
            </a>
            <a href="profile.php" <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-user-circle"></i> Profil
            </a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1><span><?= htmlspecialchars($nama_user) ?>'s</span> Dashboard</h1>
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

        <!-- WELCOME BANNER -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Halo, <?= htmlspecialchars($nama_user) ?>! 👋</h2>
                <p>Selamat datang di dashboard personal Anda. Terus tingkatkan progress akreditasi!</p>
            </div>
            <div class="welcome-stats">
                <div class="welcome-stat">
                    <div class="number"><?= $user_dokumen ?></div>
                    <div class="label">Total Dokumen</div>
                </div>
                <div class="welcome-stat">
                    <div class="number"><?= $selesai ?></div>
                    <div class="label">Selesai</div>
                </div>
                <div class="welcome-stat">
                    <div class="number"><?= $belum ?></div>
                    <div class="label">Belum</div>
                </div>
            </div>
        </div>

        <!-- PROGRESS BAR -->
        <div class="progress-card">
            <div class="progress-title">
                <span><i class="fas fa-chart-line"></i> Progress Personal Anda</span>
                <span><?= $progress_user ?>%</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $progress_user ?>%;"></div>
            </div>
            <div class="progress-stats">
                <span>✅ Selesai: <?= $selesai ?> dokumen</span>
                <span>🟡 Proses: <?= $proses ?> dokumen</span>
                <span>🔴 Belum: <?= $belum ?> dokumen</span>
            </div>
        </div>

        <!-- DASHBOARD GRID -->
        <div class="dashboard-grid">
            
            <!-- TUGAS -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tasks"></i> Tugas yang Harus Dikerjakan</h3>
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <?php if ($tugas_list && $tugas_list->num_rows > 0): ?>
                        <?php while($tugas = $tugas_list->fetch_assoc()): ?>
                            <div class="task-item">
                                <div class="task-info">
                                    <h4><?= htmlspecialchars($tugas['judul']) ?></h4>
                                    <p><?= htmlspecialchars($tugas['standar']) ?></p>
                                </div>
                                <a href="dokumen.php?folder_id=<?= $tugas['folder_id'] ?>" class="btn-sm btn-outline">
                                    <i class="fas fa-arrow-right"></i> Kerjakan
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>Semua tugas selesai! Selamat! 🎉</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($task_exists): ?>
                <div class="add-task-form">
                    <form method="POST">
                        <div class="form-row">
                            <input type="text" name="task_name" placeholder="Tugas baru..." required>
                            <input type="date" name="due_date">
                            <select name="priority">
                                <option value="high">🔥 High</option>
                                <option value="medium">🟡 Medium</option>
                                <option value="low">✅ Low</option>
                            </select>
                            <button type="submit" name="add_task" class="btn-sm btn-success">+ Tambah</button>
                        </div>
                        <input type="hidden" name="description" value="">
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- DEADLINE -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Deadline Pribadi</h3>
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <?php if ($deadline_list && $deadline_list->num_rows > 0): ?>
                        <?php while($deadline = $deadline_list->fetch_assoc()): 
                            $deadline_date = $deadline['deadline'];
                            $is_soon = ($deadline_date <= date('Y-m-d', strtotime('+3 days')));
                        ?>
                            <div class="deadline-item">
                                <div>
                                    <h4><?= htmlspecialchars($deadline['judul']) ?></h4>
                                    <p><?= htmlspecialchars($deadline['standar']) ?></p>
                                </div>
                                <div>
                                    <?php if($is_soon): ?>
                                        <span class="deadline-soon"><i class="fas fa-exclamation-triangle"></i> Segera!</span>
                                    <?php endif; ?>
                                    <div class="deadline-date">
                                        <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($deadline_date)) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <p>Tidak ada deadline mendekati</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($task_exists): ?>
                <div class="card-header" style="margin-top: 16px; border-top: 1px solid #eef2f8; padding-top: 16px;">
                    <h3><i class="fas fa-list"></i> Task List Saya</h3>
                </div>
                <div>
                    <?php if ($user_tasks && $user_tasks->num_rows > 0): ?>
                        <?php while($task = $user_tasks->fetch_assoc()): 
                            $priority_class = $task['priority'] == 'high' ? 'priority-high' : ($task['priority'] == 'medium' ? 'priority-medium' : 'priority-low');
                        ?>
                            <div class="task-item">
                                <div class="task-info">
                                    <h4><?= htmlspecialchars($task['task_name']) ?></h4>
                                    <p>
                                        <span class="task-priority <?= $priority_class ?>"><?= ucfirst($task['priority']) ?></span>
                                        <?php if($task['due_date']): ?>
                                            <span style="margin-left: 8px;"><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($task['due_date'])) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <a href="?complete_task=<?= $task['id'] ?>" class="btn-sm btn-success" onclick="return confirm('Selesaikan tugas ini?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="?delete_task=<?= $task['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Hapus tugas ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-square"></i>
                            <p>Belum ada task list</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- NOTIFIKASI -->
            <?php if ($notif_exists): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-bell"></i> Notifikasi</h3>
                    <div>
                        <?php if($unread_count > 0): ?>
                            <span class="badge-warning" style="margin-right: 8px;"><?= $unread_count ?> baru</span>
                        <?php endif; ?>
                        <a href="?read_all=1" class="btn-sm btn-outline" style="font-size: 0.6rem;">Tandai semua</a>
                    </div>
                </div>
                <div>
                    <?php if ($notifications && $notifications->num_rows > 0): ?>
                        <?php while($notif = $notifications->fetch_assoc()): ?>
                            <div class="notification-item <?= $notif['is_read'] ? '' : 'notification-unread' ?>">
                                <div style="flex: 1;">
                                    <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
                                    <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
                                    <div class="notification-time">
                                        <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                    </div>
                                </div>
                                <div>
                                    <?php if(!$notif['is_read']): ?>
                                        <a href="?read_notif=<?= $notif['id'] ?>" class="btn-sm btn-outline" style="font-size: 0.6rem;">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?delete_notif=<?= $notif['id'] ?>" class="btn-sm btn-danger" style="font-size: 0.6rem;" onclick="return confirm('Hapus notifikasi?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <p>Tidak ada notifikasi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- RINGKASAN -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-simple"></i> Ringkasan Aktivitas</h3>
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>📄 Total Dokumen</span>
                            <strong><?= $user_dokumen ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>✅ Dokumen Selesai</span>
                            <strong style="color: #10b981;"><?= $selesai ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>🟡 Dalam Proses</span>
                            <strong style="color: #f59e0b;"><?= $proses ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>🔴 Belum Dikerjakan</span>
                            <strong style="color: #ef4444;"><?= $belum ?></strong>
                        </div>
                    </div>
                    <div class="progress-card" style="margin-bottom: 0; padding: 12px;">
                        <div class="progress-title">
                            <span>Target Completion</span>
                            <span><?= $progress_user ?>%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= $progress_user ?>%;"></div>
                        </div>
                        <div class="progress-stats" style="margin-top: 8px;">
                            <span>Target: 100%</span>
                            <span><?= 100 - $progress_user ?>% lagi</span>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 16px; text-align: center;">
                    <a href="dokumen.php" class="btn-sm btn-success">
                        <i class="fas fa-upload"></i> Upload Dokumen Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        document.getElementById('profileDropdown').classList.toggle('active');
    }
    
    window.onclick = function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const avatar = document.querySelector('.avatar');
        if (dropdown && !dropdown.contains(event.target) && event.target !== avatar) {
            dropdown.classList.remove('active');
        }
    }
</script>

<?php $conn->close(); ?>
</body>
</html>