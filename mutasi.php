<?php

require_once __DIR__ . '/includes/header.php';
check_login();

$sumber_filter = isset($_GET['sumber_id']) ? intval($_GET['sumber_id']) : 0;
$tgl_from      = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
$tgl_to        = isset($_GET['to']) ? $_GET['to'] : date('Y-m-t');

$where  = " WHERE m.tanggal BETWEEN ? AND ? ";
$params = [$tgl_from, $tgl_to];
$types  = "ss";

if ($sumber_filter > 0) {
    $where .= " AND m.sumber_id = ? ";
    $types .= "i";
    $params[] = $sumber_filter;
}

$sql = "SELECT m.*, sd.nama AS sumber_nama
        FROM mutasi m
        LEFT JOIN sumber_dana sd ON m.sumber_id = sd.id
        $where
        ORDER BY m.tanggal ASC, m.id ASC";
        

$stmt = $conn->prepare($sql);

if ($sumber_filter > 0) {
    $stmt->bind_param($types, $params[0], $params[1], $params[2]);
} else {
    $stmt->bind_param($types, $params[0], $params[1]);
}

$stmt->execute();
$res = $stmt->get_result();
?>

<h3>Laporan Mutasi</h3>
</div> 
<form class="form-inline mb-2">
    <select name="sumber_id" class="form-control">
        <option value="0">-- Semua Sumber --</option>
        <?php
        $q = $conn->query("SELECT id, nama FROM sumber_dana ORDER BY nama ASC");
        while ($r = $q->fetch_assoc()) {
            $sel = ($sumber_filter == $r['id']) ? 'selected' : '';
            echo "<option value='{$r['id']}' $sel>" . htmlspecialchars($r['nama']) . "</option>";
        }
        ?>
    </select>

    <input type="date" name="from" value="<?= $tgl_from ?>" class="form-control ml-1">
    <input type="date" name="to" value="<?= $tgl_to ?>" class="form-control ml-1">
    <button class="btn btn-primary ml-1">Filter</button>
</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Sumber Dana</th>
            <th>Keterangan</th>
            <th>Debit (+)</th>
            <th>Kredit (-)</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['tanggal'] ?></td>
            <td><?= htmlspecialchars($row['sumber_nama']) ?></td>
            <td><?= htmlspecialchars($row['keterangan']) ?></td>
            <td class="text-end"><?= number_format($row['debit'], 2) ?></td>
            <td class="text-end"><?= number_format($row['kredit'], 2) ?></td>
            <td class="text-end"><?= number_format($row['saldo'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
