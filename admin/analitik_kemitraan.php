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
    $total_rating = 0;
    $rating_count = 0;
    $total_price = 0;
    
    // Service categories counter
    $service_counts = [
        'kiloan' => 0,
        'express' => 0,
        'sepatu' => 0,
        'eco' => 0,
        'satuan' => 0
    ];
    
    foreach ($raw_mitras as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../Mitra laundry/' . $file_name)) {
            $mitra_list[] = $mitra;
            
            if ($mitra['rating'] > 0) {
                $total_rating += $mitra['rating'];
                $rating_count++;
            }
            
            $total_price += $mitra['harga_per_kg'];
            
            $type = $mitra['icon_type'] ?? 'kiloan';
            if (isset($service_counts[$type])) {
                $service_counts[$type]++;
            } else {
                $service_counts['kiloan']++;
            }
        }
    }
    
    $active_count = count($mitra_list);
    $avg_rating = $rating_count > 0 ? number_format($total_rating / $rating_count, 1, '.', '') : '0.0';
    $avg_price = $active_count > 0 ? round($total_price / $active_count) : 0;
    
    // Calculate percentages for service types
    $service_percentages = [];
    foreach ($service_counts as $key => $count) {
        $service_percentages[$key] = $active_count > 0 ? round(($count / $active_count) * 100) : 0;
    }
    
    // Calculate actual total revenue (Omset Bulanan) from orders table
    $total_revenue = 0;
    try {
        $rev_stmt = $pdo->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM orders WHERE status_pembayaran = 'success'");
        $total_revenue = (float)$rev_stmt->fetchColumn();
    } catch (PDOException $e) {
        $total_revenue = 0;
    }

    // Calculate weekly revenue trend from orders table
    $weekly_revenue = [
        'Minggu 1' => 0,
        'Minggu 2' => 0,
        'Minggu 3' => 0,
        'Minggu 4' => 0,
    ];
    try {
        $weeks_stmt = $pdo->query("
            SELECT 
                CASE 
                    WHEN DAY(created_at) <= 7 THEN 'Minggu 1'
                    WHEN DAY(created_at) <= 14 THEN 'Minggu 2'
                    WHEN DAY(created_at) <= 21 THEN 'Minggu 3'
                    ELSE 'Minggu 4'
                END as week_num,
                COALESCE(SUM(total_harga), 0) as weekly_sum
            FROM orders
            WHERE status_pembayaran = 'success'
              AND MONTH(created_at) = MONTH(CURRENT_DATE())
              AND YEAR(created_at) = YEAR(CURRENT_DATE())
            GROUP BY week_num
        ");
        
        while ($row = $weeks_stmt->fetch()) {
            $weekly_revenue[$row['week_num']] = (float)$row['weekly_sum'];
        }
    } catch (PDOException $e) {
        // Fallback to empty weekly revenue
    }
    
    $max_revenue = max($weekly_revenue);
    if ($max_revenue <= 0) {
        $max_revenue = 1; // avoid division by zero
    }
    
} catch (PDOException $e) {
    $mitra_list = [];
    $active_count = 0;
    $avg_rating = '0.0';
    $avg_price = 0;
    $service_counts = [];
    $weekly_revenue = [];
    $max_revenue = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Analitik Kemitraan | MataramWash Admin</title>
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
            <a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="dashboard_admin.php">
                <span class="material-symbols-outlined">dashboard</span>
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
            <a class="flex items-center gap-sm px-md py-sm bg-primary-container text-on-primary-container rounded-lg font-bold translate-x-1 transition-transform" href="analitik_kemitraan.php">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">analytics</span>
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
                <div class="text-headline-sm font-bold text-on-surface">Analitik Kemitraan</div>
            </div>
            <div class="flex items-center gap-md">
                <div class="flex items-center gap-sm">
                    <p class="text-label-md font-bold leading-none hidden sm:block"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></p>
                    <img alt="Admin profile" class="w-9 h-9 rounded-full object-cover border border-outline-variant" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVvTfpl6gmSbn7utdVTjVT1ZrHaIbCt76OBU9jA9oc3rue19H1ElhbliNLU8FUVfCMZWMCOXO6ZI0EBlE68GvL7TdpDcdz05FrUqtzRUVrrTQKcC_MwtAKGFkV_XAbFOxIpl3JRF93_22IuQMGYGKqzXSHUZRnab8I7P_AWzrPQKLrh9PmQd4pqpbRW8v-5sKU_uUJt1jpvrX5bWXDDQshtNQtM9DcfB5GsKwZW-zFy6P6DnFBWUY_oCDubbBHW4BXb1p5RWiXyyg">
                </div>
            </div>
        </header>

        <!-- Scrollable Area -->
        <div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
            <div class="max-w-7xl mx-auto space-y-lg">
                <!-- Header -->
                <div>
                    <h1 class="text-headline-lg font-headline-lg text-on-surface">Dashboard Analitik Kemitraan</h1>
                    <p class="text-body-md text-on-surface-variant">Analisis performa bisnis, persebaran layanan, omset transaksi, dan reputasi mitra laundry secara terpadu.</p>
                </div>

                <!-- Stats Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-lg">
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md">
                        <div class="w-12 h-12 bg-primary-fixed text-primary rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-[28px]">handshake</span>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Mitra Aktif</p>
                            <p class="text-headline-md font-bold text-on-surface"><?= $active_count; ?> Toko</p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md">
                        <div class="w-12 h-12 bg-secondary-container text-secondary rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-[28px]">grade</span>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Avg Rating Kepuasan</p>
                            <p class="text-headline-md font-bold text-on-surface"><?= $avg_rating; ?> / 5.0</p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md">
                        <div class="w-12 h-12 bg-tertiary-fixed text-tertiary rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-[28px]">payments</span>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Omset Pendapatan Riil</p>
                            <p class="text-headline-md font-bold text-on-surface">Rp <?= number_format($total_revenue, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md">
                        <div class="w-12 h-12 bg-surface-container-highest text-on-surface-variant rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-[28px]">sell</span>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Rata-rata Tarif</p>
                            <p class="text-headline-md font-bold text-on-surface">Rp <?= number_format($avg_price, 0, ',', '.'); ?>/kg</p>
                        </div>
                    </div>
                </div>

                <!-- Graphs Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
                    
                    <!-- Line Chart: Weekly Revenue Trend -->
                    <div class="bento-card p-lg rounded-xl flex flex-col justify-between h-[360px]">
                        <div>
                            <h2 class="text-headline-sm font-bold text-on-surface">Tren Transaksi Kemitraan (Bulanan)</h2>
                            <p class="text-body-xs text-on-surface-variant">Estimasi akumulasi pendapatan bruto per minggu dalam Rupiah.</p>
                        </div>
                        <div class="flex-grow flex items-end justify-between gap-lg pt-lg px-sm h-48">
                            <?php foreach ($weekly_revenue as $week => $amount): ?>
                                <?php 
                                $height_percent = $max_revenue > 0 ? ($amount / $max_revenue) * 75 : 0;
                                ?>
                                <div class="flex-grow flex flex-col items-center gap-xs h-full justify-end group relative">
                                    <div class="absolute bottom-full mb-xs bg-inverse-surface text-inverse-on-surface text-[10px] font-bold px-sm py-[2px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-md z-20">
                                        Rp <?= number_format($amount, 0, ',', '.'); ?>
                                    </div>
                                    <div class="w-full bg-primary-container rounded-t-lg transition-all duration-300 hover:brightness-105" style="height: <?= $height_percent; ?>%;"></div>
                                    <span class="text-[11px] font-semibold text-outline"><?= htmlspecialchars($week); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Horizontal Progress Bars: Popular Services -->
                    <div class="bento-card p-lg rounded-xl flex flex-col justify-between h-[360px]">
                        <div>
                            <h2 class="text-headline-sm font-bold text-on-surface">Sebaran Kategori Layanan Mitra</h2>
                            <p class="text-body-xs text-on-surface-variant">Persentase outlet mitra berdasarkan kategori pelayanan utama.</p>
                        </div>
                        <div class="flex-grow flex flex-col justify-center space-y-md">
                            <!-- Kiloan -->
                            <div class="space-y-xs">
                                <div class="flex justify-between text-label-sm font-semibold">
                                    <span class="text-on-surface flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-[16px] text-primary">local_laundry_service</span>
                                        Cuci Kiloan &amp; Satuan
                                    </span>
                                    <span class="text-primary"><?= $service_percentages['kiloan']; ?>% (<?= $service_counts['kiloan']; ?> Mitra)</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: <?= $service_percentages['kiloan']; ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Express -->
                            <div class="space-y-xs">
                                <div class="flex justify-between text-label-sm font-semibold">
                                    <span class="text-on-surface flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-[16px] text-amber-500">bolt</span>
                                        Layanan Kilat (Express)
                                    </span>
                                    <span class="text-amber-600"><?= $service_percentages['express']; ?>% (<?= $service_counts['express']; ?> Mitra)</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: <?= $service_percentages['express']; ?>%"></div>
                                </div>
                            </div>

                            <!-- Sepatu -->
                            <div class="space-y-xs">
                                <div class="flex justify-between text-label-sm font-semibold">
                                    <span class="text-on-surface flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-[16px] text-[#7c3aed]">footprint</span>
                                        Spesialis Sepatu (Shoe Care)
                                    </span>
                                    <span class="text-[#7c3aed]"><?= $service_percentages['sepatu']; ?>% (<?= $service_counts['sepatu']; ?> Mitra)</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-[#7c3aed] h-full rounded-full transition-all duration-500" style="width: <?= $service_percentages['sepatu']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard: Top Performing Partners -->
                <div class="bento-card rounded-xl overflow-hidden">
                    <div class="px-lg py-md border-b border-outline-variant">
                        <h2 class="text-headline-sm font-bold text-on-surface">Peringkat Performa Kemitraan</h2>
                        <p class="text-body-xs text-on-surface-variant">Daftar mitra terurut berdasarkan skor rating tertinggi dari kepuasan pelanggan.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low">
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-center w-20">Rank</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Mitra</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Wilayah</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Tarif Dasar</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right w-36">Rating Kepuasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                <?php if (empty($mitra_list)): ?>
                                    <tr>
                                        <td colspan="5" class="px-lg py-12 text-center text-on-surface-variant">
                                            <span class="material-symbols-outlined text-outline text-[48px] mb-2">trending_down</span>
                                            <p class="text-body-md font-semibold">Belum ada data performa mitra saat ini</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $rank = 1;
                                    foreach ($mitra_list as $mitra): 
                                        $address_parts = explode(',', $mitra['alamat']);
                                        $city = trim(end($address_parts));
                                        if (empty($city) && count($address_parts) > 1) {
                                            $city = trim($address_parts[count($address_parts) - 2]);
                                        }
                                        if (empty($city)) {
                                            $city = 'Mataram';
                                        }
                                        if (strlen($city) > 18) {
                                            $city = substr($city, 0, 15) . '...';
                                        }
                                        
                                        // Rank badges styling
                                        $rank_class = "bg-slate-100 text-on-surface-variant";
                                        if ($rank === 1) $rank_class = "bg-amber-100 text-amber-800 font-extrabold";
                                        if ($rank === 2) $rank_class = "bg-slate-200 text-slate-800 font-extrabold";
                                        if ($rank === 3) $rank_class = "bg-orange-100 text-orange-800 font-extrabold";
                                    ?>
                                        <tr class="hover:bg-surface-container-low transition-colors">
                                            <td class="px-lg py-md text-center">
                                                <span class="inline-block w-7 h-7 rounded-full text-label-sm flex items-center justify-center <?= $rank_class; ?>">
                                                    <?= $rank++; ?>
                                                </span>
                                            </td>
                                            <td class="px-lg py-md text-body-md font-bold text-on-surface">
                                                <div class="flex items-center gap-sm">
                                                    <img src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="" class="w-8 h-8 rounded-lg object-cover">
                                                    <span><?= htmlspecialchars($mitra['nama_mitra']); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-lg py-md text-body-md text-on-surface-variant"><?= htmlspecialchars($city); ?></td>
                                            <td class="px-lg py-md text-body-md font-bold text-primary">Rp <?= number_format($mitra['harga_per_kg'], 0, ',', '.'); ?>/kg</td>
                                            <td class="px-lg py-md text-right">
                                                <div class="flex items-center justify-end gap-[4px] text-tertiary">
                                                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                                    <span class="font-bold text-body-md"><?= htmlspecialchars($mitra['rating']); ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
</body>
</html>
