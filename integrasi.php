<?php
include 'config.php';

$id_siswa_terpilih = isset($_GET['id_siswa']) ? $_GET['id_siswa'] : '';
$nama_siswa = "";
$hasil_hybrid = [];

if (!empty($id_siswa_terpilih)) {
    // 1. Ambil nama siswa
    $q_siswa = mysqli_query($koneksi, "SELECT nama_siswa FROM tb_siswa WHERE id_siswa = $id_siswa_terpilih");
    $d_siswa = mysqli_fetch_assoc($q_siswa);
    $nama_siswa = $d_siswa['nama_siswa'];

    // 2. Ambil data bobot kriteria SAW
    $bobot = [];
    $q_kriteria = mysqli_query($koneksi, "SELECT * FROM tb_kriteria");
    while ($k = mysqli_fetch_assoc($q_kriteria)) {
        $bobot[$k['id_kriteria']] = $k['bobot'];
    }

    // 3. Ambil nilai MAX kriteria untuk normalisasi SAW
    $q_max = mysqli_query($koneksi, "SELECT MAX(c1_minat) as max_c1, MAX(c2_bakat) as max_c2, MAX(c3_nilai) as max_c3, MAX(c4_rencana) as max_c4 FROM tb_matriks WHERE id_siswa = $id_siswa_terpilih");
    $data_max = mysqli_fetch_assoc($q_max);

    if ($data_max['max_c1'] > 0) {
        // Ambil data nilai asli matriks siswa
        $q_matriks = mysqli_query($koneksi, "SELECT m.*, p.nama_mapel FROM tb_matriks m JOIN tb_mapel p ON m.id_mapel = p.id_mapel WHERE m.id_siswa = $id_siswa_terpilih");
        
        $data_matriks = [];
        $payload_ml = [];

        while ($row = mysqli_fetch_assoc($q_matriks)) {
            $data_matriks[] = $row;
            
            // Siapkan paket data (payload) untuk dikirim ke API Python 
            $payload_ml[] = [
                'nama_mapel' => $row['nama_mapel'],
                'minat' => (float)$row['c1_minat'],
                'bakat' => (float)$row['c2_bakat'],
                'nilai_rapor' => (float)$row['c3_nilai'],
                'rencana_studi' => (float)$row['c4_rencana']
            ];
        }

        // 4. KIRIM DATA KE FASTAPI MENGGUNAKAN cURL PHP 
        $ch = curl_init("http://127.0.0.1:8000/prediksi-batch"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_ml)); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); 
        
        $response = curl_exec($ch); 
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch); 

        if ($http_code !== 200) { 
            die("<div class='alert alert-danger'>API Machine Learning Python belum aktif! Jalankan 'uvicorn api_ml:app' terlebih dahulu.</div>"); 
        }

        // Parse hasil prediksi dari API AI 
        $response_data = json_decode($response, true); 
        $prediksi_ml = $response_data['hasil']; 

        // 5. GABUNGKAN HITUNGAN SAW + PREDIKSI ML (HYBRID INTERACTION) 
        $bobot_ml = 0.3; // Kontribusi AI sebesar 30% ke skor akhir 
        foreach ($data_matriks as $i => $row) {
            // Normalisasi SAW 
            $norm_c1 = $row['c1_minat'] / $data_max['max_c1'];
            $norm_c2 = $row['c2_bakat'] / $data_max['max_c2'];
            $norm_c3 = $row['c3_nilai'] / $data_max['max_c3'];
            $norm_c4 = $row['c4_rencana'] / $data_max['max_c4'];

            $skor_saw = ($norm_c1 * $bobot['C1']) + ($norm_c2 * $bobot['C2']) + ($norm_c3 * $bobot['C3']) + ($norm_c4 * $bobot['C4']);

            // Ambil output dari AI Python 
            $proba_ml = $prediksi_ml[$i]['proba']; 
            $label_ml = $prediksi_ml[$i]['label']; 

            // Rumus Integrasi Hybrid 
            $skor_hybrid = (0.7 * $skor_saw) + ($bobot_ml * $proba_ml); 

            $hasil_hybrid[] = [ 
                'nama_mapel' => $row['nama_mapel'], 
                'skor_saw' => round($skor_saw, 4), 
                'proba_ml' => round($proba_ml, 4), 
                'skor_hybrid' => round($skor_hybrid, 4), 
                'status' => $label_ml ? 'Direkomendasikan' : 'Tidak Direkomendasikan' 
            ];
        }

        // 6. Urutkan Ranking Berdasarkan Skor Hybrid Terbesar 
        $kolom_hybrid = array_column($hasil_hybrid, 'skor_hybrid');
        array_multisort($kolom_hybrid, SORT_DESC, $hasil_hybrid); 
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Rekomendasi Hybrid SPK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container mb-5">
    <h3 class="mb-4 text-secondary fw-bold">Hasil Akhir: Simulasi Ranking Hybrid (SAW + Machine Learning)</h3> 

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="integrasi.php" method="GET">
                <label class="form-label small fw-bold text-muted">Pilih Nama Siswa</label>
                <select name="id_siswa" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Nama Siswa --</option>
                    <?php
                    $q_all_siswa = mysqli_query($koneksi, "SELECT * FROM tb_siswa ORDER BY nama_siswa ASC");
                    while ($s = mysqli_fetch_assoc($q_all_siswa)) {
                        $selected = ($id_siswa_terpilih == $s['id_siswa']) ? 'selected' : '';
                        echo "<option value='{$s['id_siswa']}' {$selected}>{$s['nama_siswa']}</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
    </div>

    <?php if (!empty($id_siswa_terpilih)): ?>
        <?php if (!empty($hasil_hybrid)): ?>
            <div class="alert alert-success shadow-sm mb-4">
                Analisis Berhasil! Hasil di bawah merupakan perpaduan kalkulasi pembobotan kaku <strong>SAW (70%)</strong> dengan probabilitas kecerdasan pola data historis dari <strong>Machine Learning Random Forest (30%)</strong>. 
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0 text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th width="10%">Rank</th> 
                                    <th class="text-start ps-4">Mata Pelajaran Pilihan</th> 
                                    <th>Skor SAW (70%)</th> 
                                    <th>Proba ML (30%)</th> 
                                    <th>Skor Akhir Hybrid</th> 
                                    <th>Status Kelayakan AI</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($hasil_hybrid as $h): 
                                    // PERUBAHAN UTAMA: Pengecekan dilakukan secara dinamis berdasarkan status rekomendasi AI
                                    $is_rekomen = ($h['status'] == 'Direkomendasikan'); 
                                    $row_class = $is_rekomen ? 'table-success fw-bold' : '';
                                    $badge_rank_class = $is_rekomen ? 'bg-success' : 'bg-secondary';
                                    $badge_status_class = $is_rekomen ? 'bg-success' : 'bg-secondary';
                                ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td><span class="badge <?php echo $badge_rank_class; ?> fs-6 px-3"><?php echo $rank; ?></span></td> 
                                    <td class="text-start ps-4 text-dark fs-5"><?php echo $h['nama_mapel']; ?></td> 
                                    <td class="text-muted"><?php echo $h['skor_saw']; ?></td> 
                                    <td class="text-muted"><?php echo $h['proba_ml']; ?></td> 
                                    <td class="text-primary fs-5 fw-bold"><?php echo $h['skor_hybrid']; ?></td> 
                                    <td><span class="badge <?php echo $badge_status_class; ?> p-2"><?php echo $h['status']; ?></span></td> 
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
            <div class="alert alert-warning text-center">Data siswa ini belum diisi di menu Matriks Nilai.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded shadow-sm text-muted">
            <p class="mb-0">Silakan pilih nama siswa untuk memuat simulasi integrasi hasil keputusan cerdas.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>