<?php
// C:\xampp\htdocs\e-akreditasi-app\views\reset_password.php
require_once __DIR__ . '/../koneksi.php';

$message = '';
$error = '';

// Proses reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "Password baru tidak cocok!";
    } elseif (strlen($new_password) < 4) {
        $error = "Password minimal 4 karakter!";
    } else {
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $result = $conn->query("UPDATE users SET password = '$hashed_password' WHERE username = '$username' OR email = '$username'");
        
        if ($conn->affected_rows > 0) {
            $message = "Password berhasil direset! Silakan login dengan password baru Anda.";
        } else {
            $error = "Username/Email tidak ditemukan!";
        }
    }
}

// Cek daftar user yang ada
$users_result = $conn->query("SELECT id, username, email, full_name FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Akreditasia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f2b3d 0%, #1a4a6e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
        }
        
        .card {
            background: white;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 12px;
        }
        
        .logo-icon i {
            color: white;
        }
        
        .logo h2 {
            font-size: 1.4rem;
            background: linear-gradient(135deg, #2563eb, #6366f1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
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
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            font-family: inherit;
            font-size: 0.9rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(95deg, #2563eb, #1e40af);
            color: white;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .message {
            padding: 12px;
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
        
        .user-list {
            background: white;
            border-radius: 24px;
            padding: 24px;
        }
        
        .user-list h3 {
            font-size: 1rem;
            margin-bottom: 16px;
            color: #0f2b3d;
        }
        
        .user-item {
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }
        
        .user-item strong {
            color: #2563eb;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: white;
            text-decoration: none;
        }
        
        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h2>Reset Password</h2>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 8px;">Lupa password? Reset di sini</p>
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
            
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username atau Email</label>
                    <input type="text" name="username" placeholder="Masukkan username atau email" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password Baru</label>
                    <input type="password" name="new_password" placeholder="Password baru" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Konfirmasi Password</label>
                    <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-sync-alt"></i> Reset Password
                </button>
            </form>
        </div>
        
        <div class="user-list">
            <h3><i class="fas fa-users"></i> Daftar User dalam Database:</h3>
            <?php if ($users_result && $users_result->num_rows > 0): ?>
                <?php while($user = $users_result->fetch_assoc()): ?>
                    <div class="user-item">
                        <strong><?= htmlspecialchars($user['username']) ?></strong> - 
                        <?= htmlspecialchars($user['email']) ?> 
                        (<?= htmlspecialchars($user['full_name']) ?>)
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="user-item" style="color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i> Tidak ada user ditemukan!
                </div>
            <?php endif; ?>
            
            <hr>
            
            <div class="user-item" style="background: #dbeafe;">
                <strong>Default Login:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>admin123</code>
            </div>
        </div>
        
        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>
    </div>
</body>
</html>