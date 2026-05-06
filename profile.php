<?php
// C:\xampp\htdocs\e-akreditasi-app\views\profile.php
require_once __DIR__ . '/../koneksi.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user = getCurrentUser($conn);
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = $conn->real_escape_string($_POST['full_name']);
        $email = $conn->real_escape_string($_POST['email']);
        
        // Check if email already exists for other users
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != {$user['id']}");
        if ($check_email->num_rows > 0) {
            $error = "Email sudah digunakan oleh pengguna lain!";
        } else {
            $conn->query("UPDATE users SET full_name = '$full_name', email = '$email' WHERE id = {$user['id']}");
            $_SESSION['full_name'] = $full_name;
            $message = "Profil berhasil diperbarui!";
            $user = getCurrentUser($conn);
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $user['password'])) {
            $error = "Password saat ini salah!";
        } elseif ($new_password !== $confirm_password) {
            $error = "Password baru tidak cocok!";
        } elseif (strlen($new_password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashed_password' WHERE id = {$user['id']}");
            $message = "Password berhasil diubah!";
        }
    }
    
    // Handle avatar upload
    if (isset($_POST['upload_avatar']) && isset($_FILES['avatar'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $upload_dir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_filename)) {
                // Delete old avatar if exists
                if ($user['avatar'] && file_exists($upload_dir . $user['avatar'])) {
                    unlink($upload_dir . $user['avatar']);
                }
                
                $conn->query("UPDATE users SET avatar = '$new_filename' WHERE id = {$user['id']}");
                $message = "Avatar berhasil diupload!";
                $user = getCurrentUser($conn);
            } else {
                $error = "Gagal mengupload avatar!";
            }
        } else {
            $error = "Format file tidak didukung! (JPG, JPEG, PNG, GIF)";
        }
    }
}

$avatar_url = $user['avatar'] ? '../uploads/avatars/' . $user['avatar'] : 'https://ui-avatars.com/api/?background=2563eb&color=fff&name=' . urlencode($user['full_name'] ?: $user['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | Akreditasia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f0f4f8;
            overflow-x: hidden;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f2b3d 0%, #0a1a28 100%);
            color: #e8edf2;
            padding: 28px 20px;
            flex-shrink: 0;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
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

        .menu {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration: none;
            color: #cbd5e6;
            font-weight: 500;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .menu a i {
            width: 22px;
        }

        .menu a:hover {
            background: rgba(59, 130, 246, 0.2);
            color: white;
        }

        .menu a.active {
            background: linear-gradient(95deg, #2563eb, #1e40af);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .main {
            flex: 1;
            padding: 28px 32px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f2b3d;
        }

        .header h1 span {
            background: linear-gradient(135deg, #2563eb, #6366f1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .profile-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
        }

        .profile-card {
            background: white;
            border-radius: 24px;
            padding: 28px;
            border: 1px solid #eef2f8;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 24px;
        }

        .avatar-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #eef2f8;
            margin-bottom: 16px;
        }

        .profile-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f2b3d;
        }

        .profile-username {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .profile-role {
            display: inline-block;
            padding: 4px 12px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 12px;
        }

        .form-section {
            background: white;
            border-radius: 24px;
            padding: 28px;
            border: 1px solid #eef2f8;
            margin-bottom: 28px;
        }

        .form-section h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f2b3d;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eef2ff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 8px;
            color: #334155;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn {
            padding: 10px 24px;
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

        .btn-primary {
            background: linear-gradient(95deg, #2563eb, #1e40af);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-logout {
            background: #ef4444;
            color: white;
        }

        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .message {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .message-success {
            background: #d1fae5;
            color: #065f46;
        }

        .message-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .file-input-wrapper {
            position: relative;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .layout {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .main {
                padding: 20px;
            }
            .profile-container {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

<div class="layout">
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h2>Akreditasia</h2>
        </div>
        <div class="menu">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="borang.php">
                <i class="fas fa-file-alt"></i> Borang
            </a>
            <a href="profile.php" class="active">
                <i class="fas fa-user-circle"></i> Profil
            </a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Profil <span>Saya</span></h1>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if ($message): ?>
            <div class="message message-success">
                <i class="fas fa-check-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <!-- Left Column - Avatar & Info -->
            <div class="profile-card">
                <div class="profile-avatar">
                    <img src="<?= $avatar_url ?>" alt="Avatar" class="avatar-image">
                    <h3 class="profile-name"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h3>
                    <p class="profile-username">@<?= htmlspecialchars($user['username']) ?></p>
                    <span class="profile-role">
                        <i class="fas fa-shield-alt"></i> <?= ucfirst($user['role']) ?>
                    </span>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Upload Avatar</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="avatar" accept="image/*" required>
                            <button type="button" class="btn btn-secondary" style="width: 100%;" onclick="this.parentElement.querySelector('input[type=file]').click()">
                                <i class="fas fa-camera"></i> Pilih Gambar
                            </button>
                        </div>
                        <small style="color: #64748b; display: block; margin-top: 8px;">Format: JPG, JPEG, PNG, GIF</small>
                    </div>
                    <button type="submit" name="upload_avatar" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-upload"></i> Upload Avatar
                    </button>
                </form>
            </div>

            <!-- Right Column - Edit Forms -->
            <div>
                <!-- Edit Profile Form -->
                <div class="form-section">
                    <h3>
                        <i class="fas fa-user-edit"></i>
                        Edit Profil
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            <small style="color: #64748b;">Username tidak dapat diubah</small>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="form-section">
                    <h3>
                        <i class="fas fa-key"></i>
                        Ganti Password
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Password Saat Ini</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="new_password" required>
                            <small>Minimal 6 karakter</small>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-lock"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview avatar before upload
    document.querySelector('input[name="avatar"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.avatar-image').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>