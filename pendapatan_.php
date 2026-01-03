<?php
// File: pendapatan.php
require_once __DIR__ . '/includes/header.php';
check_login();

$user_id = $_SESSION['user_id'];
$message = '';

// --- LOGIKA UNTUK PROSES POST (TAMBAH, EDIT, HAPUS) ---

// Tambah Pendapatan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pendapatan'])) {
    $tanggal = $_POST['tanggal'];
    $kategori_id = $_POST['kategori_id'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    $stmt = $conn->prepare("INSERT INTO transaksi (user_id, kategori_id, tipe, jumlah, keterangan, tanggal) VALUES (?, ?, 'Pemasukan', ?, ?, ?)");
    // FIX: Mengubah tipe data dari "iisds" menjadi "iidss" agar sesuai.
    // jumlah (d) -> double, keterangan (s) -> string.
    $stmt->bind_param("iidss", $user_id, $kategori_id, $jumlah, $keterangan, $tanggal);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Pendapatan berhasil ditambahkan.</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal menambahkan pendapatan.</div>';
    }
}

// Edit Pendapatan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pendapatan'])) {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $kategori_id = $_POST['kategori_id'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    $stmt = $conn->prepare("UPDATE transaksi SET tanggal=?, kategori_id=?, jumlah=?, keterangan=? WHERE id=? AND user_id=?");
    // FIX: Mengubah tipe data dari "sidssi" menjadi "sidsii" agar sesuai.
    $stmt->bind_param("sidsii", $tanggal, $kategori_id, $jumlah, $keterangan, $id, $user_id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Pendapatan berhasil diubah.</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal mengubah pendapatan.</div>';
    }
}

// Hapus Pendapatan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM transaksi WHERE id=? AND user_id=? AND tipe='Pemasukan'");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Pendapatan berhasil dihapus.</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal menghapus pendapatan.</div>';
    }
}

// --- LOGIKA UNTUK FETCH DATA ---

// Ambil semua kategori pemasukan
$kategori_pemasukan = $conn->query("SELECT * FROM kategori WHERE tipe = 'Pemasukan' ORDER BY nama_kategori ASC");

// Filter tanggal
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';

$query_pendapatan = "SELECT t.id, t.tanggal, k.nama_kategori, t.jumlah, t.keterangan 
                     FROM transaksi t 
                     JOIN kategori k ON t.kategori_id = k.id 
                     WHERE t.user_id = ? AND t.tipe = 'Pemasukan'";
if ($filter_start_date && $filter_end_date) {
    $query_pendapatan .= " AND t.tanggal BETWEEN ? AND ?";
}
$query_pendapatan .= " ORDER BY t.tanggal DESC";

$stmt = $conn->prepare($query_pendapatan);
if ($filter_start_date && $filter_end_date) {
    $stmt->bind_param("iss", $user_id, $filter_start_date, $filter_end_date);
} else {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result_pendapatan = $stmt->get_result();
?>

<h1 class="h2">Manajemen Pendapatan</h1>
</div> <!-- Penutup div dari header -->

<?= $message ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pendapatan</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Pendapatan
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-5">
                <label for="start_date" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="start_date" id="start_date" value="<?= htmlspecialchars($filter_start_date) ?>">
            </div>
            <div class="col-md-5">
                <label for="end_date" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="end_date" id="end_date" value="<?= htmlspecialchars($filter_end_date) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-info w-100">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result_pendapatan->num_rows > 0): $no = 1; ?>
                    <?php while($row = $result_pendapatan->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td class="text-end"><?= format_rupiah($row['jumlah']) ?></td>
                            <td class="text-center">
                                <a href="pages/pendapatan/edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                <a href="pendapatan.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Tidak ada data pendapatan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pendapatan -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tambahModalLabel">Tambah Pendapatan Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label for="kategori_id" class="form-label">Kategori</label>
                <select class="form-select" name="kategori_id" id="kategori_id" required>
                    <?php mysqli_data_seek($kategori_pemasukan, 0); // Reset pointer ?>
                    <?php while($kat = $kategori_pemasukan->fetch_assoc()): ?>
                    <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="number" class="form-control" id="jumlah" name="jumlah" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control" name="keterangan" id="keterangan" rows="3" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah_pendapatan" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
