<?php
// File: pages/user/proses.php
// Pastikan file ini disimpan di dalam folder: laporan-keuangan/pages/user/

require_once __DIR__ . '/../../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
check_login();

$user_id = $_SESSION['user_id'];
$redirect_page = $_SERVER['HTTP_REFERER'] ?? '/laporan-keuangan/index.php';

// --- GANTI PASSWORD ---
if (isset($_POST['ganti_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Password baru dan konfirmasi tidak cocok.'];
    } else {
        // 1. Ambil password saat ini dari DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // 2. Verifikasi password lama
        if ($result && password_verify($old_password, $result['password'])) {
            // 3. Hash password baru dan update
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $new_hashed_password, $user_id);
            
            if ($stmt_update->execute()) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Password berhasil diubah.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal mengubah password.'];
            }
            $stmt_update->close();
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Password lama Anda salah.'];
        }
    }
}

header("Location: " . $redirect_page);
exit();
?>
