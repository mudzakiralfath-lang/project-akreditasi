<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerjasama Tridharma - Teknik Instrumentasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #fff8f0;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #ff7b00 0%, #ff5500 100%);
            color: white;
            padding: 25px 0;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .back-button {
            background: linear-gradient(to right, #ff9800, #f57c00);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-left: 15px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            font-size: 2.5rem;
        }

        .logo-text h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .logo-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Main Content - YouTube Style */
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Breadcrumb */
        .breadcrumb {
            background-color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #ff9800;
        }

        .breadcrumb a {
            color: #ff6d00;
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumb span {
            color: #666;
        }

        /* Page Title */
        .page-title {
            background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(255, 140, 0, 0.2);
            margin-bottom: 10px;
        }

        .page-title h2 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .page-title p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Card Grid Container - YouTube Style */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        /* YouTube Style Card */
        .card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-top: 5px solid #ff9800;
            position: relative;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(255, 140, 0, 0.2);
        }

        .card-thumbnail {
            position: relative;
            height: 180px;
            overflow: hidden;
        }

        .card-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Gradient overlay untuk thumbnail */
        .card-thumbnail::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(255, 152, 0, 0.1), rgba(255, 152, 0, 0.4));
        }

        /* Live badge */
        .live-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #ff3d00;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .live-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            background-color: white;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Card content */
        .card-content {
            padding: 20px;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .card-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        /* Stats container (mirip YouTube) */
        .card-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #777;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat i {
            color: #ff9800;
        }

        /* Action buttons */
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .card-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .primary-btn {
            background: linear-gradient(to right, #ff9800, #ffb74d);
            color: white;
        }

        .primary-btn:hover {
            background: linear-gradient(to right, #f57c00, #ff9800);
        }

        .secondary-btn {
            background-color: #f5f5f5;
            color: #555;
        }

        .secondary-btn:hover {
            background-color: #eeeeee;
        }

        /* Link badge */
        .link-badge {
            display: inline-block;
            background-color: #fff3e0;
            color: #e65100;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            padding: 20px;
            border-top: 1px solid #ffe0b2;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .card-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .back-button {
                margin-left: 0;
                margin-top: 10px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .page-title {
                padding: 20px;
            }
            
            .page-title h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 10px;
            }
            
            .card {
                margin-bottom: 15px;
            }
            
            .card-actions {
                flex-direction: column;
            }
        }

        /* Empty card untuk placeholder */
        .empty-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 250px;
            background-color: #fff8e1;
            border: 2px dashed #ffb74d;
            border-radius: 12px;
            color: #ff8a00;
            text-align: center;
            padding: 30px;
        }

        .empty-card i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="logo-text">
                    <h1>Kerjasama Tridharma</h1>
                    <p>Teknik Instrumentasi - LAM Teknik 2025</p>
                </div>
            </div>
            <a href="data_borang.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Data Borang</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="data_borang.php">Data Borang</a> > 
                <span>Kerjasama Tridharma</span>
            </div>

            <!-- Page Title -->
            <div class="page-title">
                <h2>Kerjasama Tridharma Perguruan Tinggi</h2>
                <p>Pilih jenis kerjasama yang ingin Anda lihat atau kelola</p>
            </div>

            <!-- Card Grid - YouTube Style -->
            <div class="card-grid">
                <!-- Card 1 -->
                <div class="card">
                    <div class="card-thumbnail">
                        <!-- Thumbnail placeholder dengan warna orange -->
                        <div style="width:100%; height:100%; background: linear-gradient(135deg, #ff9800 0%, #ffcc80 100%);"></div>
                        <span class="live-badge">
                            <i class="fas fa-play-circle"></i> LIVE
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="link-badge">
                            <i class="fas fa-link"></i> Link Tersedia
                        </div>
                        <h3 class="card-title">Kerjasama Pendidikan</h3>
                        <p class="card-description">
                            Kerjasama dalam bidang pendidikan seperti pertukaran mahasiswa, guest lecture, magang industri, dan program double degree dengan mitra institusi.
                        </p>
                        <div class="card-stats">
                            <div class="stat">
                                <i class="fas fa-university"></i>
                                <span>15 Mitra</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Aktif 2025</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-file-contract"></i>
                                <span>22 Dokumen</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn primary-btn" onclick="openKerjasama('pendidikan')">
                                <i class="fas fa-eye"></i> Lihat Data
                            </button>
                            <button class="card-btn secondary-btn" onclick="openForm('pendidikan')">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="card">
                    <div class="card-thumbnail">
                        <div style="width:100%; height:100%; background: linear-gradient(135deg, #ffb74d 0%, #ffcc80 100%);"></div>
                        <span class="live-badge">
                            <i class="fas fa-flask"></i> PRO
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="link-badge">
                            <i class="fas fa-link"></i> Link Tersedia
                        </div>
                        <h3 class="card-title">Kerjasama Penelitian</h3>
                        <p class="card-description">
                            Kolaborasi penelitian dengan industri, lembaga penelitian, dan perguruan tinggi lain baik nasional maupun internasional.
                        </p>
                        <div class="card-stats">
                            <div class="stat">
                                <i class="fas fa-microscope"></i>
                                <span>8 Proyek</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-users"></i>
                                <span>12 Peneliti</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-award"></i>
                                <span>5 Publikasi</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn primary-btn" onclick="openKerjasama('penelitian')">
                                <i class="fas fa-eye"></i> Lihat Data
                            </button>
                            <button class="card-btn secondary-btn" onclick="openForm('penelitian')">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="card">
                    <div class="card-thumbnail">
                        <div style="width:100%; height:100%; background: linear-gradient(135deg, #ff8a00 0%, #ffcc80 100%);"></div>
                        <span class="live-badge">
                            <i class="fas fa-briefcase"></i> PLUS
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="link-badge">
                            <i class="fas fa-link"></i> Link Tersedia
                        </div>
                        <h3 class="card-title">Kerjasama Pengabdian</h3>
                        <p class="card-description">
                            Program pengabdian kepada masyarakat melalui penerapan teknologi instrumentasi untuk industri kecil, sekolah, dan komunitas.
                        </p>
                        <div class="card-stats">
                            <div class="stat">
                                <i class="fas fa-hands-helping"></i>
                                <span>10 Program</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>5 Lokasi</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-chart-line"></i>
                                <span>85% Selesai</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn primary-btn" onclick="openKerjasama('pengabdian')">
                                <i class="fas fa-eye"></i> Lihat Data
                            </button>
                            <button class="card-btn secondary-btn" onclick="openForm('pengabdian')">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="card">
                    <div class="card-thumbnail">
                        <div style="width:100%; height:100%; background: linear-gradient(135deg, #ff7043 0%, #ffcc80 100%);"></div>
                        <span class="live-badge">
                            <i class="fas fa-globe"></i> GO
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="link-badge">
                            <i class="fas fa-link"></i> Link Tersedia
                        </div>
                        <h3 class="card-title">Kerjasama Internasional</h3>
                        <p class="card-description">
                            Kerjasama dengan institusi luar negeri untuk program student exchange, joint research, dan konferensi internasional.
                        </p>
                        <div class="card-stats">
                            <div class="stat">
                                <i class="fas fa-globe-americas"></i>
                                <span>6 Negara</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-plane"></i>
                                <span>12 Exchange</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-handshake"></i>
                                <span>Aktif</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn primary-btn" onclick="openKerjasama('internasional')">
                                <i class="fas fa-eye"></i> Lihat Data
                            </button>
                            <button class="card-btn secondary-btn" onclick="openForm('internasional')">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="card">
                    <div class="card-thumbnail">
                        <div style="width:100%; height:100%; background: linear-gradient(135deg, #ff9800 0%, #ffd54f 100%);"></div>
                        <span class="live-badge">
                            <i class="fas fa-industry"></i> PRO
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="link-badge">
                            <i class="fas fa-link"></i> Link Tersedia
                        </div>
                        <h3 class="card-title">Kerjasama Industri</h3>
                        <p class="card-description">
                            Kolaborasi dengan perusahaan industri untuk program magang, penelitian terapan, dan pengembangan kurikulum.
                        </p>
                        <div class="card-stats">
                            <div class="stat">
                                <i class="fas fa-industry"></i>
                                <span>25 Perusahaan</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-user-graduate"></i>
                                <span>150 Magang</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-tools"></i>
                                <span>8 Laboratorium</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn primary-btn" onclick="openKerjasama('industri')">
                                <i class="fas fa-eye"></i> Lihat Data
                            </button>
                            <button class="card-btn secondary-btn" onclick="openForm('industri')">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="card">
                    <div class="card-thumbnail">
                        <div style="width:100%; height:100%; background: linear-gradient(135deg, #ffa726 0%, #ffcc80 100%);"></div>
                        <span class="live-badge">
                            <i class="fas fa-graduation-cap"></i> PLUS
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="link-badge">
                            <i class="fas fa-link"></i> Link Tersedia
                        </div>
                        <h3 class="card-title">Kerjasama Pendidikan Vokasi</h3>
                        <p class="card-description">
                            Program link and match dengan SMK dan politeknik untuk pengembangan kompetensi berbasis industri.
                        </p>
                        <div class="card-stats">
                            <div class="stat">
                                <i class="fas fa-school"></i>
                                <span>10 SMK</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-certificate"></i>
                                <span>5 Sertifikasi</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>8 Pelatihan</span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn primary-btn" onclick="openKerjasama('vokasi')">
                                <i class="fas fa-eye"></i> Lihat Data
                            </button>
                            <button class="card-btn secondary-btn" onclick="openForm('vokasi')">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Placeholder untuk tambah baru -->
                <div class="card">
                    <div class="empty-card">
                        <i class="fas fa-plus-circle"></i>
                        <h3 style="color: #ff9800; margin-bottom: 10px;">Tambah Jenis Kerjasama Baru</h3>
                        <p>Klik untuk menambahkan jenis kerjasama baru sesuai kebutuhan program studi.</p>
                        <button class="card-btn primary-btn" onclick="openNewKerjasamaForm()" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Tambah Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>© 2025 Program Studi Teknik Instrumentasi - Sistem Data Borang Akreditasi</p>
            <p style="margin-top: 10px; font-size: 0.8rem; color: #999;">Halaman Kerjasama Tridharma | Tema Orange-Kuning</p>
        </div>
    </div>

    <script>
        function openKerjasama(jenis) {
            // Arahkan ke halaman sesuai jenis kerjasama
            // Anda bisa mengganti URL ini sesuai dengan halaman yang sudah ada
            switch(jenis) {
                case 'pendidikan':
                    window.location.href = 'kerjasama_pendidikan.php';
                    break;
                case 'penelitian':
                    window.location.href = 'kerjasama_penelitian.php';
                    break;
                case 'pengabdian':
                    window.location.href = 'kerjasama_pengabdian.php';
                    break;
                case 'internasional':
                    window.location.href = 'kerjasama_internasional.php';
                    break;
                case 'industri':
                    window.location.href = 'kerjasama_industri.php';
                    break;
                case 'vokasi':
                    window.location.href = 'kerjasama_vokasi.php';
                    break;
                default:
                    alert('Membuka data kerjasama: ' + jenis);
            }
        }

        function openForm(jenis) {
            // Arahkan ke form tambah data
            alert('Membuka form tambah untuk kerjasama: ' + jenis);
            // window.location.href = 'form_kerjasama.php?jenis=' + jenis;
        }

        function openNewKerjasamaForm() {
            alert('Membuka form untuk menambahkan jenis kerjasama baru');
            // window.location.href = 'form_jenis_kerjasama_baru.php';
        }

        // Animasi untuk card hover
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                    this.style.boxShadow = '0 12px 30px rgba(255, 140, 0, 0.2)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.08)';
                });
            });
        });
    </script>
</body>
</html>