<?php

require_once '../config/database.php';

$nama = trim($_POST['nama_kategori']);

if ($nama === '') {
    echo json_encode(['status' => 'empty']);
    exit;
}

$tipe = $_POST['tipe'] ?? 'Pengeluaran'; // Default ke Pengeluaran jika tidak ada
$tipe = ($tipe === 'Pemasukan') ? 'Pemasukan' : 'Pengeluaran'; // Validasi sederhana

// 🔍 Cek duplikasi (case-insensitive)
$cek = $conn->prepare("
    SELECT id 
    FROM kategori 
    WHERE LOWER(nama_kategori) = LOWER(?) 
      AND tipe = ?
    LIMIT 1
");
$cek->bind_param("ss", $nama, $tipe);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    echo json_encode(['status' => 'exists']);
    exit;
}

// ✅ Simpan jika belum ada
$stmt = $conn->prepare("
    INSERT INTO kategori (nama_kategori, tipe)
    VALUES (?, ?)
");
$stmt->bind_param("ss", $nama, $tipe);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'id'     => $stmt->insert_id,
        'nama'   => $nama
    ]);
} else {
    echo json_encode(['status' => 'error']);
}