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
    <title>Layanan Mitra - KosanLaundry</title>
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
                <img alt="KosanLaundry Logo" class="h-10 w-10 object-contain" src="../logo.png?v=3">
                <span class="">KosanLaundry</span>
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
                <span>Direktori Layanan KosanLaundry</span>
            </div>
            <h1 class="text-display-lg text-primary font-display-lg leading-tight md:text-5xl">
                Temukan Mitra Laundry Terbaik
            </h1>
            <p class="text-body-lg text-on-surface-variant max-w-xl mx-auto">
                Cari kecamatan, pilih jenis layanan, dan masukkan rentang tarif di bawah untuk menemukan mitra laundry yang paling pas.
            </p>
        </div>
        <!-- Decorative Circle Blurs -->
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-primary-fixed-dim/15 rounded-full blur-3xl opacity-55"></div>
        <div class="absolute -bottom-40 -right-40 w-80 h-80 bg-secondary-fixed-dim/15 rounded-full blur-3xl opacity-55"></div>
    </section>

    <!-- Horizontal Search/Filter Bar (KosanLaundry Design System Style) -->
    <section class="max-w-4xl mx-auto px-container-margin -mt-8 relative z-20">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-md p-6 lg:py-4 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-0 lg:divide-x lg:divide-outline-variant">
                
                <!-- Segment 1: LOKASI (Semua Kecamatan) -->
                <div class="flex flex-col space-y-1 lg:px-6">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-primary select-none">LOKASI</span>
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">location_on</span>
                        <select id="filter-location" class="w-full bg-transparent border-0 p-0 text-on-surface font-semibold focus:ring-0 focus:outline-none text-base cursor-pointer" onchange="applyFilters()">
                            <option value="all">Semua Kecamatan</option>
                            <option value="sekarbela">Sekarbela / Kekalik</option>
                            <option value="ampenan">Ampenan</option>
                            <option value="cilinaya">Cilinaya</option>
                            <option value="pagutan">Pagutan</option>
                            <option value="mataram">Mataram (Pusat)</option>
                        </select>
                    </div>
                </div>

                <!-- Segment 2: TIPE LAYANAN -->
                <div class="flex flex-col space-y-1 lg:px-6">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-primary select-none">TIPE LAYANAN</span>
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">local_laundry_service</span>
                        <select id="filter-service" class="w-full bg-transparent border-0 p-0 text-on-surface font-semibold focus:ring-0 focus:outline-none text-base cursor-pointer" onchange="applyFilters()">
                            <option value="all">Semua Tipe</option>
                            <option value="baju">Laundry Baju (Kiloan)</option>
                            <option value="sepatu">Cuci Sepatu &amp; Tas</option>
                            <option value="satuan">Satuan &amp; Dry Cleaning</option>
                        </select>
                    </div>
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
<footer class="w-full py-xl px-gutter grid grid-cols-1 md:grid-cols-4 gap-lg bg-surface-container-highest">
    <div class="space-y-md">
        <div class="flex items-center gap-xs">
            <img alt="KosanLaundry Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
            <span class="text-headline-sm font-headline-md font-bold text-primary">KosanLaundry</span>
        </div>
        <p class="text-on-surface-variant font-body-md">Freshness delivered to your doorstep. Laundry solusi cerdas untuk hidup lebih produktif.</p>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Company</h5>
        <ul class="space-y-xs">
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Privacy Policy</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Terms of Service</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Contact Us</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">FAQ</a></li>
        </ul>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Layanan</h5>
        <ul class="space-y-xs">
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#" onclick="setFilterCategory('baju')">Laundry Baju</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#" onclick="setFilterCategory('satuan')">Laundry Satuan</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#" onclick="setFilterCategory('sepatu')">Cuci Sepatu</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#" onclick="setFilterCategory('satuan')">Dry Cleaning</a></li>
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
    // Filtering & Pagination Logic for Mitra List
    let currentPage = 1;
    const itemsPerPage = 4; // Menampilkan 4 mitra per halaman

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

        if (totalPages <= 1) {
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

    // Helper function for footer links to trigger filtering
    function setFilterCategory(category) {
        document.getElementById('filter-service').value = category;
        applyFilters();
        // Scroll to filters
        document.getElementById('filter-location').scrollIntoView({ behavior: 'smooth' });
    }

    // Initial load filtering & pagination
    document.addEventListener('DOMContentLoaded', () => {
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
