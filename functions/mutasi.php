<?php
// File: functions/mutasi.php
// Mengharapkan $conn (mysqli) sudah tersedia

function get_saldo_terakhir($conn, $sumber_id) {
    $sql = "SELECT saldo FROM mutasi WHERE sumber_id = ? ORDER BY tanggal DESC, id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sumber_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return (float)$row['saldo'];
    }
    // fallback ke saldo_awal terbaru
    $sql2 = "SELECT saldo_awal FROM saldo_awal WHERE sumber_id = ? ORDER BY tahun DESC, bulan DESC, id DESC LIMIT 1";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $sumber_id);
    $stmt2->execute();
    $r2 = $stmt2->get_result();
    if ($row2 = $r2->fetch_assoc()) {
        return (float)$row2['saldo_awal'];
    }
    return 0.0;
}

function catat_mutasi($conn, $sumber_id, $tanggal, $keterangan, $debit, $kredit, $transaksi_id = null, $jenis_transaksi = null) {
    // Mulai transaksi db
    if (method_exists($conn, 'begin_transaction')) {
        $conn->begin_transaction();
    }

    try {
        $saldo_sebelumnya = get_saldo_terakhir($conn, $sumber_id);
        $saldo_baru = $saldo_sebelumnya + floatval($debit) - floatval($kredit);

        $sql = "INSERT INTO mutasi (sumber_id, tanggal, keterangan, debit, kredit, saldo, transaksi_id, jenis_transaksi)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // types: i s s d d d i s  => sumber_id, tanggal, keterangan, debit, kredit, saldo, transaksi_id, jenis_transaksi
        $transaksi_id_param = is_null($transaksi_id) ? null : intval($transaksi_id);
        $jenis_param = is_null($jenis_transaksi) ? null : $jenis_transaksi;
        $stmt->bind_param("issdddis",
            $sumber_id,
            $tanggal,
            $keterangan,
            $debit,
            $kredit,
            $saldo_baru,
            $transaksi_id_param,
            $jenis_param
        );
        $stmt->execute();

        if (method_exists($conn, 'commit')) {
            $conn->commit();
        }
        return true;
    } catch (Exception $e) {
        if (method_exists($conn, 'rollback')) {
            $conn->rollback();
        }
        error_log("catat_mutasi error: " . $e->getMessage());
        return false;
    }
}

// Utility: hapus mutasi berdasarkan transaksi id (dipakai saat delete transaksi)
function hapus_mutasi_by_transaksi($conn, $transaksi_id) {
    $stmt = $conn->prepare("DELETE FROM mutasi WHERE transaksi_id = ?");
    $stmt->bind_param("i", $transaksi_id);
    return $stmt->execute();
}
?>
