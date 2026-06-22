<?php
// detail_template.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db_connect.php';

if (!isset($nama_mitra)) {
    die("Nama mitra tidak ditentukan.");
}

// Fetch partner details
try {
    $stmt = $pdo->prepare("SELECT * FROM mitra_laundry WHERE nama_mitra = ?");
    $stmt->execute([$nama_mitra]);
    $mitra = $stmt->fetch();
    
    if (!$mitra) {
        // Fallback search if name is slightly different
        $stmt = $pdo->prepare("SELECT * FROM mitra_laundry WHERE nama_mitra LIKE ?");
        $stmt->execute(['%' . $nama_mitra . '%']);
        $mitra = $stmt->fetch();
    }
    
    if (!$mitra) {
        die("Mitra '" . htmlspecialchars($nama_mitra) . "' tidak ditemukan di database.");
    }
} catch (PDOException $e) {
    die("Terjadi kesalahan database: " . $e->getMessage());
}

$is_self_service = (strpos(strtolower($mitra['nama_mitra']), 'washtra') !== false);

$is_logged_in = isset($_SESSION['user_id']);
$dashboard_url = "../dashboard.php";
$login_url = "../login/login.php";
$daftar_url = "../login/daftar.php";
$logout_url = "../logout.php";
$edit_profile_url = "../user/edit_profile.php";
$notifikasi_url = "../user/notifikasi.php";

$id_mitra = $mitra['id'];
$alamat = $mitra['alamat'];
$no_telp = $mitra['no_telp'] ?? '+62 812-3456-7890';
$rating = number_format($mitra['rating'], 1);
$status_buka = $mitra['status_buka'];
$harga_per_kg = $mitra['harga_per_kg'];
$jam_buka = $mitra['jam_buka'] ?? '08:00 - 21:00';
$foto_toko = $mitra['foto_toko'] ?? 'uploads/mitra_1.png';

// Resolve image path relative to the Mitra laundry directory
if (strpos($foto_toko, 'uploads/') === 0) {
    $foto_toko_path = '../' . $foto_toko;
} else {
    $foto_toko_path = '../uploads/' . $foto_toko;
}

// Check if image exists, fallback to placeholder if not
if (!file_exists('../' . $foto_toko) && !file_exists($foto_toko_path)) {
    $foto_toko_path = '../uploads/mitra_1.png';
}

// Calculate service pricing based on base harga_per_kg (allow custom override)
$harga_lipat_reguler = isset($custom_harga_lipat_reguler) ? $custom_harga_lipat_reguler : $harga_per_kg;
$harga_setrika_reguler = isset($custom_harga_setrika_reguler) ? $custom_harga_setrika_reguler : $harga_per_kg + 2000;
$harga_setrika_saja = isset($custom_harga_setrika_saja) ? $custom_harga_setrika_saja : $harga_per_kg - 2000;
if (!isset($custom_harga_setrika_saja) && $harga_setrika_saja < 3000) {
    $harga_setrika_saja = 3000;
}

// Express pricing
$harga_express_lipat = isset($custom_harga_express_lipat) ? $custom_harga_express_lipat : $harga_per_kg + 4000;
$harga_express_setrika = isset($custom_harga_express_setrika) ? $custom_harga_express_setrika : $harga_per_kg + 6000;

// Delivery options text overrides
$delivery_label = isset($custom_delivery_label) ? $custom_delivery_label : ($is_self_service ? 'Layanan Mandiri' : 'Jemput-Antar');
$delivery_advice = isset($custom_delivery_advice) ? $custom_delivery_advice : ($is_self_service ? 'Cuci Mandiri di Toko' : 'Gratis ongkir < 2km');

// Satuan pricing
$harga_satuan_jaket = 15000;
$harga_satuan_selimut = 20000;
$harga_satuan_bed_cover = 30000;

// Render different reviews depending on the shop
$reviews_pool = [
    [
        'user' => 'r*****a',
        'initials' => 'RA',
        'stars' => 5,
        'date' => '2026-06-18 14:30',
        'layanan' => 'Cuci Lipat Reguler',
        'comment' => 'Sangat puas! Wangi parfumnya tahan lama dan lipatannya rapi banget. Jemput antarnya juga on time.',
        'photos' => ['../uploads/mitra_1.png', '../uploads/mitra_2.png'],
        'response' => 'Terima kasih Kak Rizky atas ulasannya! Senang bisa membantu. Ditunggu orderan selanjutnya ya Kak! :)'
    ],
    [
        'user' => 'n*****a',
        'initials' => 'NS',
        'stars' => 5,
        'date' => '2026-06-17 09:15',
        'layanan' => 'Cuci Express',
        'comment' => 'Layanan express-nya benar-benar membantu saat butuh cepat. Pakaian tetap wangi walau prosesnya cepat.',
        'photos' => [],
        'response' => ''
    ],
    [
        'user' => 'a*****d',
        'initials' => 'AD',
        'stars' => 4,
        'date' => '2026-06-15 11:20',
        'layanan' => 'Cuci Setrika Reguler',
        'comment' => 'Setrikanya rapi sekali, baju langsung siap pakai. Pelayanan ramah.',
        'photos' => [],
        'response' => 'Terima kasih ulasannya Kak! Kami selalu menjaga kualitas untuk kepuasan pelanggan.'
    ]
];

