<?php
// File: pages/kategori/hapus.php
require_once __DIR__ . '/../../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
check_login();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Cek apakah kategori digunakan di tabel transaksi
    $check_stmt = $conn->prepare("SELECT id FROM transaksi WHERE kategori_id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $_SESSION['message'] = "Gagal menghapus: Kategori sedang digunakan dalam transaksi.";
    } else {
        // Jika tidak digunakan, hapus kategori
        $delete_stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        if ($delete_stmt->execute()) {
            $_SESSION['message'] = "Kategori berhasil dihapus.";
        } else {
            $_SESSION['message'] = "Gagal menghapus kategori.";
        }
        $delete_stmt->close();
    }
    $check_stmt->close();
}

header("Location: ../../kategori.php");
exit();
?>
