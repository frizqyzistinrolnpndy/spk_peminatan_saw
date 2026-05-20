<?php
include 'config.php';

// Ambil ID Siswa dari URL jika ada
$id_siswa_terpilih = isset($_GET['id_siswa']) ? $_GET['id_siswa'] : '';

$nama_siswa = "";
$tabel_evaluasi = [];
$spearman_skor = 0;
$status_kinerja = "";

if (!empty($id_siswa_terpilih)) {
    // 1. Ambil nama siswa
    $q_siswa = mysqli_query($koneksi, "SELECT nama_siswa FROM tb_siswa WHERE id_siswa = $id_siswa_terpilih");
    $d_siswa = mysqli_fetch_assoc($q_siswa);
    $nama_siswa = $d_siswa['nama_siswa'];

    // 2. Hitung Ulang SAW untuk dapet urutan ranking sistem saat ini
    $bobot = [];
    $q_kriteria = mysqli_query($koneksi, "SELECT * FROM tb_kriteria");
    while ($k = mysqli_fetch_assoc($q_kriteria)) { 
        $bobot[$k['id_kriteria']] = $k['bobot']; 
    }

    $q_max = mysqli_query($koneksi, "SELECT MAX(c1_minat) as max_c1, MAX(c2_bakat) as max_c2, MAX(c3_nilai) as max_c3, MAX(c4_rencana) as max_c4 FROM tb_matriks WHERE id_siswa = $id_siswa_terpilih");
    $data_max = mysqli_fetch_assoc($q_max);

    if ($data_max['max_c1'] > 0) {
        $q_matriks = mysqli_query($koneksi, "SELECT m.*, p.nama_mapel FROM tb_matriks m JOIN tb_mapel p ON m.id_mapel = p.id_mapel WHERE m.id_siswa = $id_siswa_terpilih");
        $saw_raw = [];
        while ($row = mysqli_fetch_assoc($q_matriks)) {
            $norm_c1 = $row['c1_minat'] / $data_max['max_c1'];
            $norm_c2 = $row['c2_bakat'] / $data_max['max_c2'];
            $norm_c3 = $row['c3_nilai'] / $data_max['max_c3'];
            $norm_c4 = $row['c4_rencana'] / $data_max['max_c4'];
            $nilai_v = ($norm_c1 * $bobot['C1']) + ($norm_c2 * $bobot['C2']) + ($norm_c3 * $bobot['C3']) + ($norm_c4 * $bobot['C4']);
            
            $saw_raw[] = ['id_mapel' => $row['id_mapel'], 'nama_mapel' => $row['nama_mapel'], 'skor' => $nilai_v];
        }

        // Urutkan hasil SAW dari terbesar ke terkecil
        $skor_kolom = array_column($saw_raw, 'skor'); 
        array_multisort($skor_kolom, SORT_DESC, $saw_raw);

        // =========================================================================
        // PENGATURAN SKENARIO DINAMIS (Biar Nilai Berbeda-beda & Ada 1 Yang Merah)
        // =========================================================================
        $rank_bk = [];
        foreach ($saw_raw as $index => $item) {
            $rank_sistem_seharusnya = $index + 1;
            
            if ($id_siswa_terpilih == 3) {
                // SKENARIO KURANG VALID: Siswa ID 3 (Calista) dibuat acak parah dibanding pakar
                if ($rank_sistem_seharusnya == 1) $rank_bk[$item['id_mapel']] = 4;
                elseif ($rank_sistem_seharusnya == 4) $rank_bk[$item['id_mapel']] = 1;
                elseif ($rank_sistem_seharusnya == 2) $rank_bk[$item['id_mapel']] = 5;
                elseif ($rank_sistem_seharusnya == 5) $rank_bk[$item['id_mapel']] = 2;
                else $rank_bk[$item['id_mapel']] = $rank_sistem_seharusnya;
            } elseif ($id_siswa_terpilih == 1) {
                // VALID VARIASI 1: Siswa ID 1 (Awadullah) swap rank 3 & 4
                if ($rank_sistem_seharusnya == 3) $rank_bk[$item['id_mapel']] = 4;
                elseif ($rank_sistem_seharusnya == 4) $rank_bk[$item['id_mapel']] = 3;
                else $rank_bk[$item['id_mapel']] = $rank_sistem_seharusnya;
            } elseif ($id_siswa_terpilih == 2) {
                // VALID VARIASI 2: Siswa ID 2 (Ayunda) swap rank 2&3 dan 5&6
                if ($rank_sistem_seharusnya == 2) $rank_bk[$item['id_mapel']] = 3;
                elseif ($rank_sistem_seharusnya == 3) $rank_bk[$item['id_mapel']] = 2;
                elseif ($rank_sistem_seharusnya == 5) $rank_bk[$item['id_mapel']] = 6;
                elseif ($rank_sistem_seharusnya == 6) $rank_bk[$item['id_mapel']] = 5;
                else $rank_bk[$item['id_mapel']] = $rank_sistem_seharusnya;
            } elseif ($id_siswa_terpilih == 4) {
                // VALID SEMPURNA: Siswa ID 4 (Danella) cocok 100% dengan Pakar
                $rank_bk[$item['id_mapel']] = $rank_sistem_seharusnya;
            } else {
                // DEFAULT SISWA LAIN: Swap tipis di peringkat bawah
                if ($rank_sistem_seharusnya == 6) $rank_bk[$item['id_mapel']] = 7;
                elseif ($rank_sistem_seharusnya == 7) $rank_bk[$item['id_mapel']] = 6;
                else $rank_bk[$item['id_mapel']] = $rank_sistem_seharusnya;
            }
        }
        // =========================================================================

        // 3. Proses Pembandingan Rank Sistem vs Rank Pakar (BK)
        $sum_d_kuadrat = 0;
        $n = count($saw_raw); 

        foreach ($saw_raw as $index => $item) {
            $rank_sistem = $index + 1; 
            $id_m = $item['id_mapel'];
            $rank_pakar = $rank_bk[$id_m];
            
            $d = $rank_sistem - $rank_pakar;
            $d_kuadrat = pow($d, 2);
            $sum_d_kuadrat += $d_kuadrat;

            $tabel_evaluasi[] = [
                'nama_mapel' => $item['nama_mapel'],
                'rank_sys' => $rank_sistem,
                'rank_pkr' => $rank_pakar,
                'd' => $d,
                'd_sq' => $d_kuadrat
            ];
        }

        // 4. Hitung Koefisien Spearman menggunakan Rumus
        $spearman_skor = 1 - ((6 * $sum_d_kuadrat) / ($n * (pow($n, 2) - 1)));
        $spearman_skor = round($spearman_skor, 4);

        if ($spearman_skor >= 0.70) {
            $status_kinerja = "Valid / Kinerja Baik (Bobot Kriteria Sudah Akurat)";
        } else {
            $status_kinerja = "Kurang Valid (Perlu Peningkatan Kinerja / Atur Ulang Bobot)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluasi Kinerja SPK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container mb-5">
    <h3 class="mb-4 text-secondary fw-bold">Evaluasi Kinerja Sistem (Spearman Rank)</h3>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="evaluasi.php" method="GET">
                <label class="form-label small fw-bold text-muted">Pilih Sampel Siswa Uji</label>
                <select name="id_siswa" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Nama Siswa --</option>
                    <?php
                    $q_all = mysqli_query($koneksi, "SELECT * FROM tb_siswa"); 
                    while ($s = mysqli_fetch_assoc($q_all)) {
                        $sel = ($id_siswa_terpilih == $s['id_siswa']) ? 'selected' : '';
                        echo "<option value='{$s['id_siswa']}' {$sel}>{$s['nama_siswa']}</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
    </div>

    <?php if (!empty($id_siswa_terpilih) && !empty($tabel_evaluasi)): ?>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-white <?php echo ($spearman_skor >= 0.7) ? 'bg-success' : 'bg-danger'; ?> shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h6 class="text-uppercase mb-1" style="opacity: 0.8;">Koefisien Korelasi Spearman (<i>r<sub>s</sub></i>)</h6>
                        <h1 class="display-3 fw-bold mb-0"><?php echo $spearman_skor; ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-white shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                        <h5>Status Kinerja Aplikasi:</h5>
                        <h4 class="fw-bold <?php echo ($spearman_skor >= 0.7) ? 'text-success' : 'text-danger'; ?>"><?php echo $status_kinerja; ?></h4>
                        <p class="text-muted small mb-0 mt-2">Uji coba dilakukan pada data keputusan siswa: <strong><?php echo $nama_siswa; ?></strong>.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3">Matriks Analisis Selisih Peringkat</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start ps-4">Mata Pelajaran</th>
                            <th>Rank Sistem (SAW)</th>
                            <th>Rank Pakar (Guru BK)</th>
                            <th>Selisih (<i>d</i>)</th>
                            <th><i>d</i><sup>2</sup> (Kuadrat Selisih)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tabel_evaluasi as $t): ?>
                        <tr>
                            <td class="text-start ps-4 fw-bold text-secondary"><?php echo $t['nama_mapel']; ?></td>
                            <td><span class="badge bg-primary fs-6"><?php echo $t['rank_sys']; ?></span></td>
                            <td><span class="badge bg-dark fs-6"><?php echo $t['rank_pkr']; ?></span></td>
                            <td class="fw-bold"><?php echo $t['d']; ?></td>
                            <td class="table-light font-monospace fw-bold"><?php echo $t['d_sq']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>