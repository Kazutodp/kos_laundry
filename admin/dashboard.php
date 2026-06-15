<?php
session_start();
require_once '../koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php?error=" . urlencode("Silakan masuk terlebih dahulu untuk mengakses panel admin."));
    exit();
}

$admin_nama = htmlspecialchars($_SESSION['admin_nama']);
?>
<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Mataram Partnership Portal | KosanLaundry</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<img src="../logo.png?v=2" alt="Logo" class="w-8 h-8 object-contain" />
<span class="text-headline-sm font-headline-md font-bold text-primary">KosanLaundry</span>
</div>
<div class="flex flex-col gap-xs py-md border-b border-outline-variant">
<p class="px-md text-label-sm text-outline uppercase tracking-widest">Main Menu</p>
<a class="flex items-center gap-sm px-md py-sm bg-primary-container text-on-primary-container rounded-lg font-bold translate-x-1 transition-transform" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
<span class="text-label-md font-label-md">Dashboard</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">group</span>
<span class="text-label-md font-label-md">Manajemen Mitra</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">map</span>
<span class="text-label-md font-label-md">Wilayah Operasional</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">analytics</span>
<span class="text-label-md font-label-md">Analitik Mataram</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">payments</span>
<span class="text-label-md font-label-md">Laporan Keuangan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">settings</span>
<span class="text-label-md font-label-md">Settings</span>
</a>
</div>
<div class="flex-grow"></div>
<div class="flex flex-col gap-xs py-md">
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">help</span>
<span class="text-label-md font-label-md">Help</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="logout.php">
<span class="material-symbols-outlined text-error">logout</span>
<span class="text-label-md font-label-md text-error">Logout</span>
</a>
</div>
<div class="p-md flex items-center gap-sm border-t border-outline-variant">
<div class="w-10 h-10 bg-primary/20 text-primary rounded-full flex items-center justify-center font-bold shrink-0">
    A
</div>
<div class="overflow-hidden">
<p class="text-label-md font-bold truncate"><?= $admin_nama; ?></p>
<p class="text-label-sm text-on-surface-variant truncate">KosanLaundry Mataram</p>
</div>
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
<input class="w-full pl-xl pr-md py-xs bg-surface-container-low border-none rounded-xl focus:ring-2 focus:ring-primary text-body-md" placeholder="Cari mitra, wilayah, atau laporan..." type="text"/>
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
<p class="text-label-md font-bold leading-none"><?= $admin_nama; ?></p>
<p class="text-label-sm text-outline leading-tight">Mataram Lead Partner</p>
</div>
<div class="w-9 h-9 bg-primary/25 text-primary rounded-full flex items-center justify-center font-bold border border-outline-variant">
    A
</div>
</div>
</div>
</header>
<!-- Scrollable Area -->
<div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
<div class="max-w-7xl mx-auto space-y-xl">
<!-- Greeting Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-md">
<div>
<h1 class="text-headline-lg font-headline-lg text-on-surface">Pusat Kendali Kemitraan Mataram</h1>
<p class="text-body-lg text-on-surface-variant">Monitor performa dan sebaran mitra di seluruh wilayah Kota Mataram.</p>
</div>
<button class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all">
<span class="material-symbols-outlined">person_add</span>
                        Tambah Mitra Baru
                    </button>
