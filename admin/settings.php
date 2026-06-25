<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

$success_message = '';
$error_message = '';
$active_tab = 'profile'; // default tab to show

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $active_tab = 'profile';
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');

    if (empty($nama) || empty($username)) {
        $error_message = 'Nama lengkap dan Username tidak boleh kosong.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE admins SET nama = ?, username = ? WHERE id = ?");
            $stmt->execute([$nama, $username, $_SESSION['admin_id']]);
            
            $_SESSION['admin_nama'] = $nama;
            $_SESSION['admin_username'] = $username;
            $success_message = 'Profil administrator berhasil diperbarui!';
        } catch (PDOException $e) {
            $error_message = 'Gagal memperbarui profil: ' . $e->getMessage();
        }
    }
}

// Handle Password Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_security'])) {
    $active_tab = 'security';
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'Semua kolom password wajib diisi.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Konfirmasi password baru tidak cocok.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'Password baru minimal harus terdiri dari 6 karakter.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($old_password, $admin['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $up_stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $up_stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                
                $success_message = 'Kata sandi berhasil diperbarui!';
            } else {
                $error_message = 'Kata sandi lama salah.';
            }
        } catch (PDOException $e) {
            $error_message = 'Gagal memperbarui kata sandi: ' . $e->getMessage();
        }
    }
}

// Handle Configuration Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_config'])) {
    $active_tab = 'config';
    $commission = intval($_POST['platform_commission'] ?? 10);
    $whatsapp = trim($_POST['whatsapp_contact'] ?? '628983887223');
    $region = trim($_POST['region_name'] ?? 'Mataram, Nusa Tenggara Barat');
    $maintenance = isset($_POST['maintenance_mode']) ? 1 : 0;
    $branding = trim($_POST['system_branding'] ?? 'MataramWash');
    $midtrans_env = trim($_POST['midtrans_environment'] ?? 'sandbox');
    $midtrans_client = trim($_POST['midtrans_client_key'] ?? '');
    $midtrans_server = trim($_POST['midtrans_server_key'] ?? '');

    $config_data = [
        'platform_commission' => $commission,
        'whatsapp_contact' => $whatsapp,
        'region_name' => $region,
        'maintenance_mode' => $maintenance,
        'system_branding' => $branding,
        'midtrans_environment' => $midtrans_env,
        'midtrans_client_key' => $midtrans_client,
        'midtrans_server_key' => $midtrans_server
    ];

    if (file_put_contents('settings_config.json', json_encode($config_data, JSON_PRETTY_PRINT))) {
        $success_message = 'Konfigurasi platform berhasil disimpan!';
    } else {
        $error_message = 'Gagal menyimpan konfigurasi sistem.';
    }
}

// Load current configuration
$config_file = 'settings_config.json';
$config = [
    'platform_commission' => 10,
    'whatsapp_contact' => '628983887223',
    'region_name' => 'Mataram, Nusa Tenggara Barat',
    'maintenance_mode' => 0,
    'system_branding' => 'MataramWash',
    'midtrans_environment' => 'sandbox',
    'midtrans_client_key' => '',
    'midtrans_server_key' => ''
];

if (file_exists($config_file)) {
    $loaded_config = json_decode(file_get_contents($config_file), true);
    if ($loaded_config) {
        $config = array_merge($config, $loaded_config);
    }
}

