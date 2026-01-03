<?php
require_once '../config/database.php';
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id             = $_POST['id'];
    $tanggal        = $_POST['tanggal'];
    $kategori_id    = $_POST['kategori_id'];
    $jumlah         = $_POST['jumlah'];
    $keterangan     = $_POST['keterangan'];
    $sumber_id      = $_POST['sumber_id'];
    $user_id        = $_SESSION['user_id'];

    // Format jumlah (hapus titik, ubah koma jadi titik desimal)
    // 10.000 -> 10000
    // 10.000,50 -> 10000.50
    $jumlah = str_replace('.', '', $jumlah);
    $jumlah = str_replace(',', '.', $jumlah);

    $stmt = $conn->prepare("
        UPDATE transaksi 
        SET tanggal=?, kategori_id=?, jumlah=?, keterangan=?, sumber_dana_id=? 
        WHERE id=? AND user_id=? AND tipe='Pengeluaran'
    ");

    $stmt->bind_param("sidsiii", 
        $tanggal, 
        $kategori_id, 
        $jumlah, 
        $keterangan, 
        $sumber_id, 
        $id, 
        $user_id
    );

    if ($stmt->execute()) {
        echo 'ok';
    } else {
        echo 'err';
    }

}
?>
