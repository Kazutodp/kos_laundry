<?php
// index.php
session_start();
require_once 'db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$dashboard_url = "dashboard.php";
$login_url = "login/login.php";

$upcoming_cards = [
    [
        'nama' => 'Mitra Baru Mataram',
        'icon' => 'store',
        'area' => 'Area Mataram',
        'color' => 'text-primary/40'
    ],
    [
        'nama' => 'Clean & Fresh Express',
        'icon' => 'local_laundry_service',
        'area' => 'Area Sekarbela',
        'color' => 'text-secondary/40'
    ],
    [
        'nama' => 'Shoes Clinic & Care',
        'icon' => 'dry_cleaning',
        'area' => 'Area Ampenan',
        'color' => 'text-[#7c3aed]/40'
    ],
    [
        'nama' => 'MataramWash Outlet #8',
        'icon' => 'handshake',
        'area' => 'Area Pagutan',
        'color' => 'text-amber-600/40'
    ]
];

// Fetch mitra laundry whose profile files exist and are marked for homepage recommendation
try {
    $stmt = $pdo->query("SELECT * FROM mitra_laundry WHERE is_rekomendasi = 1 ORDER BY rating DESC");
    $all_mitra = $stmt->fetchAll();
    
    $mitra_list = [];
    foreach ($all_mitra as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('Mitra laundry/' . $file_name)) {
            $mitra_list[] = $mitra;
        }
    }
    
    // Limit to maximum 8 recommended partners on the homepage
    $mitra_list = array_slice($mitra_list, 0, 8);
    
    // Calculate upcoming cards needed to fill the 8-card grid
    $active_count = count($mitra_list);
    $upcoming_count_needed = max(0, 8 - $active_count);
} catch (PDOException $e) {
    $mitra_list = [];
    $upcoming_count_needed = 8;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>MataramWash - Laundry Bersih, Kosan Nyaman</title>
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
            },
            animation: {
                marquee: 'marquee 35s linear infinite',
            },
            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' }
                }
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
                <img alt="MataramWash Logo" class="h-10 w-10 object-contain" src="logo.png?v=3">
                <span class="">MataramWash</span>
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
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-md" href="#">Beranda</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="layanan/layanan.php">Layanan</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="lokasi/locations.php">Lokasi</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="bantuan/bantuan.php">Bantuan</a>
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
                <div class="flex items-center space-x-xs sm:space-x-sm">
                    <button onclick="window.location.href='<?= $login_url; ?>'" class="px-lg py-xs border-2 border-primary text-primary rounded-xl font-bold hover:bg-primary-fixed transition-all active:scale-95 duration-150 text-sm">Masuk</button>
                    <button onclick="window.location.href='login/daftar.php'" class="px-lg py-xs bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-all active:scale-95 duration-150 text-sm shadow-sm">Daftar</button>
                </div>
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
                Pilih dan pesan jasa laundry kiloan, satuan, hingga cuci sepatu dari puluhan mitra terpercaya di sekitar Mataram. Nikmati layanan antar-jemput murah hanya Rp 1.500 dan pembayaran instan yang aman.
            </p>
            <div class="flex flex-wrap gap-md pt-md">
                <button onclick="findNearestLaundry(this)" class="px-xl py-md bg-primary text-on-primary rounded-xl font-bold text-body-md shadow-lg hover:shadow-primary/20 transition-all active:scale-95">
                    Cari Laundry Terdekat
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
        <h2 class="text-headline-lg font-headline-lg text-primary">Mengapa MataramWash?</h2>
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
<section id="laundry-section" class="py-xl bg-surface-container-high px-container-margin">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end mb-xl gap-md">
            <div class="space-y-sm">
                <h2 class="text-headline-lg font-headline-lg text-on-surface">Rekomendasi Laundry Terdekat</h2>
                <p class="text-on-surface-variant font-body-md">Mitra terpercaya di sekitar area kosan Anda.</p>
            </div>
            <a class="text-primary font-bold flex items-center space-x-xs group" href="layanan/layanan.php">
                <span class="">Lihat Semua Toko</span>
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
        </div>
        <div id="laundry-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg">
            <?php foreach ($mitra_list as $mitra): ?>
                <?php
                $is_washtra = strpos(strtolower($mitra['nama_mitra']), 'washtra') !== false;
                $slug = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
                $foto = !empty($mitra['foto_toko']) ? $mitra['foto_toko'] : 'uploads/mitra_1.png';
                $jarak = $is_washtra ? '1.5 km' : '1.2 km';
                ?>
                <!-- Shop Card -->
                <div class="group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant hover:shadow-md transition-all laundry-card" data-lat="<?= htmlspecialchars($mitra['latitude']); ?>" data-lng="<?= htmlspecialchars($mitra['longitude']); ?>">
                    <div class="h-48 relative overflow-hidden bg-slate-100 flex items-center justify-center">
                        <img src="<?= htmlspecialchars($foto); ?>" alt="<?= htmlspecialchars($mitra['nama_mitra']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <?php if ($is_washtra): ?>
                            <div class="absolute top-md right-md bg-secondary-fixed text-on-secondary-fixed px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Self Service</div>
                        <?php elseif ($mitra['icon_type'] === 'sepatu'): ?>
                            <div class="absolute top-md right-md bg-[#7c3aed] text-white px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Shoe Care</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-md space-y-md">
                        <div class="flex justify-between items-start">
                            <h4 class="font-headline-md text-on-surface text-base"><?= htmlspecialchars($mitra['nama_mitra']); ?></h4>
                            <div class="flex items-center text-tertiary font-bold">
                                <span class="material-symbols-outlined text-[18px] mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="text-label-md"><?= htmlspecialchars($mitra['rating']); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-md text-on-surface-variant text-label-sm">
                            <div class="flex items-center">
                                <span class="material-symbols-outlined text-[16px] mr-1">location_on</span>
                                <span class="distance-text"><?= $jarak; ?></span>
                            </div>
                            <div class="flex items-center">
                                <span class="material-symbols-outlined text-[16px] mr-1">schedule</span>
                                <span class=""><?= htmlspecialchars($mitra['jam_buka']); ?></span>
                            </div>
                        </div>
                        <div class="pt-md border-t border-outline-variant flex justify-between items-center">
                            <div class="bg-tertiary-container/10 px-md py-xs rounded-full">
                                <span class="text-tertiary font-bold text-label-md">
                                    <?php 
                                    if ($mitra['icon_type'] === 'sepatu') {
                                        echo 'Rp 20.000/pasang';
                                    } else {
                                        echo $is_washtra ? 'Rp ' . number_format($mitra['harga_per_kg'], 0, ',', '.') . ' Flat' : 'Rp ' . number_format($mitra['harga_per_kg'], 0, ',', '.') . '/kg';
                                    }
                                    ?>
                                </span>
                            </div>
                            <button onclick="window.location.href='Mitra laundry/<?= $slug; ?>'" class="text-primary font-bold text-label-md hover:underline">Pilih</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            
            <?php for ($i = 0; $i < $upcoming_count_needed; $i++): ?>
                <?php $uc = $upcoming_cards[$i % count($upcoming_cards)]; ?>
                <!-- Upcoming Shop Card -->
                <div class="group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant/60 opacity-85 hover:shadow-md transition-all flex flex-col justify-between">
                    <div class="h-48 relative overflow-hidden bg-slate-100 flex items-center justify-center">
                        <div class="w-full h-full bg-gradient-to-br from-primary-fixed/30 to-secondary-fixed/30 flex flex-col items-center justify-center text-outline gap-xs">
                            <span class="material-symbols-outlined text-4xl <?= $uc['color']; ?> animate-pulse"><?= $uc['icon']; ?></span>
                        </div>
                        <div class="absolute top-md right-md bg-primary-container text-on-primary-container px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Upcoming</div>
                    </div>
                    <div class="p-md space-y-md flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-headline-md text-on-surface/85 text-base font-bold"><?= htmlspecialchars($uc['nama']); ?></h4>
                                <div class="flex items-center text-outline/50 font-bold">
                                    <span class="material-symbols-outlined text-[18px] mr-1">star</span>
                                    <span class="text-label-md">-.-</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-md text-on-surface-variant text-label-sm">
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined text-[16px] mr-1 text-outline">location_on</span>
                                    <span class=""><?= htmlspecialchars($uc['area']); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined text-[16px] mr-1 text-outline">schedule</span>
                                    <span class="">TBA</span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-md border-t border-outline-variant/60 flex justify-between items-center">
                            <div class="bg-surface-container px-md py-xs rounded-full">
                                <span class="text-on-surface-variant/70 font-bold text-label-md">Tarif TBA</span>
                            </div>
                            <span class="text-outline text-label-md font-bold select-none">Segera Hadir</span>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Stats & Testimonials Section -->
