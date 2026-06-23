<?php
// layanan/layanan.php
session_start();
require_once '../db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$dashboard_url = "../dashboard.php";
$login_url = "../login/login.php";

// Fetch active mitra laundry whose profile files exist
try {
    $stmt = $pdo->query("SELECT * FROM mitra_laundry ORDER BY rating DESC");
    $all_mitra = $stmt->fetchAll();
    
    $mitra_list = [];
    foreach ($all_mitra as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../Mitra laundry/' . $file_name)) {
            $mitra_list[] = $mitra;
        }
    }
} catch (PDOException $e) {
    $mitra_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Layanan Mitra - MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
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
            vertical-align: middle;
        }
        .accordion-content {
            transition: max-height 0.3s ease-out, padding 0.3s ease-out;
        }
        .mitra-item {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md overflow-x-hidden">

<!-- TopNavBar -->
<nav class="sticky top-0 w-full z-50 bg-surface shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-gutter py-md flex justify-between items-center">
        <div class="flex items-center space-x-md lg:space-x-lg">
            <a class="flex items-center space-x-xs text-headline-md font-headline-md font-bold text-primary" href="../index.php">
                <img alt="MataramWash Logo" class="h-10 w-10 object-contain" src="../logo.png?v=3">
                <span class="">MataramWash</span>
            </a>
        </div>
        <div class="flex items-center space-x-md">
            <!-- Desktop Nav -->
            <div class="hidden md:flex space-x-lg items-center mr-lg">
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../index.php">Beranda</a>
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-md" href="#">Layanan</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../lokasi/locations.php">Lokasi</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../bantuan/bantuan.php">Bantuan</a>
            </div>
            
            <?php if ($is_logged_in): ?>
                <!-- Profile Indicator with Hover Dropdown -->
                <div class="relative group" id="profile-dropdown-container">
                    <button class="flex items-center justify-center w-10 h-10 rounded-full border border-outline-variant focus:outline-none select-none overflow-hidden bg-primary text-on-primary font-bold text-body-lg shadow-sm hover:scale-105 transition-all">
                        <?php if (!empty($_SESSION['profile_pic'])): ?>
                            <img src="../<?= htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-xs w-48 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-xs z-50 transform origin-top-right scale-95 opacity-0 pointer-events-none group-hover:scale-100 group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200">
                        <a href="../user/edit_profile.php" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">edit</span>
                            <span>Edit Profil</span>
                        </a>
                        <a href="#" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">history</span>
                            <span>Riwayat Pesanan</span>
                        </a>
                        <a href="../user/notifikasi.php" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">notifications</span>
                            <span>Notifikasi</span>
                        </a>
                        <div class="border-t border-outline-variant my-xs"></div>
                        <a href="../logout.php" class="flex items-center gap-xs px-md py-sm text-body-md text-error hover:bg-error-container/10 transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-error">logout</span>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center space-x-xs sm:space-x-sm">
                    <button onclick="window.location.href='<?= $login_url; ?>'" class="px-lg py-xs border-2 border-primary text-primary rounded-xl font-bold hover:bg-primary-fixed transition-all active:scale-95 duration-150 text-sm">Masuk</button>
                    <button onclick="window.location.href='../login/daftar.php'" class="px-lg py-xs bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-all active:scale-95 duration-150 text-sm shadow-sm">Daftar</button>
                </div>
            <?php endif; ?>
            <button class="md:hidden flex items-center" id="mobile-menu-btn">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
    
    <!-- Mobile Navigation Menu -->
    <div class="hidden md:hidden w-full bg-surface border-t border-outline-variant py-md px-gutter space-y-md" id="mobile-menu">
        <a class="block text-on-surface-variant hover:text-primary transition-colors font-label-md py-xs" href="../index.php">Beranda</a>
        <a class="block text-primary font-bold font-label-md py-xs" href="#">Layanan</a>
        <a class="block text-on-surface-variant hover:text-primary transition-colors font-label-md py-xs" href="../lokasi/locations.php">Lokasi</a>
        <a class="block text-on-surface-variant hover:text-primary transition-colors font-label-md py-xs" href="../bantuan/bantuan.php">Bantuan</a>
    </div>
</nav>

<main class="min-h-screen pb-20">

    <!-- Header Section -->
    <section class="relative bg-gradient-to-br from-surface-container-low to-background py-16 px-container-margin overflow-hidden border-b border-outline-variant/25">
        <div class="max-w-7xl mx-auto text-center space-y-md relative z-10">
            <div class="inline-flex items-center space-x-xs px-md py-xs bg-primary-container/10 border border-primary-container/20 rounded-full text-primary font-label-sm mx-auto">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Direktori Layanan MataramWash</span>
            </div>
            <h1 class="text-display-lg text-primary font-display-lg leading-tight md:text-5xl">
                Temukan Mitra Laundry Terbaik
            </h1>
            <p class="text-body-lg text-on-surface-variant max-w-xl mx-auto">
                Pilih kecamatan dan tipe layanan di bawah untuk menemukan mitra laundry terdekat yang paling pas untuk Anda.
            </p>
        </div>
        <!-- Decorative Circle Blurs -->
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-primary-fixed-dim/15 rounded-full blur-3xl opacity-55"></div>
        <div class="absolute -bottom-40 -right-40 w-80 h-80 bg-secondary-fixed-dim/15 rounded-full blur-3xl opacity-55"></div>
    </section>

    <!-- Horizontal Search/Filter Bar (MataramWash Design System Style) -->
    <section class="max-w-4xl mx-auto px-container-margin -mt-8 relative z-20" id="filter-bar">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-md p-6 lg:py-4 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-0 lg:divide-x lg:divide-outline-variant">
                
                <!-- Segment 1: LOKASI (Semua Kecamatan) -->
                <div class="flex flex-col space-y-1 lg:px-6 relative" id="dropdown-location-container">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-primary select-none">LOKASI</span>
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">location_on</span>
                        <button type="button" id="trigger-location" onclick="toggleDropdown('location')" class="w-full flex items-center justify-between text-on-surface font-semibold text-base py-1 border-0 bg-transparent cursor-pointer focus:outline-none">
                            <span id="label-location">Semua Kecamatan</span>
                            <span class="material-symbols-outlined text-outline transition-transform duration-200" id="chevron-location">expand_more</span>
                        </button>
                    </div>
                    <!-- Dropdown Options -->
                    <div id="options-location" class="hidden absolute left-4 lg:left-6 right-4 lg:right-6 top-full mt-2 bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-lg py-2 z-50 transform origin-top transition-all duration-200">
                        <button type="button" onclick="selectOption('location', 'all', 'Semua Kecamatan')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Semua Kecamatan</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="all">check</span>
                        </button>
                        <button type="button" onclick="selectOption('location', 'sekarbela', 'Sekarbela / Kekalik')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Sekarbela / Kekalik</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="sekarbela">check</span>
                        </button>
                        <button type="button" onclick="selectOption('location', 'ampenan', 'Ampenan')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Ampenan</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="ampenan">check</span>
                        </button>
                        <button type="button" onclick="selectOption('location', 'cilinaya', 'Cilinaya')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Cilinaya</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="cilinaya">check</span>
                        </button>
                        <button type="button" onclick="selectOption('location', 'pagutan', 'Pagutan')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Pagutan</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="pagutan">check</span>
                        </button>
                        <button type="button" onclick="selectOption('location', 'mataram', 'Mataram (Pusat)')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Mataram (Pusat)</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="mataram">check</span>
                        </button>
                    </div>
                    <input type="hidden" id="filter-location" value="all">
                </div>

                <!-- Segment 2: TIPE LAYANAN -->
                <div class="flex flex-col space-y-1 lg:px-6 relative" id="dropdown-service-container">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-primary select-none">TIPE LAYANAN</span>
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">local_laundry_service</span>
                        <button type="button" id="trigger-service" onclick="toggleDropdown('service')" class="w-full flex items-center justify-between text-on-surface font-semibold text-base py-1 border-0 bg-transparent cursor-pointer focus:outline-none">
                            <span id="label-service">Semua Tipe</span>
                            <span class="material-symbols-outlined text-outline transition-transform duration-200" id="chevron-service">expand_more</span>
                        </button>
                    </div>
                    <!-- Dropdown Options -->
                    <div id="options-service" class="hidden absolute left-4 lg:left-6 right-4 lg:right-6 top-full mt-2 bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-lg py-2 z-50 transform origin-top transition-all duration-200">
                        <button type="button" onclick="selectOption('service', 'all', 'Semua Tipe')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Semua Tipe</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="all">check</span>
                        </button>
                        <button type="button" onclick="selectOption('service', 'baju', 'Laundry Baju (Kiloan)')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Laundry Baju (Kiloan)</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="baju">check</span>
                        </button>
                        <button type="button" onclick="selectOption('service', 'sepatu', 'Cuci Sepatu &amp; Tas')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Cuci Sepatu &amp; Tas</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="sepatu">check</span>
                        </button>
                        <button type="button" onclick="selectOption('service', 'satuan', 'Satuan &amp; Dry Cleaning')" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors flex justify-between items-center text-on-surface">
                            <span>Satuan &amp; Dry Cleaning</span>
                            <span class="material-symbols-outlined text-primary text-[20px] hidden check-icon" data-val="satuan">check</span>
                        </button>
                    </div>
                    <input type="hidden" id="filter-service" value="all">
                </div>

            </div>
        </div>
    </section>

    <!-- Rekomendasi Mitra Section -->
    <section class="py-16 px-container-margin max-w-7xl mx-auto">
        <div id="rekomendasi-mitra-section" class="space-y-lg">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-xs">
                <div>
                    <h2 class="text-headline-lg font-headline-lg text-primary text-xl lg:text-2xl" id="section-title">Semua Mitra Laundry Terdekat</h2>
                    <p class="text-on-surface-variant text-sm" id="section-subtitle">Menampilkan seluruh mitra laundry aktif di sekitar area kosan Anda.</p>
                </div>
                <div class="text-label-sm text-on-surface-variant bg-surface-container px-md py-xs rounded-full font-bold" id="mitra-count">
                    Jumlah: <?= count($mitra_list); ?> Mitra
                </div>
            </div>

            <!-- Grid Mitra Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg mt-md" id="mitra-grid">
                <?php if (empty($mitra_list)): ?>
                    <div class="col-span-full text-center py-12 bg-surface-container-lowest border border-outline-variant/60 rounded-2xl">
                        <span class="material-symbols-outlined text-outline text-5xl mb-2">storefront_off</span>
                        <p class="text-on-surface-variant font-bold">Belum ada mitra laundry terdaftar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mitra_list as $mitra): ?>
                        <?php
                        $is_washtra = strpos(strtolower($mitra['nama_mitra']), 'washtra') !== false;
                        $slug = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
                        $foto = !empty($mitra['foto_toko']) ? $mitra['foto_toko'] : '../uploads/mitra_1.png';
                        if (strpos($foto, '../') === false && strpos($foto, 'http') === false) {
                            $foto = '../' . $foto;
                        }
                        
                        // Map database icon_type to category flags
                        $iconType = strtolower($mitra['icon_type']);
                        $isBaju = in_array($iconType, ['kiloan', 'express', 'eco']) ? 'true' : 'false';
                        $isSepatu = ($iconType === 'sepatu') ? 'true' : 'false';
                        $isSatuan = ($iconType === 'satuan' || $iconType === 'express' || $is_washtra) ? 'true' : 'false';
                        
                        // Determine base price representation
                        $basePrice = $mitra['harga_per_kg'];
                        if ($iconType === 'sepatu') {
                            $basePrice = 20000; // Shoe care is 20,000 in general/Mate Shoe Care
                        }
                        ?>
                        <!-- Shop Card Item -->
                        <div class="mitra-item group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant hover:shadow-md transition-all flex flex-col justify-between"
                             data-baju="<?= $isBaju; ?>" 
                             data-sepatu="<?= $isSepatu; ?>" 
                             data-satuan="<?= $isSatuan; ?>"
                             data-price="<?= $basePrice; ?>"
                             data-address="<?= htmlspecialchars($mitra['alamat']); ?>">
                            
                            <div class="h-48 relative overflow-hidden bg-slate-100 flex items-center justify-center">
                                <img src="<?= htmlspecialchars($foto); ?>" alt="<?= htmlspecialchars($mitra['nama_mitra']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.src='../uploads/mitra_1.png'">
                                <?php if ($is_washtra): ?>
                                    <div class="absolute top-md right-md bg-secondary-fixed text-on-secondary-fixed px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Self Service</div>
                                <?php elseif ($mitra['icon_type'] === 'sepatu'): ?>
                                    <div class="absolute top-md right-md bg-[#7c3aed] text-white px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Shoe Care</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-md flex-1 flex flex-col justify-between space-y-md">
                                <div>
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="font-bold text-on-surface text-base leading-tight"><?= htmlspecialchars($mitra['nama_mitra']); ?></h4>
                                    </div>
                                    <div class="flex items-center text-tertiary font-bold mb-2">
                                        <span class="material-symbols-outlined text-[18px] mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                                        <span class="text-label-md"><?= htmlspecialchars($mitra['rating']); ?></span>
                                    </div>
                                    <p class="text-on-surface-variant text-xs line-clamp-2 leading-relaxed flex items-start">
                                        <span class="material-symbols-outlined text-sm mr-1 mt-[2px] text-outline">location_on</span>
                                        <span><?= htmlspecialchars($mitra['alamat']); ?></span>
                                    </p>
                                </div>
                                <div class="pt-md border-t border-outline-variant/60 flex justify-between items-center">
                                    <div class="bg-tertiary-container/10 px-md py-xs rounded-full">
                                        <span class="text-tertiary font-bold text-xs">
                                            <?php 
                                            if ($mitra['icon_type'] === 'sepatu') {
                                                echo 'Rp 20.000/pasang';
                                            } else {
                                                echo $is_washtra ? 'Rp ' . number_format($mitra['harga_per_kg'], 0, ',', '.') . ' Flat' : 'Rp ' . number_format($mitra['harga_per_kg'], 0, ',', '.') . '/kg';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <a href="../Mitra laundry/<?= $slug; ?>" class="text-primary font-bold text-label-md hover:underline flex items-center">
                                        <span>Pilih</span>
                                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Upcoming Card 1 -->
                <div class="mitra-item group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant/60 opacity-80 flex flex-col justify-between"
                     data-baju="true" 
                     data-sepatu="true" 
                     data-satuan="true" 
                     data-address="sekarbela ampenan cilinaya pagutan ampenan mataram ampenan kekalik">
                    <div class="h-48 relative overflow-hidden bg-surface-container flex items-center justify-center">
                        <div class="w-full h-full bg-gradient-to-br from-primary-fixed/30 to-secondary-fixed/30 flex flex-col items-center justify-center text-outline gap-xs">
                            <span class="material-symbols-outlined text-4xl text-primary/40 animate-pulse">store</span>
                        </div>
                        <div class="absolute top-md right-md bg-primary-container text-on-primary-container px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Upcoming</div>
                    </div>
                    <div class="p-md flex-1 flex flex-col justify-between space-y-md">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-on-surface/80 text-base leading-tight">Mitra Baru Mataram</h4>
                            </div>
                            <div class="flex items-center text-outline/50 font-bold mb-2">
                                <span class="material-symbols-outlined text-[18px] mr-1">star</span>
                                <span class="text-label-md">-.-</span>
                            </div>
                            <p class="text-on-surface-variant text-xs line-clamp-2 leading-relaxed flex items-start">
                                <span class="material-symbols-outlined text-sm mr-1 mt-[2px] text-outline">location_on</span>
                                <span>Area Mataram &amp; Sekitarnya</span>
                            </p>
                        </div>
                        <div class="pt-md border-t border-outline-variant/40 flex justify-between items-center">
                            <div class="bg-surface-container px-md py-xs rounded-full">
                                <span class="text-on-surface-variant/70 font-bold text-xs">Tarif TBA</span>
                            </div>
                            <span class="text-outline text-label-md font-bold flex items-center gap-base select-none">
                                <span>Segera Hadir</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Card 2 -->
                <div class="mitra-item group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant/60 opacity-80 flex flex-col justify-between"
                     data-baju="true" 
                     data-sepatu="true" 
                     data-satuan="true" 
                     data-address="sekarbela ampenan cilinaya pagutan ampenan mataram ampenan kekalik">
                    <div class="h-48 relative overflow-hidden bg-surface-container flex items-center justify-center">
                        <div class="w-full h-full bg-gradient-to-br from-primary-fixed/30 to-secondary-fixed/30 flex flex-col items-center justify-center text-outline gap-xs">
                            <span class="material-symbols-outlined text-4xl text-secondary/40 animate-pulse">local_laundry_service</span>
                        </div>
                        <div class="absolute top-md right-md bg-primary-container text-on-primary-container px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Upcoming</div>
                    </div>
                    <div class="p-md flex-1 flex flex-col justify-between space-y-md">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-on-surface/80 text-base leading-tight">Clean &amp; Fresh Express</h4>
                            </div>
                            <div class="flex items-center text-outline/50 font-bold mb-2">
                                <span class="material-symbols-outlined text-[18px] mr-1">star</span>
                                <span class="text-label-md">-.-</span>
                            </div>
                            <p class="text-on-surface-variant text-xs line-clamp-2 leading-relaxed flex items-start">
                                <span class="material-symbols-outlined text-sm mr-1 mt-[2px] text-outline">location_on</span>
                                <span>Area Sekarbela &amp; Kekalik</span>
                            </p>
                        </div>
                        <div class="pt-md border-t border-outline-variant/40 flex justify-between items-center">
                            <div class="bg-surface-container px-md py-xs rounded-full">
                                <span class="text-on-surface-variant/70 font-bold text-xs">Tarif TBA</span>
                            </div>
                            <span class="text-outline text-label-md font-bold flex items-center gap-base select-none">
                                <span>Segera Hadir</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Card 3 -->
                <div class="mitra-item group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant/60 opacity-80 flex flex-col justify-between"
                     data-baju="true" 
                     data-sepatu="true" 
                     data-satuan="true" 
                     data-address="sekarbela ampenan cilinaya pagutan ampenan mataram ampenan kekalik">
                    <div class="h-48 relative overflow-hidden bg-surface-container flex items-center justify-center">
                        <div class="w-full h-full bg-gradient-to-br from-primary-fixed/30 to-secondary-fixed/30 flex flex-col items-center justify-center text-outline gap-xs">
                            <span class="material-symbols-outlined text-4xl text-[#7c3aed]/40 animate-pulse">dry_cleaning</span>
                        </div>
                        <div class="absolute top-md right-md bg-primary-container text-on-primary-container px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Upcoming</div>
                    </div>
                    <div class="p-md flex-1 flex flex-col justify-between space-y-md">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-on-surface/80 text-base leading-tight">Shoes Clinic &amp; Care</h4>
                            </div>
                            <div class="flex items-center text-outline/50 font-bold mb-2">
                                <span class="material-symbols-outlined text-[18px] mr-1">star</span>
                                <span class="text-label-md">-.-</span>
                            </div>
                            <p class="text-on-surface-variant text-xs line-clamp-2 leading-relaxed flex items-start">
                                <span class="material-symbols-outlined text-sm mr-1 mt-[2px] text-outline">location_on</span>
                                <span>Area Ampenan &amp; Cilinaya</span>
                            </p>
                        </div>
                        <div class="pt-md border-t border-outline-variant/40 flex justify-between items-center">
                            <div class="bg-surface-container px-md py-xs rounded-full">
                                <span class="text-on-surface-variant/70 font-bold text-xs">Tarif TBA</span>
                            </div>
                            <span class="text-outline text-label-md font-bold flex items-center gap-base select-none">
                                <span>Segera Hadir</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Card 4 -->
                <div class="mitra-item group bg-surface rounded-xl overflow-hidden shadow-sm border border-outline-variant/60 opacity-80 flex flex-col justify-between"
                     data-baju="true" 
                     data-sepatu="true" 
                     data-satuan="true" 
                     data-address="sekarbela ampenan cilinaya pagutan ampenan mataram ampenan kekalik">
                    <div class="h-48 relative overflow-hidden bg-surface-container flex items-center justify-center">
                        <div class="w-full h-full bg-gradient-to-br from-primary-fixed/30 to-secondary-fixed/30 flex flex-col items-center justify-center text-outline gap-xs">
                            <span class="material-symbols-outlined text-4xl text-amber-600/40 animate-pulse">handshake</span>
                        </div>
                        <div class="absolute top-md right-md bg-primary-container text-on-primary-container px-sm py-[2px] rounded-full text-label-sm font-bold shadow-sm">Upcoming</div>
                    </div>
                    <div class="p-md flex-1 flex flex-col justify-between space-y-md">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-on-surface/80 text-base leading-tight">MataramWash Outlet #8</h4>
                            </div>
                            <div class="flex items-center text-outline/50 font-bold mb-2">
                                <span class="material-symbols-outlined text-[18px] mr-1">star</span>
                                <span class="text-label-md">-.-</span>
                            </div>
                            <p class="text-on-surface-variant text-xs line-clamp-2 leading-relaxed flex items-start">
                                <span class="material-symbols-outlined text-sm mr-1 mt-[2px] text-outline">location_on</span>
                                <span>Area Pagutan &amp; Mataram</span>
                            </p>
                        </div>
                        <div class="pt-md border-t border-outline-variant/40 flex justify-between items-center">
                            <div class="bg-surface-container px-md py-xs rounded-full">
                                <span class="text-on-surface-variant/70 font-bold text-xs">Tarif TBA</span>
                            </div>
                            <span class="text-outline text-label-md font-bold flex items-center gap-base select-none">
                                <span>Segera Hadir</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Empty search alert -->
                <div id="no-mitra-alert" class="col-span-full text-center py-12 bg-surface-container-lowest border border-outline-variant/60 rounded-2xl hidden">
                    <span class="material-symbols-outlined text-outline text-5xl mb-2">search_off</span>
                    <p class="text-on-surface-variant font-bold">Tidak ada mitra laundry yang cocok untuk pencarian ini.</p>
                </div>
            </div>
            <!-- Pagination Controls -->
            <div id="pagination-container" class="flex justify-center items-center gap-xs mt-xl pt-lg border-t border-outline-variant/30"></div>
        </div>
    </section>


</main>

<!-- Footer -->
<footer class="w-full bg-slate-950 text-slate-400 border-t border-slate-900 mt-xl">
    <div class="max-w-7xl mx-auto px-gutter py-xl grid grid-cols-1 md:grid-cols-4 gap-xl">
        <!-- Brand Section -->
        <div class="space-y-md">
            <div class="flex items-center gap-xs">
                <img alt="MataramWash Logo" class="h-10 w-10 object-contain" src="../logo.png?v=3">
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
                    <a href="../bantuan/bantuan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Bantuan &amp; FAQ</a>
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
                    <a href="#" onclick="setFilterCategory('baju')" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Laundry Kiloan</a>
                </li>
                <li>
                    <a href="#" onclick="setFilterCategory('satuan')" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Laundry Satuan</a>
                </li>
                <li>
                    <a href="#" onclick="setFilterCategory('sepatu')" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Cuci Sepatu Premium</a>
                </li>
                <li>
                    <a href="#" onclick="setFilterCategory('satuan')" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Express Delivery</a>
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
    // Filtering & Pagination Logic for Mitra List
    let currentPage = 1;
    const itemsPerPage = 8; // Menampilkan 8 mitra per halaman

    function applyFilters(resetPage = true) {
        if (resetPage) {
            currentPage = 1;
        }

        const locationVal = document.getElementById('filter-location').value.toLowerCase();
        const serviceVal = document.getElementById('filter-service').value;
        
        const items = document.querySelectorAll('.mitra-item');
        let visibleCount = 0;
        let matchedItems = [];
        
        // Define title and description updates
        const titleEl = document.getElementById('section-title');
        const subtitleEl = document.getElementById('section-subtitle');
        
        if (serviceVal === 'all') {
            titleEl.innerText = "Semua Mitra Laundry Terdekat";
            subtitleEl.innerText = "Menampilkan seluruh mitra laundry aktif di sekitar area kosan Anda.";
        } else if (serviceVal === 'baju') {
            titleEl.innerText = "Mitra Laundry Baju";
            subtitleEl.innerText = "Menyediakan layanan kiloan, setrika uap, dan layanan cuci kilat/express.";
        } else if (serviceVal === 'sepatu') {
            titleEl.innerText = "Mitra Cuci Sepatu & Tas";
            subtitleEl.innerText = "Spesialis perawatan sepatu (deep cleaning, unyellowing) dan tas premium.";
        } else if (serviceVal === 'satuan') {
            titleEl.innerText = "Mitra Laundry Satuan & Dry Cleaning";
            subtitleEl.innerText = "Menyediakan pembersihan bed cover, jaket denim/kulit, sprei, jas, & kebaya.";
        }

        items.forEach(item => {
            // 1. Service Filter Check
            let matchService = false;
            if (serviceVal === 'all') {
                matchService = true;
            } else if (serviceVal === 'baju') {
                matchService = item.getAttribute('data-baju') === 'true';
            } else if (serviceVal === 'sepatu') {
                matchService = item.getAttribute('data-sepatu') === 'true';
            } else if (serviceVal === 'satuan') {
                matchService = item.getAttribute('data-satuan') === 'true';
            }
            
            // 2. Location Filter Check
            let matchLocation = false;
            const address = item.getAttribute('data-address').toLowerCase();
            if (locationVal === 'all') {
                matchLocation = true;
            } else if (locationVal === 'sekarbela') {
                matchLocation = address.includes('sekarbela') || address.includes('kekalik') || address.includes('swasembada') || address.includes('swakarya');
            } else if (locationVal === 'ampenan') {
                matchLocation = address.includes('ampenan') || address.includes('saleh sungkar');
            } else if (locationVal === 'cilinaya') {
                matchLocation = address.includes('cilinaya') || address.includes('panca usaha');
            } else if (locationVal === 'pagutan') {
                matchLocation = address.includes('pagutan') || address.includes('bung karno');
            } else if (locationVal === 'mataram') {
                matchLocation = address.includes('pejanggik') || address.includes('airlangga') || (address.includes('mataram') && !address.includes('kekalik') && !address.includes('cilinaya'));
            }
            
            // Combine checks
            if (matchService && matchLocation) {
                matchedItems.push(item);
                visibleCount++;
            } else {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.95)';
                item.style.display = 'none';
            }
        });

        // Paginate matched items
        const totalPages = Math.ceil(matchedItems.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        matchedItems.forEach((item, index) => {
            const startIdx = (currentPage - 1) * itemsPerPage;
            const endIdx = currentPage * itemsPerPage - 1;
            if (index >= startIdx && index <= endIdx) {
                item.style.display = 'flex';
                // Trigger transition
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'scale(1)';
                }, 50);
            } else {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.95)';
                item.style.display = 'none';
            }
        });

        // Show/hide empty state
        const alertEl = document.getElementById('no-mitra-alert');
        if (visibleCount === 0) {
            setTimeout(() => {
                alertEl.classList.remove('hidden');
            }, 200);
        } else {
            alertEl.classList.add('hidden');
        }

        // Update count label
        document.getElementById('mitra-count').innerText = "Jumlah: " + visibleCount + " Mitra";

        // Render Pagination Controls
        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        const container = document.getElementById('pagination-container');
        if (!container) return;

        if (totalPages === 0) {
            container.innerHTML = '';
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        let html = '';

        // Previous Button
        if (currentPage > 1) {
            html += `<button type="button" onclick="changePage(${currentPage - 1})" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all cursor-pointer"><span class="material-symbols-outlined text-[20px]">chevron_left</span></button>`;
        } else {
            html += `<button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 text-outline-variant/40 cursor-not-allowed" disabled><span class="material-symbols-outlined text-[20px] opacity-40">chevron_left</span></button>`;
        }

        // Page Numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                html += `<button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl border border-primary bg-primary text-white font-bold transition-all">${i}</button>`;
            } else {
                html += `<button type="button" onclick="changePage(${i})" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all cursor-pointer">${i}</button>`;
            }
        }

        // Next Button
        if (currentPage < totalPages) {
            html += `<button type="button" onclick="changePage(${currentPage + 1})" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all cursor-pointer"><span class="material-symbols-outlined text-[20px]">chevron_right</span></button>`;
        } else {
            html += `<button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 text-outline-variant/40 cursor-not-allowed" disabled><span class="material-symbols-outlined text-[20px] opacity-40">chevron_right</span></button>`;
        }

        container.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        applyFilters(false);
        // Scroll to grid top smoothly
        document.getElementById('rekomendasi-mitra-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Toggle dropdown visibility
    function toggleDropdown(type) {
        const optionsEl = document.getElementById('options-' + type);
        const chevronEl = document.getElementById('chevron-' + type);
        if (!optionsEl) return;
        
        const isHidden = optionsEl.classList.contains('hidden');
        
        // Close all dropdowns first
        closeAllDropdowns();
        
        if (isHidden) {
            optionsEl.classList.remove('hidden');
            chevronEl.style.transform = 'rotate(180deg)';
        }
    }
    
    // Close all dropdowns
    function closeAllDropdowns() {
        ['location', 'service'].forEach(type => {
            const optionsEl = document.getElementById('options-' + type);
            const chevronEl = document.getElementById('chevron-' + type);
            if (optionsEl) optionsEl.classList.add('hidden');
            if (chevronEl) chevronEl.style.transform = 'rotate(0deg)';
        });
    }

    // Select an option
    function selectOption(type, value, text) {
        // Set hidden input value
        const inputEl = document.getElementById('filter-' + type);
        if (inputEl) inputEl.value = value;

        // Set trigger label text
        const labelEl = document.getElementById('label-' + type);
        if (labelEl) labelEl.textContent = text;

        // Update active checkmarks inside popover
        updateDropdownCheckmarks(type, value);

        // Close dropdown
        closeAllDropdowns();

        // Trigger existing filters
        applyFilters(true);
    }

    // Update checkmark visibility based on current selection
    function updateDropdownCheckmarks(type, activeValue) {
        const popover = document.getElementById('options-' + type);
        if (!popover) return;
        
        const checkmarks = popover.querySelectorAll('.check-icon');
        checkmarks.forEach(check => {
            if (check.getAttribute('data-val') === activeValue) {
                check.classList.remove('hidden');
            } else {
                check.classList.add('hidden');
            }
        });
    }

    // Click outside handler to close dropdowns
    document.addEventListener('click', (event) => {
        const isDropdownLocation = event.target.closest('#dropdown-location-container');
        const isDropdownService = event.target.closest('#dropdown-service-container');
        
        if (!isDropdownLocation && !isDropdownService) {
            closeAllDropdowns();
        }
    });

    // Helper function for footer links to trigger filtering
    function setFilterCategory(category) {
        let text = "Semua Tipe";
        if (category === 'baju') text = "Laundry Baju (Kiloan)";
        else if (category === 'sepatu') text = "Cuci Sepatu & Tas";
        else if (category === 'satuan') text = "Satuan & Dry Cleaning";
        
        selectOption('service', category, text);
        
        // Scroll to filters
        const filterBar = document.getElementById('filter-bar');
        if (filterBar) {
            filterBar.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Initial load filtering & pagination
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const serviceParam = urlParams.get('service') || 'all';
        const locationParam = urlParams.get('location') || 'all';
        
        let serviceText = "Semua Tipe";
        if (serviceParam === 'baju') serviceText = "Laundry Baju (Kiloan)";
        else if (serviceParam === 'sepatu') serviceText = "Cuci Sepatu & Tas";
        else if (serviceParam === 'satuan') serviceText = "Satuan & Dry Cleaning";
        
        let locationText = "Semua Kecamatan";
        if (locationParam === 'sekarbela') locationText = "Sekarbela / Kekalik";
        else if (locationParam === 'ampenan') locationText = "Ampenan";
        else if (locationParam === 'cilinaya') locationText = "Cilinaya";
        else if (locationParam === 'pagutan') locationText = "Pagutan";
        else if (locationParam === 'mataram') locationText = "Mataram (Pusat)";

        // Set values and labels
        const filterLoc = document.getElementById('filter-location');
        const labelLoc = document.getElementById('label-location');
        const filterServ = document.getElementById('filter-service');
        const labelServ = document.getElementById('label-service');

        if (filterLoc) filterLoc.value = locationParam;
        if (labelLoc) labelLoc.textContent = locationText;
        if (filterServ) filterServ.value = serviceParam;
        if (labelServ) labelServ.textContent = serviceText;

        updateDropdownCheckmarks('location', locationParam);
        updateDropdownCheckmarks('service', serviceParam);
        applyFilters(true);
    });

    // Mobile Navbar Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>
</body>
</html>
