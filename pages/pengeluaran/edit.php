<?php
// File: pages/pengeluaran/edit.php
require_once __DIR__ . '/../../includes/header.php';
check_login();

$user_id = $_SESSION['user_id'];
$id_transaksi = $_GET['id'] ?? null;

if (!$id_transaksi) {
    header("Location: /laporan-keuangan/pengeluaran.php");
    exit();
}

// Ambil data transaksi yang akan diedit
$stmt = $conn->prepare("SELECT * FROM transaksi WHERE id = ? AND user_id = ? AND tipe = 'Pengeluaran'");
$stmt->bind_param("ii", $id_transaksi, $user_id);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();

if (!$transaksi) {
    header("Location: /laporan-keuangan/pengeluaran.php");
    exit();
}

// Ambil semua kategori pengeluaran
$kategori_pengeluaran = $conn->query("SELECT * FROM kategori WHERE tipe = 'Pengeluaran' ORDER BY nama_kategori ASC");

?>
<h1 class="h2">Edit Pengeluaran</h1>
</div> <!-- Penutup div dari header -->

<div class="card">
    <div class="card-header">
        <h5>Form Edit Pengeluaran</h5>
    </div>
    <div class="card-body">
        <form action="/laporan-keuangan/pengeluaran.php" method="POST">
            <input type="hidden" name="id" value="<?= $transaksi['id'] ?>">
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($transaksi['tanggal']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="kategori_id" class="form-label">Kategori</label>
                <select class="form-select" name="kategori_id" id="kategori_id" required>
                    <?php while($kat = $kategori_pengeluaran->fetch_assoc()): ?>
                    <option value="<?= $kat['id'] ?>" <?= ($kat['id'] == $transaksi['kategori_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="number" class="form-control" id="jumlah" name="jumlah" step="0.01" value="<?= htmlspecialchars($transaksi['jumlah']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control" name="keterangan" id="keterangan" rows="3" required><?= htmlspecialchars($transaksi['keterangan']) ?></textarea>
            </div>
            <a href="/laporan-keuangan/pengeluaran.php" class="btn btn-secondary">Batal</a>
            <button type="submit" name="edit_pengeluaran" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
