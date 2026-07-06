<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

// Handle AJAX Request for Financial Data
if (isset($_GET['action']) && $_GET['action'] === 'get_financial_data') {
    header('Content-Type: application/json');
    $req_month = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
    $req_year = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
    
    if ($req_month < 1 || $req_month > 12) $req_month = (int)date('n');
    if ($req_year < 2024 || $req_year > 2030) $req_year = (int)date('Y');
    
    try {
        $stmt = $pdo->prepare("SELECT m.*, 
                                   COUNT(o.id) as real_orders, 
                                   COALESCE(SUM(o.total_harga), 0) as real_gross
                            FROM mitra_laundry m
                            LEFT JOIN orders o ON m.id = o.mitra_id 
                                AND o.status_pembayaran = 'success'
                                AND MONTH(o.created_at) = ?
                                AND YEAR(o.created_at) = ?
                            GROUP BY m.id
                            ORDER BY real_gross DESC");
        $stmt->execute([$req_month, $req_year]);
        $raw_mitras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $mitra_list = [];
        $total_gross = 0;
        $total_orders = 0;
        
        foreach ($raw_mitras as $mitra) {
            $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
            if (file_exists('../Mitra laundry/' . $file_name)) {
                $orders = (int)$mitra['real_orders'];
                $gross = (float)$mitra['real_gross'];
                
                $mitra['simulated_orders'] = $orders;
                $mitra['simulated_gross'] = $gross;
                $mitra['simulated_platform'] = $gross * 0.10;
                $mitra['simulated_net'] = $gross * 0.90;
                
                $total_orders += $orders;
                $total_gross += $gross;
                $mitra_list[] = $mitra;
            }
        }
        
        $active_count = count($mitra_list);
        $platform_share = $total_gross * 0.10;
        $net_share = $total_gross * 0.90;
        $avg_transaction = $active_count > 0 ? round($net_share / $active_count) : 0;
        
        echo json_encode([
            'success' => true,
            'total_orders' => $total_orders,
            'total_gross' => $total_gross,
            'platform_share' => $platform_share,
            'net_share' => $net_share,
            'avg_transaction' => $avg_transaction,
            'mitras' => $mitra_list
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Indonesian month names
$bulan_indo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Get selected month/year from GET params, default to current
$selected_month = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$selected_year = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Clamp values
if ($selected_month < 1 || $selected_month > 12) $selected_month = (int)date('n');
if ($selected_year < 2024 || $selected_year > 2030) $selected_year = (int)date('Y');

$selected_label = $bulan_indo[$selected_month] . ' ' . $selected_year;

try {
    // Fetch all active partners with order metrics filtered by selected month, ordered by revenue descending
    $stmt = $pdo->prepare("SELECT m.*, 
                               COUNT(o.id) as real_orders, 
                               COALESCE(SUM(o.total_harga), 0) as real_gross
                        FROM mitra_laundry m
                        LEFT JOIN orders o ON m.id = o.mitra_id 
                            AND o.status_pembayaran = 'success'
                            AND MONTH(o.created_at) = ?
                            AND YEAR(o.created_at) = ?
                        GROUP BY m.id
                        ORDER BY real_gross DESC");
    $stmt->execute([$selected_month, $selected_year]);
    $raw_mitras = $stmt->fetchAll();
    
    $mitra_list = [];
    $total_gross = 0;
    $total_orders = 0;
    
    foreach ($raw_mitras as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../Mitra laundry/' . $file_name)) {
            $orders = (int)$mitra['real_orders'];
            $gross = (float)$mitra['real_gross'];
            
            $mitra['simulated_orders'] = $orders;
            $mitra['simulated_gross'] = $gross;
            $mitra['simulated_platform'] = $gross * 0.10;
            $mitra['simulated_net'] = $gross * 0.90;
            
            $total_orders += $orders;
            $total_gross += $gross;
            $mitra_list[] = $mitra;
        }
    }
    
    $active_count = count($mitra_list);
    $platform_share = $total_gross * 0.10;
    $net_share = $total_gross * 0.90;
    $avg_transaction = $active_count > 0 ? round($total_gross / $active_count) : 0;
} catch (PDOException $e) {
    $mitra_list = [];
    $active_count = 0;
    $total_gross = 0;
    $platform_share = 0;
    $net_share = 0;
    $avg_transaction = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Laporan Keuangan | MataramWash Admin</title>
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
    <!-- SideNavBar -->
<aside class="hidden lg:flex flex-col h-screen sticky top-0 p-md space-y-md bg-slate-900 border-r border-slate-800 w-64 shrink-0 text-slate-300">
<div class="flex items-center gap-xs px-xs py-sm border-b border-slate-800">
<img alt="MataramWash Logo" class="h-8 w-8 object-contain brightness-110 filter" src="../Logo_MataramWash.png?v=3">
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
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="operational_area.php">
<span class="material-symbols-outlined text-[20px]">map</span>
<span class="text-label-md font-label-md">Wilayah Operasional</span>
</a>

<a class="flex items-center gap-sm px-md py-sm bg-blue-600 text-white rounded-xl font-bold border-l-4 border-blue-400 shadow-lg shadow-blue-900/30 transition-all duration-200" href="financial_statements.php">
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
                <div class="text-headline-sm font-bold text-on-surface">Laporan Keuangan</div>
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
                
                <!-- Dynamic Toast alert (hidden by default) -->
                <div id="toast" class="fixed top-6 right-6 p-md rounded-xl border flex items-center gap-md shadow-lg z-50 transition-all duration-300 transform translate-y-[-100px] opacity-0">
                    <span id="toast-icon" class="material-symbols-outlined text-2xl"></span>
                    <div>
                        <p id="toast-title" class="text-label-md font-bold"></p>
                        <p id="toast-message" class="text-body-xs"></p>
                    </div>
                </div>

                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-md">
                    <div>
                        <h1 class="text-headline-lg font-headline-lg text-on-surface">Pusat Laporan Keuangan</h1>
                        <p class="text-body-md text-on-surface-variant">Laporan bagi hasil platform 10% dan total rincian pembayaran ke mitra laundry.</p>
                    </div>
                    <div id="download-btn-container">
                        <?php if ($total_orders == 0 && $total_gross == 0): ?>
                            <button onclick="showNoDataToast()" class="bg-slate-100 border border-slate-200 text-slate-400 px-lg py-sm rounded-xl font-bold flex items-center gap-sm cursor-not-allowed w-fit" title="Tidak ada data keuangan pada periode ini">
                                <span class="material-symbols-outlined text-slate-400">download</span>
                                Unduh Laporan (PDF)
                            </button>
                        <?php else: ?>
                            <a href="laporan/unduh_pdf.php?bulan=<?= $selected_month; ?>&tahun=<?= $selected_year; ?>" class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all w-fit">
                                <span class="material-symbols-outlined">download</span>
                                Unduh Laporan (PDF)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Financial Stats Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-lg">
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-primary">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Total Omset Pendapatan</p>
                            <p id="stat-total-gross" class="text-headline-sm font-bold text-on-surface">Rp <?= number_format($total_gross, 0, ',', '.'); ?></p>
                            <p id="stat-total-orders-label" class="text-[10px] text-outline mt-base">Dari <?= $total_orders; ?> pesanan — <?= $selected_label; ?></p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-secondary">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Bagi Hasil Platform (10%)</p>
                            <p id="stat-platform-share" class="text-headline-sm font-bold text-secondary">Rp <?= number_format($platform_share, 0, ',', '.'); ?></p>
                            <p class="text-[10px] text-outline mt-base">Biaya administrasi sistem</p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-tertiary">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Payout Bersih Mitra (90%)</p>
                            <p id="stat-net-share" class="text-headline-sm font-bold text-tertiary">Rp <?= number_format($net_share, 0, ',', '.'); ?></p>
                            <p class="text-[10px] text-outline mt-base">Ditransfer ke rekening mitra</p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-slate-400">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Rata-rata Payout Toko</p>
                            <p id="stat-avg-payout" class="text-headline-sm font-bold text-on-surface">Rp <?= number_format($active_count > 0 ? round($net_share / $active_count) : 0, 0, ',', '.'); ?></p>
                            <p class="text-[10px] text-outline mt-base">Payout per outlet aktif</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Statement Table -->
                <div class="bento-card rounded-xl overflow-hidden">
                    <div class="px-lg py-md border-b border-outline-variant flex justify-between items-center bg-slate-50">
                        <div>
                            <h2 class="text-headline-sm font-bold text-on-surface">Rincian Bagi Hasil per Mitra</h2>
                            <p class="text-body-xs text-on-surface-variant">Laporan rincian omset bruto, potongan platform 10%, dan payout bersih mitra.</p>
                        </div>
                        <div class="flex items-center gap-sm">
                            <select onchange="filterByPeriod()" id="filter-bulan" class="text-[12px] font-semibold bg-white border border-outline-variant rounded-lg px-sm py-1.5 text-on-surface cursor-pointer focus:ring-primary focus:border-primary">
                                <?php foreach ($bulan_indo as $num => $nama): ?>
                                    <option value="<?= $num; ?>" <?= $num == $selected_month ? 'selected' : ''; ?>><?= $nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select onchange="filterByPeriod()" id="filter-tahun" class="text-[12px] font-semibold bg-white border border-outline-variant rounded-lg px-sm py-1.5 text-on-surface cursor-pointer focus:ring-primary focus:border-primary">
                                <?php for ($y = 2024; $y <= (int)date('Y') + 1; $y++): ?>
                                    <option value="<?= $y; ?>" <?= $y == $selected_year ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low border-b border-outline-variant">
                                    <th class="pl-lg pr-1 py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-center">No</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Mitra Laundry</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-center">Total Orders</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right">Omset Bruto</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right">Komisi Platform (10%)</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right">Payout Mitra (90%)</th>
                                </tr>
                            </thead>
                            <tbody id="mitra-table-body" class="divide-y divide-outline-variant">
                                <?php if (empty($mitra_list)): ?>
                                    <tr id="no-data-row">
                                        <td colspan="6" class="px-lg py-12 text-center text-on-surface-variant">
                                            <span class="material-symbols-outlined text-outline text-[48px] mb-2">money_off</span>
                                            <p class="text-body-md font-semibold">Belum ada transaksi laporan keuangan terdaftar</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($mitra_list as $mitra): ?>
                                        <tr data-mitra-id="<?= $mitra['id']; ?>" class="hover:bg-surface-container-low transition-colors duration-200">
                                            <td class="pl-lg pr-1 py-md text-body-sm text-slate-500 font-medium text-center row-number"><?= $no++; ?></td>
                                            <td class="px-lg py-md">
                                                <div class="flex items-center gap-sm">
                                                    <img src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="" class="w-10 h-10 rounded object-cover border border-outline-variant">
                                                    <div>
                                                        <p class="text-body-sm font-bold text-on-surface leading-tight"><?= htmlspecialchars($mitra['nama_mitra']); ?></p>
                                                        <p class="text-[10px] text-outline">Tarif: Rp <?= number_format($mitra['harga_per_kg'], 0, ',', '.'); ?>/kg</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-lg py-md text-center text-body-sm font-semibold text-on-surface-variant row-orders"><?= $mitra['simulated_orders']; ?></td>
                                            <td class="px-lg py-md text-right text-body-sm font-bold text-on-surface row-gross">Rp <?= number_format($mitra['simulated_gross'], 0, ',', '.'); ?></td>
                                            <td class="px-lg py-md text-right text-body-sm font-bold text-secondary row-platform">- Rp <?= number_format($mitra['simulated_platform'], 0, ',', '.'); ?></td>
                                            <td class="px-lg py-md text-right text-body-sm font-extrabold text-primary row-net">Rp <?= number_format($mitra['simulated_net'], 0, ',', '.'); ?></td>
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
        function showToast(title, message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const titleEl = document.getElementById('toast-title');
            const msgEl = document.getElementById('toast-message');

            if (type === 'error') {
                toast.className = "fixed top-6 right-6 p-md bg-rose-50 text-rose-800 rounded-xl border border-rose-200 flex items-center gap-md shadow-lg z-50 transition-all duration-300 transform";
                icon.className = "material-symbols-outlined text-rose-600 text-2xl";
                icon.innerText = "error";
            } else {
                toast.className = "fixed top-6 right-6 p-md bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-center gap-md shadow-lg z-50 transition-all duration-300 transform";
                icon.className = "material-symbols-outlined text-emerald-600 text-2xl";
                icon.innerText = "check_circle";
            }

            titleEl.innerText = title;
            msgEl.innerText = message;

            // Show toast
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';

            // Hide toast after 3.5 seconds
            setTimeout(() => {
                toast.style.transform = 'translateY(-100px)';
                toast.style.opacity = '0';
            }, 3500);
        }

        function showNoDataToast() {
            showToast("Unduh Gagal", "Tidak ada data transaksi yang dapat diunduh pada periode ini.", "error");
        }

        // Helper to format currency
        function formatRupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
        }

        function filterByPeriod() {
            const bulan = document.getElementById('filter-bulan').value;
            const tahun = document.getElementById('filter-tahun').value;
            
            // 1. Get current layout positions (FLIP: First)
            const tableBody = document.getElementById('mitra-table-body');
            const rows = Array.from(tableBody.querySelectorAll('tr[data-mitra-id]'));
            const firstPositions = {};
            
            rows.forEach(row => {
                const id = row.getAttribute('data-mitra-id');
                firstPositions[id] = row.getBoundingClientRect().top;
            });
            
            // Disable inputs during fetch
            document.getElementById('filter-bulan').disabled = true;
            document.getElementById('filter-tahun').disabled = true;

            fetch(`financial_statements.php?action=get_financial_data&bulan=${bulan}&tahun=${tahun}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        // Get Month Name for label
                        const monthSelect = document.getElementById('filter-bulan');
                        const monthLabel = monthSelect.options[monthSelect.selectedIndex].text;
                        
                        // Update Stat Cards with simple text replacement
                        document.getElementById('stat-total-gross').innerText = formatRupiah(res.total_gross);
                        document.getElementById('stat-total-orders-label').innerText = `Dari ${res.total_orders} pesanan — ${monthLabel} ${tahun}`;
                        document.getElementById('stat-platform-share').innerText = formatRupiah(res.platform_share);
                        document.getElementById('stat-net-share').innerText = formatRupiah(res.net_share);
                        document.getElementById('stat-avg-payout').innerText = formatRupiah(res.avg_transaction);
                        
                        // Update Download Link Container
                        const downloadContainer = document.getElementById('download-btn-container');
                        if (res.total_orders === 0 && res.total_gross === 0) {
                            downloadContainer.innerHTML = `
                                <button onclick="showNoDataToast()" class="bg-slate-100 border border-slate-200 text-slate-400 px-lg py-sm rounded-xl font-bold flex items-center gap-sm cursor-not-allowed w-fit" title="Tidak ada data keuangan pada periode ini">
                                    <span class="material-symbols-outlined text-slate-400">download</span>
                                    Unduh Laporan (PDF)
                                </button>
                            `;
                        } else {
                            downloadContainer.innerHTML = `
                                <a href="laporan/unduh_pdf.php?bulan=${bulan}&tahun=${tahun}" class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all w-fit">
                                    <span class="material-symbols-outlined">download</span>
                                    Unduh Laporan (PDF)
                                </a>
                            `;
                        }

                        // Check if no mitras returned
                        if (!res.mitras || res.mitras.length === 0) {
                            tableBody.innerHTML = `
                                <tr id="no-data-row">
                                    <td colspan="6" class="px-lg py-12 text-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-outline text-[48px] mb-2">money_off</span>
                                        <p class="text-body-md font-semibold">Belum ada transaksi laporan keuangan terdaftar</p>
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        // Remove empty row indicator if present
                        const emptyRow = document.getElementById('no-data-row');
                        if (emptyRow) emptyRow.remove();

                        // Map data for fast DOM updates
                        const mitrasMap = {};
                        res.mitras.forEach(m => {
                            mitrasMap[m.id] = m;
                        });

                        // 2. Update values of existing rows & re-append in new order (FLIP: Last)
                        const sortedRows = [];
                        res.mitras.forEach((mitraData, index) => {
                            let row = tableBody.querySelector(`tr[data-mitra-id="${mitraData.id}"]`);
                            
                            // If row doesn't exist, create it (fallback case)
                            if (!row) {
                                row = document.createElement('tr');
                                row.setAttribute('data-mitra-id', mitraData.id);
                                row.className = 'hover:bg-surface-container-low transition-colors duration-200';
                                row.innerHTML = `
                                    <td class="pl-lg pr-1 py-md text-body-sm text-slate-500 font-medium text-center row-number">${index + 1}</td>
                                    <td class="px-lg py-md">
                                        <div class="flex items-center gap-sm">
                                            <img src="../${mitraData.foto_toko}" alt="" class="w-10 h-10 rounded object-cover border border-outline-variant">
                                            <div>
                                                <p class="text-body-sm font-bold text-on-surface leading-tight">${mitraData.nama_mitra}</p>
                                                <p class="text-[10px] text-outline">Tarif: Rp ${new Intl.NumberFormat('id-ID').format(mitraData.harga_per_kg)}/kg</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-lg py-md text-center text-body-sm font-semibold text-on-surface-variant row-orders">${mitraData.simulated_orders}</td>
                                    <td class="px-lg py-md text-right text-body-sm font-bold text-on-surface row-gross">${formatRupiah(mitraData.simulated_gross)}</td>
                                    <td class="px-lg py-md text-right text-body-sm font-bold text-secondary row-platform">- ${formatRupiah(mitraData.simulated_platform)}</td>
                                    <td class="px-lg py-md text-right text-body-sm font-extrabold text-primary row-net">${formatRupiah(mitraData.simulated_net)}</td>
                                `;
                            } else {
                                // Update row values
                                row.querySelector('.row-number').innerText = index + 1;
                                row.querySelector('.row-orders').innerText = mitraData.simulated_orders;
                                row.querySelector('.row-gross').innerText = formatRupiah(mitraData.simulated_gross);
                                row.querySelector('.row-platform').innerText = '- ' + formatRupiah(mitraData.simulated_platform);
                                row.querySelector('.row-net').innerText = formatRupiah(mitraData.simulated_net);
                            }
                            
                            // Re-append to change DOM order
                            tableBody.appendChild(row);
                            sortedRows.push(row);
                        });

                        // 3. FLIP: Invert and Play
                        sortedRows.forEach(row => {
                            const id = row.getAttribute('data-mitra-id');
                            const firstY = firstPositions[id];
                            
                            if (firstY !== undefined) {
                                const lastY = row.getBoundingClientRect().top;
                                const diffY = firstY - lastY;
                                
                                if (diffY !== 0) {
                                    // Invert position instantly (disable transition)
                                    row.style.transition = 'none';
                                    row.style.transform = `translateY(${diffY}px)`;
                                    
                                    // Highlight row temporarily during transition
                                    row.classList.add('bg-blue-50/50');
                                    
                                    // Play animation in next repaint
                                    requestAnimationFrame(() => {
                                        row.style.transition = 'transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                                        row.style.transform = '';
                                        
                                        // Remove highlight after transition completes
                                        setTimeout(() => {
                                            row.classList.remove('bg-blue-50/50');
                                            row.style.transition = '';
                                        }, 600);
                                    });
                                }
                            }
                        });

                    } else {
                        showToast("Gagal Memuat Laporan", res.message || "Terjadi kesalahan.", "error");
                    }
                })
                .catch(() => {
                    showToast("Kesalahan Jaringan", "Gagal memproses data laporan keuangan.", "error");
                })
                .finally(() => {
                    // Re-enable inputs
                    document.getElementById('filter-bulan').disabled = false;
                    document.getElementById('filter-tahun').disabled = false;
                });
        }

        // Detect URL error parameters on load
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error') === 'nodata') {
                showNoDataToast();
            } else if (urlParams.get('error') === 'db') {
                showToast("Gagal Memuat Laporan", "Terjadi kesalahan pada database saat memproses laporan.", "error");
            }
        });
    </script>
</body>
</html>
