<?php
// index.php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
$dashboard_url = "dashboard.php";
$login_url = "login/login.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>KosanLaundry - Laundry Bersih, Kosan Nyaman</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-surface-variant": "#424754",
                    "surface-container-low": "#f0f3ff",
                    "tertiary-container": "#a36700",
                    "on-background": "#151c27",
                    "surface-bright": "#f9f9ff",
                    "outline-variant": "#c2c6d6",
                    "surface-container-lowest": "#ffffff",
                    "secondary-container": "#6df5e1",
                    "outline": "#727785",
                    "on-tertiary-fixed": "#2a1700",
                    "inverse-surface": "#2a313d",
                    "on-primary-fixed-variant": "#004395",
                    "surface-container": "#e7eefe",
                    "primary": "#0058be",
                    "on-secondary": "#ffffff",
                    "on-primary-fixed": "#001a42",
                    "primary-container": "#2170e4",
                    "background": "#f9f9ff",
                    "on-tertiary": "#ffffff",
                    "surface-variant": "#dce2f3",
                    "secondary": "#006b5f",
                    "secondary-fixed": "#71f8e4",
                    "error-container": "#ffdad6",
                    "surface": "#f9f9ff",
                    "surface-dim": "#d3daea",
                    "on-surface": "#151c27",
                    "error": "#ba1a1a",
                    "inverse-on-surface": "#ebf1ff",
                    "on-error": "#ffffff",
                    "tertiary-fixed-dim": "#ffb95f",
                    "secondary-fixed-dim": "#4fdbc8",
                    "surface-container-highest": "#dce2f3",
                    "tertiary": "#825100",
                    "on-error-container": "#93000a",
                    "surface-tint": "#005ac2",
                    "on-primary-container": "#fefcff",
                    "on-tertiary-container": "#fffbff",
                    "on-secondary-container": "#006f64",
                    "on-secondary-fixed-variant": "#005048",
                    "surface-container-high": "#e2e8f8",
                    "on-primary": "#ffffff",
                    "inverse-primary": "#adc6ff",
                    "primary-fixed": "#d8e2ff",
                    "primary-fixed-dim": "#adc6ff",
                    "on-secondary-fixed": "#00201c",
                    "on-tertiary-fixed-variant": "#653e00",
                    "tertiary-fixed": "#ffddb8"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "xl": "32px",
                    "base": "4px",
                    "gutter": "16px",
                    "container-margin": "20px",
                    "xs": "8px",
                    "sm": "12px",
                    "md": "16px",
                    "lg": "24px"
            },
            "fontFamily": {
                    "label-md": ["Inter"],
                    "headline-md": ["Inter"],
                    "body-lg": ["Inter"],
                    "display-lg": ["Inter"],
                    "headline-lg": ["Inter"],
                    "label-sm": ["Inter"],
                    "headline-lg-mobile": ["Inter"],
                    "body-md": ["Inter"]
            },
            "fontSize": {
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .bento-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 88, 190, 0.1);
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md overflow-x-hidden">

<!-- TopNavBar -->
<nav class="sticky top-0 w-full z-50 bg-surface shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-gutter py-md flex justify-between items-center">
        <div class="flex items-center space-x-md lg:space-x-lg">
            <a class="flex items-center space-x-xs text-headline-md font-headline-md font-bold text-primary" href="#">
                <img alt="KosanLaundry Logo" class="h-10 w-10 object-contain" src="logo.png?v=3">
                <span class="">KosanLaundry</span>
            </a>
            <div class="hidden md:block relative w-72 lg:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </div>
                <input class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-xl bg-white text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Cari layanan atau mitra..." type="text">
            </div>
        </div>
        <div class="flex items-center space-x-md">
            <!-- Desktop Nav moved to right -->
            <div class="hidden md:flex space-x-lg items-center mr-lg">
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-md" href="#">Home</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="#">Services</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="#">Locations</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="#">Support</a>
            </div>
            <?php if ($is_logged_in): ?>
                <!-- Profile Indicator with Hover Dropdown -->
                <div class="relative group" id="profile-dropdown-container">
                    <button class="flex items-center justify-center w-10 h-10 rounded-full border border-outline-variant focus:outline-none select-none overflow-hidden bg-primary text-on-primary font-bold text-body-lg shadow-sm hover:scale-105 transition-all">
                        <?php if (!empty($_SESSION['profile_pic'])): ?>
                            <img src="<?= htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-xs w-48 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-xs z-50 transform origin-top-right scale-95 opacity-0 pointer-events-none group-hover:scale-100 group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200">
                        <a href="user/edit_profile.php" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">edit</span>
                            <span>Edit Profil</span>
                        </a>
                        <a href="#" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">history</span>
                            <span>Riwayat Pesanan</span>
                        </a>
                        <a href="user/notifikasi.php" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">notifications</span>
                            <span>Notifikasi</span>
                        </a>
                        <div class="border-t border-outline-variant my-xs"></div>
                        <a href="logout.php" class="flex items-center gap-xs px-md py-sm text-body-md text-error hover:bg-error-container/10 transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-error">logout</span>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <button onclick="window.location.href='<?= $login_url; ?>'" class="px-lg py-xs rounded-xl bg-primary text-on-primary font-bold hover:bg-primary-container transition-colors active:scale-95 duration-150">Login</button>
            <?php endif; ?>
            <button class="md:hidden flex items-center">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
