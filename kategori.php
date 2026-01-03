<?php
// File: kategori.php
require_once __DIR__ . '/includes/header.php';
check_login();

// Ambil data kategori Pemasukan
$pemasukan_res = $conn->query("SELECT * FROM kategori WHERE tipe = 'Pemasukan' ORDER BY nama_kategori");
// Ambil data kategori Pengeluaran
$pengeluaran_res = $conn->query("SELECT * FROM kategori WHERE tipe = 'Pengeluaran' ORDER BY nama_kategori");

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<h1 class="h2">Manajemen Kategori</h1>
</div> <!-- Penutup div dari header -->

<?php if ($message): ?>
<div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<div class="row">
    <!-- Kolom Kategori Pemasukan -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kategori Pemasukan</h5>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#tambahKategoriModal" data-tipe="Pemasukan">
                    <i class="bi bi-plus-circle"></i> Tambah
                </button>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while($row = $pemasukan_res->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($row['nama_kategori']) ?>
                        <div>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editKategoriModal" data-id="<?= $row['id'] ?>" data-nama="<?= htmlspecialchars($row['nama_kategori']) ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="pages/kategori/hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kolom Kategori Pengeluaran -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kategori Pengeluaran</h5>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#tambahKategoriModal" data-tipe="Pengeluaran">
                    <i class="bi bi-plus-circle"></i> Tambah
                </button>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php while($row = $pengeluaran_res->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($row['nama_kategori']) ?>
                        <div>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editKategoriModal" data-id="<?= $row['id'] ?>" data-nama="<?= htmlspecialchars($row['nama_kategori']) ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="pages/kategori/hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>


<!-- Modal Tambah Kategori -->
<div class="modal fade" id="tambahKategoriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="pages/kategori/proses.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahKategoriModalLabel">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tipe" id="tambah-tipe">
                    <div class="mb-3">
                        <label for="tambah-nama" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" id="tambah-nama" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editKategoriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="pages/kategori/proses.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKategoriModalLabel">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-nama" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" id="edit-nama" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$footer_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    var tambahModal = document.getElementById("tambahKategoriModal");
    tambahModal.addEventListener("show.bs.modal", function (event) {
        var button = event.relatedTarget;
        var tipe = button.getAttribute("data-tipe");
        var modalTitle = tambahModal.querySelector(".modal-title");
        var modalTipeInput = tambahModal.querySelector("#tambah-tipe");
        modalTitle.textContent = "Tambah Kategori " + tipe;
        modalTipeInput.value = tipe;
    });

    var editModal = document.getElementById("editKategoriModal");
    editModal.addEventListener("show.bs.modal", function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute("data-id");
        var nama = button.getAttribute("data-nama");
        var modalIdInput = editModal.querySelector("#edit-id");
        var modalNamaInput = editModal.querySelector("#edit-nama");
        modalIdInput.value = id;
        modalNamaInput.value = nama;
    });
});
</script>
';
require_once __DIR__ . '/includes/footer.php'; 
// Note: You might need to adjust footer.php to accept and print $footer_scripts
?>