// Custom logo per partner (using nice icons or images)
$partner_logos = [
    1 => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA8hpXrKjHo_GmheUautuzBqIVrV-ZcNeHdG_n6cNAhLNvH5TESFu8QFA38cKC9QTFMkP9AqsLlbxD9A7jMkpS5bDc1cvQg3pEM6-9FTKgyOKmb4Um8Fu-9J2HLl3o_dYOultZMk25RmnXZTxvJUzFUITxPtRha8Uc3v8FBB4hpL8Yg3dNywxyYn-LvAuzpqu0NAGSJDuHH-31Z0nx7ju563gHnFklat65bjZNPshB6-PtyB8hTjl6sJoGIuQyakmczeIv0E1cBZPc',
    2 => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAQ6mXwN3q2Y8yD3qF3t5v5HhN4K5T8t0_t7v7k8z5u8v7p9m8r8s8t8u8v8w8x8y8z',
    3 => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB0rpXrPqtfvEqJ19EMrjwsBRpqn7paL6McNQXc_AeZ78BwyHSSmKt_N9a-pEBC6PvvbZRtKoI07cVjqA3aKd-0wruDmaHKuUBB9fenszoyd-Fq0jHKza9wO3pbGdUfjNMvMFxqfy0S9JDmwu9OXDVQSDsmHF6-l22EuomzPk4YbKTo5mYXjyu7uX2BqA7u8YkA7oDeM1zDOWnXWEFo7BzJ-_TYdRlHHR-Ltk5KCizaLlGdOMLr6KJpDNs6FCLN7sCYLSVxt2THjP8',
    4 => 'https://lh3.googleusercontent.com/aida-public/AB6AXuASon9t6vTl9JcuyZQ-2nlWr7A1mGGc888rBgDFJaXybyQnO01ipIg3B8nVMDH1lhHBpTBOq-bV4Qnmq0R-aEqRdZFImodHQlPAmg-2CQ1cUakHpTyt2mxGA_qsGwHoz_73wTqWffkr6LLK216Sij2GsOZF_EOkxQcel3Go8bL0BF6BVky1yJNMoMqGaeUn8A4CMHCWPrQQiOpAQekbIoSP7HLYV2Dy3Jp3H8Gzxn6imB5W9Ogh5isnwFOri0QFjgN07GP95oxqqV4',
    5 => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAujDASjotNPhxVCbFSMamJHuY0tt4fH4wHzInaJo76NKY8h96K_hXs-SA3TO25Bk9GuHaSOAlZvhZbjkbs39cziUvC94KRm0NJhIG60l0lCYUIJEUFeq0IWNwgdtnwzPPLEsGh5WEq-UcoP_JfCRWvwn9kcvt06eGJitMTyRv3OCnRC2qjryAwPwir7UqK_NzpS8Uvnw6YVv581zpb8LKXDamalit1xNglj8ZYlclmFFB8b-VXAk2jlRRj9C76Sk6pYTOMHHwIJZ4',
    6 => '../uploads/logo_washtra.png'
];

$logo_url = $partner_logos[$id_mitra] ?? $partner_logos[1];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($mitra['nama_mitra']); ?> - Profil Partner | KosanLaundry</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    
    <!-- Leaflet.js for Interactive Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .fill-icon {
            font-variation-settings: 'FILL' 1;
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .tab-btn.active {
            border-bottom-color: #0058be;
            color: #0058be;
            font-weight: 700;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "secondary-fixed": "#71f8e4",
                      "primary-fixed-dim": "#adc6ff",
                      "on-secondary": "#ffffff",
                      "secondary-container": "#6df5e1",
                      "primary-fixed": "#d8e2ff",
                      "inverse-on-surface": "#ebf1ff",
                      "surface-container": "#e7eefe",
                      "on-primary-fixed-variant": "#004395",
                      "on-secondary-fixed": "#00201c",
                      "secondary-fixed-dim": "#4fdbc8",
                      "secondary": "#006b5f",
                      "on-tertiary-container": "#fffbff",
                      "on-tertiary": "#ffffff",
                      "outline": "#727785",
                      "on-surface-variant": "#424754",
                      "on-primary-container": "#fefcff",
                      "on-error-container": "#93000a",
                      "surface-dim": "#d3daea",
                      "primary": "#0058be",
                      "on-secondary-container": "#006f64",
                      "on-background": "#151c27",
                      "surface-variant": "#dce2f3",
                      "surface": "#f9f9ff",
                      "error": "#ba1a1a",
                      "tertiary-container": "#a36700",
                      "on-tertiary-fixed": "#2a1700",
                      "surface-container-lowest": "#ffffff",
                      "error-container": "#ffdad6",
                      "on-primary-fixed": "#001a42",
                      "on-surface": "#151c27",
                      "on-error": "#ffffff",
                      "tertiary-fixed": "#ffddb8",
                      "background": "#f9f9ff",
                      "surface-container-low": "#f0f3ff",
                      "tertiary": "#825100",
                      "primary-container": "#2170e4",
                      "surface-container-high": "#e2e8f8",
                      "surface-bright": "#f9f9ff",
                      "on-secondary-fixed-variant": "#005048",
                      "inverse-surface": "#2a313d",
                      "surface-tint": "#005ac2",
                      "inverse-primary": "#adc6ff",
                      "tertiary-fixed-dim": "#ffb95f",
                      "surface-container-highest": "#dce2f3",
                      "on-tertiary-fixed-variant": "#653e00",
                      "outline-variant": "#c2c6d6",
                      "on-primary": "#ffffff"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "base": "4px",
                      "sm": "12px",
                      "lg": "24px",
                      "xl": "32px",
                      "md": "16px",
                      "xs": "8px",
                      "gutter": "16px",
                      "container-margin": "20px"
              },
              "fontFamily": {
                      "label-md": ["Inter"],
                      "display-lg": ["Inter"],
                      "body-md": ["Inter"],
                      "label-sm": ["Inter"],
                      "headline-md": ["Inter"],
                      "headline-lg-mobile": ["Inter"],
                      "headline-lg": ["Inter"],
                      "body-lg": ["Inter"]
              },
              "fontSize": {
                      "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                      "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                      "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                      "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                      "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                      "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                      "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                      "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}]
              }
            },
          },
        }
      </script>
</head>
<body class="bg-background text-on-surface font-body-md overflow-x-hidden">

