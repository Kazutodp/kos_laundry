<?php
// Mulai session
session_start();

// Hubungkan ke database
require_once 'koneksi.php';

// Proteksi halaman: periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.html?error=" . urlencode("Silakan masuk terlebih dahulu untuk mengakses dashboard."));
    exit();
}

$user_id = mysqli_real_escape_string($koneksi, $_SESSION['user_id']);
$query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    // Jika user id tidak ada di DB, hancurkan sesi
    session_destroy();
    header("Location: auth/login.html?error=" . urlencode("Sesi Anda tidak valid."));
    exit();
}

$user = mysqli_fetch_assoc($result);

// Menghitung inisial nama untuk avatar
$nama = htmlspecialchars($user['nama']);
$email = htmlspecialchars($user['email']);
$inisial = strtoupper(substr($nama, 0, 1));
$login_via_google = !empty($user['google_id']);
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Dashboard - FreshLaundry</title>
    <!-- Google Identity Services SDK jika ingin hubungkan Google -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "tertiary-fixed-dim": "#ffb596",
                        "surface-container-high": "#dce9ff",
                        "on-tertiary-fixed-variant": "#7d2d00",
                        "on-primary-fixed": "#00174b",
                        "tertiary-container": "#bc4800",
                        "surface-container-lowest": "#ffffff",
                        "surface": "#f8f9ff",
                        "on-tertiary-container": "#ffede6",
                        "tertiary": "#943700",
                        "outline-variant": "#c3c6d7",
                        "surface-container-highest": "#d3e4fe",
                        "primary-container": "#2563eb",
                        "on-primary-container": "#eeefff",
                        "on-secondary": "#ffffff",
                        "surface-variant": "#d3e4fe",
                        "primary": "#004ac6",
                        "on-primary-fixed-variant": "#003ea8",
                        "on-secondary-fixed-variant": "#454747",
                        "secondary-fixed": "#e2e2e2",
                        "secondary": "#5d5f5f",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "inverse-surface": "#213145",
                        "tertiary-fixed": "#ffdbcd",
                        "on-secondary-container": "#616363",
                        "inverse-on-surface": "#eaf1ff",
                        "primary-fixed": "#dbe1ff",
                        "on-tertiary-fixed": "#360f00",
                        "error": "#ba1a1a",
                        "on-surface": "#0b1c30",
                        "surface-container-low": "#eff4ff",
                        "surface-dim": "#cbdbf5",
                        "secondary-container": "#dfe0e0",
                        "surface-container": "#e5eeff",
                        "surface-tint": "#0053db",
                        "on-secondary-fixed": "#1a1c1c",
                        "on-background": "#0b1c30",
                        "error-container": "#ffdad6",
                        "background": "#f8f9ff",
                        "outline": "#737686",
                        "surface-bright": "#f8f9ff",
                        "secondary-fixed-dim": "#c6c6c7",
                        "on-surface-variant": "#434655",
                        "on-primary": "#ffffff",
                        "inverse-primary": "#b4c5ff",
                        "on-tertiary": "#ffffff",
                        "primary-fixed-dim": "#b4c5ff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sm": "16px",
                        "base": "4px",
                        "md": "24px",
                        "container-margin": "20px",
                        "lg": "32px",
                        "xs": "8px",
                        "xl": "48px",
                        "gutter": "16px"
                    },
                    "fontFamily": {
                        "headline-md": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"],
                        "body-md": ["Inter"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "label-sm": ["Inter"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "body-lg": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    "fontSize": {
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "display-lg": ["40px", {"lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600"}]
                    }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-headline { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .sidebar-item-active {
            background-color: rgba(0, 74, 198, 0.1);
            color: #004ac6;
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar (Desktop) -->
    <aside class="hidden md:flex flex-col w-64 bg-surface-container-low border-r border-outline-variant p-md shrink-0">
        <div class="flex items-center gap-xs mb-xl">
            <img src="logo.png" alt="Logo" class="w-8 h-8 object-contain" />
            <span class="font-headline text-headline-md text-primary font-bold">KosanLaundry</span>
        </div>
        
        <nav class="flex-grow space-y-base">
            <a href="dashboard.php" class="sidebar-item-active flex items-center gap-sm px-sm py-3 rounded-xl font-label-md text-label-md transition-colors hover:bg-surface-container-high">
                <span class="material-symbols-outlined">dashboard</span>
                Ringkasan
            </a>
            <a href="#" class="flex items-center gap-sm px-sm py-3 text-on-surface-variant rounded-xl font-label-md text-label-md transition-colors hover:bg-surface-container-high hover:text-on-surface">
                <span class="material-symbols-outlined">shopping_cart</span>
                Pesanan Baru
            </a>
            <a href="#" class="flex items-center gap-sm px-sm py-3 text-on-surface-variant rounded-xl font-label-md text-label-md transition-colors hover:bg-surface-container-high hover:text-on-surface">
                <span class="material-symbols-outlined">history</span>
                Riwayat Transaksi
            </a>
            <a href="#" class="flex items-center gap-sm px-sm py-3 text-on-surface-variant rounded-xl font-label-md text-label-md transition-colors hover:bg-surface-container-high hover:text-on-surface">
                <span class="material-symbols-outlined">settings</span>
                Pengaturan
            </a>
        </nav>

        <div class="pt-md border-t border-outline-variant">
            <a href="auth/logout.php" class="flex items-center gap-sm px-sm py-3 text-error rounded-xl font-label-md text-label-md transition-colors hover:bg-error-container hover:text-on-error-container">
                <span class="material-symbols-outlined">logout</span>
                Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- Mobile Top Navigation Header -->
    <header class="md:hidden flex items-center justify-between bg-surface-container-low border-b border-outline-variant px-container-margin py-sm">
        <div class="flex items-center gap-xs">
            <img src="logo.png" alt="Logo" class="w-6 h-6 object-contain" />
            <span class="font-headline text-headline-md text-primary font-bold">KosanLaundry</span>
        </div>
        <a href="auth/logout.php" class="text-error flex items-center justify-center p-2 rounded-lg hover:bg-error-container hover:text-on-error-container transition-colors">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow p-sm md:p-lg overflow-y-auto max-w-7xl mx-auto w-full">
        <!-- Top bar with user greeting and notifications -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-lg gap-sm">
            <div>
                <h1 class="font-headline text-headline-lg text-on-surface">Halo, <?= $nama; ?>! 👋</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Selamat datang kembali di panel pelanggan KosanLaundry Anda.</p>
            </div>
            
            <div class="flex items-center gap-sm">
                <!-- Laundry Balance Widget -->
                <div class="bg-primary-container text-on-primary-container px-md py-sm rounded-2xl flex items-center gap-sm shadow-md">
                    <span class="material-symbols-outlined bg-white/20 p-xs rounded-full">account_balance_wallet</span>
                    <div>
                        <p class="font-label-sm text-label-sm text-primary-fixed opacity-90">Saldo Anda</p>
                        <p class="font-headline text-label-md text-white font-bold">Rp 75.000</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-md mb-lg">
            <!-- User Profile Details Card (2 Cols on large screens) -->
            <div class="lg:col-span-2 glass-card rounded-3xl p-md flex flex-col md:flex-row items-center gap-md relative overflow-hidden">
                <!-- Background decoration -->
                <div class="absolute -right-10 -bottom-10 opacity-5 pointer-events-none">
                    <img src="logo.png" alt="" class="w-[160px] h-[160px] object-contain" />
                </div>
                
                <!-- Profile Avatar -->
                <div class="w-24 h-24 bg-primary text-on-primary rounded-full flex items-center justify-center font-headline text-[36px] font-bold shadow-lg shrink-0">
                    <?= $inisial; ?>
                </div>

                <div class="flex-grow space-y-xs text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center gap-xs md:gap-sm">
                        <h2 class="font-headline text-headline-md text-on-surface"><?= $nama; ?></h2>
                        <?php if ($login_via_google): ?>
                            <span class="inline-flex items-center gap-xs bg-[#e8f0fe] text-[#1a73e8] text-xs font-semibold px-2.5 py-1 rounded-full w-fit mx-auto md:mx-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                                </svg>
                                Google Account
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-xs bg-secondary-container text-on-secondary-container text-xs font-semibold px-2.5 py-1 rounded-full w-fit mx-auto md:mx-0">
                                <span class="material-symbols-outlined text-[14px]">mail</span>
                                Email &amp; Password
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant"><?= $email; ?></p>
                    <p class="font-label-sm text-label-sm text-outline">Terdaftar Sejak: <?= date('d M Y', strtotime($user['created_at'])); ?></p>
                    
                    <!-- Link Google Account Button if not already linked -->
                    <?php if (!$login_via_google): ?>
                        <div id="google-link-section" class="pt-sm border-t border-outline-variant mt-sm">
                            <p class="font-label-sm text-label-sm text-on-surface-variant mb-xs">Sambungkan akun Google Anda untuk login instan di lain waktu:</p>
                            <div class="flex justify-center md:justify-start">
                                <div id="google-login-btn-container"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats/Metrics Cards (1 Col) -->
            <div class="grid grid-cols-2 gap-sm">
                <!-- Card Active Orders -->
                <div class="glass-card rounded-2xl p-sm flex flex-col justify-between hover:translate-y-[-2px] transition-transform shadow-sm">
                    <span class="material-symbols-outlined text-primary bg-primary-fixed p-sm rounded-xl w-fit">local_laundry_service</span>
                    <div class="mt-md">
                        <p class="font-headline text-[28px] font-bold text-on-surface">2</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">Cucian Aktif</p>
                    </div>
                </div>

                <!-- Card Points -->
                <div class="glass-card rounded-2xl p-sm flex flex-col justify-between hover:translate-y-[-2px] transition-transform shadow-sm">
                    <span class="material-symbols-outlined text-tertiary bg-tertiary-fixed p-sm rounded-xl w-fit">award_star</span>
                    <div class="mt-md">
                        <p class="font-headline text-[28px] font-bold text-on-surface">120</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">Loyalty Poin</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Active Laundry Status -->
        <section class="mb-lg">
            <div class="flex items-center justify-between mb-sm">
                <h3 class="font-headline text-headline-md text-on-surface">Pesanan Laundry Terkini</h3>
                <button class="font-label-sm text-label-sm text-primary hover:underline font-semibold flex items-center gap-xs">
                    Lihat Semua
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </button>
            </div>
            
            <!-- Mobile list or Desktop Table -->
            <div class="glass-card rounded-3xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant font-label-md text-label-md text-on-surface-variant">
                                <th class="px-md py-4">ID Pesanan</th>
                                <th class="px-md py-4">Layanan</th>
                                <th class="px-md py-4">Berat/Jumlah</th>
                                <th class="px-md py-4">Status Cucian</th>
                                <th class="px-md py-4">Total Biaya</th>
                                <th class="px-md py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                            <tr class="hover:bg-surface-container-low/50 transition-colors">
                                <td class="px-md py-4 font-semibold text-primary">#FL-8831</td>
                                <td class="px-md py-4">
                                    <p class="font-semibold">Cuci Kering Setrika</p>
                                    <p class="text-xs text-on-surface-variant font-light">Estimasi selesai: Besok, 15:00</p>
                                </td>
                                <td class="px-md py-4">3.5 Kg</td>
                                <td class="px-md py-4">
                                    <span class="inline-flex items-center gap-xs bg-primary-fixed text-on-primary-fixed-variant px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <span class="w-2.5 h-2.5 bg-primary rounded-full animate-ping"></span>
                                        Sedang Dicuci
                                    </span>
                                </td>
                                <td class="px-md py-4 font-semibold">Rp 28.000</td>
                                <td class="px-md py-4 text-right">
                                    <button class="px-sm py-2 bg-surface-container-high hover:bg-primary-fixed text-primary rounded-lg font-label-sm text-label-sm transition-colors">Pantau</button>
                                </td>
                            </tr>
                            <tr class="hover:bg-surface-container-low/50 transition-colors">
                                <td class="px-md py-4 font-semibold text-primary">#FL-8790</td>
                                <td class="px-md py-4">
                                    <p class="font-semibold">Setrika Kilat (Express)</p>
                                    <p class="text-xs text-on-surface-variant font-light">Selesai pada: 12 Jun, 10:20</p>
                                </td>
                                <td class="px-md py-4">2.0 Kg</td>
                                <td class="px-md py-4">
                                    <span class="inline-flex items-center gap-xs bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                                        Selesai &amp; Diambil
                                    </span>
                                </td>
                                <td class="px-md py-4 font-semibold">Rp 20.000</td>
                                <td class="px-md py-4 text-right">
                                    <button class="px-sm py-2 bg-surface-container-high hover:bg-primary-fixed text-primary rounded-lg font-label-sm text-label-sm transition-colors">Struk</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Google Sign-In SDK configuration for account linking -->
    <?php if (!$login_via_google): ?>
    <script>
        window.onload = function () {
            google.accounts.id.initialize({
                client_id: "615380191081-lpbn8t1lfb9r675qh34i82nmgou3l5l4.apps.googleusercontent.com", 
                callback: handleLinkResponse
            });

            const container = document.getElementById("google-login-btn-container");
            if (container) {
                google.accounts.id.renderButton(container, {
                    theme: "outline",
                    size: "medium",
                    width: "250",
                    text: "signin_with",
                    shape: "rectangular",
                    logo_alignment: "left"
                });
            }
        }

        function handleLinkResponse(response) {
            const token = response.credential;
            
            // Mengirim token ke backend auth_google.php.
            // Karena auth_google.php mencocokkan email user saat ini dan mengupdate google_id,
            // proses ini akan menautkan akun Google ke akun email manual yang sedang aktif ini.
            fetch('auth_google.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_token: token })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error("HTTP error " + res.status);
                }
                return res.json();
            })
            .then(data => {
                if(data.success) {
                    // Berhasil menghubungkan akun Google, refresh halaman untuk update tampilan
                    window.location.reload();
                } else {
                    alert('Gagal menghubungkan Google Account: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat menghubungkan akun Google: ' + err.message);
            });
        }
    </script>
    <?php endif; ?>

    <!-- Micro-interactions scripts -->
    <script>
        document.querySelectorAll('button, a').forEach(el => {
            el.addEventListener('mousedown', () => {
                el.classList.add('scale-95');
            });
            el.addEventListener('mouseup', () => {
                el.classList.remove('scale-95');
            });
            el.addEventListener('mouseleave', () => {
                el.classList.remove('scale-95');
            });
        });
    </script>
</body>
</html>
