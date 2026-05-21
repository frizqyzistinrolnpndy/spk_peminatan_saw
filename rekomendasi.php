<?php
include 'config.php';

// Ambil ID Siswa dari URL jika ada
$id_siswa_terpilih = isset($_GET['id_siswa']) ? $_GET['id_siswa'] : '';

$nama_siswa = "";
$hasil_akhir = [];

if (!empty($id_siswa_terpilih)) {
    // 1. Ambil nama siswa yang dipilih
    $q_siswa = mysqli_query($koneksi, "SELECT nama_siswa FROM tb_siswa WHERE id_siswa = $id_siswa_terpilih");
    $d_siswa = mysqli_fetch_assoc($q_siswa);
    $nama_siswa = $d_siswa['nama_siswa'];

    // 2. Ambil data bobot kriteria dan simpan ke array
    $bobot = [];
    $q_kriteria = mysqli_query($koneksi, "SELECT * FROM tb_kriteria");
    while ($k = mysqli_fetch_assoc($q_kriteria)) {
        $bobot[$k['id_kriteria']] = $k['bobot'];
    }

    // 3. Ambil nilai MAX untuk masing-masing kriteria (karena semua kriteria adalah BENEFIT)
    $q_max = mysqli_query($koneksi, "
        SELECT 
            MAX(c1_minat) as max_c1, 
            MAX(c2_bakat) as max_c2, 
            MAX(c3_nilai) as max_c3, 
            MAX(c4_rencana) as max_c4 
        FROM tb_matriks 
        WHERE id_siswa = $id_siswa_terpilih
    ");
    $data_max = mysqli_fetch_assoc($q_max);

    // Cek apakah siswa ini sudah diisi nilainya di tb_matriks
    if ($data_max['max_c1'] > 0) {
        
        // 4. Proses Normalisasi & Perhitungan Skor Akhir SAW
        $q_matriks = mysqli_query($koneksi, "
            SELECT m.*, p.nama_mapel 
            FROM tb_matriks m
            JOIN tb_mapel p ON m.id_mapel = p.id_mapel
            WHERE m.id_siswa = $id_siswa_terpilih
        ");

        while ($row = mysqli_fetch_assoc($q_matriks)) {
            // Rumus Normalisasi Benefit: Nilai / Nilai Max
            $norm_c1 = $row['c1_minat'] / $data_max['max_c1'];
            $norm_c2 = $row['c2_bakat'] / $data_max['max_c2'];
            $norm_c3 = $row['c3_nilai'] / $data_max['max_c3'];
            $norm_c4 = $row['c4_rencana'] / $data_max['max_c4'];

            // Rumus Nilai Preferensi (V): Penjumlahan (Normalisasi * Bobot)
            $nilai_v = ($norm_c1 * $bobot['C1']) + 
                       ($norm_c2 * $bobot['C2']) + 
                       ($norm_c3 * $bobot['C3']) + 
                       ($norm_c4 * $bobot['C4']);

            // Tampung hasil kalkulasi ke array
            $hasil_akhir[] = [
                'nama_mapel' => $row['nama_mapel'],
                'c1_asli' => $row['c1_minat'],
                'c2_asli' => $row['c2_bakat'],
                'c3_asli' => $row['c3_nilai'],
                'c4_asli' => $row['c4_rencana'],
                'nilai_akhir' => round($nilai_v, 4)
            ];
        }

        // 5. Proses Perankingan (Sorting Descending / Besar ke Kecil)
        $kolom_nilai = array_column($hasil_akhir, 'nilai_akhir');
        array_multisort($kolom_nilai, SORT_DESC, $hasil_akhir);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Rekomendasi SPK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mb-5">
    <h3 class="mb-4 text-secondary fw-bold">Hasil Rekomendasi Peminatan</h3>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="rekomendasi.php" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-9">
                        <label for="id_siswa" class="form-label small fw-bold text-muted">Pilih Nama Siswa untuk Melihat Hasil</label>
                        <select name="id_siswa" id="id_siswa" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Nama Siswa --</option>
                            <?php
                            $q_all_siswa = mysqli_query($koneksi, "SELECT * FROM tb_siswa ORDER BY nama_siswa ASC");
                            while ($s = mysqli_fetch_assoc($q_all_siswa)) {
                                $selected = ($id_siswa_terpilih == $s['id_siswa']) ? 'selected' : '';
                                echo "<option value='{$s['id_siswa']}' {$selected}>{$s['nama_siswa']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <a href="rekomendasi.php" class="btn btn-light border w-100">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($id_siswa_terpilih)): ?>
        <?php if (!empty($hasil_akhir)): ?>
            
            <div class="alert alert-primary shadow-sm border-0 mb-4" role="alert">
                Sistem berhasil menganalisis data kriteria untuk siswa: <strong><?php echo $nama_siswa; ?></strong>. Mata pelajaran di bawah ini telah diurutkan berdasarkan kecocokan tertinggi.
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold py-3 border-bottom">
                    Urutan Rekomendasi Mata Pelajaran Pilihan
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="10%">Ranking</th>
                                    <th>Mata Pelajaran</th>
                                    <th class="text-center">Minat</th>
                                    <th class="text-center">Bakat</th>
                                    <th class="text-center">Nilai Rapor</th>
                                    <th class="text-center">Rencana Studi</th>
                                    <th class="text-center" width="20%">Skor Preferensi (V)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($hasil_akhir as $h): 
                                    // BAGIAN YANG DIUBAH: Jika rank 1, 2, 3, atau 4, berikan warna hijau sukses
                                    $badge_color = ($rank <= 4) ? 'bg-success' : 'bg-secondary';
                                    $row_style = ($rank <= 4) ? 'table-success fw-bold' : '';
                                ?>
                                <tr class="<?php echo $row_style; ?>">
                                    <td class="text-center">
                                        <span class="badge <?php echo $badge_color; ?> fs-6 px-3 py-2"><?php echo $rank; ?></span>
                                    </td>
                                    <td class="fs-5 text-dark"><?php echo $h['nama_mapel']; ?></td>
                                    <td class="text-center text-muted small"><?php echo $h['c1_asli']; ?></td>
                                    <td class="text-center text-muted small"><?php echo $h['c2_asli']; ?></td>
                                    <td class="text-center text-muted small"><?php echo $h['c3_asli']; ?></td>
                                    <td class="text-center text-muted small"><?php echo $h['c4_asli']; ?></td>
                                    <td class="text-center fs-5 text-primary fw-bold"><?php echo $h['nilai_akhir']; ?></td>
                                </tr>
                                <?php 
                                $rank++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-warning text-center py-4 shadow-sm" role="alert">
                <strong>Data Belum Lengkap!</strong> Siswa ini belum memiliki nilai kriteria di database. Silakan isi nilainya terlebih dahulu di menu <a href="matriks.php" class="alert-link">Input Nilai & Matriks</a>.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded shadow-sm text-muted">
            <p class="mb-0">Silakan pilih nama siswa pada dropdown di atas untuk memproses hasil rekomendasi SPK.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>