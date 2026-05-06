<?php
session_start();

// Simulasi database users
if (!isset($_SESSION['registered_users'])) {
    $_SESSION['registered_users'] = [];
}

$error = '';
$success = '';

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $status = $_POST['status'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree = isset($_POST['agree']);
    
    // Validasi
    if (empty($nama) || empty($email) || empty($telepon) || empty($status) || empty($password) || empty($confirm_password)) {
        $error = 'Semua kolom harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (!$agree) {
        $error = 'Anda harus menyetujui syarat dan ketentuan!';
    } else {
        // Cek apakah email sudah terdaftar
        $emailExists = false;
        foreach ($_SESSION['registered_users'] as $user) {
            if ($user['email'] === $email) {
                $emailExists = true;
                break;
            }
        }
        
        if ($emailExists) {
            $error = 'Email sudah terdaftar! Silakan gunakan email lain.';
        } else {
            // Simpan user baru
            $_SESSION['registered_users'][] = [
                'nama' => $nama,
                'email' => $email,
                'telepon' => $telepon,
                'status' => $status,
                'password' => $password // Dalam aplikasi nyata, gunakan password_hash()
            ];
            
            $_SESSION['registration_success'] = true;
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Portal Akademik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: #fff8e1; /* Latar kuning muda */
        }

        .container {
            display: flex;
            min-height: 100vh;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Kolom Kiri - Branding */
        .left-section {
            flex: 1;
            background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%); /* Orange gradient */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
        }

        .logo-container {
            background: rgba(255, 255, 255, 0.2);
            width: 120px;
            height: 120px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logo-container i {
            font-size: 60px;
            color: white;
        }

        .program-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .institution-name {
            font-size: 18px;
            font-weight: 400;
            opacity: 0.95;
            margin-bottom: 24px;
            text-align: center;
        }

        .program-description {
            font-size: 15px;
            line-height: 1.6;
            opacity: 0.9;
            text-align: center;
            margin-bottom: 40px;
            max-width: 450px;
        }

        .features {
            list-style: none;
            width: 100%;
            max-width: 400px;
        }

        .features li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            font-size: 16px;
            opacity: 0.95;
        }

        .features li:before {
            content: "✓";
            color: #fff;
            background: #ff9800;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Kolom Kanan - Form Register */
        .right-section {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto;
        }

        .register-box {
            width: 100%;
            max-width: 550px;
            background: white;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(255, 152, 0, 0.15); /* Shadow orange subtle */
            border: 1px solid #ffecb3; /* Border kuning muda */
        }

        .register-header h2 {
            color: #ff6f00; /* Orange lebih gelap */
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .register-header p {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .error-message {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #d32f2f;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            animation: slideDown 0.3s ease;
        }

        .error-message i {
            margin-right: 10px;
        }

        .success-message {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            animation: slideDown 0.3s ease;
        }

        .success-message i {
            margin-right: 10px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            color: #5d4037; /* Coklat tua */
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff9800; /* Orange */
            font-size: 16px;
            z-index: 1;
        }

        .input-wrapper .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            z-index: 2;
            font-size: 16px;
            transition: color 0.3s;
        }

        .input-wrapper .toggle-password:hover {
            color: #ff9800;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1px solid #ffe0b2; /* Border orange muda */
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fffaf0; /* Latar kuning sangat muda */
        }

        .form-control.with-toggle {
            padding-right: 48px;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff9800;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ff9800' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            margin-bottom: 24px;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-top: 4px;
            cursor: pointer;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            accent-color: #ff9800; /* Warna checkbox orange */
        }

        .checkbox-group label {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
            cursor: pointer;
        }

        .checkbox-group label a {
            color: #ff6f00; /* Orange gelap */
            text-decoration: none;
            font-weight: 600;
        }

        .checkbox-group label a:hover {
            text-decoration: underline;
            color: #ff9800;
        }

        .btn-register {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, #ff9800, #ffb74d); /* Orange gradient */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        }

        .btn-register:hover {
            background: linear-gradient(to right, #f57c00, #ffa726);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .login-link a {
            color: #ff6f00;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #ff9800;
        }

        .back-home {
            text-align: center;
        }

        .back-home a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            transition: color 0.3s;
        }

        .back-home a:hover {
            color: #ff9800;
        }

        .back-home a i {
            margin-right: 6px;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .container {
                flex-direction: column;
            }

            .left-section {
                padding: 40px 20px;
                min-height: auto;
                background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%);
            }

            .right-section {
                padding: 20px;
            }

            .register-box {
                padding: 30px 20px;
                box-shadow: 0 4px 20px rgba(255, 152, 0, 0.15);
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .program-title {
                font-size: 24px;
            }

            .logo-container {
                width: 90px;
                height: 90px;
            }

            .logo-container i {
                font-size: 45px;
            }

            .program-description {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

        <!-- Kolom Kanan - Form Register -->
        <div class="right-section">
            <div class="register-box">
                <div class="register-header">
                    <h2>Buat Akun Baru</h2>
                    <p>Lengkapi data diri untuk mendaftar</p>
                </div>

                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input 
                                    type="text" 
                                    id="nama" 
                                    name="nama" 
                                    class="form-control" 
                                    placeholder="Nama lengkap"
                                    value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-control" 
                                    placeholder="nama@email.com"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telepon">Nomor Telepon</label>
                            <div class="input-wrapper">
                                <i class="fas fa-phone"></i>
                                <input 
                                    type="tel" 
                                    id="telepon" 
                                    name="telepon" 
                                    class="form-control" 
                                    placeholder="08XX-XXXX-XXXX"
                                    value="<?php echo isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-tag"></i>
                                <select 
                                    id="status" 
                                    name="status" 
                                    class="form-control"
                                    required
                                >
                                    <option value="">Pilih status</option>
                                    <option value="dosen" <?php echo (isset($_POST['status']) && $_POST['status'] === 'dosen') ? 'selected' : ''; ?>>Dosen</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-control with-toggle" 
                                    placeholder="Minimal 8 karakter"
                                    required
                                >
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    class="form-control with-toggle" 
                                    placeholder="Ulangi password"
                                    required
                                >
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                            </div>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">
                            Saya menyetujui <a href="terms.php">syarat dan ketentuan</a> serta <a href="privacy.php">kebijakan privasi</a>.
                        </label>
                    </div>

                    <button type="submit" class="btn-register">Daftar Sekarang</button>
                </form>

                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Masuk di sini</a>
                </div>

                <div class="back-home">
                    <a href="index.php">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('.toggle-password');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>