<?php
// Panggil koneksi database
include 'config.php';

// 1. Hitung total siswa
$q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_siswa");
$r_siswa = mysqli_fetch_assoc($q_siswa);

// 2. Hitung total mata pelajaran
$q_mapel = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_mapel");
$r_mapel = mysqli_fetch_assoc($q_mapel);

// 3. Hitung total kriteria
$q_kriteria = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_kriteria");
$r_kriteria = mysqli_fetch_assoc($q_kriteria);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SPK Peminatan SMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <div class="p-5 mb-4 bg-white rounded-3 shadow-sm border border-light">
        <div class="container-fluid py-2">
            <h1 class="display-6 fw-bold text-primary mb-3">Sistem Pendukung Keputusan Peminatan SMA</h1>
            <p class="col-md-11 fs-5 text-secondary">
                Selamat datang! Aplikasi ini digunakan untuk mendukung keputusan pemilihan mata pelajaran pilihan bagi siswa kelas X SMA. Perhitungan rekomendasi dilakukan secara objektif mengintegrasikan variabel <strong>Minat, Bakat, Nilai Akademik,</strong> dan <strong>Rencana Studi</strong> menggunakan metode <em>Simple Additive Weighting (SAW)</em>.
            </p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 bg-primary text-white rounded-3 me-3 fs-3">
                        👥
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Total Data Siswa</h6>
                        <h2 class="fw-bold mb-0"><?php echo $r_siswa['total']; ?></h2>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="siswa.php" class="text-decoration-none small text-primary">Kelola Siswa &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 bg-success text-white rounded-3 me-3 fs-3">
                        📚
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Mata Pelajaran</h6>
                        <h2 class="fw-bold mb-0"><?php echo $r_mapel['total']; ?></h2>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="matriks.php" class="text-decoration-none small text-success">Lihat Matriks &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 bg-warning text-white rounded-3 me-3 fs-3">
                        ⚙️
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Kriteria Variabel</h6>
                        <h2 class="fw-bold mb-0"><?php echo $r_kriteria['total']; ?></h2>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="kriteria.php" class="text-decoration-none small text-warning">Lihat Bobot &rarr;</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>