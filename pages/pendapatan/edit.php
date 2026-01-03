<?php
// File: pages/pendapatan/edit.php
require_once __DIR__ . '/../../includes/header.php';
check_login();

$user_id = $_SESSION['user_id'];
$id_transaksi = $_GET['id'] ?? null;

if (!$id_transaksi) {
    header("Location: /laporan-keuangan/pendapatan.php");
    exit();
}

// Ambil data transaksi yang akan diedit
$stmt = $conn->prepare("SELECT * FROM transaksi WHERE id = ? AND user_id = ? AND tipe = 'Pemasukan'");
$stmt->bind_param("ii", $id_transaksi, $user_id);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();

if (!$transaksi) {
    // Jika data tidak ditemukan, redirect
    header("Location: /laporan-keuangan/pendapatan.php");
    exit();
}
// Ambil sumber dana
$sumber_dana = $conn->query("SELECT * FROM sumber_dana");
// Ambil semua kategori pemasukan
$kategori_pemasukan = $conn->query("SELECT * FROM kategori WHERE tipe = 'Pemasukan' ORDER BY nama_kategori ASC");

?>
<h1 class="h2">Edit Pendapatan</h1>
</div> <!-- Penutup div dari header -->

<div class="card">
    <div class="card-header">
        <h5>Form Edit Pendapatan</h5>
    </div>
    <div class="card-body">
        <form action="../../pendapatan.php" method="POST">
            <input type="hidden" name="id" value="<?= $transaksi['id'] ?>">
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($transaksi['tanggal']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="sumber_dana_id" class="form-label">Sumber Dana</label>
                <select class="form-select" name="sumber_dana_id" id="sumber_dana_id" required>
                    <?php while($sumber = $sumber_dana->fetch_assoc()): ?>
                    <option value="<?= $sumber['id'] ?>" 
                        <?= htmlspecialchars($sumber['nama']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
             <div class="mb-3">
                <label for="kategori_id" class="form-label">Kategori</label>
                <select class="form-select" name="kategori_id" id="kategori_id" required>
                    <?php while($kat = $kategori_pemasukan->fetch_assoc()): ?>
                    <option value="<?= $kat['id'] ?>" <?= ($kat['id'] == $transaksi['kategori_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="text" class="form-control" id="jumlah" name="jumlah"
       value="<?= number_format($transaksi['jumlah'], 0, ',', '.') ?>" required>
            </div>
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control" name="keterangan" id="keterangan" rows="3" required><?= htmlspecialchars($transaksi['keterangan']) ?></textarea>
            </div>
            <a href="../../pendapatan.php" class="btn btn-secondary">Batal</a>
            <button type="submit" name="edit_pendapatan" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
