<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

// Fetch all partners
try {
    $stmt = $pdo->query("SELECT * FROM mitra_laundry ORDER BY created_at DESC");
    $raw_mitras = $stmt->fetchAll();
    
    // Filter to only include partners whose profile/template files exist (synchronizing with index.php)
    $all_mitras = [];
    foreach ($raw_mitras as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../Mitra laundry/' . $file_name)) {
            $all_mitras[] = $mitra;
        }
    }
    
    // Count active partners (status_buka = 1 AND detail template file exists)
    $active_mitras_count = 0;
    $total_rating = 0;
    $rating_count = 0;
    
    // Calculate active partners by area
    $sebaran_data = [];
    
    foreach ($all_mitras as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        $has_file = file_exists('../Mitra laundry/' . $file_name);
        
        if ($mitra['status_buka'] == 1 && $has_file) {
            $active_mitras_count++;
            
            if ($mitra['rating'] > 0) {
                $total_rating += $mitra['rating'];
                $rating_count++;
            }
            
            // Determine area
            $alamat_lower = strtolower($mitra['alamat']);
            if (strpos($alamat_lower, 'sekarbela') !== false || strpos($alamat_lower, 'kekalik') !== false) {
                $area = 'Sekarbela';
            } elseif (strpos($alamat_lower, 'ampenan') !== false) {
                $area = 'Ampenan';
            } elseif (strpos($alamat_lower, 'pagutan') !== false) {
                $area = 'Pagutan';
            } elseif (strpos($alamat_lower, 'cakranegara') !== false || strpos($alamat_lower, 'cilinaya') !== false) {
                $area = 'Cakranegara';
            } else {
                $area = 'Mataram Kota';
            }
            
            if (!isset($sebaran_data[$area])) {
                $sebaran_data[$area] = 0;
            }
            $sebaran_data[$area]++;
        }
    }
    
    $avg_rating = $rating_count > 0 ? number_format($total_rating / $rating_count, 1, '.', '') : '0.0';
    
    // Define operational areas to display in the chart
    $chart_areas = ['Sekarbela', 'Mataram Kota', 'Ampenan', 'Pagutan', 'Cakranegara'];
    $max_count = !empty($sebaran_data) ? max($sebaran_data) : 0;
} catch (PDOException $e) {
    $all_mitras = [];
    $active_mitras_count = 0;
    $avg_rating = '0.0';
    $sebaran_data = [];
    $chart_areas = ['Sekarbela', 'Mataram Kota', 'Ampenan', 'Pagutan', 'Cakranegara'];
    $max_count = 0;
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Portal Kemitraan | MataramWash Admin</title>
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
            },
            "fontFamily": {
                    "headline-md": ["Inter"],
                    "headline-lg": ["Inter"],
                    "label-sm": ["Inter"],
                    "display-lg": ["Inter"],
                    "headline-lg-mobile": ["Inter"],
                    "body-md": ["Inter"],
                    "label-md": ["Inter"],
                    "body-lg": ["Inter"]
            },
            "fontSize": {
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}]
            }
          },
        },
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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .bento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0px 8px 30px rgba(0, 0, 0, 0.08);
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
<aside class="hidden lg:flex flex-col h-screen sticky top-0 p-md space-y-md bg-surface-container-low border-r border-outline-variant w-64 shrink-0">
<div class="flex items-center gap-xs px-xs py-sm">
<img alt="MataramWash Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
<span class="text-headline-sm font-headline-md font-bold text-primary">MataramWash</span>
</div>
<div class="flex flex-col gap-xs py-md border-b border-outline-variant">
<p class="px-md text-label-sm text-outline uppercase tracking-widest">Main Menu</p>
<a class="flex items-center gap-sm px-md py-sm bg-primary-container text-on-primary-container rounded-lg font-bold translate-x-1 transition-transform" href="dashboard_admin.php">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
<span class="text-label-md font-label-md">Dashboard</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="manajemen_mitra.php">
<span class="material-symbols-outlined">group</span>
<span class="text-label-md font-label-md">Manajemen Mitra</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="operational_area.php">
<span class="material-symbols-outlined">map</span>
<span class="text-label-md font-label-md">Wilayah Operasional</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="analitik_kemitraan.php">
<span class="material-symbols-outlined">analytics</span>
<span class="text-label-md font-label-md">Analitik Kemitraan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="financial_statements.php">
<span class="material-symbols-outlined">payments</span>
<span class="text-label-md font-label-md">Laporan Keuangan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="settings.php">
<span class="material-symbols-outlined">settings</span>
<span class="text-label-md font-label-md">Settings</span>
</a>
</div>
<div class="flex-grow"></div>
<div class="flex flex-col gap-xs py-md">
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="../bantuan/bantuan.php">
<span class="material-symbols-outlined">help</span>
<span class="text-label-md font-label-md">Help</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="logout_admin.php">
<span class="material-symbols-outlined text-error">logout</span>
<span class="text-label-md font-label-md text-error">Logout</span>
</a>
</div>

