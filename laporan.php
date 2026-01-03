<?php
// File: laporan.php
require_once __DIR__ . '/includes/header.php';
check_login();

$user_id = $_SESSION['user_id'];

// 1. Ambil daftar Sumber Dana untuk filter
$sumber_dana = $conn->query("SELECT * FROM sumber_dana ORDER BY nama ASC");

// 2. Ambil Parameter Filter
$bulan      = $_GET['bulan'] ?? date('m');
$tahun      = $_GET['tahun'] ?? date('Y');
$sumber_id  = isset($_GET['sumber_id']) ? (int)$_GET['sumber_id'] : 0; // 0 = Semua

// Helper untuk membangun query dinamis
function build_query($conn, $user_id, $bulan, $tahun, $sumber_id, $tipe = null, $is_chart = false) {
    if ($is_chart) {
        $sql = "SELECT DAY(tanggal) as hari, tipe, SUM(jumlah) as total 
                FROM transaksi 
                WHERE user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?";
    } else {
        $sql = "SELECT SUM(jumlah) as total FROM transaksi 
                WHERE user_id = ? AND tipe = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?";
    }

    $types = "iss"; // default types
    $params = [$user_id, $bulan, $tahun];
    
    // Jika bukan chart, ada parameter 'tipe' disisipkan sebelum MONTH/YEAR di query dasar
    // Query dasar di atas: ... tipe = ? ...
    // Jadi urutan params: user_id (i), tipe (s), bulan (s), tahun (s)
    if (!$is_chart) {
        $types = "isss";
        $params = [$user_id, $tipe, $bulan, $tahun];
    }

    // Tambahan filter Sumber Dana
    if ($sumber_id > 0) {
        $sql .= " AND sumber_dana_id = ?";
        $types .= "i";
        $params[] = $sumber_id;
    }
    
    if ($is_chart) {
        $sql .= " GROUP BY DAY(tanggal), tipe";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

// 3. Eksekusi Query Ringkasan
$res_pemasukan = build_query($conn, $user_id, $bulan, $tahun, $sumber_id, 'Pemasukan');
$total_pemasukan = $res_pemasukan->fetch_assoc()['total'] ?? 0;

$res_pengeluaran = build_query($conn, $user_id, $bulan, $tahun, $sumber_id, 'Pengeluaran');
$total_pengeluaran = $res_pengeluaran->fetch_assoc()['total'] ?? 0;

$saldo = $total_pemasukan - $total_pengeluaran;

// 4. Eksekusi Query Grafik
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$labels = range(1, $days_in_month);
$data_pemasukan = array_fill(1, $days_in_month, 0);
$data_pengeluaran = array_fill(1, $days_in_month, 0);

$result_chart = build_query($conn, $user_id, $bulan, $tahun, $sumber_id, null, true);

while ($row = $result_chart->fetch_assoc()) {
    $hari = (int)$row['hari'];
    if ($row['tipe'] == 'Pemasukan') {
        $data_pemasukan[$hari] = (float)$row['total'];
    } else {
        $data_pengeluaran[$hari] = (float)$row['total'];
    }
}
?>

<h1 class="h2">Laporan Bulanan</h1>
</div> <!-- Penutup div dari header -->

<div class="card mb-4 shadow-sm">
    <div class="card-header">
        <i class="bi bi-filter me-1"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form class="row g-3 align-items-center">
            
            <div class="col-md-3">
                <label for="bulan" class="form-label">Bulan</label>
                <select name="bulan" id="bulan" class="form-select">
                    <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ($m == $bulan) ? 'selected' : '' ?>>
                            <?= date('F', mktime(0,0,0,$m, 1, date('Y'))) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="tahun" class="form-label">Tahun</label>
                <select name="tahun" id="tahun" class="form-select">
                    <?php for ($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= ($y == $tahun) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="sumber_id" class="form-label">Sumber Dana</label>
                <select name="sumber_id" id="sumber_id" class="form-select">
                    <option value="0">-- Semua Sumber --</option>
                    <?php while($s = $sumber_dana->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>" <?= ($s['id'] == $sumber_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nama']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan Laporan -->
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card border-success border-2">
            <div class="card-body text-success">
                <h5 class="card-title">Total Pemasukan</h5>
                <p class="card-text fs-4 fw-bold"><?= format_rupiah($total_pemasukan) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-danger border-2">
            <div class="card-body text-danger">
                <h5 class="card-title">Total Pengeluaran</h5>
                <p class="card-text fs-4 fw-bold"><?= format_rupiah($total_pengeluaran) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-info border-2">
            <div class="card-body text-info">
                <h5 class="card-title">Saldo Bulan Ini</h5>
                <p class="card-text fs-4 fw-bold"><?= format_rupiah($saldo) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Grafik -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-bar-chart-line-fill me-1"></i> Grafik Pemasukan & Pengeluaran Harian
    </div>
    <div class="card-body">
        <canvas id="financialChart"></canvas>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('financialChart').getContext('2d');
    const financialChart = new Chart(ctx, {
        type: 'line', // bisa 'bar' atau 'line'
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Pemasukan',
                data: <?= json_encode(array_values($data_pemasukan)) ?>,
                backgroundColor: 'rgba(25, 135, 84, 0.2)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }, {
                label: 'Pengeluaran',
                data: <?= json_encode(array_values($data_pengeluaran)) ?>,
                backgroundColor: 'rgba(220, 53, 69, 0.2)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