<section class="py-20 bg-surface-container-low px-container-margin border-t border-outline-variant/30">
    <div class="max-w-7xl mx-auto space-y-20">
        
        <!-- Platform Stats Sub-section -->
        <div class="space-y-xl text-center">
            <div class="space-y-sm">
                <h2 class="text-headline-lg font-headline-lg text-primary text-center">MataramWash Dalam Angka</h2>
                <p class="text-on-surface-variant font-body-md max-w-xl mx-auto">Kepercayaan mahasiswa dan kualitas layanan mitra adalah prioritas utama kami.</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-lg">
                <!-- Stat 1 -->
                <div class="bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col items-center text-center space-y-xs hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-primary text-3xl">check_circle</span>
                    <span class="text-3xl lg:text-4xl font-extrabold text-primary">5.000+</span>
                    <span class="text-xs lg:text-sm text-on-surface-variant font-medium">Cucian Diselesaikan</span>
                </div>
                <!-- Stat 2 -->
                <div class="bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col items-center text-center space-y-xs hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-secondary text-3xl">handshake</span>
                    <span class="text-3xl lg:text-4xl font-extrabold text-secondary">15+</span>
                    <span class="text-xs lg:text-sm text-on-surface-variant font-medium">Mitra Terpercaya</span>
                </div>
                <!-- Stat 3 -->
                <div class="bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col items-center text-center space-y-xs hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-[#7c3aed] text-3xl">groups</span>
                    <span class="text-3xl lg:text-4xl font-extrabold text-[#7c3aed]">2.500+</span>
                    <span class="text-xs lg:text-sm text-on-surface-variant font-medium">Mahasiswa Terbantu</span>
                </div>
                <!-- Stat 4 -->
                <div class="bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col items-center text-center space-y-xs hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-amber-500 text-3xl">star</span>
                    <span class="text-3xl lg:text-4xl font-extrabold text-amber-500">4.8/5.0</span>
                    <span class="text-xs lg:text-sm text-on-surface-variant font-medium">Rating Ulasan Kepuasan</span>
                </div>
            </div>
        </div>

        <!-- Student Testimonials Sub-section -->
        <div class="space-y-xl overflow-hidden">
            <div class="text-center space-y-sm">
                <h2 class="text-headline-lg font-headline-lg text-primary">Apa Kata Anak Kos?</h2>
                <p class="text-on-surface-variant font-body-md max-w-xl mx-auto">Dengarkan pengalaman nyata dari sesama mahasiswa yang telah mempercayakan cuciannya kepada kami.</p>
            </div>
            
            <div class="relative w-full overflow-hidden py-4">
                <!-- Fade gradient overlays to soften edges -->
                <div class="absolute inset-y-0 left-0 w-16 md:w-32 bg-gradient-to-r from-[#f0f3ff] to-transparent z-10 pointer-events-none"></div>
                <div class="absolute inset-y-0 right-0 w-16 md:w-32 bg-gradient-to-l from-[#f0f3ff] to-transparent z-10 pointer-events-none"></div>

                <div class="flex gap-lg w-max animate-marquee hover:[animation-play-state:paused] py-4">
                    <!-- Original Set of 5 Cards -->
                    <div class="flex gap-lg shrink-0">
                        <!-- Testimonial 1 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Sangat membantu pas lagi minggu UTS! Gak perlu pusing mikirin baju kotor menumpuk di kosan. Tinggal pesan lewat HP, kurir langsung jemput dan diantar lagi dalam kondisi wangi dan rapi."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center font-bold text-primary text-sm">
                                    AN
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Ahmad Naufal</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa UNRAM</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 2 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Harganya beneran ramah di kantong mahasiswa. Pilihan mitranya banyak, jadi bisa cari yang terdekat biar ongkirnya gratis. Washtra Laundry Express juga mantap layanannya!"
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-secondary/10 rounded-full flex items-center justify-center font-bold text-secondary text-sm">
                                    SR
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Siti Rahma</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa UMM</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 3 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Layanan jemput antarnya juara! Baju disetrika rapi banget dan wanginya segar tahan lama. Sangat merekomendasikan MataramWash buat sesama anak kosan."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-[#7c3aed]/10 rounded-full flex items-center justify-center font-bold text-[#7c3aed] text-sm">
                                    DP
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Dwi Prasetyo</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa IKIP</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 4 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Fitur deteksi laundry terdekatnya keren banget! Begitu saya aktifkan lokasi, langsung muncul rekomendasi mitra laundry terdekat dari kos saya. Bayar pakai QRIS juga instan."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-amber-500/10 rounded-full flex items-center justify-center font-bold text-amber-600 text-sm">
                                    RH
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Rian Hidayat</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa UIN</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 5 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Sering cuci sepatu premium di Mate Shoes Care lewat aplikasi ini. Hasilnya bersih banget seperti baru, pengerjaan cepat, dan harga sangat terjangkau dibanding tempat lain."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-pink-500/10 rounded-full flex items-center justify-center font-bold text-pink-600 text-sm">
                                    SA
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Sarah Amanda</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa STIKES</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Duplicate Set of 5 Cards for seamless wrapping -->
                    <div class="flex gap-lg shrink-0" aria-hidden="true">
                        <!-- Testimonial 1 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Sangat membantu pas lagi minggu UTS! Gak perlu pusing mikirin baju kotor menumpuk di kosan. Tinggal pesan lewat HP, kurir langsung jemput dan diantar lagi dalam kondisi wangi dan rapi."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center font-bold text-primary text-sm">
                                    AN
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Ahmad Naufal</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa UNRAM</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 2 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Harganya beneran ramah di kantong mahasiswa. Pilihan mitranya banyak, jadi bisa cari yang terdekat biar ongkirnya gratis. Washtra Laundry Express juga mantap layanannya!"
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-secondary/10 rounded-full flex items-center justify-center font-bold text-secondary text-sm">
                                    SR
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Siti Rahma</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa UMM</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 3 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Layanan jemput antarnya juara! Baju disetrika rapi banget dan wanginya segar tahan lama. Sangat merekomendasikan MataramWash buat sesama anak kosan."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-[#7c3aed]/10 rounded-full flex items-center justify-center font-bold text-[#7c3aed] text-sm">
                                    DP
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Dwi Prasetyo</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa IKIP</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 4 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Fitur deteksi laundry terdekatnya keren banget! Begitu saya aktifkan lokasi, langsung muncul rekomendasi mitra laundry terdekat dari kos saya. Bayar pakai QRIS juga instan."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-amber-500/10 rounded-full flex items-center justify-center font-bold text-amber-600 text-sm">
                                    RH
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Rian Hidayat</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa UIN</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonial 5 -->
                        <div class="w-[300px] md:w-[380px] shrink-0 bg-surface-container-lowest p-lg rounded-2xl border border-outline-variant/60 shadow-sm flex flex-col justify-between space-y-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200 whitespace-normal">
                            <div class="space-y-md">
                                <div class="flex text-amber-500 gap-[2px]">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                </div>
                                <p class="text-xs lg:text-sm text-on-surface-variant italic leading-relaxed">
                                    "Sering cuci sepatu premium di Mate Shoes Care lewat aplikasi ini. Hasilnya bersih banget seperti baru, pengerjaan cepat, dan harga sangat terjangkau dibanding tempat lain."
                                </p>
                            </div>
                            <div class="flex items-center gap-md border-t border-outline-variant/30 pt-md">
                                <div class="w-10 h-10 bg-pink-500/10 rounded-full flex items-center justify-center font-bold text-pink-600 text-sm">
                                    SA
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface text-sm">Sarah Amanda</h4>
                                    <p class="text-[10px] text-outline uppercase font-bold tracking-wider">Mahasiswa STIKES</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