</nav>

<main>
<!-- Hero Section -->
<section class="relative min-h-[calc(100vh-72px)] flex items-center px-container-margin overflow-hidden bg-surface-container-low">
    <!-- Background Image with low opacity -->
    <div class="absolute inset-0 bg-cover bg-center opacity-40 pointer-events-none" style="background-image: url('uploads/laundry_hero_bg.png');"></div>
    
    <div class="max-w-7xl mx-auto w-full grid lg:grid-cols-2 gap-xl items-center py-xl relative z-10">
        <div class="space-y-lg animate-fade-in">
            <div class="inline-flex items-center space-x-xs px-md py-xs bg-secondary-container rounded-full text-on-secondary-container font-label-sm">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span class="">#1 Laundry Khusus Mahasiswa &amp; Profesional</span>
            </div>
            <h1 class="text-display-lg text-primary leading-tight font-display-lg">
                Laundry Bersih,<br>
                <span class="text-secondary">Kosan Nyaman</span>
            </h1>
            <p class="text-body-lg text-on-surface-variant max-w-lg">
                Urusan baju kotor serahkan ke kami. Jemput antar gratis, proses cepat, dan hasil wangi segar seperti baru. Fokus pada studi dan karir Anda, biarkan kami yang mencuci.
            </p>
            <div class="flex flex-wrap gap-md pt-md">
                <button onclick="window.location.href='<?= $is_logged_in ? $dashboard_url : 'login/daftar.php'; ?>'" class="px-xl py-md bg-primary text-on-primary rounded-xl font-bold text-body-md shadow-lg hover:shadow-primary/20 transition-all active:scale-95">
                    Pesan Sekarang
                </button>
                <button class="px-xl py-md border-2 border-primary text-primary rounded-xl font-bold text-body-md hover:bg-primary-fixed transition-all">
                    Lihat Menu &amp; Harga
                </button>
            </div>
        </div>
        
        <div class="relative hidden lg:block">
            <!-- Premium Laundry Room Photo -->
            <div class="h-[450px] w-full rounded-[2.5rem] overflow-hidden shadow-2xl relative bg-cover bg-center border border-outline-variant/30" style="background-image: url('uploads/clean_washer_hero.png');">
            </div>
        </div>
    </div>
    <!-- Decorative circle -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-fixed-dim/30 rounded-full blur-3xl"></div>
</section>

<!-- Key Benefits -->
<section class="py-xl px-container-margin max-w-7xl mx-auto">
    <div class="text-center mb-xl space-y-md">
        <h2 class="text-headline-lg font-headline-lg text-primary">Mengapa KosanLaundry?</h2>
        <p class="text-on-surface-variant max-w-2xl mx-auto font-body-md">Kami memberikan layanan terbaik untuk memastikan pakaian Anda tetap awet dan bersih maksimal.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-lg">
        <div class="bento-card p-xl bg-surface-container-lowest rounded-xl border border-outline-variant">
            <div class="w-12 h-12 bg-primary-container text-on-primary-container rounded-lg flex items-center justify-center mb-md">
                <span class="material-symbols-outlined">bolt</span>
            </div>
            <h3 class="text-headline-md font-headline-md mb-xs">Fast Delivery</h3>
            <p class="text-on-surface-variant font-body-md">Layanan kilat jemput antar. Baju kotor di pagi hari, bersih di sore hari.</p>
        </div>
        <div class="bento-card p-xl bg-surface-container-lowest rounded-xl border border-outline-variant">
            <div class="w-12 h-12 bg-secondary-container text-on-secondary-container rounded-lg flex items-center justify-center mb-md">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <h3 class="text-headline-md font-headline-md mb-xs">Affordable Prices</h3>
            <p class="text-on-surface-variant font-body-md">Harga ramah di kantong mahasiswa mulai dari Rp 6.000 per kg tanpa biaya tersembunyi.</p>
        </div>
        <div class="bento-card p-xl bg-surface-container-lowest rounded-xl border border-outline-variant">
            <div class="w-12 h-12 bg-tertiary-fixed text-on-tertiary-fixed-variant rounded-lg flex items-center justify-center mb-md">
                <span class="material-symbols-outlined">high_quality</span>
            </div>
            <h3 class="text-headline-md font-headline-md mb-xs">High-Quality Cleaning</h3>
            <p class="text-on-surface-variant font-body-md">Detergen premium dan setrika uap modern untuk menjaga serat kain dan keharuman tahan lama.</p>
        </div>
    </div>
