
<?php
// File: pendapatan.php

require_once __DIR__ . '/config/database.php';
check_login();

$user_id = $_SESSION['user_id'];
$message = '';

// --- LOGIKA UNTUK PROSES POST (TAMBAH, EDIT, HAPUS) ---

// Tambah Pendapatan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_pendapatan'])) {
    
        $tanggal        = $_POST['tanggal'];
        $kategori_id    = $_POST['kategori_id'];
        $jumlah         = $_POST['jumlah'];
        $keterangan     = $_POST['keterangan'];
        $sumber_id = $_POST['sumber_id'];
       
        // Normalisasi format angka: 1.000,25 -> 1000.25
        $jumlah = str_replace('.', '', $jumlah);
        $jumlah = str_replace(',', '.', $jumlah);
    
        $stmt = $conn->prepare("
            INSERT INTO transaksi 
            (user_id, kategori_id, sumber_dana_id, tipe, jumlah, keterangan, tanggal) 
            VALUES (?, ?, ?, 'Pemasukan', ?, ?, ?)
        ");
    
        // BENAR: i i d s s i  (6 parameter)
        $stmt->bind_param(
            "iiidss",
            $user_id,
            $kategori_id,
            $sumber_id,
            $jumlah,
            $keterangan,
            $tanggal
            
        );
    
        if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Pendapatan berhasil ditambahkan.'];
        header("Location: pendapatan.php");
        exit();
    } else {
        $message = '<div class="alert alert-danger">Gagal menambahkan pendapatan.</div>';
    }
}
    
    


}

// Hapus Pendapatan
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
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
// Ambil sumber dana
$sumber_dana = $conn->query("SELECT * FROM sumber_dana");
// Ambil semua kategori pemasukan
$kategori_pemasukan = $conn->query("SELECT * FROM kategori WHERE tipe = 'Pemasukan' ORDER BY nama_kategori ASC");

// Filter sumber dana
$sumber_filter = isset($_GET['sumber_id']) ? intval($_GET['sumber_id']) : 0;
// Filter tanggal
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';





$query_pendapatan = "
    SELECT 
        t.id,
        t.tanggal,
        t.sumber_dana_id,
        t.kategori_id,
        k.nama_kategori,
        t.jumlah,
        t.keterangan,
        s.nama
    FROM transaksi t
    JOIN kategori k ON t.kategori_id = k.id
    JOIN sumber_dana s ON s.id = t.sumber_dana_id
    WHERE t.user_id = ?
      AND t.tipe = 'Pemasukan'
";
$params = [$user_id];
$types  = "i";

if ($sumber_filter) {
    $query_pendapatan .= " AND s.id = ?";
    $types  .= "i";
    $params[] = $sumber_filter;
}

if ($filter_start_date && $filter_end_date) {
    $query_pendapatan .= " AND t.tanggal BETWEEN ? AND ?";
    $types  .= "ss";
    $params[] = $filter_start_date;
    $params[] = $filter_end_date;
}

// ORDER BY HARUS PALING AKHIR
$query_pendapatan .= " ORDER BY t.tanggal DESC";




$stmt = $conn->prepare($query_pendapatan);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result_pendapatan = $stmt->get_result();

require_once __DIR__ . '/includes/header.php';
?>

<h1 class="h2">Manajemen Pendapatan</h1>
</div> <!-- Penutup div dari header -->

<?= $message ?>

