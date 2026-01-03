<?php

require_once __DIR__ . '/includes/header.php';
check_login();

$sumber_filter = isset($_GET['sumber_id']) ? intval($_GET['sumber_id']) : 0;
$tgl_from      = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
$tgl_to        = isset($_GET['to']) ? $_GET['to'] : date('Y-m-t');

// 1. Hitung Saldo Awal (sebelum tanggal filter)
$saldo_sebelumnya = 0;

// Ambil saldo awal dari tabel sumber_dana (master)
$sql_master = "SELECT SUM(saldo_awal) as total FROM sumber_dana";
if ($sumber_filter > 0) {
    $sql_master .= " WHERE id = $sumber_filter";
}
$res_master = $conn->query($sql_master);
$saldo_sebelumnya += ($res_master->fetch_assoc()['total'] ?? 0);

// Hitung mutasi transaksi SEBELUM tanggal filter
$sql_prev = "SELECT SUM(IF(tipe='Pemasukan', jumlah, -jumlah)) as mutasi_lalu 
             FROM transaksi 
             WHERE tanggal < ?";
$params_prev = [$tgl_from];
$types_prev = "s";

if ($sumber_filter > 0) {
    $sql_prev .= " AND sumber_dana_id = ?";
    $params_prev[] = $sumber_filter;
    $types_prev .= "i";
}

$stmt_prev = $conn->prepare($sql_prev);
$stmt_prev->bind_param($types_prev, ...$params_prev);
$stmt_prev->execute();
$saldo_sebelumnya += ($stmt_prev->get_result()->fetch_assoc()['mutasi_lalu'] ?? 0);


// 2. Ambil Transaksi dalam Range Filter
$where  = " WHERE t.tanggal BETWEEN ? AND ? ";
$params = [$tgl_from, $tgl_to];
$types  = "ss";

if ($sumber_filter > 0) {
    $where .= " AND t.sumber_dana_id = ? ";
    $types .= "i";
    $params[] = $sumber_filter;
}

$sql = "SELECT t.*, sd.nama AS sumber_nama
        FROM transaksi t
        LEFT JOIN sumber_dana sd ON t.sumber_dana_id = sd.id
        $where
        ORDER BY t.tanggal ASC, t.id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// Variabel untuk running balance
$saldo_berjalan = $saldo_sebelumnya;

?>

<h1 class="h2">Laporan Mutasi Dana</h1>
</div> <!-- Penutup div dari header -->

<div class="card shadow-sm">
    <div class="card-header">
        <i class="bi bi-bank me-1"></i> Filter Data Mutasi
    </div>
    <div class="card-body">
        <form class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Sumber Dana</label>
                <select name="sumber_id" class="form-select">
                    <option value="0">-- Semua Sumber --</option>
                    <?php
                    $q = $conn->query("SELECT id, nama FROM sumber_dana ORDER BY nama ASC");
                    while ($r = $q->fetch_assoc()) {
                        $sel = ($sumber_filter == $r['id']) ? 'selected' : '';
                        echo "<option value='{$r['id']}' $sel>" . htmlspecialchars($r['nama']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="from" value="<?= $tgl_from ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="to" value="<?= $tgl_to ?>" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped" id="mutasiTable">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Sumber Dana</th>
                        <th>Keterangan</th>
                        <th class="text-end text-success">Debit (+)</th>
                        <th class="text-end text-danger">Kredit (-)</th>
                        <th class="text-end fw-bold">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Baris Saldo Awal -->
                    <tr class="table-info">
                        <td></td> <!-- Tanggal -->
                        <td></td> <!-- Sumber -->
                        <td class="fw-bold">Saldo Awal (Sebelum <?= date('d M Y', strtotime($tgl_from)) ?>)</td>
                        <td class="text-end"></td>
                        <td class="text-end"></td>
                        <td class="text-end fw-bold"><?= format_rupiah($saldo_berjalan) ?></td>
                    </tr>

                    <?php if ($res->num_rows > 0): ?>
                        <?php while ($row = $res->fetch_assoc()): 
                            $debit = ($row['tipe'] == 'Pemasukan') ? $row['jumlah'] : 0;
                            $kredit = ($row['tipe'] == 'Pengeluaran') ? $row['jumlah'] : 0;
                            
                            // Hitung running balance
                            $saldo_berjalan += ($debit - $kredit);
                        ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['sumber_nama']) ?></span></td>
                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td class="text-end text-success"><?= $debit > 0 ? format_rupiah($debit) : '-' ?></td>
                            <td class="text-end text-danger"><?= $kredit > 0 ? format_rupiah($kredit) : '-' ?></td>
                            <td class="text-end fw-bold"><?= format_rupiah($saldo_berjalan) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#mutasiTable').DataTable({
        "pageLength": 25,
        "language": {
            "search": "Cari data:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "zeroRecords": "Tidak ditemukan data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(difilter dari _MAX_ total data)"
        },
        "order": [] // Disable initial sorting to respect SQL order
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
