<?php
// File: pages/kategori/proses.php
require_once __DIR__ . '/../../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
check_login();

// Tambah Kategori
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_kategori'];
    $tipe = $_POST['tipe'];
    
    $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, tipe) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $tipe);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Kategori berhasil ditambahkan.";
    } else {
        $_SESSION['message'] = "Gagal menambahkan kategori.";
    }
}

// Edit Kategori
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_kategori'];

    $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
    $stmt->bind_param("si", $nama, $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Kategori berhasil diubah.";
    } else {
        $_SESSION['message'] = "Gagal mengubah kategori.";
    }
}

header("Location: ../../kategori.php");
exit();
?>
