<?php
// mitra/settings.php
session_start();
require_once '../db_connect.php';

// Authentication Check
if (!isset($_SESSION['mitra_logged_in']) || !isset($_SESSION['mitra_id'])) {
    header("Location: login.php");
    exit();
}

$mitra_id = $_SESSION['mitra_id'];

// Fetch Partner Profile Data from DB
$stmt = $pdo->prepare("SELECT * FROM mitra_laundry WHERE id = ?");
$stmt->execute([$mitra_id]);
$mitra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mitra) {
    unset($_SESSION['mitra_logged_in']);
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua kolom kata sandi wajib diisi.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Konfirmasi kata sandi baru tidak cocok.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Kata sandi baru harus minimal 6 karakter.';
    } else {
        try {
            // Verify old password
            if (password_verify($old_password, $mitra['password'])) {
                // Hash new password
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update database
                $stmt_update = $pdo->prepare("UPDATE mitra_laundry SET password = ? WHERE id = ?");
                $stmt_update->execute([$new_hash, $mitra_id]);
                
                $success = 'Kata sandi berhasil diperbarui.';
            } else {
                $error = 'Kata sandi lama salah.';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pengaturan Akun | <?= htmlspecialchars($mitra['nama_mitra']); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: "#0058be",
              secondary: "#00a389",
              dark: "#0f172a"
            },
            fontFamily: {
              sans: ["Inter", "sans-serif"]
            }
          }
        }
      }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col">

    <!-- Top Navbar -->
    <nav class="sticky top-0 z-40 bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="p-2 hover:bg-slate-100 rounded-xl transition-colors flex items-center justify-center text-slate-500 hover:text-slate-900">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Portal Mitra</span>
                <h1 class="text-md font-extrabold text-slate-900 leading-tight font-headline">Pengaturan Keamanan Akun</h1>
            </div>
        </div>
        
        <div class="text-xs font-semibold text-slate-400 bg-slate-100 px-3.5 py-2 rounded-xl">
            Outlet: <span class="font-bold text-slate-900"><?= htmlspecialchars($mitra['nama_mitra']); ?></span>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-md w-full mx-auto p-6 flex flex-col justify-center">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-xl p-8 space-y-6">
            
            <div class="text-center space-y-2">
                <div class="inline-flex p-3 bg-blue-50 text-primary rounded-2xl">
                    <span class="material-symbols-outlined text-[32px]">lock_reset</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900">Ubah Kata Sandi</h2>
                <p class="text-xs text-slate-400">Jaga keamanan akun portal mitra Anda dengan mengganti kata sandi secara berkala</p>
            </div>

            <!-- Alerts -->
            <?php if (!empty($success)): ?>
                <div class="p-4 bg-emerald-50 text-emerald-800 rounded-2xl border border-emerald-200 flex items-center gap-3 text-sm animate-pulse">
                    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                    <span class="font-semibold"><?= htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="p-4 bg-rose-50 text-rose-800 rounded-2xl border border-rose-200 flex items-center gap-3 text-sm">
                    <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
                    <span class="font-semibold"><?= htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <!-- Old Password -->
                <div class="space-y-2">
                    <label for="old_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Kata Sandi Lama</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">lock</span>
                        <input type="password" id="old_password" name="old_password" placeholder="Masukkan kata sandi lama" required
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                    </div>
                </div>

                <!-- New Password -->
                <div class="space-y-2">
                    <label for="new_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">lock_open</span>
                        <input type="password" id="new_password" name="new_password" placeholder="Minimal 6 karakter" required
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <label for="confirm_password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">verified_user</span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi kata sandi baru" required
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-2">
                    <a href="dashboard.php" class="w-1/2 text-center border border-slate-200 text-slate-500 hover:bg-slate-50 py-3 rounded-xl font-bold text-sm transition-colors">
                        Kembali
                    </a>
                    <button type="submit" class="w-1/2 bg-primary hover:brightness-110 text-white py-3 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 transition-all">
                        Simpan Sandi
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-4 px-6 text-center text-xs text-slate-400 mt-12">
        © 2026 MataramWash Portal Kemitraan. Pengaturan Keamanan Akun.
    </footer>

</body>
</html>
