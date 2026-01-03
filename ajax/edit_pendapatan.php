<?php
require_once '../config/database.php';
session_start();

$id = $_POST['id'];
$user_id = $_SESSION['user_id'];

$tanggal = $_POST['tanggal'];
$sumber  = $_POST['sumber_id'];
$kategori = $_POST['kategori_id'];
$jumlah = $_POST['jumlah'];
$keterangan = $_POST['keterangan'];

$stmt = $conn->prepare("
    UPDATE transaksi
    SET tanggal=?, sumber_dana_id=?, kategori_id=?, jumlah=?, keterangan=?
    WHERE id=? AND user_id=? AND tipe='Pemasukan'
");

$stmt->bind_param(
    "siidsii",
    $tanggal,
    $sumber,
    $kategori,
    $jumlah,
    $keterangan,
    $id,
    $user_id
);

echo $stmt->execute() ? 'ok' : 'fail';
