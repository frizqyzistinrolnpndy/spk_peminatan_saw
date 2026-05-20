<?php
include 'config.php';

// ==========================================
// PROSES UPDATE BOBOT KRITERIA
// ==========================================
if (isset($_POST['btn_update_bobot'])) {
    foreach ($_POST['bobot'] as $id_kriteria => $nilai_bobot) {
        $nilai_bobot = mysqli_real_escape_string($koneksi, $nilai_bobot);
        
        // Update bobot ke database
        mysqli_query($koneksi, "UPDATE tb_kriteria SET bobot = '$nilai_bobot' WHERE id_kriteria = '$id_kriteria'");
    }
    header("Location: kriteria.php?status=sukses");
    exit;
}

// ==========================================
// AMBIL DATA KRITERIA & HITUNG TOTAL BOBOT
// ==========================================
$query_kriteria = mysqli_query($koneksi, "SELECT * FROM tb_kriteria");
$data_kriteria = [];
$total_bobot = 0;

while ($row = mysqli_fetch_assoc($query_kriteria)) {
    $data_kriteria[] = $row;
    $total_bobot += $row['bobot']; // Jumlahkan semua bobot yang ada
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Bobot Kriteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h3 class="mb-4 text-secondary fw-bold">Kelola Bobot Kriteria (SAW)</h3>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            Bobot kriteria berhasil diperbarui!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (round($total_bobot, 2) != 1.00): ?>
        <div class="alert alert-danger shadow-sm" role="alert">
            <strong>Perhatian!</strong> Total bobot saat ini adalah <strong><?php echo $total_bobot; ?></strong>. Dalam metode SAW, total penjumlahan seluruh bobot kriteria disarankan pas <strong>1.00 (100%)</strong> agar hasil perankingan akurat.
        </div>
    <?php else: ?>
        <div class="alert alert-success shadow-sm" role="alert">
            <strong>Sip!</strong> Total bobot kriteria sudah ideal dan konsisten (Pas 1.00 / 100%).
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold py-3">
            Daftar Variabel & Pengaturan Bobot
        </div>
        <div class="card-body p-0">
            <form action="kriteria.php" method="POST">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="15%">Kode</th>
                                <th>Nama Kriteria Variable</th>
                                <th class="text-center" width="20%">Tipe Kriteria</th>
                                <th class="text-center" width="25%">Bobot Nilai (Desimal)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_kriteria as $k): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?php echo $k['id_kriteria']; ?></td>
                                <td class="fw-semibold text-dark"><?php echo $k['nama_kriteria']; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark text-capitalize fs-6 px-3">
                                        <?php echo $k['tipe']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="input-group justify-content-center">
                                        <input type="number" name="bobot[<?php echo $k['id_kriteria']; ?>]" 
                                               class="form-control text-center fw-bold mx-auto" style="max-width: 150px;"
                                               step="0.01" min="0" max="1" value="<?php echo $k['bobot']; ?>" required>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end pe-5">Total Penjumlahan Bobot :</td>
                                <td class="text-center fs-5 text-primary"><?php echo $total_bobot; ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer bg-white text-end py-3">
                    <button type="submit" name="btn_update_bobot" class="btn btn-primary fw-bold px-4">Simpan Perubahan Bobot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>