</main>

<!-- Footer -->
<footer class="w-full bg-slate-950 text-slate-400 border-t border-slate-900 mt-xl">
    <div class="max-w-7xl mx-auto px-gutter py-xl grid grid-cols-1 md:grid-cols-4 gap-xl">
        <!-- Brand Section -->
        <div class="space-y-md">
            <div class="flex items-center gap-xs">
                <img alt="MataramWash Logo" class="h-10 w-10 object-contain" src="logo.png?v=3">
                <span class="text-headline-sm font-headline-md font-bold text-white">MataramWash</span>
            </div>
            <p class="text-slate-400 font-body-md leading-relaxed">
                Freshness delivered to your doorstep. Solusi laundry cerdas dan praktis khusus mahasiswa & profesional di Mataram.
            </p>
            <div class="space-y-sm pt-xs text-label-sm">
                <div class="flex items-center space-x-xs">
                    <span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
                    <span class="text-slate-300">Mataram, Nusa Tenggara Barat</span>
                </div>
                <div class="flex items-center space-x-xs">
                    <span class="material-symbols-outlined text-[18px] text-primary">call</span>
                    <span class="text-slate-300">+62 823-4196-1954</span>
                </div>
                <div class="flex items-center space-x-xs">
                    <span class="material-symbols-outlined text-[18px] text-primary">mail</span>
                    <span class="text-slate-300">support@mataramwash.com</span>
                </div>
            </div>
        </div>

        <!-- Company Links -->
        <div class="space-y-md md:pl-lg">
            <h5 class="font-bold text-white font-label-md tracking-wider uppercase text-xs">Perusahaan</h5>
            <ul class="space-y-sm text-body-md">
                <li>
                    <a href="#" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Tentang Kami</a>
                </li>
                <li>
                    <a href="bantuan/bantuan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Bantuan &amp; FAQ</a>
                </li>
                <li>
                    <a href="#" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Kontak Kami</a>
                </li>
                <li>
                    <a href="#" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Syarat &amp; Ketentuan</a>
                </li>
            </ul>
        </div>

        <!-- Service Links -->
        <div class="space-y-md">
            <h5 class="font-bold text-white font-label-md tracking-wider uppercase text-xs">Layanan Kami</h5>
            <ul class="space-y-sm text-body-md">
                <li>
                    <a href="layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Laundry Kiloan</a>
                </li>
                <li>
                    <a href="layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Laundry Satuan</a>
                </li>
                <li>
                    <a href="layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Cuci Sepatu Premium</a>
                </li>
                <li>
                    <a href="layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Express Delivery</a>
                </li>
            </ul>
        </div>

        <!-- Follow & Payments -->
        <div class="space-y-md">
            <h5 class="font-bold text-white font-label-md tracking-wider uppercase text-xs">Ikuti Kami</h5>
            <div class="flex space-x-sm">
                <a class="w-9 h-9 rounded-full bg-slate-900 border border-slate-800 text-slate-300 hover:bg-primary hover:text-white hover:border-primary transition-all duration-300 flex items-center justify-center" href="#" aria-label="Instagram">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051C.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                </a>
                <a class="w-9 h-9 rounded-full bg-slate-900 border border-slate-800 text-slate-300 hover:bg-primary hover:text-white hover:border-primary transition-all duration-300 flex items-center justify-center" href="#" aria-label="TikTok">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.99-1.72-.08-.07-.17-.17-.25-.26v6.52c-.03 2.32-.83 4.67-2.61 6.13-1.89 1.56-4.52 2.1-6.91 1.62-2.73-.55-5.17-2.45-6.07-5.13-.99-2.94-.3-6.42 1.83-8.66 1.73-1.83 4.37-2.68 6.81-2.28v4.11c-1.12-.22-2.34-.05-3.32.54-.99.6-1.63 1.65-1.79 2.79-.27 1.93.99 3.88 2.89 4.26 1.43.29 2.99-.14 3.89-1.27.46-.57.69-1.29.69-2v-12.3c0-.02 0-.03-.01-.05z"/>
                    </svg>
                </a>
                <a class="w-9 h-9 rounded-full bg-slate-900 border border-slate-800 text-slate-300 hover:bg-primary hover:text-white hover:border-primary transition-all duration-300 flex items-center justify-center" href="#" aria-label="WhatsApp">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.248 8.477 3.517 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.66.986 3.278 1.488 5.339 1.49 5.485-.002 9.948-4.469 9.95-9.956.002-2.657-1.02-5.155-2.877-7.017C17.18 1.81 14.685.787 12.03.785 6.544.787 2.08 5.253 2.078 10.743c-.001 2.045.513 3.626 1.486 5.23L2.553 21.64l5.885-1.543-.791.757z"/>
                    </svg>
                </a>
            </div>
            <div class="space-y-xs pt-xs">
                <h6 class="text-white font-bold text-xs uppercase tracking-wider">Metode Pembayaran</h6>
                <div class="flex flex-wrap gap-xs">
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">MIDTRANS</span>
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">QRIS</span>
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">GOPAY</span>
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">OVO</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-slate-900 py-md text-xs text-slate-500 bg-slate-950">
        <div class="max-w-7xl mx-auto px-gutter flex flex-col md:flex-row justify-between items-center gap-xs">
            <span>&copy; 2026 MataramWash. Semua Hak Dilindungi.</span>
            <div class="flex space-x-md">
                <a href="#" class="hover:text-primary transition-colors">Kebijakan Privasi</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-primary transition-colors">Syarat &amp; Ketentuan</a>
            </div>
        </div>
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

    // Geolocation and sorting logic
    function findNearestLaundry(button) {
        const originalText = button.innerHTML;
        button.innerHTML = `
            <span class="inline-flex items-center gap-xs">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mendeteksi Lokasi...
            </span>
        `;
        button.disabled = true;

        // Default location: Universitas Mataram (Unram)
        const defaultLat = -8.589808;
        const defaultLng = 116.096316;

        if (!navigator.geolocation) {
            alert("Geolocation tidak didukung oleh browser Anda. Menggunakan lokasi default (Universitas Mataram).");
            processLocation(defaultLat, defaultLng);
            resetButton();
        } else {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    processLocation(lat, lng);
                    resetButton();
                },
                (error) => {
                    console.warn("Geolocation error:", error);
                    let msg = "Akses lokasi ditolak atau tidak tersedia. Menggunakan lokasi default (Universitas Mataram).";
                    if (error.code === error.PERMISSION_DENIED) {
                        msg = "Izin akses lokasi ditolak. Menggunakan lokasi default (Universitas Mataram).";
                    }
                    alert(msg);
                    processLocation(defaultLat, defaultLng);
                    resetButton();
                },
                { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
            );
        }

        function resetButton() {
            button.innerHTML = originalText;
            button.disabled = false;
            
            // Scroll smoothly to laundry section
            const targetSection = document.getElementById('laundry-section');
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function processLocation(userLat, userLng) {
            const container = document.getElementById('laundry-list');
            if (!container) return;

            const cards = Array.from(container.querySelectorAll('.laundry-card'));
            const upcomingCards = Array.from(container.querySelectorAll('.group:not(.laundry-card)'));

            // Calculate distance for each card
            cards.forEach(card => {
                const lat = parseFloat(card.getAttribute('data-lat'));
                const lng = parseFloat(card.getAttribute('data-lng'));
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const distance = calculateDistance(userLat, userLng, lat, lng);
                    card.dataset.distance = distance;
                    
                    // Update distance text inside the card
                    const distanceSpan = card.querySelector('.distance-text');
                    if (distanceSpan) {
                        distanceSpan.textContent = distance.toFixed(1) + ' km';
                    }
                } else {
                    card.dataset.distance = 999999;
                }
            });

            // Sort active cards ascending by distance
            cards.sort((a, b) => parseFloat(a.dataset.distance) - parseFloat(b.dataset.distance));

            // Re-append sorted cards and then upcoming cards
            cards.forEach(card => container.appendChild(card));
            upcomingCards.forEach(card => container.appendChild(card));
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the Earth in km
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c; // Distance in km
        }

        function deg2rad(deg) {
            return deg * (Math.PI/180);
        }
    }
</script>

</body>
</html>
