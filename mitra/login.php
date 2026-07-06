<?php
// mitra/login.php
session_start();
require_once '../db_connect.php';

// Helper to route partners to their respective custom folders
function get_redirect_url($username) {
    switch ($username) {
        case 'washtra':
            return 'WashTrash/dashboard.php';
        case 'lombok':
            return 'LAUNDRY_LOMBOK/dashboard.php';
        case 'maulaundry':
            return 'MAULaundry/dashboard.php';
        case 'mateshoes':
            return 'MateShoesCare/dashboard.php';
        case 'nekolaundry':
            return 'NekoLaundry/dashboard.php';
        default:
            return 'dashboard.php'; // fallback generic folder
    }
}

// If already logged in, redirect to their folder
if (isset($_SESSION['mitra_logged_in']) && isset($_SESSION['mitra_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT username FROM mitra_laundry WHERE id = ?");
        $stmt->execute([$_SESSION['mitra_id']]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($m) {
            header("Location: " . get_redirect_url($m['username']));
            exit();
        }
    } catch (PDOException $e) {
        // Fallback if query fails
        header("Location: dashboard.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM mitra_laundry WHERE username = ?");
            $stmt->execute([$username]);
            $mitra = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mitra && password_verify($password, $mitra['password'])) {
                $_SESSION['mitra_logged_in'] = true;
                $_SESSION['mitra_id'] = $mitra['id'];
                $_SESSION['mitra_nama'] = $mitra['nama_mitra'];
                
                header("Location: " . get_redirect_url($mitra['username']));
                exit();
            } else {
                $error = 'Username atau password salah.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Portal Kemitraan | MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet">
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
<body class="bg-dark text-slate-200 min-h-screen flex items-center justify-center relative overflow-hidden font-sans">

    <!-- Decorative Glow Blobs -->
    <div class="absolute w-[500px] h-[500px] rounded-full bg-primary/20 blur-[120px] -top-48 -left-48 animate-pulse"></div>
    <div class="absolute w-[500px] h-[500px] rounded-full bg-secondary/15 blur-[120px] -bottom-48 -right-48 animate-pulse" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-md p-6 relative z-10">
        
        <!-- Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center p-3 bg-slate-800/80 backdrop-blur-md rounded-2xl border border-slate-700/60 shadow-xl mb-4">
                <img alt="MataramWash Logo" class="h-12 w-12 object-contain" src="../Logo_MataramWash.png">
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">Portal Mitra Laundry</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola pesanan dan operasional toko Anda</p>
        </div>

        <!-- Card Container -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            
            <!-- Glow effect on form top border -->
            <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent opacity-80"></div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 bg-rose-950/40 border border-rose-800/60 text-rose-300 rounded-xl text-sm flex items-center gap-3 animate-headShake">
                    <span class="material-symbols-outlined text-[20px] shrink-0 text-rose-400">error</span>
                    <span><?= htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <!-- Username Input -->
                <div class="space-y-2">
                    <label for="username" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Username Kemitraan</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-500">storefront</span>
                        <input type="text" id="username" name="username" placeholder="Masukkan username outlet" required
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-950/60 border border-slate-800 focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm text-white placeholder-slate-600 transition-all outline-none">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Kata Sandi</label>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-500">lock</span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                               class="w-full pl-12 pr-4 py-3.5 bg-slate-950/60 border border-slate-800 focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm text-white placeholder-slate-600 transition-all outline-none">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-primary to-blue-600 hover:brightness-110 active:scale-[0.98] text-white py-3.5 px-4 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                    <span>Masuk ke Dashboard</span>
                    <span class="material-symbols-outlined text-[18px]">login</span>
                </button>
            </form>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-8">
            <a href="../index.php" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Kembali ke Beranda MataramWash
            </a>
        </div>

    </div>

</body>
</html>