</div>
<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-lg">
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-primary-fixed text-primary rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">handshake</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Total Mitra Aktif</p>
<p class="text-headline-md font-bold">18</p>
</div>
</div>
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-secondary-container text-secondary rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">orders</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Total Pesanan Mataram</p>
<p class="text-headline-md font-bold">1,284</p>
</div>
</div>
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-tertiary-fixed text-tertiary rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">payments</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Pendapatan Kemitraan</p>
<p class="text-headline-md font-bold">Rp 4.5M</p>
</div>
</div>
<div class="bento-card p-lg rounded-xl flex items-center gap-lg">
<div class="w-14 h-14 bg-surface-container-highest text-on-surface-variant rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-[32px]">grade</span>
</div>
<div>
<p class="text-label-md text-on-surface-variant">Avg Rating Mataram</p>
<p class="text-headline-md font-bold">4.8/5</p>
</div>
</div>
</div>
<!-- Performance Section (Bento Layout) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
<!-- Regional Performance Bar Chart -->
<div class="lg:col-span-2 bento-card p-lg rounded-xl flex flex-col">
<div class="flex justify-between items-center mb-lg">
<h2 class="text-headline-sm font-bold">Performa Berdasarkan Kecamatan</h2>
<select class="bg-surface-container-low border-none rounded-lg text-label-sm py-xs pl-sm pr-lg focus:ring-primary">
<option>Bulan Ini</option>
<option>Kuartal Ini</option>
</select>
</div>
<div class="flex-grow flex items-end justify-between gap-base h-48 pt-md px-md">
<div class="w-full flex flex-col items-center gap-xs">
<div class="w-full bg-primary-fixed rounded-t-lg transition-all duration-500 hover:bg-primary" style="height: 95%;"></div>
<span class="text-label-sm text-outline">Mataram</span>
</div>
<div class="w-full flex flex-col items-center gap-xs">
<div class="w-full bg-primary-fixed rounded-t-lg transition-all duration-500 hover:bg-primary" style="height: 45%;"></div>
<span class="text-label-sm text-outline">Ampenan</span>
</div>
<div class="w-full flex flex-col items-center gap-xs">
<div class="w-full bg-primary-fixed rounded-t-lg transition-all duration-500 hover:bg-primary" style="height: 75%;"></div>
<span class="text-label-sm text-outline">Cakra</span>
</div>
<div class="w-full flex flex-col items-center gap-xs">
<div class="w-full bg-primary-container rounded-t-lg transition-all duration-500 hover:bg-primary" style="height: 60%;"></div>
<span class="text-label-sm text-outline font-bold text-on-surface">Selaparang</span>
</div>
<div class="w-full flex flex-col items-center gap-xs">
<div class="w-full bg-primary-fixed rounded-t-lg transition-all duration-500 hover:bg-primary" style="height: 35%;"></div>
<span class="text-label-sm text-outline">Sekarbela</span>
</div>
<div class="w-full flex flex-col items-center gap-xs">
<div class="w-full bg-primary-fixed rounded-t-lg transition-all duration-500 hover:bg-primary" style="height: 55%;"></div>
<span class="text-label-sm text-outline">Sandubaya</span>
</div>
</div>
</div>
<!-- Partner Distribution Card -->
<div class="bento-card p-lg rounded-xl space-y-md">
<h2 class="text-headline-sm font-bold">Sebaran Mitra</h2>
<div class="space-y-sm">
<div class="flex items-center justify-between p-sm bg-surface rounded-lg border border-outline-variant">
<div class="flex items-center gap-sm">
<div class="w-2 h-2 bg-primary rounded-full"></div>
<span class="text-label-md">Kec. Mataram</span>
</div>
<span class="text-label-sm font-bold">6 Mitra</span>
</div>
<div class="flex items-center justify-between p-sm bg-surface rounded-lg border border-outline-variant">
<div class="flex items-center gap-sm">
<div class="w-2 h-2 bg-primary rounded-full"></div>
<span class="text-label-md">Kec. Ampenan</span>
</div>
<span class="text-label-sm font-bold">4 Mitra</span>
</div>
<div class="flex items-center justify-between p-sm bg-surface rounded-lg border border-outline-variant">
<div class="flex items-center gap-sm">
<div class="w-2 h-2 bg-primary rounded-full"></div>
<span class="text-label-md">Kec. Cakranegara</span>
</div>
<span class="text-label-sm font-bold">4 Mitra</span>
</div>
<div class="flex items-center justify-between p-sm bg-surface rounded-lg border border-outline-variant">
<div class="flex items-center gap-sm">
<div class="w-2 h-2 bg-primary rounded-full"></div>
<span class="text-label-md">Kec. Sekarbela</span>
</div>
<span class="text-label-sm font-bold">2 Mitra</span>
</div>
</div>
<button class="w-full py-xs text-primary font-bold text-label-md hover:underline">Detail Peta Wilayah</button>
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
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="px-lg py-md text-body-md font-bold">Laundry Sejahtera Ampenan</td>
<td class="px-lg py-md text-body-md">Ampenan, Mataram</td>
<td class="px-lg py-md text-body-md">8/10</td>
<td class="px-lg py-md">
<span class="px-sm py-xs bg-secondary-container text-secondary rounded-full text-label-sm font-bold">Online</span>
</td>
<td class="px-lg py-md text-right">
<span class="font-bold text-primary">9.8</span>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="px-lg py-md text-body-md font-bold">Bersih Kilat Cakranegara</td>
<td class="px-lg py-md text-body-md">Cakranegara, Mataram</td>
<td class="px-lg py-md text-body-md">12/12</td>
<td class="px-lg py-md">
<span class="px-sm py-xs bg-secondary-container text-secondary rounded-full text-label-sm font-bold">Online</span>
</td>
<td class="px-lg py-md text-right">
<span class="font-bold text-primary">9.5</span>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="px-lg py-md text-body-md font-bold">Mitra Laundry Selaparang</td>
<td class="px-lg py-md text-body-md">Selaparang, Mataram</td>
<td class="px-lg py-md text-body-md">4/10</td>
<td class="px-lg py-md">
<span class="px-sm py-xs bg-surface-container-highest text-on-surface-variant rounded-full text-label-sm font-bold">Maintenance</span>
</td>
<td class="px-lg py-md text-right">
<span class="font-bold text-tertiary">7.2</span>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="px-lg py-md text-body-md font-bold">Rapi Jaya Sekarbela</td>
<td class="px-lg py-md text-body-md">Sekarbela, Mataram</td>
<td class="px-lg py-md text-body-md">0/8</td>
<td class="px-lg py-md">
<span class="px-sm py-xs bg-error-container text-error rounded-full text-label-sm font-bold">Offline</span>
</td>
<td class="px-lg py-md text-right">
<span class="font-bold text-error">4.5</span>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="px-lg py-md text-body-md font-bold">Laundry Sandubaya Super</td>
<td class="px-lg py-md text-body-md">Sandubaya, Mataram</td>
<td class="px-lg py-md text-body-md">6/6</td>
<td class="px-lg py-md">
<span class="px-sm py-xs bg-secondary-container text-secondary rounded-full text-label-sm font-bold">Online</span>
</td>
<td class="px-lg py-md text-right">
<span class="font-bold text-primary">9.2</span>
</td>
</tr>
</tbody>
</table>
</div>
<div class="px-lg py-md bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
<p class="text-label-sm text-outline">Menampilkan 5 dari 18 mitra terdaftar</p>
<div class="flex gap-xs">
<button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-white transition-colors disabled:opacity-50" disabled=""><span class="material-symbols-outlined text-[18px]">chevron_left</span></button>
<button class="w-8 h-8 flex items-center justify-center rounded border border-primary bg-primary text-on-primary font-bold text-label-sm transition-colors">1</button>
<button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-white transition-colors font-bold text-label-sm">2</button>
<button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-white transition-colors font-bold text-label-sm">3</button>
<button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-white transition-colors"><span class="material-symbols-outlined text-[18px]">chevron_right</span></button>
</div>
</div>
</div>
</div>
</div>
<!-- Footer -->
<footer class="w-full py-md px-lg bg-surface-container-highest flex justify-between items-center text-on-surface-variant">
<p class="text-label-sm">© 2024 KosanLaundry Mataram Partnership Program. Freshness across the city.</p>
<div class="flex gap-lg">
<a class="text-label-sm hover:text-primary transition-colors" href="#">Pusat Bantuan</a>
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
