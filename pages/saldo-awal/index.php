<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sumber_id = $_POST['sumber_id'];
    $saldo = $_POST['saldo'];
    $tahun = $_POST['tahun'];
    $bulan = $_POST['bulan'];

    $stmt = $conn->prepare("
        INSERT INTO saldo_awal (sumber_id, saldo_awal, tahun, bulan, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("idis", $sumber_id, $saldo, $tahun, $bulan);
    $stmt->execute();
}
?>
