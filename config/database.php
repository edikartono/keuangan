<?php
// File: config/database.php

// Mulai session di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan dengan password database Anda, kosongkan jika tidak ada
define('DB_NAME', 'dbkeuanganaf');

// Menentukan base path aplikasi secara dinamis
$base_path = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
define('BASE_PATH', $base_path);

// Buat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Fungsi helper untuk format Rupiah
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi untuk mengecek apakah user sudah login
function check_login() {
    // Jika session user_id tidak ada, redirect ke halaman login
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_PATH . "auth/login.php");
        exit();
    }
}
?>
