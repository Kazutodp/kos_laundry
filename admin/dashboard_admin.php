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
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-card-blue:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(59, 130, 246, 0.18), 0 8px 16px -8px rgba(59, 130, 246, 0.18);
            border-color: rgba(59, 130, 246, 0.4);
        }
        .bento-card-emerald:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(16, 185, 129, 0.18), 0 8px 16px -8px rgba(16, 185, 129, 0.18);
            border-color: rgba(16, 185, 129, 0.4);
        }
        .bento-card-violet:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(139, 92, 246, 0.18), 0 8px 16px -8px rgba(139, 92, 246, 0.18);
            border-color: rgba(139, 92, 246, 0.4);
        }
        .bento-card-amber:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -10px rgba(245, 158, 11, 0.18), 0 8px 16px -8px rgba(245, 158, 11, 0.18);
            border-color: rgba(245, 158, 11, 0.4);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(203, 213, 225, 0.6);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.8);
        }
        .chart-grid {
            background-image: linear-gradient(to top, #f1f5f9 1px, transparent 1px);
            background-size: 100% 25%;
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex overflow-hidden">
<!-- SideNavBar -->
<aside class="hidden lg:flex flex-col h-screen sticky top-0 p-md space-y-md bg-slate-900 border-r border-slate-800 w-64 shrink-0 text-slate-300">
<div class="flex items-center gap-xs px-xs py-sm border-b border-slate-800">
<img alt="MataramWash Logo" class="h-8 w-8 object-contain brightness-110 filter" src="../logo.png?v=3">
<span class="text-headline-sm font-headline-md font-extrabold text-white">MataramWash</span>
</div>
<div class="flex flex-col gap-xs py-md border-b border-slate-800">
<p class="px-md text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-xs">Main Menu</p>
<a class="flex items-center gap-sm px-md py-sm bg-blue-600 text-white rounded-xl font-bold border-l-4 border-blue-400 shadow-lg shadow-blue-900/30 transition-all duration-200" href="dashboard_admin.php">
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
<header class="sticky top-0 w-full z-40 flex justify-between items-center px-lg py-md bg-white border-b border-slate-100 max-w-none">
<div class="flex items-center gap-md flex-1">
<div class="lg:hidden cursor-pointer hover:text-primary transition-colors">
<span class="material-symbols-outlined text-slate-600">menu</span>
</div>
<div class="relative max-w-md w-full">
<span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-slate-400">search</span>
<input class="w-full pl-xl pr-md py-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-body-md transition-all placeholder:text-slate-400 text-slate-700" placeholder="Cari mitra, wilayah, atau laporan..." type="text">
</div>
</div>
<div class="flex items-center gap-md">
<button class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-50 text-slate-600 hover:text-blue-500 transition-colors relative">
<span class="material-symbols-outlined text-[22px]">notifications</span>
<span class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
<span class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 rounded-full"></span>
</button>
<div class="h-6 w-[1px] bg-slate-200 mx-xs"></div>
<div class="flex items-center gap-sm">
<div class="text-right hidden sm:block">
<p class="text-label-md font-extrabold text-slate-800 leading-none"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Budi Santoso'); ?></p>
<p class="text-label-sm text-slate-400 leading-tight mt-1">Administrator</p>
</div>
<img alt="Admin profile" class="w-9 h-9 rounded-full object-cover border-2 border-slate-100 shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVvTfpl6gmSbn7utdVTjVT1ZrHaIbCt76OBU9jA9oc3rue19H1ElhbliNLU8FUVfCMZWMCOXO6ZI0EBlE68GvL7TdpDcdz05FrUqtzRUVrrTQKcC_MwtAKGFkV_XAbFOxIpl3JRF93_22IuQMGYGKqzXSHUZRnab8I7P_AWzrPQKLrh9PmQd4pqpbRW8v-5sKU_uUJt1jpvrX5bWXDDQshtNQtM9DcfB5GsKwZW-zFy6P6DnFBWUY_oCDubbBHW4BXb1p5RWiXyyg">
</div>
</div>
</header>
<!-- Scrollable Area -->
<div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
<div class="max-w-7xl mx-auto space-y-xl">
<!-- Greeting Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-md">
<div>
<h1 class="text-headline-lg font-headline-lg text-slate-800 font-extrabold">Pusat Kendali Mitra MataramWash</h1>
<p class="text-body-lg text-slate-500 mt-1">Monitor performa dan sebaran mitra di wilayah Mataram dan sekitarnya.</p>
</div>
</div>
<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-lg">
<!-- Card 1 -->
<div class="bento-card bento-card-blue p-lg rounded-2xl flex items-center gap-lg bg-gradient-to-br from-blue-50/50 to-white border-blue-100/60 shadow-xs">
<div class="w-14 h-14 bg-blue-500/10 text-blue-600 rounded-2xl flex items-center justify-center">
<span class="material-symbols-outlined text-[32px] fill-icon">handshake</span>
</div>
<div>
<p class="text-label-md text-slate-500 font-semibold">Total Mitra Aktif</p>
<p class="text-xl font-extrabold text-slate-800 mt-0.5"><?= $active_mitras_count; ?></p>
<span class="text-[11px] text-emerald-600 font-bold flex items-center gap-[2px] mt-1">
  <span class="material-symbols-outlined text-[12px]">trending_up</span> +2 baru minggu ini
</span>
</div>
</div>
<!-- Card 2 -->
<div class="bento-card bento-card-emerald p-lg rounded-2xl flex items-center gap-lg bg-gradient-to-br from-emerald-50/50 to-white border-emerald-100/60 shadow-xs">
<div class="w-14 h-14 bg-emerald-500/10 text-emerald-600 rounded-2xl flex items-center justify-center">
<span class="material-symbols-outlined text-[32px] fill-icon">local_laundry_service</span>
</div>
<div>
<p class="text-label-md text-slate-500 font-semibold">Total Pesanan Provinsi</p>
<p class="text-xl font-extrabold text-slate-800 mt-0.5"><?= $active_mitras_count > 0 ? 120 + $active_mitras_count * 15 : 0; ?></p>
<span class="text-[11px] text-emerald-600 font-bold flex items-center gap-[2px] mt-1">
  <span class="material-symbols-outlined text-[12px]">trending_up</span> +14.2% vs bln lalu
</span>
</div>
</div>
<!-- Card 3 -->
<div class="bento-card bento-card-violet p-lg rounded-2xl flex items-center gap-lg bg-gradient-to-br from-violet-50/50 to-white border-violet-100/60 shadow-xs">
<div class="w-14 h-14 bg-violet-500/10 text-violet-600 rounded-2xl flex items-center justify-center">
<span class="material-symbols-outlined text-[32px] fill-icon">payments</span>
</div>
<div>
<p class="text-label-md text-slate-500 font-semibold">Pendapatan Mitra</p>
<p class="text-xl font-extrabold text-slate-800 mt-0.5">Rp <?= number_format($active_mitras_count > 0 ? 1500000 + $active_mitras_count * 220000 : 0, 0, ',', '.'); ?></p>
<span class="text-[11px] text-emerald-600 font-bold flex items-center gap-[2px] mt-1">
  <span class="material-symbols-outlined text-[12px]">trending_up</span> +Rp 440rb bln ini
</span>
</div>
</div>
<!-- Card 4 -->
<div class="bento-card bento-card-amber p-lg rounded-2xl flex items-center gap-lg bg-gradient-to-br from-amber-50/50 to-white border-amber-100/60 shadow-xs">
<div class="w-14 h-14 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center">
<span class="material-symbols-outlined text-[32px] fill-icon">grade</span>
</div>
<div>
<p class="text-label-md text-slate-500 font-semibold">Avg Rating Provinsi</p>
<p class="text-xl font-extrabold text-slate-800 mt-0.5"><?= $avg_rating; ?>/5</p>
<span class="text-[11px] text-amber-600 font-bold flex items-center gap-[2px] mt-1">
  <span class="material-symbols-outlined text-[12px]">star</span> Sangat Baik
</span>
</div>
</div>
</div>
<!-- Performance Section (Bento Layout) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
<!-- Region Active Partners Chart -->
<div class="lg:col-span-2 bento-card p-lg rounded-2xl flex flex-col border border-slate-100 shadow-xs">
<div class="flex justify-between items-center mb-lg">
<h2 class="text-headline-sm font-extrabold text-slate-800 text-[18px]">Mitra Aktif Berdasarkan Wilayah</h2>
<select class="bg-slate-50 border border-slate-200 rounded-xl text-label-sm py-xs pl-sm pr-lg text-slate-600 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
<option>Semua Wilayah</option>
</select>
</div>
<div class="flex-grow flex items-end justify-between gap-md h-56 pt-md px-md chart-grid">
<?php foreach ($chart_areas as $area): ?>
<?php 
$count = $sebaran_data[$area] ?? 0;
$height = $max_count > 0 ? ($count / $max_count) * 80 : 0; 
$is_max = ($max_count > 0 && $count === $max_count);
$bg_class = $is_max ? 'from-blue-500 to-blue-600 shadow-md shadow-blue-500/10' : 'from-blue-200 to-blue-300 hover:from-blue-300 hover:to-blue-400';
$font_class = $is_max ? 'font-bold text-blue-600' : 'text-slate-400';
?>
<div class="w-full flex flex-col items-center gap-xs group relative h-full justify-end cursor-pointer">
    <!-- Tooltip on hover -->
    <div class="absolute bottom-full mb-xs bg-slate-800 text-white text-[11px] px-sm py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-md">
        <?= $count; ?> Mitra Aktif
    </div>
    <div class="w-8 bg-gradient-to-t <?= $bg_class; ?> rounded-t-lg transition-all duration-300 group-hover:scale-105" style="height: <?= max(4, $height); ?>%;"></div>
    <span class="text-[11px] <?= $font_class; ?> text-center truncate w-full mt-xs" title="<?= htmlspecialchars($area); ?>"><?= htmlspecialchars($area); ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
<!-- Region Distribution Gauges -->
<div class="bento-card p-lg rounded-2xl flex flex-col justify-between border border-slate-100 shadow-xs">
<h2 class="text-headline-sm font-extrabold text-slate-800 text-[18px] mb-md">Sebaran Mitra</h2>
<div class="space-y-md overflow-y-auto max-h-56 custom-scrollbar pr-xs">
<?php if (empty($sebaran_data)): ?>
<div class="text-center py-10 text-slate-400">
<span class="material-symbols-outlined text-[40px] mb-2">location_off</span>
<p class="text-label-md font-semibold">Belum ada sebaran mitra</p>
</div>
<?php else: ?>
<?php 
$total_mitras = count($all_mitras);
foreach ($sebaran_data as $area => $count): 
$percentage = $total_mitras > 0 ? ($count / $total_mitras) * 100 : 0;
?>
<div class="space-y-xs">
    <div class="flex justify-between items-center text-[12px]">
        <span class="font-bold text-slate-700 flex items-center gap-1">
            <span class="material-symbols-outlined text-blue-500 text-[18px]">location_on</span>
            <?= htmlspecialchars($area); ?>
        </span>
        <span class="text-slate-500 font-semibold"><?= $count; ?> Mitra (<?= number_format($percentage, 0); ?>%)</span>
    </div>
    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
        <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-full rounded-full" style="width: <?= $percentage; ?>%"></div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<a href="../lokasi/locations.php" class="block w-full text-center py-xs bg-slate-50 hover:bg-slate-100 text-blue-600 font-bold text-label-md rounded-xl border border-slate-200 mt-md transition-all">Detail Peta Wilayah</a>
</div>
</div>
<!-- Recent Partners Table -->
<div class="bento-card rounded-2xl overflow-hidden border border-slate-100 shadow-xs">
<div class="px-lg py-md flex justify-between items-center border-b border-slate-100">
<h2 class="text-headline-sm font-extrabold text-slate-800 text-[18px]">Aktivitas Mitra Terkini</h2>
<button class="text-blue-600 font-bold text-label-md hover:underline">Lihat Semua Mitra</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-slate-50 border-b border-slate-100">
<th class="px-lg py-md text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama Mitra</th>
<th class="px-lg py-md text-[11px] font-bold text-slate-400 uppercase tracking-wider">Wilayah</th>
<th class="px-lg py-md text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tipe Layanan</th>
<th class="px-lg py-md text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
<th class="px-lg py-md text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Skor Performa</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
<?php if (empty($all_mitras)): ?>
<tr>
<td colspan="5" class="px-lg py-12 text-center text-slate-400">
<span class="material-symbols-outlined text-[48px] mb-2">storefront_off</span>
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
<tr class="hover:bg-slate-50/50 transition-colors">
<td class="px-lg py-md text-body-md font-extrabold text-slate-800"><?= htmlspecialchars($mitra['nama_mitra']); ?></td>
<td class="px-lg py-md text-body-md text-slate-500"><?= htmlspecialchars($city); ?></td>
<td class="px-lg py-md text-body-md text-slate-500">
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
        <span class="px-sm py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-label-sm text-[12px] inline-flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Aktif / Buka
        </span>
    <?php elseif (!$has_file): ?>
        <span class="px-sm py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-bold text-label-sm text-[12px] inline-flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            Draft / Belum Rilis
        </span>
    <?php else: ?>
        <span class="px-sm py-1 bg-slate-50 text-slate-400 border border-slate-200 rounded-full font-bold text-label-sm text-[12px] inline-flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
            Tutup
        </span>
    <?php endif; ?>
</td>
<td class="px-lg py-md text-body-md font-bold text-right text-amber-500">
    <div class="flex items-center justify-end gap-[2px]">
        <span class="material-symbols-outlined text-[18px] fill-icon" style="font-variation-settings: 'FILL' 1;">star</span>
        <span><?= htmlspecialchars($mitra['rating']); ?></span>
    </div>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
<div class="px-lg py-md bg-slate-50 flex justify-between items-center border-t border-slate-100">
<p class="text-label-sm text-slate-400">Menampilkan <?= count($all_mitras); ?> dari <?= count($all_mitras); ?> mitra terdaftar</p>
<div class="flex gap-xs">
<button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-white hover:text-slate-600 transition-colors disabled:opacity-50" disabled=""><span class="material-symbols-outlined text-[18px]">chevron_left</span></button>
<button class="w-8 h-8 flex items-center justify-center rounded-lg border border-blue-600 bg-blue-600 text-white font-bold text-label-sm transition-colors shadow-sm shadow-blue-500/10">1</button>
<button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-white hover:text-slate-600 transition-colors disabled:opacity-50" disabled=""><span class="material-symbols-outlined text-[18px]">chevron_right</span></button>
</div>
</div>
</div>
</div>
</div>
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