</aside>
<!-- Main Content Canvas -->
<main class="flex-grow flex flex-col h-screen overflow-hidden">
<!-- TopAppBar -->
<header class="sticky top-0 w-full z-50 flex justify-between items-center px-lg py-md bg-surface shadow-sm max-w-none">
<div class="flex items-center gap-md flex-1">
<div class="lg:hidden">
<span class="material-symbols-outlined text-on-surface-variant">menu</span>
</div>
<div class="relative max-w-md w-full">
<span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline">search</span>
<input class="w-full pl-xl pr-md py-xs bg-surface-container-low border-none rounded-xl focus:ring-2 focus:ring-primary text-body-md" placeholder="Cari mitra, wilayah, atau laporan..." type="text">
</div>
</div>
<div class="flex items-center gap-md">
<button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-high transition-colors relative">
<span class="material-symbols-outlined text-on-surface-variant">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
</button>
<div class="h-8 w-[1px] bg-outline-variant mx-xs"></div>
<div class="flex items-center gap-sm">
<div class="text-right hidden sm:block">
<p class="text-label-md font-bold leading-none"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Budi Santoso'); ?></p>
<p class="text-label-sm text-outline leading-tight">Administrator</p>
</div>
<img alt="Admin profile" class="w-9 h-9 rounded-full object-cover border border-outline-variant" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVvTfpl6gmSbn7utdVTjVT1ZrHaIbCt76OBU9jA9oc3rue19H1ElhbliNLU8FUVfCMZWMCOXO6ZI0EBlE68GvL7TdpDcdz05FrUqtzRUVrrTQKcC_MwtAKGFkV_XAbFOxIpl3JRF93_22IuQMGYGKqzXSHUZRnab8I7P_AWzrPQKLrh9PmQd4pqpbRW8v-5sKU_uUJt1jpvrX5bWXDDQshtNQtM9DcfB5GsKwZW-zFy6P6DnFBWUY_oCDubbBHW4BXb1p5RWiXyyg">
</div>
</div>
</header>
<!-- Scrollable Area -->
<div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
<div class="max-w-7xl mx-auto space-y-xl">
<!-- Greeting Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-md">
<div>
<h1 class="text-headline-lg font-headline-lg text-on-surface">Pusat Kendali Mitra MataramWash</h1>
<p class="text-body-lg text-on-surface-variant">Monitor performa dan sebaran mitra di wilayah Mataram dan sekitarnya.</p>
</div>
</div>
<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-lg">
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-primary-fixed text-primary rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">handshake</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Total Mitra Aktif</p>
<p class="text-headline-md font-bold"><?= $active_mitras_count; ?></p>
</div>
</div>
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-secondary-container text-secondary rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">orders</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Total Pesanan Provinsi</p>
<p class="text-headline-md font-bold"><?= $active_mitras_count > 0 ? 120 + $active_mitras_count * 15 : 0; ?></p>
</div>
</div>
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-tertiary-fixed text-tertiary rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">payments</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Pendapatan Mitra</p>
<p class="text-headline-md font-bold">Rp <?= number_format($active_mitras_count > 0 ? 1500000 + $active_mitras_count * 220000 : 0, 0, ',', '.'); ?></p>
</div>
</div>
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-surface-container-highest text-on-surface-variant rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">grade</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Avg Rating Provinsi</p>
<p class="text-headline-md font-bold"><?= $avg_rating; ?>/5</p>
</div>
</div>
</div>
<!-- Performance Section (Bento Layout) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
<div class="lg:col-span-2 bento-card p-lg rounded-xl flex flex-col">
<div class="flex justify-between items-center mb-lg">
<h2 class="text-headline-sm font-bold">Mitra Aktif Berdasarkan Wilayah</h2>
<select class="bg-surface-container-low border-none rounded-lg text-label-sm py-xs pl-sm pr-lg focus:ring-primary">
<option>Semua Wilayah</option>
</select>
</div>
<div class="flex-grow flex items-end justify-between gap-md h-48 pt-md px-md">
<?php foreach ($chart_areas as $area): ?>
<?php 
$count = $sebaran_data[$area] ?? 0;
$height = $max_count > 0 ? ($count / $max_count) * 80 : 0; 
$is_max = ($max_count > 0 && $count === $max_count);
$bg_class = $is_max ? 'bg-primary' : 'bg-primary-fixed hover:bg-primary/80';
$font_class = $is_max ? 'font-bold text-on-surface' : 'text-outline';
?>
<div class="w-full flex flex-col items-center gap-xs group relative h-full justify-end">
    <!-- Tooltip on hover -->
    <div class="absolute bottom-full mb-xs bg-inverse-surface text-inverse-on-surface text-[11px] px-xs py-[2px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-sm">
        <?= $count; ?> Mitra Aktif
    </div>
    <div class="w-12 <?= $bg_class; ?> rounded-t-lg transition-all duration-300" style="height: <?= max(4, $height); ?>%;"></div>
    <span class="text-[11px] <?= $font_class; ?> text-center truncate w-full mt-xs" title="<?= htmlspecialchars($area); ?>"><?= htmlspecialchars($area); ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
