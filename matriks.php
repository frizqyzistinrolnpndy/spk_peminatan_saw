<?php
include 'config.php';

// Ambil ID Siswa dari URL jika ada
$id_siswa_terpilih = isset($_GET['id_siswa']) ? $_GET['id_siswa'] : '';

// ==========================================
// PROSES SIMPAN / UPDATE MATRIKS
// ==========================================
if (isset($_POST['btn_simpan_matriks'])) {
    $id_siswa = $_POST['id_siswa'];
    
    // Trik Pemula Mudah: Hapus data lama siswa ini di tb_matriks agar tidak duplikat saat di-insert baru
    mysqli_query($koneksi, "DELETE FROM tb_matriks WHERE id_siswa = $id_siswa");
    
    // Ambil semua ID mapel untuk looping proses insert
    $query_mapel = mysqli_query($koneksi, "SELECT id_mapel FROM tb_mapel");
    while ($m = mysqli_fetch_assoc($query_mapel)) {
        $id_mapel = $m['id_mapel'];
        
        // Ambil data dari array form berdasarkan ID Mapel
        $c1 = $_POST['c1'][$id_mapel];
        $c2 = $_POST['c2'][$id_mapel];
        $c3 = $_POST['c3'][$id_mapel];
        $c4 = $_POST['c4'][$id_mapel];
        
        // Insert data baru ke matriks
        mysqli_query($koneksi, "INSERT INTO tb_matriks (id_siswa, id_mapel, c1_minat, c2_bakat, c3_nilai, c4_rencana) 
                                VALUES ($id_siswa, $id_mapel, $c1, $c2, $c3, $c4)");
    }
    
    header("Location: matriks.php?id_siswa=$id_siswa&status=sukses");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Matriks Penilaian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mb-5">
    <h3 class="mb-4 text-secondary fw-bold">Input & Kelola Nilai Matriks</h3>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            Nilai matriks kriteria berhasil disimpan ke sistem!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="matriks.php" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="id_siswa" class="form-label small fw-bold text-muted">Pilih Siswa Terlebih Dahulu</label>
                        <select name="id_siswa" id="id_siswa" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Nama Siswa --</option>
                            <?php
                            $q_siswa = mysqli_query($koneksi, "SELECT * FROM tb_siswa ORDER BY nama_siswa ASC");
                            while ($s = mysqli_fetch_assoc($q_siswa)) {
                                $selected = ($id_siswa_terpilih == $s['id_siswa']) ? 'selected' : '';
                                echo "<option value='{$s['id_siswa']}' {$selected}>{$s['nama_siswa']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <a href="matriks.php" class="btn btn-light border w-100">Reset Pilihan</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($id_siswa_terpilih)): ?>
        <form action="matriks.php" method="POST">
            <input type="hidden" name="id_siswa" value="<?php echo $id_siswa_terpilih; ?>">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold py-3">
                    Form Pengisian Kriteria per Mata Pelajaran
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <th width="18%">C1: Minat (1-5)</th>
                                    <th width="18%">C2: Bakat (1-100)</th>
                                    <th width="18%">C3: Nilai Rapor</th>
                                    <th width="18%">C4: Rencana Studi (1-5)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Ambil semua daftar mapel
                                $q_mapel = mysqli_query($koneksi, "SELECT * FROM tb_mapel");
                                while ($m = mysqli_fetch_assoc($q_mapel)) {
                                    $id_mapel = $m['id_mapel'];

                                    // Cek apakah siswa ini sudah punya nilai di database sebelumnya
                                    $q_cek = mysqli_query($koneksi, "SELECT * FROM tb_matriks WHERE id_siswa = $id_siswa_terpilih AND id_mapel = $id_mapel");
                                    $d_nilai = mysqli_fetch_assoc($q_cek);

                                    // Jika ada nilainya, pakai nilai lama. Jika tidak, kosongkan (0)
                                    $val_c1 = isset($d_nilai['c1_minat']) ? $d_nilai['c1_minat'] : '';
                                    $val_c2 = isset($d_nilai['c2_bakat']) ? $d_nilai['c2_bakat'] : '';
                                    $val_c3 = isset($d_nilai['c3_nilai']) ? $d_nilai['c3_nilai'] : '';
                                    $val_c4 = isset($d_nilai['c4_rencana']) ? $d_nilai['c4_rencana'] : '';
                                ?>
                                <tr>
                                    <td class="fw-bold text-secondary"><?php echo $m['nama_mapel']; ?></td>
                                    
                                    <td>
                                        <input type="number" name="c1[<?php echo $id_mapel; ?>]" class="form-control" 
                                               min="1" max="5" value="<?php echo $val_c1; ?>" placeholder="Skala 1-5" required>
                                    </td>
                                    
                                    <td>
                                        <input type="number" name="c2[<?php echo $id_mapel; ?>]" class="form-control" 
                                               min="1" max="100" value="<?php echo $val_c2; ?>" placeholder="Skala 1-100" required>
                                    </td>
                                    
                                    <td>
                                        <input type="number" name="c3[<?php echo $id_mapel; ?>]" class="form-control" 
                                               min="0" max="100" value="<?php echo $val_c3; ?>" placeholder="Nilai Rapor" required>
                                    </td>
                                    
                                    <td>
                                        <input type="number" name="c4[<?php echo $id_mapel; ?>]" class="form-control" 
                                               min="1" max="5" value="<?php echo $val_c4; ?>" placeholder="Skala 1-5" required>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-end py-3">
                    <button type="submit" name="btn_simpan_matriks" class="btn btn-success fw-bold px-4">Simpan Semua Nilai</button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <p class="text-muted mb-0">Silakan pilih nama siswa terlebih dahulu untuk mengisi matriks keputusan.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>