</section>

<!-- Recommended Shops -->
<section class="py-xl bg-surface-container-high px-container-margin">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end mb-xl gap-md">
            <div class="space-y-sm">
                <h2 class="text-headline-lg font-headline-lg text-on-surface">Rekomendasi Laundry Terdekat</h2>
                <p class="text-on-surface-variant font-body-md">Mitra terpercaya di sekitar area kosan Anda.</p>
            </div>
            <a class="text-primary font-bold flex items-center space-x-xs group" href="#">
                <span class="">Lihat Semua Toko</span>
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg">
            <!-- Shop Card 1 -->
            <div class="group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant hover:shadow-md transition-all">
                <div class="h-48 bg-primary-container/20 flex items-center justify-center relative">
                    <span class="material-symbols-outlined text-primary text-[64px]">local_laundry_service</span>
                    <div class="absolute top-md right-md bg-secondary-fixed text-on-secondary-fixed px-sm py-[2px] rounded-full text-label-sm font-bold">Terpopuler</div>
                </div>
                <div class="p-md space-y-md">
                    <div class="flex justify-between items-start">
                        <h4 class="font-headline-md text-on-surface text-base">KosanFresh Laundry</h4>
                        <div class="flex items-center text-tertiary font-bold">
                            <span class="material-symbols-outlined text-[18px] mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="text-label-md">4.9</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-md text-on-surface-variant text-label-sm">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">location_on</span>
                            <span class="">0.4 km</span>
                        </div>
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">schedule</span>
                            <span class="">Open until 21:00</span>
                        </div>
                    </div>
                    <div class="pt-md border-t border-outline-variant flex justify-between items-center">
                        <div class="bg-tertiary-container/10 px-md py-xs rounded-full">
                            <span class="text-tertiary font-bold text-label-md">Rp 7.000/kg</span>
                        </div>
                        <button onclick="window.location.href='<?= $is_logged_in ? $dashboard_url : $login_url; ?>'" class="text-primary font-bold text-label-md hover:underline">Pilih</button>
                    </div>
                </div>
            </div>
            <!-- Shop Card 2 -->
            <div class="group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant hover:shadow-md transition-all">
                <div class="h-48 bg-secondary-container/20 flex items-center justify-center relative">
                    <span class="material-symbols-outlined text-secondary text-[64px]">dry_cleaning</span>
                </div>
                <div class="p-md space-y-md">
                    <div class="flex justify-between items-start">
                        <h4 class="font-headline-md text-on-surface text-base">Express Shine</h4>
                        <div class="flex items-center text-tertiary font-bold">
                            <span class="material-symbols-outlined text-[18px] mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="text-label-md">4.7</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-md text-on-surface-variant text-label-sm">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">location_on</span>
                            <span class="">1.2 km</span>
                        </div>
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">schedule</span>
                            <span class="">Open 24 Hours</span>
                        </div>
                    </div>
                    <div class="pt-md border-t border-outline-variant flex justify-between items-center">
                        <div class="bg-tertiary-container/10 px-md py-xs rounded-full">
                            <span class="text-tertiary font-bold text-label-md">Rp 9.500/kg</span>
                        </div>
                        <button onclick="window.location.href='<?= $is_logged_in ? $dashboard_url : $login_url; ?>'" class="text-primary font-bold text-label-md hover:underline">Pilih</button>
                    </div>
                </div>
            </div>
            <!-- Shop Card 3 -->
            <div class="group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant hover:shadow-md transition-all">
                <div class="h-48 bg-yellow-500/10 flex items-center justify-center relative">
                    <span class="material-symbols-outlined text-yellow-600 text-[64px]">iron</span>
                </div>
                <div class="p-md space-y-md">
                    <div class="flex justify-between items-start">
                        <h4 class="font-headline-md text-on-surface text-base">Sahabat Kos</h4>
                        <div class="flex items-center text-tertiary font-bold">
                            <span class="material-symbols-outlined text-[18px] mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="text-label-md">4.5</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-md text-on-surface-variant text-label-sm">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">location_on</span>
                            <span class="">0.8 km</span>
                        </div>
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">schedule</span>
                            <span class="">Open until 20:00</span>
                        </div>
                    </div>
                    <div class="pt-md border-t border-outline-variant flex justify-between items-center">
                        <div class="bg-tertiary-container/10 px-md py-xs rounded-full">
                            <span class="text-tertiary font-bold text-label-md">Rp 6.000/kg</span>
                        </div>
                        <button onclick="window.location.href='<?= $is_logged_in ? $dashboard_url : $login_url; ?>'" class="text-primary font-bold text-label-md hover:underline">Pilih</button>
                    </div>
                </div>
            </div>
            <!-- Shop Card 4 -->
            <div class="group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant hover:shadow-md transition-all">
                <div class="h-48 bg-green-500/10 flex items-center justify-center relative">
                    <span class="material-symbols-outlined text-green-600 text-[64px]">eco</span>
                </div>
                <div class="p-md space-y-md">
                    <div class="flex justify-between items-start">
                        <h4 class="font-headline-md text-on-surface text-base">EcoWash Pure</h4>
                        <div class="flex items-center text-tertiary font-bold">
                            <span class="material-symbols-outlined text-[18px] mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="text-label-md">4.8</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-md text-on-surface-variant text-label-sm">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">location_on</span>
                            <span class="">2.1 km</span>
                        </div>
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-[16px] mr-1">schedule</span>
                            <span class="">Open until 22:00</span>
                        </div>
                    </div>
                    <div class="pt-md border-t border-outline-variant flex justify-between items-center">
                        <div class="bg-tertiary-container/10 px-md py-xs rounded-full">
                            <span class="text-tertiary font-bold text-label-md">Rp 8.500/kg</span>
                        </div>
                        <button onclick="window.location.href='<?= $is_logged_in ? $dashboard_url : $login_url; ?>'" class="text-primary font-bold text-label-md hover:underline">Pilih</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-xl px-container-margin">
    <div class="max-w-7xl mx-auto bg-primary rounded-3xl overflow-hidden relative p-xl lg:p-[80px]">
        <div class="relative z-10 text-center space-y-lg max-w-2xl mx-auto">
            <h2 class="text-display-lg text-on-primary font-display-lg leading-tight">Siap Untuk Baju Wangi &amp; Rapi Hari Ini?</h2>
            <p class="text-body-lg text-primary-fixed/80">Bergabunglah dengan ribuan mahasiswa lainnya yang sudah beralih ke KosanLaundry.</p>
            <div class="pt-md">
                <button onclick="window.location.href='<?= $is_logged_in ? $dashboard_url : 'login/daftar.php'; ?>'" class="px-[48px] py-[20px] bg-secondary-container text-on-secondary-container rounded-2xl font-bold text-headline-md shadow-xl hover:scale-105 transition-transform active:scale-95">
                    Pesan Sekarang
                </button>
            </div>
        </div>
        <!-- Decorative pattern -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        <div class="absolute -bottom-20 -right-20 w-[400px] h-[400px] bg-secondary rounded-full blur-[100px] opacity-20"></div>
    </div>
