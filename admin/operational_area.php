<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

try {
    // Fetch all active partners (with template files)
    $stmt = $pdo->query("SELECT * FROM mitra_laundry ORDER BY rating DESC");
    $raw_mitras = $stmt->fetchAll();
    
    $mitra_list = [];
    foreach ($raw_mitras as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../Mitra laundry/' . $file_name)) {
            // Determine area
            $alamat_lower = strtolower($mitra['alamat']);
            if (strpos($alamat_lower, 'sekarbela') !== false || strpos($alamat_lower, 'kekalik') !== false) {
                $mitra['area'] = 'Sekarbela';
            } elseif (strpos($alamat_lower, 'ampenan') !== false) {
                $mitra['area'] = 'Ampenan';
            } elseif (strpos($alamat_lower, 'pagutan') !== false) {
                $mitra['area'] = 'Pagutan';
            } elseif (strpos($alamat_lower, 'cakranegara') !== false || strpos($alamat_lower, 'cilinaya') !== false) {
                $mitra['area'] = 'Cakranegara';
            } else {
                $mitra['area'] = 'Mataram Kota';
            }
            $mitra_list[] = $mitra;
        }
    }
    
    // Group partners by area
    $areas = [
        'Sekarbela' => [],
        'Mataram Kota' => [],
        'Ampenan' => [],
        'Pagutan' => [],
        'Cakranegara' => []
    ];
    
    foreach ($mitra_list as $mitra) {
        $areas[$mitra['area']][] = $mitra;
    }
} catch (PDOException $e) {
    $mitra_list = [];
    $areas = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Wilayah Operasional | MataramWash Admin</title>
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
        #map {
            z-index: 10;
            border-radius: 0.75rem;
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
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="kelola_pesanan.php">
<span class="material-symbols-outlined text-[20px]">local_laundry_service</span>
<span class="text-label-md font-label-md">Kelola Pesanan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm bg-blue-600 text-white rounded-xl font-bold border-l-4 border-blue-400 shadow-lg shadow-blue-900/30 transition-all duration-200" href="operational_area.php">
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
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="settings.php">
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
                <div class="text-headline-sm font-bold text-on-surface">Wilayah Operasional</div>
            </div>
            <div class="flex items-center gap-md">
                <div class="flex items-center gap-sm">
                    <p class="text-label-md font-bold leading-none hidden sm:block"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></p>
                    <img alt="Admin profile" class="w-9 h-9 rounded-full object-cover border-2 border-slate-100 shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVvTfpl6gmSbn7utdVTjVT1ZrHaIbCt76OBU9jA9oc3rue19H1ElhbliNLU8FUVfCMZWMCOXO6ZI0EBlE68GvL7TdpDcdz05FrUqtzRUVrrTQKcC_MwtAKGFkV_XAbFOxIpl3JRF93_22IuQMGYGKqzXSHUZRnab8I7P_AWzrPQKLrh9PmQd4pqpbRW8v-5sKU_uUJt1jpvrX5bWXDDQshtNQtM9DcfB5GsKwZW-zFy6P6DnFBWUY_oCDubbBHW4BXb1p5RWiXyyg">
                </div>
            </div>
        </header>

        <!-- Scrollable Area -->
        <div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
            <div class="max-w-7xl mx-auto space-y-lg">
                <!-- Header -->
                <div>
                    <h1 class="text-headline-lg font-headline-lg text-on-surface">Peta Sebaran Wilayah Kemitraan</h1>
                    <p class="text-body-md text-on-surface-variant">Monitor cakupan geografis dan lokasi outlet fisik seluruh mitra MataramWash.</p>
                </div>

                <!-- Main Bento Layout (Map + Area List) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
                    
                    <!-- Map Box (Left 2 columns) -->
                    <div class="lg:col-span-2 bento-card rounded-xl p-md flex flex-col h-[550px] relative">
                        <div class="flex justify-between items-center mb-sm">
                            <span class="text-label-md font-bold text-on-surface">Peta Distribusi Geografis</span>
                            <span class="px-xs py-[2px] bg-primary-fixed text-primary rounded font-bold text-[11px] flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[14px]">my_location</span>
                                Mataram City Center
                            </span>
                        </div>
                        <div id="map" class="flex-grow w-full bg-slate-100 border border-outline-variant"></div>
                    </div>

                    <!-- Area Breakdown (Right 1 column) -->
                    <div class="bento-card rounded-xl p-md flex flex-col h-[550px] space-y-md">
                        <span class="text-label-md font-bold text-on-surface">Breakdown Cakupan Wilayah</span>
                        <div class="flex-grow overflow-y-auto custom-scrollbar pr-xs space-y-sm">
                            <?php foreach ($areas as $area_name => $mitras): ?>
                                <?php 
                                $count = count($mitras);
                                $badge_color = $count > 0 ? 'bg-primary-container text-on-primary-container' : 'bg-surface-variant text-outline';
                                ?>
                                <div class="border border-outline-variant rounded-xl p-sm hover:bg-surface-container-low transition-colors space-y-sm">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-xs">
                                            <span class="material-symbols-outlined text-primary text-[20px]">explore</span>
                                            <span class="text-label-md font-bold text-on-surface"><?= htmlspecialchars($area_name); ?></span>
                                        </div>
                                        <span class="px-xs py-[2px] <?= $badge_color; ?> rounded-full text-[11px] font-bold"><?= $count; ?> Mitra</span>
                                    </div>
                                    
                                    <?php if ($count > 0): ?>
                                        <div class="space-y-xs pt-xs border-t border-dashed border-outline-variant">
                                            <?php foreach ($mitras as $mitra): ?>
                                                <button onclick="focusMitra(<?= $mitra['latitude']; ?>, <?= $mitra['longitude']; ?>, '<?= htmlspecialchars($mitra['nama_mitra'], ENT_QUOTES); ?>')" class="w-full flex items-center gap-sm p-[6px] hover:bg-surface rounded-lg text-left transition-colors active:scale-98">
                                                    <img src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="" class="w-8 h-8 rounded object-cover">
                                                    <div class="flex-grow truncate">
                                                        <p class="text-body-xs font-bold text-on-surface leading-tight truncate"><?= htmlspecialchars($mitra['nama_mitra']); ?></p>
                                                        <p class="text-[10px] text-outline truncate"><?= htmlspecialchars($mitra['alamat']); ?></p>
                                                    </div>
                                                    <span class="material-symbols-outlined text-primary text-[18px]">gps_fixed</span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-[11px] text-outline text-center py-xs italic">Belum ada mitra di area ini.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
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

    <!-- Leaflet.js JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Load mitra list from PHP to Javascript
        const mitras = <?= json_encode($mitra_list); ?>;
        
        // Initialize Map centered on Mataram City Center
        const map = L.map('map', {
            zoomControl: false
        }).setView([-8.5830, 116.1075], 13);
        
        // Custom zoom control placement
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Keep track of map markers & popups
        const markers = {};

        // Marker Icon Maker
        function createCustomMarkerIcon(type) {
            let color = '#3b82f6'; // primary blue
            if (type === 'express') color = '#a36700'; // yellow/amber
            if (type === 'eco') color = '#006b5f'; // green
            if (type === 'satuan') color = '#825100'; // brown
            if (type === 'sepatu') color = '#7c3aed'; // purple

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

        // Render Markers
        mitras.forEach(mitra => {
            if (mitra.latitude && mitra.longitude) {
                const markerIcon = createCustomMarkerIcon(mitra.icon_type);
                
                const imgUrl = mitra.foto_toko ? `../${mitra.foto_toko}` : '';
                const imgHtml = imgUrl ? `
                    <div class="w-full h-20 rounded overflow-hidden mb-xs bg-slate-100">
                        <img src="${imgUrl}" class="w-full h-full object-cover">
                    </div>
                ` : '';

                const popupContent = `
                    <div style="width: 180px; font-family: 'Inter', sans-serif;">
                        ${imgHtml}
                        <h4 style="font-weight: 700; margin: 0; font-size: 13px; color: #151c27;">${mitra.nama_mitra}</h4>
                        <p style="margin: 4px 0 0; font-size: 10px; color: #424754; line-height: 1.3;">${mitra.alamat}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; border-top: 1px dashed #c2c6d6; padding-top: 6px;">
                            <span style="font-weight: 700; font-size: 11px; color: #3b82f6;">Rp ${parseInt(mitra.harga_per_kg).toLocaleString('id-ID')}/kg</span>
                            <span style="font-size: 10px; padding: 2px 6px; border-radius: 9999px; background-color: #ecfdf5; color: #047857; font-weight: 700;">Active</span>
                        </div>
                    </div>
                `;

                const marker = L.marker([parseFloat(mitra.latitude), parseFloat(mitra.longitude)], {
                    icon: markerIcon
                }).bindPopup(popupContent).addTo(map);

                markers[mitra.nama_mitra] = marker;
            }
        });

        // Focus function
        function focusMitra(lat, lng, name) {
            map.setView([lat, lng], 16);
            if (markers[name]) {
                markers[name].openPopup();
            }
        }
    </script>
</body>
</html>
