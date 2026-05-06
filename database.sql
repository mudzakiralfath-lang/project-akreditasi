-- =========================
-- DATABASE
-- =========================
CREATE DATABASE IF NOT EXISTS db_akreditasi;
USE db_akreditasi;

-- =========================
-- TABEL USERS
-- =========================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nomor_telepon VARCHAR(20),
    status ENUM('mahasiswa', 'dosen', 'staff') NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX (email),
    INDEX (status)
) ENGINE=InnoDB;

-- =========================
-- TABEL SESSIONS
-- =========================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- TABEL PASSWORD RESETS
-- =========================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX (email),
    INDEX (token)
) ENGINE=InnoDB;

-- =========================
-- TABEL LOGIN ATTEMPTS
-- =========================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success TINYINT(1) DEFAULT 0,

    INDEX (email),
    INDEX (ip_address)
) ENGINE=InnoDB;

-- =========================
-- DATA DUMMY (LOGIN: password)
-- =========================
INSERT INTO users (nama_lengkap, email, nomor_telepon, status, password) VALUES
('Admin Portal', 'admin@polibatam.ac.id', '081234567890', 'staff', 'password'),
('Budi Santoso', 'budi.santoso@polibatam.ac.id', '081234567891', 'mahasiswa', 'password'),
('Dr. Siti Aminah', 'siti.aminah@polibatam.ac.id', '081234567892', 'dosen', 'password');