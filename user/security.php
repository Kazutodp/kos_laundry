<?php
session_start();
require_once '../db_connect.php';

// Cek apakah user sudah login, jika belum arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua kolom kata sandi wajib diisi.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Konfirmasi kata sandi baru tidak cocok.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Kata sandi baru minimal harus 6 karakter.';
    } else {
        try {
            // Ambil data user dari database
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            // Verifikasi kata sandi saat ini
            if ($user && ($user['password'] === null || password_verify($current_password, $user['password']))) {
                // Hash kata sandi baru dan simpan ke database
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->execute([$new_hashed, $user_id]);
                
                $success = 'Kata sandi berhasil diperbarui!';
            } else {
                $error = 'Kata sandi saat ini salah.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Password &amp; Keamanan | MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "tertiary-fixed": "#ffddb8",
                    "error": "#ba1a1a",
                    "surface-bright": "#f9f9ff",
                    "primary-container": "#2170e4",
                    "on-primary-container": "#fefcff",
                    "on-secondary-fixed-variant": "#005048",
                    "on-primary-fixed": "#001a42",
                    "on-error": "#ffffff",
                    "on-secondary-container": "#006f64",
                    "secondary-fixed": "#71f8e4",
                    "primary-fixed-dim": "#adc6ff",
                    "tertiary-fixed-dim": "#ffb95f",
                    "surface": "#f9f9ff",
                    "on-primary": "#ffffff",
                    "background": "#f9f9ff",
                    "tertiary": "#825100",
                    "on-error-container": "#93000a",
                    "surface-dim": "#d3daea",
                    "secondary-fixed-dim": "#4fdbc8",
                    "on-tertiary-fixed": "#2a1700",
                    "error-container": "#ffdad6",
                    "surface-variant": "#dce2f3",
                    "surface-container-highest": "#dce2f3",
                    "primary": "#0058be",
                    "surface-container-lowest": "#ffffff",
                    "outline": "#727785",
                    "on-secondary-fixed": "#00201c",
                    "on-secondary": "#ffffff",
                    "on-background": "#151c27",
                    "secondary": "#006b5f",
                    "on-tertiary": "#ffffff",
                    "surface-container-low": "#f0f3ff",
                    "primary-fixed": "#d8e2ff",
                    "outline-variant": "#c2c6d6",
                    "surface-container-high": "#e2e8f8",
                    "on-tertiary-fixed-variant": "#653e00",
                    "on-tertiary-container": "#fffbff",
                    "on-surface-variant": "#424754",
                    "inverse-primary": "#adc6ff",
                    "inverse-on-surface": "#ebf1ff",
                    "on-surface": "#151c27",
                    "inverse-surface": "#2a313d",
                    "tertiary-container": "#a36700",
                    "surface-container": "#e7eefe",
                    "secondary-container": "#6df5e1",
                    "on-primary-fixed-variant": "#004395",
                    "surface-tint": "#005ac2"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "md": "16px",
                    "xs": "8px",
                    "gutter": "16px",
                    "container-margin": "20px",
                    "base": "4px",
                    "sm": "12px",
                    "xl": "32px",
                    "lg": "24px"
            },
            "fontFamily": {
                    "display-lg": ["Inter"],
                    "body-md": ["Inter"],
                    "label-md": ["Inter"],
                    "headline-lg": ["Inter"],
                    "body-lg": ["Inter"],
                    "headline-md": ["Inter"],
                    "label-sm": ["Inter"],
                    "headline-lg-mobile": ["Inter"]
            },
            "fontSize": {
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}]
            }
          },
        },
      }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9ff;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-shadow {
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
        }
        .transition-standard {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="text-on-background bg-background">

<!-- Mini Header for Brand -->
<header class="w-full bg-surface-container shadow-sm py-4 px-6 border-b border-outline-variant/30 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <a class="flex items-center space-x-xs text-headline-md font-headline-md font-bold text-primary" href="../index.php">
            <img alt="MataramWash Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
            <span class="text-lg">MataramWash</span>
        </a>
        <a class="flex items-center space-x-1 text-label-md font-bold text-primary hover:underline" href="../index.php">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-12 pt-10">
<div class="flex flex-col lg:flex-row gap-8">
<!-- Sidebar Navigation -->
<aside class="w-full lg:w-72 flex-shrink-0">
<div class="bg-surface-container-lowest rounded-xl p-4 custom-shadow border border-outline-variant/30 sticky top-28">
<div class="flex flex-col space-y-1">
<a class="flex items-center space-x-3 px-4 py-3 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-standard" href="edit_profile.php">
<span class="material-symbols-outlined">person</span>
<span class="text-label-md">Personal Info</span>
</a>
<!-- Active Item: Password & Keamanan -->
<a class="flex items-center space-x-3 px-4 py-3 bg-primary-container text-on-primary-container rounded-lg font-bold transition-standard" href="security.php">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">lock</span>
<span class="text-label-md">Password &amp; Keamanan</span>
</a>
</div>
</div>
</aside>
<!-- Main Content Area -->
<section class="flex-grow">
<div class="bg-surface-container-lowest rounded-xl custom-shadow border border-outline-variant/30 overflow-hidden">
<!-- Header Form -->
<div class="p-8 border-b border-outline-variant/20">
<h1 class="text-headline-md font-headline-md text-on-surface mb-2">Password &amp; Keamanan</h1>
<p class="text-body-md font-body-md text-on-surface-variant">Kelola kata sandi dan pengaturan keamanan akun Anda.</p>
</div>
<div class="p-8 space-y-10">

<?php if (!empty($error)): ?>
    <div class="p-md bg-error-container text-on-error-container rounded-xl flex items-center gap-xs font-label-sm border border-error">
        <span class="material-symbols-outlined text-error">error</span>
        <span><?= htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="p-md bg-secondary-container text-on-secondary-container rounded-xl flex items-center gap-xs font-label-sm border border-secondary">
        <span class="material-symbols-outlined text-secondary">check_circle</span>
        <span><?= htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<!-- Form Action and Inputs Wrap -->
<form action="security.php" method="POST" class="space-y-10">
<!-- Section 1: Change Password -->
<div class="space-y-8">
<div class="flex items-center space-x-2">
<span class="material-symbols-outlined text-primary">lock_reset</span>
<h2 class="text-headline-sm font-headline-md text-on-surface">Ubah Kata Sandi</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
<div class="space-y-6">
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant" for="current-password">Kata Sandi Saat Ini</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">vpn_key</span>
<input class="w-full pl-10 pr-12 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" id="current-password" name="current_password" required placeholder="••••••••" type="password">
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors" onclick="togglePassword('current-password')" type="button">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant" for="new-password">Kata Sandi Baru</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">lock</span>
<input class="w-full pl-10 pr-12 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" id="new-password" name="new_password" required placeholder="••••••••" type="password">
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors" onclick="togglePassword('new-password')" type="button">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
<!-- Password Strength Meter Container -->
<div id="password-strength-container" class="hidden space-y-1.5 mt-2">
  <div class="flex justify-between items-center text-xs">
    <span class="font-medium text-on-surface-variant">Kekuatan Kata Sandi:</span>
    <span id="password-strength-text" class="font-bold text-error">Lemah 🔴</span>
  </div>
  <div class="h-1.5 w-full bg-surface-container-high rounded-full overflow-hidden">
    <div id="password-strength-bar" class="h-full w-0 bg-error transition-all duration-300"></div>
  </div>
</div>
<p class="text-label-sm text-on-surface-variant opacity-70">Gunakan minimal 6 karakter dengan kombinasi angka dan simbol.</p>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant" for="confirm-password">Konfirmasi Kata Sandi Baru</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">verified</span>
<input class="w-full pl-10 pr-12 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" id="confirm-password" name="confirm_password" required placeholder="••••••••" type="password">
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors" onclick="togglePassword('confirm-password')" type="button">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</div>
</div>
<div class="hidden md:flex flex-col items-center justify-center bg-surface-container-low rounded-2xl p-8 text-center border border-outline-variant/30">
<div class="relative w-24 h-24 mb-4">
<div class="absolute inset-0 bg-primary opacity-10 rounded-full animate-pulse"></div>
<div class="absolute inset-0 flex items-center justify-center">
<span class="material-symbols-outlined text-[48px] text-primary">security</span>
</div>
</div>
<h3 class="text-body-md font-bold text-on-surface">Jaga Keamanan Akun</h3>
<p class="text-label-sm text-on-surface-variant mt-2 leading-relaxed">Memperbarui kata sandi secara berkala membantu melindungi data transaksi dan informasi pribadi Anda.</p>
</div>
</div>
</div>
<!-- Form Actions -->
<div class="flex flex-col sm:flex-row justify-end items-center space-y-4 sm:space-y-0 sm:space-x-4 pt-10 border-t border-outline-variant/20">
<button onclick="window.location.href='../index.php'" class="w-full sm:w-auto px-8 py-3 rounded-xl text-label-md font-bold text-on-surface-variant hover:bg-surface-container-high transition-standard" type="button">
                                Batal
                            </button>
<button class="w-full sm:w-auto px-10 py-3 rounded-xl bg-primary text-white text-label-md font-bold custom-shadow hover:scale-[1.02] active:scale-95 transition-standard flex items-center justify-center gap-2" type="submit">
<span class="material-symbols-outlined text-[20px]">save</span>
                                Simpan Perubahan
                            </button>
</div>
</form>

<!-- Danger Zone Section -->
<div class="pt-10 border-t border-outline-variant/30">
    <div class="bg-error-container/10 border border-error/20 rounded-2xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="space-y-2">
            <h3 class="text-body-md font-bold text-on-surface">Hapus Akun Anda</h3>
            <p class="text-label-sm text-on-surface-variant max-w-xl leading-relaxed">Setelah Anda menghapus akun, semua data profil, transaksi, dan foto profil Anda akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <button onclick="openDeleteModal()" class="w-full md:w-auto px-6 py-3 rounded-xl bg-error text-white text-label-md font-bold custom-shadow hover:bg-red-700 hover:scale-[1.02] active:scale-95 transition-standard flex items-center justify-center gap-2" type="button">
            <span class="material-symbols-outlined text-[20px]">delete_forever</span>
            <span>Hapus Akun Saya</span>
        </button>
    </div>
</div>

</div>
</div>
</section>
</div>
</main>

<!-- Modal Konfirmasi Hapus Akun (Option B: Countdown Timer) -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm hidden animate-fade-in">
    <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl space-y-6">
        <div class="flex items-center space-x-3 text-error border-b border-outline-variant/20 pb-3">
            <span class="material-symbols-outlined text-3xl">warning</span>
            <h3 class="text-headline-sm font-bold text-on-surface">Hapus Akun Permanen?</h3>
        </div>
        
        <div class="space-y-4">
            <p class="text-body-md text-on-surface-variant leading-relaxed">
                Tindakan ini akan menghapus akun **MataramWash** Anda secara permanen. Semua data pesanan, alamat, dan foto profil Anda akan dihapus dan tidak bisa dikembalikan.
            </p>
            
            <div class="bg-error-container text-on-error-container p-4 rounded-xl flex gap-3 border border-error/20">
                <span class="material-symbols-outlined text-error flex-shrink-0">info</span>
                <p class="text-label-sm leading-relaxed">
                    Sesi Anda akan segera diakhiri setelah proses penghapusan berhasil diselesaikan di server kami.
                </p>
            </div>
        </div>

        <form action="delete_account.php" method="POST" id="delete-account-form">
            <div class="flex justify-end items-center space-x-3 pt-4 border-t border-outline-variant/20">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-xl text-label-md font-bold text-on-surface-variant hover:bg-surface-container transition-colors" type="button">
                    Batal
                </button>
                <button id="confirm-delete-btn" disabled class="px-6 py-2.5 rounded-xl bg-error/50 text-white text-label-md font-bold cursor-not-allowed transition-all duration-300 flex items-center gap-2" type="submit">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                    <span id="confirm-delete-text">Ya, Hapus Akun (3)</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('.material-symbols-outlined');
            if (input.type === "password") {
                input.type = "text";
                icon.innerText = "visibility_off";
            } else {
                input.type = "password";
                icon.innerText = "visibility";
            }
        }

        // Visual feedback for focus
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.querySelector('.material-symbols-outlined')?.classList.add('text-primary');
            });
            input.addEventListener('blur', () => {
                input.parentElement.querySelector('.material-symbols-outlined')?.classList.remove('text-primary');
            });
        });

        // Password Strength Meter
        const newPasswordInput = document.getElementById('new-password');
        const strengthContainer = document.getElementById('password-strength-container');
        const strengthText = document.getElementById('password-strength-text');
        const strengthBar = document.getElementById('password-strength-bar');

        newPasswordInput.addEventListener('input', () => {
            const val = newPasswordInput.value;
            if (val.length === 0) {
                strengthContainer.classList.add('hidden');
                return;
            }
            
            strengthContainer.classList.remove('hidden');
            let score = 0;
            
            // Length evaluation
            if (val.length >= 10) {
                score += 3;
            } else if (val.length >= 6) {
                score += 2;
            } else {
                score += 1;
            }
            
            // Character types check
            if (/[a-z]/.test(val)) score += 1;
            if (/[A-Z]/.test(val)) score += 1;
            if (/[0-9]/.test(val)) score += 1;
            if (/[^A-Za-z0-9]/.test(val)) score += 1;
            
            // Determine strength based on length check first
            if (val.length < 6) {
                strengthText.innerText = "Lemah 🔴";
                strengthText.className = "font-bold text-error text-xs";
                strengthBar.className = "h-full bg-error transition-all duration-300";
                strengthBar.style.width = "25%";
            } else {
                // Safe length, check score
                if (score <= 4) {
                    strengthText.innerText = "Lemah 🔴";
                    strengthText.className = "font-bold text-error text-xs";
                    strengthBar.className = "h-full bg-error transition-all duration-300";
                    strengthBar.style.width = "35%";
                } else if (score <= 5) {
                    strengthText.innerText = "Sedang 🟡";
                    strengthText.className = "font-bold text-amber-500 text-xs";
                    strengthBar.className = "h-full bg-amber-500 transition-all duration-300";
                    strengthBar.style.width = "65%";
                } else {
                    strengthText.innerText = "Kuat 🟢";
                    strengthText.className = "font-bold text-secondary text-xs";
                    strengthBar.className = "h-full bg-secondary transition-all duration-300";
                    strengthBar.style.width = "100%";
                }
            }
        });

        // Delete Account Modal Control (Option B: Countdown Timer)
        let countdownInterval;

        function openDeleteModal() {
            const modal = document.getElementById('delete-modal');
            const confirmBtn = document.getElementById('confirm-delete-btn');
            const confirmText = document.getElementById('confirm-delete-text');
            
            modal.classList.remove('hidden');
            
            // Reset countdown
            let secondsLeft = 3;
            confirmBtn.disabled = true;
            confirmBtn.className = "px-6 py-2.5 rounded-xl bg-error/50 text-white text-label-md font-bold cursor-not-allowed transition-all duration-300 flex items-center gap-2";
            confirmText.innerText = `Ya, Hapus Akun (${secondsLeft})`;
            
            // Clear any existing interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            countdownInterval = setInterval(() => {
                secondsLeft--;
                if (secondsLeft > 0) {
                    confirmText.innerText = `Ya, Hapus Akun (${secondsLeft})`;
                } else {
                    clearInterval(countdownInterval);
                    confirmText.innerText = "Ya, Hapus Akun Permanen";
                    confirmBtn.disabled = false;
                    confirmBtn.className = "px-6 py-2.5 rounded-xl bg-error text-white text-label-md font-bold hover:bg-red-700 hover:scale-[1.02] active:scale-95 transition-all duration-300 flex items-center gap-2 cursor-pointer shadow-md";
                }
            }, 1000);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('delete-modal');
            modal.classList.add('hidden');
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
        }

        // Close delete modal when clicking outside of it
        window.addEventListener('click', (event) => {
            const modal = document.getElementById('delete-modal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        });

        // Close delete modal on ESC key
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
