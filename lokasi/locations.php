<?php
// lokasi/locations.php
session_start();
require_once '../db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$dashboard_url = "../dashboard.php";
$login_url = "../login/login.php";

// Fetch mitra laundry from DB whose profile files exist
try {
    $stmt = $pdo->query("SELECT * FROM mitra_laundry ORDER BY rating DESC");
    $all_mitra = $stmt->fetchAll();
    
    $mitra_list = [];
    date_default_timezone_set('Asia/Makassar');
    $current_time = date('H:i');
    foreach ($all_mitra as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../Mitra laundry/' . $file_name)) {
            // Calculate dynamic status_buka based on jam_buka and WITA time
            $is_open_now = false;
            $jam_buka = $mitra['jam_buka'] ?? '08:00 - 21:00';
            
            if (strpos(strtolower($jam_buka), '24 hours') !== false || strpos(strtolower($jam_buka), '24 jam') !== false) {
                $is_open_now = true;
            } elseif (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $jam_buka, $matches)) {
                $start_time = $matches[1];
                $end_time = $matches[2];
                if ($start_time <= $end_time) {
                    $is_open_now = ($current_time >= $start_time && $current_time <= $end_time);
                } else {
                    $is_open_now = ($current_time >= $start_time || $current_time <= $end_time);
                }
            } elseif (preg_match('/until\s*(\d{1,2}:\d{2})/i', $jam_buka, $matches)) {
                $start_time = '07:00';
                $end_time = $matches[1];
                $is_open_now = ($current_time >= $start_time && $current_time <= $end_time);
            }
            $mitra['status_buka'] = ($mitra['status_buka'] == 1 && $is_open_now) ? 1 : 0;
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
    <title>Lokasi Mitra - MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <!-- Leaflet.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
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
        .custom-shadow {
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
        }
        .mitra-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .mitra-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 88, 190, 0.08);
        }
        .mitra-card.active-card {
            border-color: #0058be;
            background-color: #f0f3ff;
        }
        #map {
            height: calc(100vh - 73px);
            width: 100%;
            z-index: 10;
        }
        @media (max-width: 768px) {
            #map {
                height: 350px;
            }
        }
        /* Custom styling for Leaflet Popup */
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            font-family: 'Inter', sans-serif;
            padding: 4px;
        }
        .leaflet-popup-tip {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../layanan/layanan.php">Layanan</a>
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-md" href="#">Lokasi</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../bantuan/bantuan.php">Bantuan</a>
            </div>
            <?php if ($is_logged_in): ?>
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
                <button onclick="window.location.href='<?= $login_url; ?>'" class="px-lg py-xs rounded-xl bg-primary text-on-primary font-bold hover:bg-primary-container transition-colors active:scale-95 duration-150">Login</button>
            <?php endif; ?>
            <button class="md:hidden flex items-center">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
</nav>

<main class="flex flex-col md:flex-row min-h-[calc(100vh-73px)]">
    
    <!-- Sidebar Left: List of Partners & Search -->
    <div class="w-full md:w-[420px] bg-white border-r border-outline-variant flex flex-col z-20">
        
        <!-- Search Header -->
        <div class="p-lg border-b border-outline-variant/60 bg-surface-bright space-y-md">
            <h1 class="text-headline-md font-bold text-primary flex items-center gap-xs">
                <span class="material-symbols-outlined text-3xl">map</span>
                <span>Mitra di Kota Mataram</span>
            </h1>
            <p class="text-on-surface-variant text-label-sm">Temukan mitra laundry terpercaya di sekitar Anda.</p>
            
            <!-- Input Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-outline text-[20px]">search</span>
                </div>
                <input id="search-input" class="block w-full pl-10 pr-10 py-3 border border-outline-variant rounded-xl bg-white text-body-md placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all custom-shadow" placeholder="Cari nama atau alamat laundry..." type="text">
                <!-- Clear Button -->
                <button id="clear-search" class="absolute inset-y-0 right-0 pr-3 flex items-center text-outline hover:text-primary hidden">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        </div>
        
        <!-- List Container -->
        <div id="mitra-list-container" class="flex-1 overflow-y-auto p-md space-y-md max-h-[calc(100vh-250px)] md:max-h-[calc(100vh-250px)]">
            <?php if (empty($mitra_list)): ?>
                <div class="text-center py-10">
                    <span class="material-symbols-outlined text-outline text-5xl mb-2">storefront_off</span>
                    <p class="text-on-surface-variant font-bold">Belum ada mitra laundry.</p>
                </div>
            <?php else: ?>
                <?php foreach ($mitra_list as $mitra): ?>
                    <!-- Card Mitra -->
                    <div id="card-<?= $mitra['id']; ?>" class="mitra-card p-md bg-surface-container-lowest border border-outline-variant rounded-2xl cursor-pointer flex gap-md relative hover:border-primary/50" onclick="focusOnMitra(<?= htmlspecialchars(json_encode($mitra)); ?>)">
                        
                        <!-- Left: Shop Photo thumbnail -->
                        <?php if (!empty($mitra['foto_toko'])): ?>
                            <img src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="<?= htmlspecialchars($mitra['nama_mitra']); ?>" class="w-20 h-20 rounded-xl object-cover bg-slate-100 flex-shrink-0">
                        <?php else: ?>
                            <!-- Fallback icon -->
                            <div class="w-20 h-20 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-3xl">storefront</span>
                            </div>
                        <?php endif; ?>

                        <!-- Right: Details -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div class="space-y-1">
                                <div class="flex justify-between items-start gap-xs">
                                    <h4 class="font-bold text-on-surface text-[15px] leading-tight font-headline-md"><?= htmlspecialchars($mitra['nama_mitra']); ?></h4>
                                    <!-- Rating -->
                                    <div class="flex items-center text-tertiary font-bold gap-[2px] bg-tertiary-fixed/30 px-xs py-[2px] rounded-lg flex-shrink-0">
                                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                        <span class="text-[11px]"><?= htmlspecialchars($mitra['rating']); ?></span>
                                    </div>
                                </div>
                                <?php if ($mitra['icon_type'] === 'sepatu'): ?>
                                    <span class="text-[10px] text-white bg-[#7c3aed] px-sm py-[2px] rounded-full font-bold uppercase tracking-wider inline-block">
                                        <?= htmlspecialchars($mitra['icon_type']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] text-primary bg-primary-container/10 px-sm py-[2px] rounded-full font-bold uppercase tracking-wider inline-block">
                                        <?= htmlspecialchars($mitra['icon_type']); ?>
                                    </span>
                                <?php endif; ?>
                                <p class="text-on-surface-variant text-[12px] leading-relaxed flex items-start gap-xs mt-1">
                                    <span class="material-symbols-outlined text-[14px] text-outline mt-[2px] flex-shrink-0">location_on</span>
                                    <span class="line-clamp-2"><?= htmlspecialchars($mitra['alamat']); ?></span>
                                </p>
                            </div>

                            <!-- Bottom Info row -->
                            <div class="pt-xs border-t border-outline-variant/40 flex justify-between items-center mt-sm">
                                <?php if ($mitra['icon_type'] === 'sepatu'): ?>
                                    <span class="text-tertiary font-bold text-[12px]">Rp 20.000/pasang</span>
                                <?php elseif (strpos(strtolower($mitra['nama_mitra']), 'washtra') !== false): ?>
                                    <span class="text-tertiary font-bold text-[12px]">Rp <?= number_format($mitra['harga_per_kg'], 0, ',', '.'); ?> Flat</span>
                                <?php else: ?>
                                    <span class="text-tertiary font-bold text-[12px]">Rp <?= number_format($mitra['harga_per_kg'], 0, ',', '.'); ?>/kg</span>
                                <?php endif; ?>
                                <div class="flex items-center gap-xs">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $mitra['status_buka'] ? 'bg-secondary' : 'bg-outline' ?>"></span>
                                    <span class="text-[11px] font-semibold text-on-surface-variant"><?= htmlspecialchars($mitra['jam_buka']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Empty search result alert -->
            <div id="no-search-results" class="text-center py-10 hidden">
                <span class="material-symbols-outlined text-outline text-5xl mb-2">search_off</span>
                <p class="text-on-surface-variant">Tidak ada mitra laundry yang cocok.</p>
            </div>
        </div>
    </div>
    
    <!-- Map Area Right -->
    <div class="flex-1 relative">
        <div id="map"></div>
    </div>
    
</main>

<!-- Leaflet.js JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    // Load mitra list from PHP to Javascript
    const mitras = <?= json_encode($mitra_list); ?>;
    
    // Initialize Map centering dynamically
    let mapCenter = [-8.5830, 116.1075]; // default Mataram City Center
    let mapZoom = 13;
    if (mitras.length === 1) {
        mapCenter = [parseFloat(mitras[0].latitude), parseFloat(mitras[0].longitude)];
        mapZoom = 15;
    }
    
    const map = L.map('map', {
        zoomControl: false // Custom placement later
    }).setView(mapCenter, mapZoom);
    
    // Custom zoom control placement
    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    // Add OpenStreetMap tile layer (beautiful & free)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Keep track of map markers
    const markers = {};

    // Custom Icon Maker function
    function createCustomMarkerIcon(type) {
        let color = '#0058be'; // primary color
        if (type === 'express') color = '#a36700'; // secondary/tertiary container style
        if (type === 'eco') color = '#006b5f'; // green style
        if (type === 'satuan') color = '#825100';
        if (type === 'sepatu') color = '#7c3aed'; // premium purple for shoe care

        // High quality premium CSS-based HTML marker pin
        return L.divIcon({
            html: `
                <div style="
                    background-color: ${color}; 
                    width: 32px; 
                    height: 32px; 
                    border-radius: 50% 50% 50% 0; 
                    transform: rotate(-45deg); 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    border: 2px solid white; 
                    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
                ">
                    <span class="material-symbols-outlined" style="
                        transform: rotate(45deg); 
                        color: white; 
                        font-size: 16px;
                    ">
                        ${type === 'express' ? 'bolt' : (type === 'satuan' ? 'dry_cleaning' : (type === 'eco' ? 'eco' : (type === 'sepatu' ? 'footprint' : 'local_laundry_service')))}
                    </span>
                </div>
            `,
            className: 'custom-div-icon',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
    }

    // Render Markers on Map
    mitras.forEach(mitra => {
        const markerIcon = createCustomMarkerIcon(mitra.icon_type);
        
        // Popup Content with photo card
        const imgUrl = mitra.foto_toko ? `../${mitra.foto_toko}` : '';
        const imgHtml = imgUrl ? `
            <div class="w-full h-24 rounded-lg overflow-hidden mb-sm bg-slate-100">
                <img src="${imgUrl}" alt="${mitra.nama_mitra}" class="w-full h-full object-cover">
            </div>
        ` : '';

        const popupContent = `
            <div class="p-xs space-y-sm" style="min-width: 200px;">
                ${imgHtml}
                <div class="flex justify-between items-center gap-xs">
                    <strong class="text-on-surface font-headline-md text-sm">${mitra.nama_mitra}</strong>
                    <div class="flex items-center text-yellow-600 font-bold text-xs flex-shrink-0">
                        <span class="material-symbols-outlined text-sm mr-[2px]" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span>${mitra.rating}</span>
                    </div>
                </div>
                <div class="border-t border-slate-100 my-xs"></div>
                <p class="text-[11px] text-on-surface-variant leading-relaxed"><span class="font-bold">Alamat:</span> ${mitra.alamat}</p>
                <div class="flex justify-between items-center pt-xs">
                    <span class="text-xs font-bold text-primary bg-primary-container/10 px-xs py-[2px] rounded-md">
                        ${mitra.icon_type === 'sepatu' ? 'Rp 20.000/pasang' : (mitra.nama_mitra.toLowerCase().includes('washtra') ? `Rp ${parseInt(mitra.harga_per_kg).toLocaleString('id-ID')} Flat` : `Rp ${parseInt(mitra.harga_per_kg).toLocaleString('id-ID')}/kg`)}
                    </span>
                    <span class="text-[10px] ${mitra.status_buka == 1 ? 'text-green-600' : 'text-slate-500'} font-bold">${mitra.jam_buka}</span>
                </div>
                <div class="pt-sm">
                    <a href="tel:${mitra.no_telp}" class="block w-full text-center text-white bg-primary py-[6px] px-sm rounded-lg text-xs font-bold hover:bg-primary-container transition-colors">
                        Hubungi: ${mitra.no_telp}
                    </a>
                </div>
            </div>
        `;

        const marker = L.marker([parseFloat(mitra.latitude), parseFloat(mitra.longitude)], {
            icon: markerIcon
        })
        .bindPopup(popupContent)
        .addTo(map);

        // Marker click event
        marker.on('click', function() {
            highlightCard(mitra.id);
        });

        // Store reference to marker
        markers[mitra.id] = marker;
    });

    // Highlight sidebar card
    function highlightCard(id) {
        // Remove active class from all cards
        document.querySelectorAll('.mitra-card').forEach(card => {
            card.classList.remove('active-card');
        });

        const activeCard = document.getElementById(`card-${id}`);
        if (activeCard) {
            activeCard.classList.add('active-card');
            activeCard.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    // Focus on Mitra when clicking sidebar card
    function focusOnMitra(mitra) {
        highlightCard(mitra.id);
        
        // Center map on coordinates with zoom in
        map.flyTo([parseFloat(mitra.latitude), parseFloat(mitra.longitude)], 15, {
            animate: true,
            duration: 1.5
        });

        // Open popup
        setTimeout(() => {
            if (markers[mitra.id]) {
                markers[mitra.id].openPopup();
            }
        }, 1200);
    }

    // Search Interactivity
    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');
    const noResultsDiv = document.getElementById('no-search-results');

    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        
        if (query.length > 0) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }

        let visibleCount = 0;

        mitras.forEach(mitra => {
            const matchesSearch = 
                mitra.nama_mitra.toLowerCase().includes(query) || 
                mitra.alamat.toLowerCase().includes(query) ||
                mitra.icon_type.toLowerCase().includes(query);

            const card = document.getElementById(`card-${mitra.id}`);
            const marker = markers[mitra.id];

            if (matchesSearch) {
                if (card) card.style.display = 'flex';
                if (marker) {
                    if (!map.hasLayer(marker)) {
                        marker.addTo(map);
                    }
                }
                visibleCount++;
            } else {
                if (card) card.style.display = 'none';
                if (marker) {
                    marker.closePopup();
                    map.removeLayer(marker);
                }
            }
        });

        if (visibleCount === 0) {
            noResultsDiv.classList.remove('hidden');
        } else {
            noResultsDiv.classList.add('hidden');
        }
    });

    // Clear search event
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
    });
</script>
</body>
</html>
