<?php
// Panggil koneksi database
include 'config.php';

// ==========================================
// 1. PROSES SIMPAN DATA (CREATE)
// ==========================================
if (isset($_POST['btn_simpan'])) {
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    
    if (!empty($nama_siswa)) {
        $query_tambah = "INSERT INTO tb_siswa (nama_siswa) VALUES ('$nama_siswa')";
        if (mysqli_query($koneksi, $query_tambah)) {
            header("Location: siswa.php?status=sukses-tambah");
            exit;
        }
    }
}

// ==========================================
// 2. PROSES UBAH DATA (UPDATE)
// ==========================================
if (isset($_POST['btn_ubah'])) {
    $id_siswa   = $_POST['id_siswa'];
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    
    if (!empty($nama_siswa)) {
        $query_ubah = "UPDATE tb_siswa SET nama_siswa = '$nama_siswa' WHERE id_siswa = $id_siswa";
        if (mysqli_query($koneksi, $query_ubah)) {
            header("Location: siswa.php?status=sukses-ubah");
            exit;
        }
    }
}

// ==========================================
// 3. PROSES HAPUS DATA (DELETE)
// ==========================================
if (isset($_GET['hapus'])) {
    $id_siswa = $_GET['hapus'];
    $query_hapus = "DELETE FROM tb_siswa WHERE id_siswa = $id_siswa";
    if (mysqli_query($koneksi, $query_hapus)) {
        header("Location: siswa.php?status=sukses-hapus");
        exit;
    }
}

// ==========================================
// 4. AMBIL DATA UNTUK FORM EDIT (JIKA TOMBOL EDIT DIKLIK)
// ==========================================
$edit_mode = false;
$id_edit   = "";
$nama_edit = "";

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id_edit   = $_GET['edit'];
    $query_ambil = mysqli_query($koneksi, "SELECT * FROM tb_siswa WHERE id_siswa = $id_edit");
    $data_edit   = mysqli_fetch_assoc($query_ambil);
    if ($data_edit) {
        $nama_edit = $data_edit['nama_siswa'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h3 class="mb-4 text-secondary fw-bold">Kelola Data Master Siswa</h3>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php
            if ($_GET['status'] == 'sukses-tambah') echo "Data siswa baru berhasil ditambahkan!";
            if ($_GET['status'] == 'sukses-ubah') echo "Data nama siswa berhasil diperbarui!";
            if ($_GET['status'] == 'sukses-hapus') echo "Data siswa telah berhasil dihapus dari sistem!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-bold bg-white border-bottom py-3">
                    <?php echo $edit_mode ? "Form Edit Nama Siswa" : "Form Tambah Siswa"; ?>
                </div>
                <div class="card-body">
                    <form action="siswa.php" method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id_siswa" value="<?php echo $id_edit; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nama_siswa" class="form-label text-muted small fw-bold">Nama Lengkap Siswa</label>
                            <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                   value="<?php echo $nama_edit; ?>" placeholder="Masukkan nama siswa..." required autocomplete="off">
                        </div>

                        <div class="d-grid gap-2">
                            <?php if ($edit_mode): ?>
                                <button type="submit" name="btn_ubah" class="btn btn-warning fw-bold">Perbarui Data</button>
                                <a href="siswa.php" class="btn btn-light border">Batal Edit</a>
                            <?php else: ?>
                                <button type="submit" name="btn_simpan" class="btn btn-primary fw-bold">Simpan Data</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-bold bg-white border-bottom py-3">
                    Daftar Nama Siswa Terdaftar
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="10%">No</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center" width="25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_tampil = mysqli_query($koneksi, "SELECT * FROM tb_siswa ORDER BY id_siswa DESC");
                                if (mysqli_num_rows($query_tampil) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_tampil)) {
                                        echo "<tr>";
                                        echo "<td class='text-center text-muted'>{$no}</td>";
                                        echo "<td class='fw-semibold text-dark'>{$row['nama_siswa']}</td>";
                                        echo "<td class='text-center'>
                                                <a href='siswa.php?edit={$row['id_siswa']}' class='btn btn-sm btn-outline-warning me-1'>Edit</a>
                                                <a href='siswa.php?hapus={$row['id_siswa']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data siswa ini?\")\'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center py-4 text-muted'>Belum ada data siswa.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>