<div class="bento-card p-lg rounded-xl space-y-md flex flex-col justify-between">
<h2 class="text-headline-sm font-bold">Sebaran Mitra</h2>
<div class="space-y-xs overflow-y-auto max-h-48 custom-scrollbar">
<?php if (empty($sebaran_data)): ?>
<div class="text-center py-10 text-on-surface-variant">
<span class="material-symbols-outlined text-outline text-[40px] mb-2">location_off</span>
<p class="text-label-md font-semibold">Belum ada sebaran mitra</p>
</div>
<?php else: ?>
<?php foreach ($sebaran_data as $area => $count): ?>
<div class="flex justify-between items-center p-sm bg-surface-container-low rounded-xl">
<div class="flex items-center gap-xs">
<span class="material-symbols-outlined text-primary text-[20px]">location_on</span>
<span class="text-label-md font-semibold text-on-surface"><?= htmlspecialchars($area); ?></span>
</div>
<span class="px-sm py-[2px] bg-primary-container text-on-primary-container rounded-full font-bold text-label-sm text-[12px]"><?= $count; ?> Toko</span>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<a href="../lokasi/locations.php" class="block w-full text-center py-xs bg-surface-container hover:bg-surface-container-high text-primary font-bold text-label-md rounded-xl transition-colors">Detail Peta Wilayah</a>
</div>
</div>
<!-- Recent Partners Table -->
<div class="bento-card rounded-xl overflow-hidden">
<div class="px-lg py-md flex justify-between items-center border-b border-outline-variant">
<h2 class="text-headline-sm font-bold">Aktivitas Mitra Terkini</h2>
<button class="text-primary font-bold text-label-md">Lihat Semua Mitra</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low">
<th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Nama Mitra</th>
<th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Wilayah</th>
<th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Mesin Aktif</th>
<th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Status</th>
<th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right">Skor Performa</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php if (empty($all_mitras)): ?>
<tr>
<td colspan="5" class="px-lg py-12 text-center text-on-surface-variant">
<span class="material-symbols-outlined text-outline text-[48px] mb-2">storefront_off</span>
<p class="text-body-md font-semibold">Belum ada aktivitas mitra saat ini</p>
</td>
</tr>
<?php else: ?>
<?php foreach ($all_mitras as $mitra): ?>
<?php
// Extract city/district from address
$address_parts = explode(',', $mitra['alamat']);
$city = trim(end($address_parts));
if (empty($city) && count($address_parts) > 1) {
    $city = trim($address_parts[count($address_parts) - 2]);
}
if (empty($city)) {
    $city = 'Mataram';
}
// Strip details if too long
if (strlen($city) > 18) {
    $city = substr($city, 0, 15) . '...';
}