<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-wallet2 me-1"></i> Daftar Pendapatan</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Pendapatan
            </button>
        </div>
    </div>
    <div class="card-body">
        
        <!-- Filter Form -->
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="sumber_id" class="form-label">Sumber Dana</label>
                <select class="form-select" name="sumber_id" id="sumber_id">
                    <option value="">-- Pilih Sumber Dana --</option>
                    <?php 
                    // Reset pointer jika diperlukan atau pastikan query dijalankan
                    if ($sumber_dana->num_rows > 0) mysqli_data_seek($sumber_dana, 0);
                    while ($sumber = $sumber_dana->fetch_assoc()): 
                        $sel = ($sumber_filter == $sumber['id']) ? ' selected ' : '';
                    ?>
                        <option value="<?= $sumber['id']?>" <?=$sel?>>
                            <?= htmlspecialchars($sumber['nama']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="start_date" id="start_date" value="<?= htmlspecialchars($filter_start_date) ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="end_date" id="end_date" value="<?= htmlspecialchars($filter_end_date) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped" id="pendapatanTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Sumber Dana</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result_pendapatan->num_rows > 0): $no = 1; ?>
                    <?php while($row = $result_pendapatan->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nama']) ?></span></td>
                            <td><span class="badge bg-success"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td class="text-end fw-bold"><?= format_rupiah($row['jumlah']) ?></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-edit"
                                    data-id="<?= $row['id'] ?>"
                                    data-tanggal="<?= $row['tanggal'] ?>"
                                    data-sumber="<?= $row['sumber_dana_id'] ?? '' ?>"
                                    data-kategori="<?= $row['kategori_id'] ?? '' ?>"
                                    data-jumlah="<?= (float)$row['jumlah'] ?>"
                                    data-keterangan="<?= htmlspecialchars($row['keterangan']) ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="pendapatan.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>




<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalKategori" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Kategori</label>
          <input type="text" class="form-control" id="nama_kategori">
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" id="btnSimpanKategori">
          Simpan
        </button>
      </div>
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
                <label for="sumber_dana_id" class="form-label">Sumber Dana</label>
                <select class="form-select" name="sumber_id" id="sumber_id" required>
                    <option value="">-- Pilih Sumber Dana --</option>
                    <?php 
                    // Ambil sumber dana
                    $sumber_dana = $conn->query("SELECT * FROM sumber_dana");
                    
                    while ($sumber = $sumber_dana->fetch_assoc()): 
                    $sel = ($sumber_filter == $sumber['id']) ? ' selected ' : '';
                    ?>
                    
                        <option value="<?= $sumber['id']?>" <?=$sel?>>
                            <?= htmlspecialchars($sumber['nama']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="kategori_id" class="form-label mb-0">
                        Kategori
                    </label>
            
                    <!--<a href="https://keuangan-af.dkm.web.id/kategori.php"-->
                    <!--   class="btn btn-success btn-sm mb-2">-->
                    <!--    <i class="bi bi-plus-circle"></i> Tambah-->
                    <!--</a>-->
                    <a href="javascript:void(0)"
                       class="btn btn-success btn-sm mb-2"
                       data-bs-toggle="modal"
                       data-bs-target="#modalKategori">
                       <i class="bi bi-plus-circle"></i> Tambah
                    </a>

                </div>
    
                <select class="form-select select2"
                    name="kategori_id"
                    id="kategori_id"
                    required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php mysqli_data_seek($kategori_pemasukan, 0); ?>
                    <?php while($kat = $kategori_pemasukan->fetch_assoc()): ?>
                        <option value="<?= $kat['id'] ?>">
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

            </div>

            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="text" class="form-control" id="jumlah" name="jumlah" required>
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
<!--Modal Edit-->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Pendapatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">

          <div class="mb-3">
            <label class="form-label">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" id="edit_tanggal" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Sumber Dana</label>
            <select class="form-select" name="sumber_dana_id" id="edit_sumber" required>
              <?php
              mysqli_data_seek($sumber_dana, 0);
              while ($s = $sumber_dana->fetch_assoc()):
              ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select class="form-select select2-edit" name="kategori_id" id="edit_kategori" required>
              <?php
              mysqli_data_seek($kategori_pemasukan, 0);
              while ($k = $kategori_pemasukan->fetch_assoc()):
              ?>
                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="text" class="form-control" name="jumlah" id="edit_jumlah" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="3"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="edit_pendapatan" class="btn btn-primary">
                Update
            </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fungsi format rupiah
    function formatRupiah(angka) {
        if (!angka) return '';
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Terapkan pada input #jumlah dan #edit_jumlah
    var inputJumlah = document.getElementById('jumlah');
    var inputEditJumlah = document.getElementById('edit_jumlah');

    if(inputJumlah){
        inputJumlah.addEventListener('keyup', function(e){
            this.value = formatRupiah(this.value);
        });
    }
    
    if(inputEditJumlah){
        inputEditJumlah.addEventListener('keyup', function(e){
            this.value = formatRupiah(this.value);
        });
    }

    // Format nilai awal saat modal edit muncul
    var editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('shown.bs.modal', function () {
            if (inputEditJumlah && inputEditJumlah.value) {
                 inputEditJumlah.value = formatRupiah(inputEditJumlah.value);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