<!-- TopNavBar -->
<nav class="sticky top-0 z-50 bg-surface shadow-sm w-full transition-all duration-300">
    <div class="flex justify-between items-center px-lg py-md w-full mx-auto max-w-6xl">
        <div class="flex items-center gap-md">
            <a href="../index.php" class="flex items-center gap-xs">
                <img alt="KosanLaundry Logo" class="h-10 w-10 object-contain" src="../logo.png?v=3"/>
                <span class="font-headline-md text-headline-md font-bold text-primary">KosanLaundry</span>
            </a>
        </div>
        <div class="hidden md:flex gap-lg items-center">
            <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md text-label-md" href="../index.php">Beranda</a>
            <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-md text-label-md" href="#">Layanan</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md text-label-md" href="../lokasi/locations.php">Lokasi</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md text-label-md" href="../bantuan/bantuan.php">Bantuan</a>
        </div>
        <div class="flex items-center gap-md">
            <div class="hidden md:flex items-center bg-surface-container rounded-full px-md py-xs">
                <span class="material-symbols-outlined text-outline text-[20px]">search</span>
                <input class="bg-transparent border-none focus:ring-0 text-label-md font-label-md w-40" placeholder="Cari laundry..." type="text"/>
            </div>
            
            <?php if ($is_logged_in): ?>
                <!-- Profile Indicator with Hover Dropdown -->
                <div class="relative group" id="profile-dropdown-container">
                    <button class="flex items-center justify-center w-10 h-10 rounded-full border border-outline-variant focus:outline-none select-none overflow-hidden bg-primary text-on-primary font-bold text-body-lg shadow-sm hover:scale-105 transition-all">
                        <?php if (!empty($_SESSION['profile_pic'])): ?>
                            <img src="../<?= htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-xs w-48 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-xs z-50 transform origin-top-right scale-95 opacity-0 pointer-events-none group-hover:scale-100 group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200">
                        <a href="<?= $edit_profile_url; ?>" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">edit</span>
                            <span>Edit Profil</span>
                        </a>
                        <a href="#" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">history</span>
                            <span>Riwayat Pesanan</span>
                        </a>
                        <a href="<?= $notifikasi_url; ?>" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">notifications</span>
                            <span>Notifikasi</span>
                        </a>
                        <div class="border-t border-outline-variant my-xs"></div>
                        <a href="<?= $logout_url; ?>" class="flex items-center gap-xs px-md py-sm text-body-md text-error hover:bg-error-container/10 transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-error">logout</span>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center space-x-xs sm:space-x-sm">
                    <button onclick="window.location.href='<?= $login_url; ?>'" class="px-lg py-xs border-2 border-primary text-primary rounded-xl font-bold hover:bg-primary-fixed transition-all active:scale-95 duration-150 text-sm">Masuk</button>
                    <button onclick="window.location.href='<?= $daftar_url; ?>'" class="px-lg py-xs bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-all active:scale-95 duration-150 text-sm shadow-sm">Daftar</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="mx-auto px-container-margin md:px-lg py-xl max-w-6xl">
    <!-- Header Section -->
    <header class="relative rounded-xl overflow-hidden mb-xl bg-surface-container-lowest shadow-sm">
        <div class="h-64 md:h-96 w-full relative">
            <div class="w-full h-full bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($foto_toko_path); ?>')"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 to-transparent"></div>
            <div class="absolute bottom-lg left-lg right-lg flex flex-col md:flex-row md:items-end justify-between gap-md">
                <div class="flex items-center gap-md">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-xl bg-white p-xs shadow-lg flex items-center justify-center overflow-hidden">
                        <img alt="Partner Logo" class="w-full h-full object-contain" src="<?= htmlspecialchars($logo_url); ?>"/>
                    </div>
                    <div class="text-white">
                        <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg mb-xs"><?= htmlspecialchars($mitra['nama_mitra']); ?></h1>
                        <div class="flex items-center gap-sm">
                            <div class="flex items-center gap-1 bg-white/20 backdrop-blur-md px-xs py-1 rounded-lg">
                                <span class="material-symbols-outlined text-yellow-400 text-[18px] fill-icon">star</span>
                                <span class="text-label-md font-bold"><?= $rating; ?></span>
                            </div>
                            <?php if ($status_buka): ?>
                                <span class="bg-secondary text-on-secondary px-md py-1 rounded-full text-label-sm font-bold">Buka</span>
                            <?php else: ?>
                                <span class="bg-error text-on-error px-md py-1 rounded-full text-label-sm font-bold">Tutup</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Service Information Bar -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-md mb-xl">
        <div class="bg-surface-container-lowest p-md rounded-xl border border-outline-variant flex items-center gap-md shadow-sm">
            <div class="bg-primary/10 p-md rounded-xl">
                <span class="material-symbols-outlined text-primary text-[32px]">local_shipping</span>
            </div>
            <div>
                <div class="flex items-center gap-sm mb-xs">
                    <p class="font-label-md text-on-surface-variant uppercase tracking-wider"><?= htmlspecialchars($delivery_label); ?></p>
                    <?php if ($is_self_service): ?>
                        <span class="bg-amber-100 text-amber-800 px-xs py-[2px] rounded text-[10px] font-bold">Self-Service</span>
                    <?php else: ?>
                        <span class="bg-secondary-container text-on-secondary-container px-xs py-[2px] rounded text-[10px] font-bold">Tersedia</span>
                    <?php endif; ?>
                </div>
                <p class="font-headline-md text-on-surface"><?= htmlspecialchars($delivery_advice); ?></p>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-md rounded-xl border border-outline-variant flex items-center gap-md shadow-sm">
            <div class="bg-primary/10 p-md rounded-xl">
                <span class="material-symbols-outlined text-primary text-[32px]">schedule</span>
            </div>
            <div>
                <p class="font-label-md text-on-surface-variant uppercase tracking-wider mb-xs">Estimasi Pengerjaan</p>
                <p class="font-headline-md text-on-surface"><?= $is_self_service ? '1 - 2 Jam (Selesai Langsung)' : '1 - 3 Jam'; ?></p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-xl">
        <!-- Service Packages -->
        <div class="lg:col-span-2 space-y-xl">
            <div>
                <h2 class="font-headline-md text-on-surface mb-md">Menu Layanan</h2>
                <!-- Tabs -->
                <div class="flex gap-md border-b border-outline-variant mb-lg overflow-x-auto pb-1">
                    <?php if (isset($custom_tabs) && is_array($custom_tabs)): ?>
                        <?php $first = true; foreach ($custom_tabs as $tab_id => $tab_label): ?>
                            <button id="tab-<?= $tab_id; ?>" onclick="switchTab('<?= $tab_id; ?>')" class="tab-btn px-md py-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap <?= $first ? 'active' : ''; ?>"><?= htmlspecialchars($tab_label); ?></button>
                        <?php $first = false; endforeach; ?>
                    <?php elseif ($is_self_service): ?>
                        <button id="tab-self" onclick="switchTab('self')" class="tab-btn px-md py-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap active">Self Service</button>
                        <button id="tab-facility" onclick="switchTab('facility')" class="tab-btn px-md py-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Fasilitas & Keunggulan</button>
                    <?php else: ?>
                        <button id="tab-kiloan" onclick="switchTab('kiloan')" class="tab-btn px-md py-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap active">Cuci Kiloan</button>
                        <button id="tab-satuan" onclick="switchTab('satuan')" class="tab-btn px-md py-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Cuci Satuan</button>
                        <button id="tab-express" onclick="switchTab('express')" class="tab-btn px-md py-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap">Cuci Express</button>
                    <?php endif; ?>
                </div>
                
                <!-- Items Grid -->
                <?php if (isset($custom_grids_html)): ?>
                    <?= $custom_grids_html; ?>
                <?php elseif ($is_self_service): ?>
                    <!-- Grid: Self Service -->
                    <div id="grid-self" class="grid-content grid grid-cols-1 md:grid-cols-2 gap-md">
                        <!-- Item 1 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Mandiri (Self Service Wash)</h3>
                                    <span class="text-primary font-bold text-lg">Rp 15.000/siklus</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Mencuci pakaian Anda sendiri dengan mesin cuci koin premium tanpa timbang. Durasi pengerjaan ± 30 menit.</p>
                            </div>
                            <button onclick="openOrderModal('Self Service Wash', 15000, 'siklus')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <!-- Item 2 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Keringkan Mandiri (Self Service Dry)</h3>
                                    <span class="text-primary font-bold text-lg">Rp 15.000/siklus</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Mengeringkan pakaian Anda sendiri dengan mesin pengering profesional. Pakaian langsung kering 100% siap pakai. Durasi ± 30 menit.</p>
                            </div>
                            <button onclick="openOrderModal('Self Service Dry', 15000, 'siklus')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <!-- Item 3 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Paket Lengkap Mandiri (Wash & Dry)</h3>
                                    <span class="text-primary font-bold text-lg">Rp 30.000/paket</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Paket lengkap mencuci dan mengeringkan pakaian 100% tanpa timbang secara mandiri. Total pengerjaan hanya 1 jam.</p>
                            </div>
                            <button onclick="openOrderModal('Paket Lengkap Wash & Dry', 30000, 'paket')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                    </div>
                    
                    <!-- Grid: Facility -->
                    <div id="grid-facility" class="grid-content hidden space-y-md">
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant shadow-sm space-y-sm">
                            <h3 class="font-headline-md text-[20px] text-primary font-bold">Kenapa Memilih WashTra?</h3>
                            <ul class="space-y-sm text-body-md text-on-surface-variant">
                                <li class="flex items-start gap-md">
                                    <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                                    <span><strong>Laundry Ekspress Self Service Pertama</strong> di Mataram.</span>
                                </li>
                                <li class="flex items-start gap-md">
                                    <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                                    <span><strong>Mencuci Tanpa Timbang:</strong> Bebas mencuci pakaian sebanyak kapasitas mesin cuci tanpa khawatir berat timbangan.</span>
                                </li>
                                <li class="flex items-start gap-md">
                                    <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                                    <span><strong>Mencuci Hanya 15 Ribu:</strong> Sangat hemat, bersahabat dengan kantong mahasiswa.</span>
                                </li>
                                <li class="flex items-start gap-md">
                                    <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                                    <span><strong>Privasi Terjaga:</strong> Pengerjaan dikerjakan sendiri oleh konsumen sehingga pakaian dalam atau pakaian sensitif aman tidak tercampur atau tersentuh orang lain.</span>
                                </li>
                                <li class="flex items-start gap-md">
                                    <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                                    <span><strong>Ruang Tunggu Premium:</strong> Sambil menunggu, Anda bisa mengerjakan tugas kuliah atau bersantai di ruangan ber-AC dengan fasilitas Wifi gratis berkecepatan tinggi.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Grid 1: Kiloan -->
                    <div id="grid-kiloan" class="grid-content grid grid-cols-1 md:grid-cols-2 gap-md">
                        <!-- Item 1 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Lipat Reguler</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_lipat_reguler, 0, ',', '.'); ?>/kg</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Layanan cuci bersih dan lipat rapi tanpa setrika. Cocok untuk kebutuhan sehari-hari.</p>
                            </div>
                            <button onclick="openOrderModal('Cuci Lipat Reguler', <?= $harga_lipat_reguler; ?>, 'kg')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <!-- Item 2 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Setrika Reguler</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_setrika_reguler, 0, ',', '.'); ?>/kg</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Layanan cuci bersih, dikeringkan, dan disetrika licin menggunakan uap.</p>
                            </div>
                            <button onclick="openOrderModal('Cuci Setrika Reguler', <?= $harga_setrika_reguler; ?>, 'kg')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <!-- Item 3 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Setrika Saja</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_setrika_saja, 0, ',', '.'); ?>/kg</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Hanya layanan setrika uap untuk pakaian yang sudah Anda cuci sendiri.</p>
                            </div>
                            <button onclick="openOrderModal('Setrika Saja', <?= $harga_setrika_saja; ?>, 'kg')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <?php if (isset($custom_harga_pengeringan)): ?>
                            <!-- Item 4: Pengeringan -->
                            <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start mb-sm">
                                        <h3 class="font-headline-md text-[20px] text-on-surface">Pengeringan</h3>
                                        <span class="text-primary font-bold text-lg">Rp <?= number_format($custom_harga_pengeringan, 0, ',', '.'); ?>/kg</span>
                                    </div>
                                    <p class="text-on-surface-variant text-body-md mb-lg">Layanan pengeringan pakaian basah menggunakan mesin pengering komersial.</p>
                                </div>
                                <button onclick="openOrderModal('Pengeringan', <?= $custom_harga_pengeringan; ?>, 'kg')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                    <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Grid 2: Satuan -->
                    <div id="grid-satuan" class="grid-content hidden grid grid-cols-1 md:grid-cols-2 gap-md">
                        <!-- Item 1 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Satuan Jaket</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_satuan_jaket, 0, ',', '.'); ?>/pcs</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Layanan cuci satuan jaket tebal/kulit/denim dengan perawatan khusus serat kain.</p>
                            </div>
                            <button onclick="openOrderModal('Cuci Satuan Jaket', <?= $harga_satuan_jaket; ?>, 'pcs')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <!-- Item 2 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Satuan Selimut</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_satuan_selimut, 0, ',', '.'); ?>/pcs</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Layanan cuci selimut/bed cover ukuran sedang agar wangi dan bebas tungau.</p>
                            </div>
                            <button onclick="openOrderModal('Cuci Satuan Selimut', <?= $harga_satuan_selimut; ?>, 'pcs')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <!-- Item 3 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Satuan Bed Cover</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_satuan_bed_cover, 0, ',', '.'); ?>/pcs</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Layanan cuci bed cover jumbo lengkap dengan proses disinfeksi uap panas.</p>
                            </div>
                            <button onclick="openOrderModal('Cuci Satuan Bed Cover', <?= $harga_satuan_bed_cover; ?>, 'pcs')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                    </div>

                    <!-- Grid 3: Express -->
                    <div id="grid-express" class="grid-content hidden grid grid-cols-1 md:grid-cols-2 gap-md">
                        <!-- Item 1 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Lipat Express</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_express_lipat, 0, ',', '.'); ?>/kg</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Cuci bersih & lipat kilat selesai dalam 6 jam. Solusi darurat baju bersih.</p>
                            </div>
                            <button onclick="openOrderModal('Cuci Lipat Express', <?= $harga_express_lipat; ?>, 'kg')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                        <!-- Item 2 -->
                        <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Setrika Express</h3>
                                    <span class="text-primary font-bold text-lg">Rp <?= number_format($harga_express_setrika, 0, ',', '.'); ?>/kg</span>
                                </div>
                                <p class="text-on-surface-variant text-body-md mb-lg">Cuci, setrika licin dengan uap, wangi segar selesai dalam 6 jam.</p>
                            </div>
                            <button onclick="openOrderModal('Cuci Setrika Express', <?= $harga_express_setrika; ?>, 'kg')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                                <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div> <!-- End of lg:col-span-2 Left Column -->
        
        <!-- Sidebar Info -->
        <div class="lg:col-span-1 lg:mt-[48px]">
            <h2 class="font-headline-md text-on-surface mb-md flex items-center gap-xs">
                <span class="material-symbols-outlined text-primary text-[28px]">store</span>
                Informasi Toko
            </h2>
            <!-- Location & Shop Info Card -->
            <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant shadow-sm space-y-md">
                
                <!-- Interactive Map Container -->
                <div class="aspect-video bg-surface-container rounded-lg overflow-hidden relative shadow-inner border border-outline-variant/30">
                    <div id="partner-map" class="w-full h-full z-10"></div>
                </div>
                
                <div class="space-y-sm">
                    <!-- Address Block -->
                    <div class="flex items-start justify-between p-sm bg-surface-container/30 hover:bg-surface-container/50 rounded-xl transition-all group">
                        <div class="flex gap-sm items-start">
                            <span class="material-symbols-outlined text-primary mt-[2px] text-[20px]">location_on</span>
                            <div>
                                <p class="text-[10px] text-outline leading-none mb-[4px] uppercase font-bold tracking-wider">Alamat Lengkap</p>
                                <p class="text-body-md text-on-surface text-sm leading-relaxed pr-xs"><?= htmlspecialchars($alamat); ?></p>
                            </div>
                        </div>
                        <button onclick="navigator.clipboard.writeText('<?= addslashes($alamat); ?>').then(() => alert('Alamat disalin!'))" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-on-surface flex items-center justify-center transition-all shrink-0" title="Salin Alamat">
                            <span class="material-symbols-outlined text-[16px]">content_copy</span>
                        </button>
                    </div>

                    <!-- Phone Block -->
                    <div class="flex items-center justify-between p-sm bg-surface-container/30 hover:bg-surface-container/50 rounded-xl transition-all group">
                        <div class="flex gap-sm items-center">
                            <span class="material-symbols-outlined text-primary text-[20px]">phone</span>
                            <div>
                                <p class="text-[10px] text-outline leading-none mb-[2px] uppercase font-bold tracking-wider">Nomor Telepon</p>
                                <p class="text-body-md text-on-surface font-semibold text-sm"><?= htmlspecialchars($no_telp); ?></p>
                            </div>
                        </div>
                        <div class="flex gap-xs">
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $no_telp); ?>" class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all" title="Panggil">
                                <span class="material-symbols-outlined text-[18px]">call</span>
                            </a>
                            <?php 
                            $wa_number = preg_replace('/[^0-9]/', '', $no_telp);
                            if (strpos($wa_number, '08') === 0) {
                                $wa_number = '628' . substr($wa_number, 2);
                            }
                            ?>
                            <a href="https://wa.me/<?= $wa_number; ?>" target="_blank" class="w-8 h-8 rounded-lg bg-green-500/10 text-green-600 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all" title="WhatsApp">
                                <span class="material-symbols-outlined text-[18px]">chat</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <a href="https://maps.google.com/?q=<?= $mitra['latitude']; ?>,<?= $mitra['longitude']; ?>" target="_blank" class="flex items-center justify-center gap-xs w-full text-center py-sm bg-primary text-on-primary rounded-xl font-bold hover:brightness-110 active:scale-[0.98] transition-all shadow-md shadow-primary/10">
                    <span class="material-symbols-outlined text-[20px]">directions</span>
                    Petunjuk Arah di Maps
                </a>

                <!-- Jam Operasional Segment -->
                <div class="border-t border-outline-variant/60 pt-md mt-md">
                    <h4 class="font-headline-md text-[14px] text-on-surface font-bold mb-xs flex items-center gap-xs">
                        <span class="material-symbols-outlined text-primary text-[18px]">schedule</span>
                        Jam Operasional
                    </h4>
                    <div class="space-y-xs text-sm">
                        <?php if (isset($jam_operasional_html)): ?>
                            <?= $jam_operasional_html; ?>
                        <?php else: ?>
                            <div class="flex justify-between p-xs hover:bg-surface-container/30 rounded-lg transition-colors">
                                <span class="text-on-surface-variant">Senin - Jumat</span>
                                <span class="font-bold text-on-surface"><?= $jam_buka; ?></span>
                            </div>
                            <div class="flex justify-between p-xs hover:bg-surface-container/30 rounded-lg transition-colors">
                                <span class="text-on-surface-variant">Sabtu</span>
                                <span class="font-bold text-on-surface"><?= $jam_buka; ?></span>
                            </div>
                            <div class="flex justify-between p-xs hover:bg-surface-container/30 rounded-lg transition-colors">
                                <span class="text-error font-bold">Minggu</span>
                                <span class="text-error font-bold">Tutup</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div> <!-- End of lg:col-span-1 Right Column -->
    </div> <!-- End of Main Grid -->

    <!-- Reviews & Ratings Section (Full Width) -->
    <div class="mt-2xl border-t border-outline-variant/60 pt-xl">
        <div class="mb-lg">
            <h2 class="font-headline-md text-on-surface">Ulasan &amp; Rating</h2>
        </div>
        
        <?php
        $count_all = count($reviews_pool);
        $count_5 = 0;
        $count_4 = 0;
        $count_3 = 0;
        $count_photo = 0;
        $count_comment = 0;

        foreach ($reviews_pool as $rev) {
            $stars = intval($rev['stars']);
            if ($stars === 5) {
                $count_5++;
            } elseif ($stars === 4) {
                $count_4++;
            } elseif ($stars === 3) {
                $count_3++;
            }
            
            if (!empty($rev['photos'])) {
                $count_photo++;
            }
            if (!empty(trim($rev['comment']))) {
                $count_comment++;
            }
        }
        ?>
        
        <div class="flex flex-col gap-lg">
            <!-- Rating Summary & Filters -->
            <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant flex flex-col md:flex-row gap-xl items-center">
                <div class="text-center md:border-r border-outline-variant md:pr-xl shrink-0">
                    <p class="text-[48px] font-bold text-primary leading-none mb-xs"><?= $rating; ?> <span class="text-headline-md font-normal text-on-surface-variant">/ 5</span></p>
                    <div class="flex gap-1 mb-sm justify-center">
                        <span class="material-symbols-outlined text-yellow-500 fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 fill-icon">star</span>
                        <span class="material-symbols-outlined text-yellow-500 fill-icon">star</span>
                    </div>
                    <p class="text-label-sm text-on-surface-variant"><?= $count_all; ?> Penilaian</p>
                </div>
                <div class="flex-1 flex flex-wrap gap-sm" id="review-filters">
                    <button onclick="filterReviews('all', null, this)" class="review-filter-btn px-md py-xs bg-primary/10 text-primary border border-primary rounded font-label-md transition-colors">Semua</button>
                    <button onclick="filterReviews('stars', 5, this)" class="review-filter-btn px-md py-xs bg-white border border-outline-variant text-on-surface-variant rounded font-label-md hover:border-primary transition-colors">5 Bintang<?= $count_5 > 0 ? " ($count_5)" : ''; ?></button>
                    <button onclick="filterReviews('stars', 4, this)" class="review-filter-btn px-md py-xs bg-white border border-outline-variant text-on-surface-variant rounded font-label-md hover:border-primary transition-colors">4 Bintang<?= $count_4 > 0 ? " ($count_4)" : ''; ?></button>
                    <button onclick="filterReviews('stars', 3, this)" class="review-filter-btn px-md py-xs bg-white border border-outline-variant text-on-surface-variant rounded font-label-md hover:border-primary transition-colors">3 Bintang<?= $count_3 > 0 ? " ($count_3)" : ''; ?></button>
                    <button onclick="filterReviews('photo', true, this)" class="review-filter-btn px-md py-xs bg-white border border-outline-variant text-on-surface-variant rounded font-label-md hover:border-primary transition-colors">Dengan Foto<?= $count_photo > 0 ? " ($count_photo)" : ''; ?></button>
                    <button onclick="filterReviews('comment', true, this)" class="review-filter-btn px-md py-xs bg-white border border-outline-variant text-on-surface-variant rounded font-label-md hover:border-primary transition-colors">Dengan Komentar<?= $count_comment > 0 ? " ($count_comment)" : ''; ?></button>
                </div>
            </div>
            
            <!-- Review List -->
            <div class="space-y-lg">
                <?php foreach ($reviews_pool as $rev): ?>
                    <?php 
                    $has_photo = !empty($rev['photos']) ? 'true' : 'false';
                    $has_comment = !empty(trim($rev['comment'])) ? 'true' : 'false';
                    ?>
                    <div class="review-card bg-surface-container-lowest p-lg rounded-xl border border-outline-variant shadow-sm transition-all duration-300"
                         data-stars="<?= intval($rev['stars']); ?>"
                         data-has-photo="<?= $has_photo; ?>"
                         data-has-comment="<?= $has_comment; ?>">
                        <div class="flex gap-md">
                            <div class="w-10 h-10 rounded-full bg-primary-fixed flex-shrink-0 flex items-center justify-center text-primary font-bold">
                                <?= htmlspecialchars($rev['initials']); ?>
                            </div>
                            <div class="flex-1">
                                <p class="text-label-md font-bold text-on-surface"><?= htmlspecialchars($rev['user']); ?></p>
                                <div class="flex gap-1 mb-xs">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <span class="material-symbols-outlined text-yellow-500 text-[14px] <?= $i < $rev['stars'] ? 'fill-icon' : ''; ?>">star</span>
                                    <?php endfor; ?>
                                </div>
                                <div class="flex items-center gap-sm text-label-sm text-outline mb-sm">
                                    <span><?= htmlspecialchars($rev['date']); ?></span>
                                    <span class="text-outline-variant">|</span>
                                    <span>Layanan: <?= htmlspecialchars($rev['layanan']); ?></span>
                                </div>
                                <p class="text-body-md text-on-surface mb-md"><?= htmlspecialchars($rev['comment']); ?></p>
                                
                                <?php if (!empty($rev['photos'])): ?>
                                    <div class="flex gap-sm mb-md">
                                        <?php foreach ($rev['photos'] as $p): ?>
                                            <div class="w-20 h-20 rounded bg-cover bg-center border border-outline-variant" style="background-image: url('<?= htmlspecialchars($p); ?>')"></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($rev['response'])): ?>
                                    <!-- Seller Response -->
                                    <div class="bg-surface-container p-md rounded-lg">
                                        <p class="text-label-sm font-bold text-primary mb-xs">Respon Penjual:</p>
                                        <p class="text-body-md text-on-surface-variant"><?= htmlspecialchars($rev['response']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-surface-container-low border-t border-outline-variant mt-xl">
    <div class="flex flex-col md:flex-row justify-between items-center px-lg py-xl w-full mx-auto gap-md max-w-6xl">
        <div class="flex flex-col items-center md:items-start gap-xs">
            <span class="font-headline-md text-headline-md font-bold text-primary">KosanLaundry</span>
            <p class="text-label-sm text-on-surface-variant">© 2026 KosanLaundry. All rights reserved.</p>
        </div>
        <div class="flex gap-lg">
            <a class="text-on-surface-variant hover:text-primary transition-colors text-label-sm" href="#">Kebijakan Privasi</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors text-label-sm" href="#">Syarat &amp; Ketentuan</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors text-label-sm" href="#">Hubungi Kami</a>
        </div>
    </div>
</footer>

<!-- Order Modal -->
<div class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-container-margin" id="order-modal">
    <div class="bg-surface-container-lowest w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
        <div class="p-lg border-b border-outline-variant flex justify-between items-center">
            <h3 class="font-headline-md text-on-surface font-bold text-lg">Detail Pesanan</h3>
            <button class="material-symbols-outlined text-on-surface-variant hover:text-error" onclick="closeOrderModal()">close</button>
        </div>
        
        <div class="p-lg space-y-lg overflow-y-auto max-h-[70vh]">
            <div>
                <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-xs text-xs">Layanan Terpilih</p>
                <p class="font-headline-md text-[20px] text-primary font-bold" id="modal-service-name">Cuci Lipat Reguler</p>
            </div>
            
            <?php if (!$is_self_service): ?>
            <div>
                <label class="block text-label-md font-bold mb-xs text-sm" id="modal-qty-label">Jumlah (Kg)</label>
                <input id="order-qty" class="w-full bg-surface-container border border-outline-variant rounded-lg px-md py-sm focus:ring-1 focus:ring-primary outline-none" min="1" type="number" value="1" oninput="calculateTotal()"/>
            </div>
            
            <div class="space-y-sm">
                <p class="text-label-md font-bold text-sm">Layanan Tambahan</p>
                <label class="flex items-center gap-md cursor-pointer">
                    <input checked="" id="addon-jemput" class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox" onchange="calculateTotal()"/>
                    <span class="text-body-md text-sm">Jemput Pakaian</span>
                </label>
                <label class="flex items-center gap-md cursor-pointer">
                    <input checked="" id="addon-antar" class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox" onchange="calculateTotal()"/>
                    <span class="text-body-md text-sm">Antar Pakaian</span>
                </label>
            </div>
            <?php else: ?>
            <!-- Slot Booking Fields for Self Service -->
            <div class="grid grid-cols-2 gap-sm">
                <div>
                    <label class="block text-label-md font-bold mb-xs text-sm">Tanggal Reservasi</label>
                    <input id="reservation-date" class="w-full bg-surface-container border border-outline-variant rounded-lg px-md py-sm focus:ring-1 focus:ring-primary outline-none text-sm" type="date" value="<?= date('Y-m-d'); ?>" min="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d', strtotime('+3 days')); ?>"/>
                </div>
                <div>
                    <label class="block text-label-md font-bold mb-xs text-sm">Sesi Jam Kedatangan</label>
                    <select id="reservation-time" class="w-full bg-surface-container border border-outline-variant rounded-lg px-md py-sm focus:ring-1 focus:ring-primary outline-none text-sm">
                        <option value="07:00 - 08:30">07:00 - 08:30</option>
                        <option value="08:30 - 10:00">08:30 - 10:00</option>
                        <option value="10:00 - 11:30">10:00 - 11:30</option>
                        <option value="11:30 - 13:00">11:30 - 13:00</option>
                        <option value="13:00 - 14:30">13:00 - 14:30</option>
                        <option value="14:30 - 16:00">14:30 - 16:00</option>
                        <option value="16:00 - 17:30">16:00 - 17:30</option>
                        <option value="17:30 - 19:00">17:30 - 19:00</option>
                        <option value="19:00 - 20:30">19:00 - 20:30</option>
                        <option value="20:30 - 22:00">20:30 - 22:00</option>
                    </select>
                </div>
            </div>
            
            <!-- Machine availability indicator -->
            <div class="p-sm bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-xs flex items-center gap-xs">
                <span class="material-symbols-outlined text-[16px] text-blue-600 animate-pulse">info</span>
                <span><strong>Status Mesin:</strong> Tersedia 4 Washer &amp; 3 Dryer kosong untuk sesi terpilih.</span>
            </div>
            
            <div>
                <label class="block text-label-md font-bold mb-xs text-sm">Jumlah Mesin yang Dipesan</label>
                <input id="order-qty" class="w-full bg-surface-container border border-outline-variant rounded-lg px-md py-sm focus:ring-1 focus:ring-primary outline-none" min="1" max="4" type="number" value="1" oninput="calculateTotal()"/>
            </div>
            
            <input type="checkbox" id="addon-jemput" class="hidden"/>
            <input type="checkbox" id="addon-antar" class="hidden"/>
            <?php endif; ?>
            
            <div>
                <label class="block text-label-md font-bold mb-xs text-sm">Catatan Tambahan</label>
                <textarea id="order-notes" class="w-full bg-surface-container border border-outline-variant rounded-lg px-md py-sm focus:ring-1 focus:ring-primary outline-none h-24 resize-none text-sm" placeholder="Contoh: Pisahkan baju luntur, titip detergen wangi lavender, dll..."></textarea>
            </div>
            
            <div class="bg-surface-container-low p-md rounded-lg space-y-xs">
                <p class="text-label-sm font-bold text-on-surface-variant uppercase text-xs">Ringkasan Biaya</p>
                <div class="flex justify-between text-body-md text-sm">
                    <span id="price-per-unit-label">Harga Layanan (1kg)</span>
                    <span id="modal-price-per-unit">Rp 7.000</span>
                </div>
                <div class="flex justify-between text-body-md text-sm">
                    <span><?= $is_self_service ? 'Biaya Reservasi Slot' : 'Biaya Antar-Jemput'; ?></span>
                    <span id="modal-addon-price" class="<?= $is_self_service ? 'text-primary font-bold' : 'text-secondary font-bold'; ?>"><?= $is_self_service ? 'Rp 1.000' : 'Gratis'; ?></span>
                </div>
                <div class="border-t border-outline-variant mt-xs pt-xs flex justify-between font-bold text-primary text-base">
                    <span>Total Pembayaran</span>
                    <span id="modal-total-price">Rp 7.000</span>
                </div>
            </div>
        </div>
        
        <div class="p-lg bg-surface-container-lowest border-t border-outline-variant">
            <button onclick="confirmOrder()" class="w-full bg-primary text-on-primary py-md rounded-xl font-bold shadow-lg active:scale-[0.98] transition-all hover:brightness-110">
                Konfirmasi Pesanan
            </button>
        </div>
    </div>
</div>

<script>
    let activeServicePrice = 0;
    let activeUnitType = 'kg';

    // Switch Tabs
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-primary', 'text-primary', 'font-bold');
            btn.classList.add('border-transparent', 'text-on-surface-variant');
        });
        
        const activeBtn = document.getElementById('tab-' + tabId);
        activeBtn.classList.add('active', 'border-primary', 'text-primary', 'font-bold');
        activeBtn.classList.remove('border-transparent', 'text-on-surface-variant');
        
        document.querySelectorAll('.grid-content').forEach(grid => {
            grid.classList.add('hidden');
        });
        document.getElementById('grid-' + tabId).classList.remove('hidden');
    }

    // Set initial active tab styling & Map initialization
    document.addEventListener("DOMContentLoaded", function() {
        const isSelfService = <?= $is_self_service ? 'true' : 'false'; ?>;
        switchTab(isSelfService ? 'self' : 'kiloan');

        // Leaflet Map Initialization
        const lat = <?= floatval($mitra['latitude']); ?>;
        const lng = <?= floatval($mitra['longitude']); ?>;
        const name = "<?= htmlspecialchars($mitra['nama_mitra']); ?>";
        
        try {
            const map = L.map('partner-map', {
                zoomControl: false,
                attributionControl: false
            }).setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
            }).addTo(map);
            
            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);
            
            const markerIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="w-8 h-8 rounded-full bg-[#0058be] flex items-center justify-center text-white border-2 border-white shadow-lg"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">location_on</span></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });
            
            L.marker([lat, lng], { icon: markerIcon }).addTo(map)
                .bindPopup(`<strong class="text-sm text-primary">${name}</strong>`, { closeButton: false })
                .openPopup();
        } catch (e) {
            console.error("Map initialization failed: ", e);
        }
    });

    // Open Order Modal
    function openOrderModal(serviceName, price, unitType) {
        // Check if user is logged in
        const isLoggedIn = <?= $is_logged_in ? 'true' : 'false'; ?>;
        if (!isLoggedIn) {
            alert('Silakan masuk terlebih dahulu untuk melakukan pemesanan.');
            window.location.href = '<?= $login_url; ?>';
            return;
        }

        activeServicePrice = price;
        activeUnitType = unitType;

        document.getElementById('modal-service-name').innerText = serviceName;
        const qtyLabel = document.getElementById('modal-qty-label');
        if (qtyLabel) {
            qtyLabel.innerText = unitType === 'kg' ? 'Jumlah (Kg)' : 'Jumlah (Pcs)';
        }
        document.getElementById('price-per-unit-label').innerText = 'Harga Layanan (1' + unitType + ')';
        document.getElementById('modal-price-per-unit').innerText = formatRupiah(price);
        document.getElementById('order-qty').value = 1;
        
        calculateTotal();

        document.getElementById('order-modal').classList.remove('hidden');
    }

    // Close Order Modal
    function closeOrderModal() {
        document.getElementById('order-modal').classList.add('hidden');
    }

    // Recalculate Total
    function calculateTotal() {
        const qty = parseFloat(document.getElementById('order-qty').value) || 1;
        const isSelfService = <?= $is_self_service ? 'true' : 'false'; ?>;
        const reservationFee = isSelfService ? 1000 : 0;
        
        const total = (activeServicePrice * qty) + reservationFee;
        document.getElementById('modal-total-price').innerText = formatRupiah(total);
    }

    // Format Number to Rupiah
    function formatRupiah(number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    }

    // Confirm Order Mock
    function confirmOrder() {
        const qty = document.getElementById('order-qty').value;
        const serviceName = document.getElementById('modal-service-name').innerText;
        const notes = document.getElementById('order-notes').value.trim();
        
        const originalBtn = document.querySelector('#order-modal button[onclick="confirmOrder()"]');
        const originalText = originalBtn.innerHTML;
        
        originalBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[20px] mr-xs">sync</span> Memproses...';
        originalBtn.disabled = true;

        const isSelfService = <?= $is_self_service ? 'true' : 'false'; ?>;
        
        if (isSelfService) {
            const date = document.getElementById('reservation-date').value;
            const time = document.getElementById('reservation-time').value;
            setTimeout(() => {
                alert('Reservasi slot ' + serviceName + ' (' + qty + ' Mesin) pada tanggal ' + date + ' (' + time + ') berhasil dibuat! Silakan datang ke WashTra tepat waktu. Kode Tiket Anda: WT-' + Math.floor(1000 + Math.random() * 9000));
                closeOrderModal();
                originalBtn.innerHTML = originalText;
                originalBtn.disabled = false;
            }, 1500);
        } else {
            setTimeout(() => {
                alert('Pesanan ' + serviceName + ' sebanyak ' + qty + ' ' + activeUnitType + ' berhasil dibuat! Mitra akan segera menghubungi Anda.');
                closeOrderModal();
                originalBtn.innerHTML = originalText;
                originalBtn.disabled = false;
            }, 1500);
        }
    }

    // Filter Reviews Dynamically
    function filterReviews(filterType, value, button) {
        // Reset all buttons styling to inactive state
        const buttons = document.querySelectorAll('.review-filter-btn');
        buttons.forEach(btn => {
            btn.classList.remove('bg-primary/10', 'text-primary', 'border-primary');
            btn.classList.add('bg-white', 'border-outline-variant', 'text-on-surface-variant', 'hover:border-primary');
        });

        // Set clicked button to active state
        if (button) {
            button.classList.remove('bg-white', 'border-outline-variant', 'text-on-surface-variant', 'hover:border-primary');
            button.classList.add('bg-primary/10', 'text-primary', 'border-primary');
        }

        // Filter the cards
        const cards = document.querySelectorAll('.review-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const stars = parseInt(card.getAttribute('data-stars')) || 0;
            const hasPhoto = card.getAttribute('data-has-photo') === 'true';
            const hasComment = card.getAttribute('data-has-comment') === 'true';

            let show = false;
            if (filterType === 'all') {
                show = true;
            } else if (filterType === 'stars') {
                show = (stars === parseInt(value));
            } else if (filterType === 'photo') {
                show = hasPhoto;
            } else if (filterType === 'comment') {
                show = hasComment;
            }

            if (show) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Check if there is a "No reviews" message div, and show/hide it
        let noReviewsMsg = document.getElementById('no-reviews-message');
        if (!noReviewsMsg) {
            // If it doesn't exist, create it inside the parent container of review-cards
            const reviewContainer = document.querySelector('.review-card')?.parentElement;
            if (reviewContainer) {
                noReviewsMsg = document.createElement('div');
                noReviewsMsg.id = 'no-reviews-message';
                noReviewsMsg.className = 'hidden text-center py-xl text-on-surface-variant bg-surface-container-lowest rounded-xl border border-outline-variant';
                noReviewsMsg.innerHTML = `
                    <span class="material-symbols-outlined text-[48px] text-outline mb-sm">rate_review</span>
                    <p class="text-body-lg">Tidak ada ulasan yang sesuai dengan filter.</p>
                `;
                reviewContainer.appendChild(noReviewsMsg);
            }
        }

        if (noReviewsMsg) {
            if (visibleCount === 0) {
                noReviewsMsg.classList.remove('hidden');
            } else {
                noReviewsMsg.classList.add('hidden');
            }
        }
    }
</script>
</body>
</html>
