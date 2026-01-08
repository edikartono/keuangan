<?php
echo "Test";
exit;
// File: index.php (Dashboard)

// 1. Sertakan konfigurasi dan mulai sesi
require_once __DIR__ . '/config/database.php';

// 2. Periksa apakah pengguna sudah login.
check_login();
require_once __DIR__ . '/includes/header.php';


// Ambil data ringkasan
$user_id = $_SESSION['user_id'];

// Total Pemasukan
$query_pemasukan = "SELECT SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND tipe = 'Pemasukan'";
$stmt = $conn->prepare($query_pemasukan);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_pemasukan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Total Pengeluaran
$query_pengeluaran = "SELECT SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND tipe = 'Pengeluaran'";
$stmt = $conn->prepare($query_pengeluaran);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_pengeluaran = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$saldo_akhir = $total_pemasukan - $total_pengeluaran;

// 5 Transaksi Terakhir
$query_transaksi = "SELECT t.*, k.nama_kategori FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = ? ORDER BY t.tanggal DESC, t.dibuat_pada DESC LIMIT 5";
$stmt = $conn->prepare($query_transaksi);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_transaksi = $stmt->get_result();

?>
<h1 class="h2">Dashboard</h1>
</div> <!-- Penutup div dari header -->

<!-- Card Ringkasan -->
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-wallet2 me-2"></i>Total Pemasukan</h5>
                <p class="card-text fs-4 fw-bold"><?= format_rupiah($total_pemasukan) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-cash-coin me-2"></i>Total Pengeluaran</h5>
                <p class="card-text fs-4 fw-bold"><?= format_rupiah($total_pengeluaran) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-piggy-bank me-2"></i>Saldo Akhir</h5>
                <p class="card-text fs-4 fw-bold"><?= format_rupiah($saldo_akhir) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Transaksi Terakhir -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">5 Transaksi Terakhir</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result_transaksi->num_rows > 0): ?>
                    <?php while($row = $result_transaksi->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td class="text-end fw-bold <?= $row['tipe'] == 'Pemasukan' ? 'text-success' : 'text-danger' ?>">
                                <?= ($row['tipe'] == 'Pemasukan' ? '+' : '-') . format_rupiah($row['jumlah']) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Belum ada transaksi.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