</section>
</main>

<!-- Footer -->
<footer class="w-full py-xl px-gutter grid grid-cols-1 md:grid-cols-4 gap-lg bg-surface-container-highest mt-xl">
    <div class="space-y-md">
        <div class="text-headline-sm font-headline-md font-bold text-primary">KosanLaundry</div>
        <p class="text-on-surface-variant font-body-md">Freshness delivered to your doorstep. Laundry solusi cerdas untuk hidup lebih produktif.</p>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Company</h5>
        <ul class="space-y-xs">
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Privacy Policy</a></li>
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Terms of Service</a></li>
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Contact Us</a></li>
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">FAQ</a></li>
        </ul>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Services</h5>
        <ul class="space-y-xs">
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Laundry Kiloan</a></li>
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Laundry Satuan</a></li>
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Cuci Sepatu</a></li>
            <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Dry Cleaning</a></li>
        </ul>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Follow Us</h5>
        <div class="flex space-x-md">
            <a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all" href="#">
                <span class="material-symbols-outlined text-[20px]">share</span>
            </a>
            <a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all" href="#">
                <span class="material-symbols-outlined text-[20px]">public</span>
            </a>
        </div>
        <p class="text-label-sm text-on-surface-variant opacity-80 mt-lg">© 2026 KosanLaundry. Freshness delivered to your doorstep.</p>
    </div>
</footer>

<script>
    // Smooth scroll implementation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
              target.scrollIntoView({
                  behavior: 'smooth'
              });
            }
        });
    });

    // Sticky header transparency effect
    const nav = document.querySelector('nav');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            nav.classList.add('shadow-md');
            nav.classList.remove('shadow-sm');
        } else {
            nav.classList.add('shadow-sm');
            nav.classList.remove('shadow-md');
        }
    });
</script>

</body>
</html>
