<?php
require_once __DIR__ . '/../../includes/header.php';
check_login();

$user_id = $_SESSION['user_id'];

// Ambil daftar sumber dana
$sumber = $conn->query("SELECT * FROM sumber_dana ORDER BY nama_sumber");

// Tahun & bulan filter
$tahun = $_GET['tahun'] ?? date('Y');
$bulan = $_GET['bulan'] ?? date('m');

// Ambil saldo awal tiap sumber dana
$sql_saldo = $conn->prepare("
    SELECT sa.*, sd.nama_sumber 
    FROM saldo_awal sa
    JOIN sumber_dana sd ON sa.sumber_id = sd.id
    WHERE sa.tahun = ? AND sa.bulan = ?
");
$sql_saldo->bind_param("ii", $tahun, $bulan);
$sql_saldo->execute();
$saldo_awal = $sql_saldo->get_result();

// Ambil transaksi bulan terkait
$sql_trans = $conn->prepare("
    SELECT t.*, sd.nama_sumber, k.nama_kategori 
    FROM transaksi t
    JOIN sumber_dana sd ON t.sumber_id = sd.id
    LEFT JOIN kategori k ON t.kategori_id = k.id
    WHERE t.user_id = ? 
      AND MONTH(t.tanggal) = ? 
      AND YEAR(t.tanggal) = ?
    ORDER BY t.tanggal ASC, t.id ASC
");
$sql_trans->bind_param("iii", $user_id, $bulan, $tahun);
$sql_trans->execute();
$transaksi = $sql_trans->get_result();
?>

<h1 class="h2">Mutasi Keuangan</h1>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <input type="number" class="form-control" name="tahun" value="<?= $tahun ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select class="form-select" name="bulan">
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($bulan == $i ? 'selected':'') ?>>
                            <?= date("F", mktime(0,0,0,$i,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-info w-100">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<br>

<div class="card">
    <div class="card-header bg-secondary text-white">
        <b>Saldo Awal</b>
    </div>
    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sumber Dana</th>
                    <th class="text-end">Saldo Awal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $saldo_awal_total = 0;
                while($sa = $saldo_awal->fetch_assoc()):
                    $saldo_awal_total += $sa['saldo_awal'];
                ?>
                <tr>
                    <td><?= $sa['nama_sumber'] ?></td>
                    <td class="text-end"><?= format_rupiah($sa['saldo_awal']) ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="table-light">
                    <th>Total</th>
                    <th class="text-end"><?= format_rupiah($saldo_awal_total) ?></th>
                </tr>
            </tbody>
        </table>

    </div>
</div>

<br>

<div class="card">
    <div class="card-header bg-primary text-white">
        <b>Mutasi Transaksi</b>
    </div>
    <div class="card-body">

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Sumber Dana</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th class="text-end">Pemasukan</th>
                    <th class="text-end">Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_debet = 0;
                $total_kredit = 0;

                while($t = $transaksi->fetch_assoc()):
                    $debet = $t['tipe'] == 'Pemasukan' ? $t['jumlah'] : 0;
                    $kredit = $t['tipe'] == 'Pengeluaran' ? $t['jumlah'] : 0;

                    $total_debet += $debet;
                    $total_kredit += $kredit;
                ?>
                <tr>
                    <td><?= date('d M Y', strtotime($t['tanggal'])) ?></td>
                    <td><?= $t['nama_sumber'] ?></td>
                    <td><?= $t['nama_kategori'] ?></td>
                    <td><?= htmlspecialchars($t['keterangan']) ?></td>
                    <td class="text-end"><?= $debet ? format_rupiah($debet) : '-' ?></td>
                    <td class="text-end"><?= $kredit ? format_rupiah($kredit) : '-' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th class="text-end"><?= format_rupiah($total_debet) ?></th>
                    <th class="text-end"><?= format_rupiah($total_kredit) ?></th>
                </tr>
                <tr class="table-info">
                    <th colspan="4" class="text-end">Saldo Akhir</th>
                    <th colspan="2" class="text-end">
                        <?= format_rupiah($saldo_awal_total + $total_debet - $total_kredit) ?>
                    </th>
                </tr>
            </tfoot>
        </table>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