// Fetch logged-in admin data
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin_data = $stmt->fetch();
} catch (PDOException $e) {
    $admin_data = [
        'nama' => $_SESSION['admin_nama'] ?? 'Admin',
        'username' => $_SESSION['admin_username'] ?? 'admin'
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pengaturan | MataramWash Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "primary-fixed": "#d8e2ff",
                    "surface-container-high": "#e2e8f8",
                    "on-tertiary-fixed": "#2a1700",
                    "error-container": "#ffdad6",
                    "primary-fixed-dim": "#adc6ff",
                    "on-surface": "#151c27",
                    "primary": "#3b82f6",
                    "tertiary": "#825100",
                    "on-secondary": "#ffffff",
                    "error": "#ba1a1a",
                    "inverse-primary": "#adc6ff",
                    "background": "#f9f9ff",
                    "on-background": "#151c27",
                    "secondary": "#006b5f",
                    "primary-container": "#2170e4",
                    "on-secondary-fixed": "#00201c",
                    "surface-variant": "#dce2f3",
                    "on-tertiary": "#ffffff",
                    "on-error-container": "#93000a",
                    "on-surface-variant": "#424754",
                    "on-primary-fixed": "#001a42",
                    "tertiary-fixed-dim": "#ffb95f",
                    "surface-container-highest": "#dce2f3",
                    "on-secondary-container": "#006f64",
                    "tertiary-fixed": "#ffddb8",
                    "surface-bright": "#f9f9ff",
                    "surface-container": "#e7eefe",
                    "secondary-container": "#6df5e1",
                    "on-primary-fixed-variant": "#004395",
                    "tertiary-container": "#a36700",
                    "on-primary": "#ffffff",
                    "on-secondary-fixed-variant": "#005048",
                    "on-tertiary-fixed-variant": "#653e00",
                    "inverse-surface": "#2a313d",
                    "outline-variant": "#c2c6d6",
                    "outline": "#727785",
                    "on-primary-container": "#fefcff",
                    "secondary-fixed-dim": "#4fdbc8",
                    "secondary-fixed": "#71f8e4",
                    "surface-container-low": "#f0f3ff",
                    "inverse-on-surface": "#ebf1ff",
                    "surface-tint": "#005ac2",
                    "surface-container-lowest": "#ffffff",
                    "surface": "#f9f9ff",
                    "on-tertiary-container": "#fffbff",
                    "on-error": "#ffffff",
                    "surface-dim": "#d3daea"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "container-margin": "20px",
                    "xl": "32px",
                    "sm": "12px",
                    "gutter": "16px",
                    "base": "4px",
                    "md": "16px",
                    "lg": "24px",
                    "xs": "8px"
            }
          }
        }
      }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .bento-card {
            background-color: #ffffff;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex overflow-hidden">
    <!-- SideNavBar -->
    <!-- SideNavBar -->
<aside class="hidden lg:flex flex-col h-screen sticky top-0 p-md space-y-md bg-slate-900 border-r border-slate-800 w-64 shrink-0 text-slate-300">
<div class="flex items-center gap-xs px-xs py-sm border-b border-slate-800">
<img alt="MataramWash Logo" class="h-8 w-8 object-contain brightness-110 filter" src="../logo.png?v=3">
<span class="text-headline-sm font-headline-md font-extrabold text-white">MataramWash</span>
</div>
<div class="flex flex-col gap-xs py-md border-b border-slate-800">
<p class="px-md text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-xs">Main Menu</p>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="dashboard_admin.php">
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">dashboard</span>
<span class="text-label-md font-label-md">Dashboard</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="manajemen_mitra.php">
<span class="material-symbols-outlined text-[20px]">group</span>
<span class="text-label-md font-label-md">Manajemen Mitra</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="operational_area.php">
<span class="material-symbols-outlined text-[20px]">map</span>
<span class="text-label-md font-label-md">Wilayah Operasional</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="analitik_kemitraan.php">
<span class="material-symbols-outlined text-[20px]">analytics</span>
<span class="text-label-md font-label-md">Analitik Kemitraan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="financial_statements.php">
<span class="material-symbols-outlined text-[20px]">payments</span>
<span class="text-label-md font-label-md">Laporan Keuangan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm bg-blue-600 text-white rounded-xl font-bold border-l-4 border-blue-400 shadow-lg shadow-blue-900/30 transition-all duration-200" href="settings.php">
<span class="material-symbols-outlined text-[20px]">settings</span>
<span class="text-label-md font-label-md">Settings</span>
</a>
</div>
<div class="flex-grow"></div>
<div class="flex flex-col gap-xs py-md">
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="../bantuan/bantuan.php">
<span class="material-symbols-outlined text-[20px]">help</span>
<span class="text-label-md font-label-md">Help Center</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl transition-all duration-200" href="logout_admin.php">
<span class="material-symbols-outlined text-[20px] text-rose-400">logout</span>
<span class="text-label-md font-label-md text-rose-400 font-bold">Logout</span>
</a>
</div>
</aside>

    <!-- Main Content Canvas -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden">
        <!-- TopAppBar -->
        <header class="sticky top-0 w-full z-40 flex justify-between items-center px-lg py-md bg-white border-b border-slate-100 max-w-none">
            <div class="flex items-center gap-md flex-1">
                <div class="lg:hidden">
                    <span class="material-symbols-outlined text-on-surface-variant">menu</span>
                </div>
                <div class="text-headline-sm font-bold text-on-surface">Pengaturan</div>
            </div>
            <div class="flex items-center gap-md">
                <div class="flex items-center gap-sm">
                    <p class="text-label-md font-bold leading-none hidden sm:block"><?= htmlspecialchars($admin_data['nama']); ?></p>
                    <img alt="Admin profile" class="w-9 h-9 rounded-full object-cover border-2 border-slate-100 shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVvTfpl6gmSbn7utdVTjVT1ZrHaIbCt76OBU9jA9oc3rue19H1ElhbliNLU8FUVfCMZWMCOXO6ZI0EBlE68GvL7TdpDcdz05FrUqtzRUVrrTQKcC_MwtAKGFkV_XAbFOxIpl3JRF93_22IuQMGYGKqzXSHUZRnab8I7P_AWzrPQKLrh9PmQd4pqpbRW8v-5sKU_uUJt1jpvrX5bWXDDQshtNQtM9DcfB5GsKwZW-zFy6P6DnFBWUY_oCDubbBHW4BXb1p5RWiXyyg">
                </div>
            </div>
        </header>

        <!-- Scrollable Area -->
        <div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
            <div class="max-w-4xl mx-auto space-y-lg">

                <!-- Alert Messages -->
                <?php if ($success_message): ?>
                    <div id="alert-success" class="p-md bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-center gap-md shadow-sm transition-all duration-300">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <div class="text-body-sm font-medium"><?= htmlspecialchars($success_message); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div id="alert-error" class="p-md bg-red-50 text-red-800 rounded-xl border border-red-200 flex items-center gap-md shadow-sm transition-all duration-300">
                        <span class="material-symbols-outlined text-red-600">error</span>
                        <div class="text-body-sm font-medium"><?= htmlspecialchars($error_message); ?></div>
                    </div>
                <?php endif; ?>

                <!-- Header Title -->
                <div>
                    <h1 class="text-headline-lg font-headline-lg text-on-surface">Pusat Pengaturan</h1>
                    <p class="text-body-md text-on-surface-variant">Kelola identitas administrator, keamanan autentikasi, dan konfigurasi default platform.</p>
                </div>

                <!-- Main Bento Box -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-lg">
                    
                    <!-- Left Column Tabs -->
                    <div class="md:col-span-1 flex flex-col gap-xs">
                        <button onclick="switchTab('profile')" id="btn-profile" class="flex items-center gap-sm px-md py-sm rounded-xl font-bold transition-all text-left w-full justify-start select-none">
                            <span class="material-symbols-outlined">account_circle</span>
                            <span class="text-label-md">Profil Akun</span>
                        </button>
                        <button onclick="switchTab('security')" id="btn-security" class="flex items-center gap-sm px-md py-sm rounded-xl font-bold transition-all text-left w-full justify-start select-none">
                            <span class="material-symbols-outlined">security</span>
                            <span class="text-label-md">Keamanan & Sandi</span>
                        </button>
                        <button onclick="switchTab('config')" id="btn-config" class="flex items-center gap-sm px-md py-sm rounded-xl font-bold transition-all text-left w-full justify-start select-none">
                            <span class="material-symbols-outlined">settings_suggest</span>
                            <span class="text-label-md">Konfigurasi Sistem</span>
                        </button>
                    </div>

                    <!-- Right Column Panel Details -->
                    <div class="md:col-span-3 bento-card rounded-2xl p-lg relative overflow-hidden">
                        
                        <!-- TAB 1: PROFIL AKUN -->
                        <div id="tab-content-profile" class="hidden space-y-lg transition-opacity duration-300">
                            <div>
                                <h3 class="text-title-lg font-bold text-on-surface">Profil Administrator</h3>
                                <p class="text-body-sm text-on-surface-variant">Perbarui nama tampilan dan data identitas login Anda.</p>
                            </div>
                            <form action="" method="POST" class="space-y-md">
                                <input type="hidden" name="update_profile" value="1">
                                <div>
                                    <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="nama">Nama Lengkap</label>
                                    <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($admin_data['nama']); ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                </div>
                                <div>
                                    <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="username">Username</label>
                                    <input type="text" id="username" name="username" required value="<?= htmlspecialchars($admin_data['username']); ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                </div>
                                <div class="pt-sm flex justify-end">
                                    <button type="submit" class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all">
                                        <span class="material-symbols-outlined">save</span>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 2: KEAMANAN & SANDI -->
                        <div id="tab-content-security" class="hidden space-y-lg transition-opacity duration-300">
                            <div>
                                <h3 class="text-title-lg font-bold text-on-surface">Ubah Kata Sandi</h3>
                                <p class="text-body-sm text-on-surface-variant">Ganti kata sandi secara berkala untuk menjaga keamanan akun administrasi.</p>
                            </div>
                            <form action="" method="POST" class="space-y-md">
                                <input type="hidden" name="update_security" value="1">
                                <div>
                                    <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="old_password">Kata Sandi Lama</label>
                                    <input type="password" id="old_password" name="old_password" required class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                </div>
                                <div>
                                    <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="new_password">Kata Sandi Baru</label>
                                    <input type="password" id="new_password" name="new_password" required placeholder="Minimal 6 karakter" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                </div>
                                <div>
                                    <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                </div>
                                <div class="pt-sm flex justify-end">
                                    <button type="submit" class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all">
                                        <span class="material-symbols-outlined">vpn_key</span>
                                        Perbarui Kata Sandi
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 3: KONFIGURASI SISTEM -->
                        <div id="tab-content-config" class="hidden space-y-lg transition-opacity duration-300">
                            <div>
                                <h3 class="text-title-lg font-bold text-on-surface">Parameter Konfigurasi Sistem</h3>
                                <p class="text-body-sm text-on-surface-variant">Atur variabel default yang mempengaruhi sistem frontend dan perhitungan platform.</p>
                            </div>
                            <form action="" method="POST" class="space-y-md">
                                <input type="hidden" name="update_config" value="1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                                    <div>
                                        <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="platform_commission">Komisi Platform (%)</label>
                                        <input type="number" id="platform_commission" name="platform_commission" min="1" max="100" required value="<?= htmlspecialchars($config['platform_commission']); ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="system_branding">Branding Sistem</label>
                                        <input type="text" id="system_branding" name="system_branding" required value="<?= htmlspecialchars($config['system_branding']); ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="whatsapp_contact">Kontak WhatsApp Pusat Bantuan</label>
                                    <input type="text" id="whatsapp_contact" name="whatsapp_contact" required value="<?= htmlspecialchars($config['whatsapp_contact']); ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                </div>
                                <div>
                                    <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="region_name">Wilayah Operasional Utama</label>
                                    <input type="text" id="region_name" name="region_name" required value="<?= htmlspecialchars($config['region_name']); ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                </div>
                                <div class="border-t border-outline-variant pt-md mt-md space-y-md">
                                    <h4 class="text-title-sm font-bold text-primary flex items-center gap-xs">
                                        <span class="material-symbols-outlined">payments</span>
                                        <span>Pengaturan Midtrans Payment Gateway</span>
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-md">
                                        <div class="sm:col-span-1">
                                            <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="midtrans_environment">Environment *</label>
                                            <select id="midtrans_environment" name="midtrans_environment" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer">
                                                <option value="sandbox" <?= ($config['midtrans_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option>
                                                <option value="production" <?= ($config['midtrans_environment'] ?? 'sandbox') === 'production' ? 'selected' : ''; ?>>Production (Live/Asli)</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="midtrans_client_key">Midtrans Client Key</label>
                                            <input type="text" id="midtrans_client_key" name="midtrans_client_key" value="<?= htmlspecialchars($config['midtrans_client_key'] ?? ''); ?>" placeholder="Contoh: SB-Mid-client-..." class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-label-md font-bold mb-xs text-on-surface-variant" for="midtrans_server_key">Midtrans Server Key</label>
                                        <input type="text" id="midtrans_server_key" name="midtrans_server_key" value="<?= htmlspecialchars($config['midtrans_server_key'] ?? ''); ?>" placeholder="Contoh: SB-Mid-server-..." class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-md py-sm text-body-md focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-sm bg-slate-50 border border-slate-200 rounded-xl mt-md">
                                    <div>
                                        <p class="text-body-sm font-bold text-on-surface leading-tight">Maintenance Mode (Mode Pemeliharaan)</p>
                                        <p class="text-[11px] text-outline">Menonaktifkan akses platform frontend sementara untuk pemeliharaan sistem.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="maintenance_mode" class="sr-only peer" <?= $config['maintenance_mode'] ? 'checked' : ''; ?>>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>
                                <div class="pt-sm flex justify-end">
                                    <button type="submit" class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all">
                                        <span class="material-symbols-outlined">settings</span>
                                        Simpan Konfigurasi
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <!-- Footer -->
<footer class="w-full py-md px-lg bg-slate-50 border-t border-slate-100 flex justify-between items-center text-slate-400">
<p class="text-label-sm">© 2024 MataramWash Provincial Partnership Program. Freshness across the region.</p>
<div class="flex gap-lg">
<a class="text-label-sm hover:text-blue-600 transition-colors" href="../bantuan/bantuan.php">Pusat Bantuan</a>
<a class="text-label-sm hover:text-blue-600 transition-colors" href="#">Kebijakan Kemitraan</a>
</div>
</footer>
    </main>

    <script>
        function switchTab(tabId) {
            // Hide all contents
            document.getElementById('tab-content-profile').classList.add('hidden');
            document.getElementById('tab-content-security').classList.add('hidden');
            document.getElementById('tab-content-config').classList.add('hidden');
            
            // Remove active styles from buttons
            const activeBtnClass = ['bg-primary-container', 'text-on-primary-container'];
            const inactiveBtnClass = ['text-on-surface-variant', 'hover:bg-surface-container-high'];
            
            ['profile', 'security', 'config'].forEach(t => {
                const btn = document.getElementById('btn-' + t);
                btn.classList.remove(...activeBtnClass);
                btn.classList.add(...inactiveBtnClass);
            });
            
            // Show current tab content & set active button
            const activeContent = document.getElementById('tab-content-' + tabId);
            activeContent.classList.remove('hidden');
            activeContent.style.opacity = 0;
            setTimeout(() => activeContent.style.opacity = 1, 50);
            
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.remove(...inactiveBtnClass);
            activeBtn.classList.add(...activeBtnClass);
        }

        // Initialize active tab from PHP
        document.addEventListener('DOMContentLoaded', () => {
            switchTab('<?= $active_tab; ?>');
        });

        // Hide success alerts after 4 seconds
        const alertSuccess = document.getElementById('alert-success');
        if (alertSuccess) {
            setTimeout(() => {
                alertSuccess.style.opacity = '0';
                setTimeout(() => alertSuccess.remove(), 300);
            }, 4000);
        }
        const alertError = document.getElementById('alert-error');
        if (alertError) {
            setTimeout(() => {
                alertError.style.opacity = '0';
                setTimeout(() => alertError.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>
