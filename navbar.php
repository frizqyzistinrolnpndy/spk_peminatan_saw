<?php
// Mendapatkan nama file yang sedang aktif saat ini (contoh: rekomendasi.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">SPK PEMINATAN SMA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php') ? 'active fw-bold text-white' : ''; ?>" href="index.php">Dashboard</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'siswa.php') ? 'active fw-bold text-white' : ''; ?>" href="siswa.php">Data Siswa</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'matriks.php') ? 'active fw-bold text-white' : ''; ?>" href="matriks.php">Input Nilai & Matriks</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'kriteria.php') ? 'active fw-bold text-white' : ''; ?>" href="kriteria.php">Bobot Kriteria</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'rekomendasi.php') ? 'active fw-bold text-white' : ''; ?>" href="rekomendasi.php">Hasil Rekomendasi</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'evaluasi.php') ? 'active fw-bold text-white' : ''; ?>" href="evaluasi.php">Evaluasi Sistem</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'integrasi.php') ? 'active fw-bold text-white' : ''; ?>" href="integrasi.php">Ranking Hybrid</a>
                </li>
                
            </ul>
        </div>
    </div>
</nav>