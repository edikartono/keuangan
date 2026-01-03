<?php
// File: export_excel.php
require_once __DIR__ . '/config/database.php';
check_login();

$user_id = $_SESSION['user_id'];
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$nama_bulan = date('F', mktime(0, 0, 0, $bulan, 10));

// Set header untuk download file excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan Keuangan - $nama_bulan $tahun.xls");

// Ambil semua transaksi pada periode yang dipilih
$query = "SELECT t.tanggal, k.nama_kategori, t.tipe, t.keterangan, t.jumlah
          FROM transaksi t
          JOIN kategori k ON t.kategori_id = k.id
          WHERE t.user_id = ? AND MONTH(t.tanggal) = ? AND YEAR(t.tanggal) = ?
          ORDER BY t.tanggal ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $user_id, $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

// Kalkulasi total
$total_pemasukan = 0;
$total_pengeluaran = 0;

// Buat konten Excel (dalam format tabel HTML)
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        .header { background-color: #f2f2f2; font-weight: bold; }
        .total { font-weight: bold; }
        .pemasukan { color: #198754; }
        .pengeluaran { color: #dc3545; }
    </style>
</head>
<body>

    <h2>Laporan Keuangan - <?= "$nama_bulan $tahun" ?></h2>

    <table>
        <thead>
            <tr class="header">
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Tipe</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        if ($row['tipe'] == 'Pemasukan') {
                            $total_pemasukan += $row['jumlah'];
                        } else {
                            $total_pengeluaran += $row['jumlah'];
                        }
                    ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['tipe']) ?></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td class="<?= $row['tipe'] == 'Pemasukan' ? 'pemasukan' : 'pengeluaran' ?>">
                            <?= format_rupiah($row['jumlah']) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Tidak ada data untuk periode ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="4" style="text-align: right;">Total Pemasukan</td>
                <td class="pemasukan"><?= format_rupiah($total_pemasukan) ?></td>
            </tr>
            <tr class="total">
                <td colspan="4" style="text-align: right;">Total Pengeluaran</td>
                <td class="pengeluaran"><?= format_rupiah($total_pengeluaran) ?></td>
            </tr>
            <tr class="total">
                <td colspan="4" style="text-align: right;">Saldo Akhir</td>
                <td><?= format_rupiah($total_pemasukan - $total_pengeluaran) ?></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
<?php
$stmt->close();
$conn->close();
exit();
?>