$file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
$has_file = file_exists('../Mitra laundry/' . $file_name);
?>
<tr class="hover:bg-surface-container-low transition-colors">
<td class="px-lg py-md text-body-md font-bold text-on-surface"><?= htmlspecialchars($mitra['nama_mitra']); ?></td>
<td class="px-lg py-md text-body-md text-on-surface-variant"><?= htmlspecialchars($city); ?></td>
<td class="px-lg py-md text-body-md text-on-surface-variant">
    <?php 
    if ($mitra['icon_type'] === 'sepatu') {
        echo 'Special Care';
    } else {
        echo 'Kiloan & Satuan';
    }
    ?>
</td>
<td class="px-lg py-md">
    <?php if ($mitra['status_buka'] == 1 && $has_file): ?>
        <span class="px-sm py-xs bg-emerald-50 text-emerald-700 rounded-full font-bold text-label-sm">Aktif / Buka</span>
    <?php elseif (!$has_file): ?>
        <span class="px-sm py-xs bg-amber-50 text-amber-700 rounded-full font-bold text-label-sm">Draft / Belum Rilis</span>
    <?php else: ?>
        <span class="px-sm py-xs bg-slate-100 text-slate-500 rounded-full font-bold text-label-sm">Tutup</span>
    <?php endif; ?>
</td>
<td class="px-lg py-md text-body-md font-bold text-right text-tertiary">
    <div class="flex items-center justify-end gap-[2px]">
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
        <span><?= htmlspecialchars($mitra['rating']); ?></span>
    </div>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
<div class="px-lg py-md bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
<p class="text-label-sm text-outline">Menampilkan <?= count($all_mitras); ?> dari <?= count($all_mitras); ?> mitra terdaftar</p>
<div class="flex gap-xs">
<button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-white transition-colors disabled:opacity-50" disabled=""><span class="material-symbols-outlined text-[18px]">chevron_left</span></button>
<button class="w-8 h-8 flex items-center justify-center rounded border border-primary bg-primary text-on-primary font-bold text-label-sm transition-colors">1</button>
<button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-white transition-colors disabled:opacity-50" disabled=""><span class="material-symbols-outlined text-[18px]">chevron_right</span></button>
</div>
</div>
</div>
</div>
</div>
<!-- Footer -->
<footer class="w-full py-md px-lg bg-surface-container-highest flex justify-between items-center text-on-surface-variant">
<p class="text-label-sm">© 2024 MataramWash Provincial Partnership Program. Freshness across the region.</p>
<div class="flex gap-lg">
<a class="text-label-sm hover:text-primary transition-colors" href="../bantuan/bantuan.php">Pusat Bantuan</a>
<a class="text-label-sm hover:text-primary transition-colors" href="#">Kebijakan Kemitraan</a>
</div>
</footer>
</main>
<script>
        // Simple micro-interactions
        document.querySelectorAll('.bento-card').forEach(card => {
            card.addEventListener('mousedown', () => {
                card.style.transform = 'scale(0.98)';
            });
            card.addEventListener('mouseup', () => {
                card.style.transform = 'translateY(-2px)';
            });
        });

        // Search bar focus effect
        const searchInput = document.querySelector('input[type="text"]');
        if(searchInput) {
            searchInput.addEventListener('focus', () => {
                searchInput.parentElement.classList.add('ring-2', 'ring-primary');
            });
            searchInput.addEventListener('blur', () => {
                searchInput.parentElement.classList.remove('ring-2', 'ring-primary');
            });
        }
    </script>
</body></html>
