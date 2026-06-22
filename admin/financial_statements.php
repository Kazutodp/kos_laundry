<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

try {
    // Fetch all active partners along with their real order metrics
    $stmt = $pdo->query("SELECT m.*, 
                               COUNT(o.id) as real_orders, 
                               COALESCE(SUM(o.total_harga), 0) as real_gross,
                               COUNT(CASE WHEN o.status_transfer = 'Proses' THEN 1 END) as pending_transfers
                        FROM mitra_laundry m
                        LEFT JOIN orders o ON m.id = o.mitra_id AND o.status_pembayaran = 'success'
                        GROUP BY m.id
                        ORDER BY m.rating DESC");
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
            $mitra['simulated_platform'] = $gross * 0.10; // 10% platform share
            $mitra['simulated_net'] = $gross * 0.90; // 90% partner share
            
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
    <title>Laporan Keuangan | KosanLaundry Admin</title>
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
            <img alt="KosanLaundry Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
            <span class="text-headline-sm font-headline-md font-bold text-primary">KosanLaundry</span>
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
            <a class="flex items-center gap-sm px-md py-sm text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" href="analitik_kemitraan.php">
                <span class="material-symbols-outlined">analytics</span>
                <span class="text-label-md font-label-md">Analitik Kemitraan</span>
            </a>
            <a class="flex items-center gap-sm px-md py-sm bg-primary-container text-on-primary-container rounded-lg font-bold translate-x-1 transition-transform" href="financial_statements.php">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">payments</span>
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
                <div class="text-headline-sm font-bold text-on-surface">Laporan Keuangan</div>
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
                
                <!-- Toast alert (hidden by default) -->
                <div id="toast" class="fixed top-6 right-6 p-md bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-center gap-md shadow-lg z-50 transition-all duration-300 transform translate-y-[-100px] opacity-0">
                    <span class="material-symbols-outlined text-emerald-600 text-2xl">check_circle</span>
                    <div>
                        <p class="text-label-md font-bold">Unduh Berhasil</p>
                        <p class="text-body-xs text-emerald-700">Laporan keuangan telah diexport ke format PDF.</p>
                    </div>
                </div>

                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-md">
                    <div>
                        <h1 class="text-headline-lg font-headline-lg text-on-surface">Pusat Laporan Keuangan</h1>
                        <p class="text-body-md text-on-surface-variant">Laporan bagi hasil platform 10% dan total rincian pembayaran ke mitra laundry.</p>
                    </div>
                    <button onclick="downloadReport()" class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all w-fit">
                        <span class="material-symbols-outlined">download</span>
                        Unduh Laporan (PDF)
                    </button>
                </div>

                <!-- Financial Stats Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-lg">
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-primary">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Total Omset Pendapatan</p>
                            <p class="text-headline-sm font-bold text-on-surface">Rp <?= number_format($total_gross, 0, ',', '.'); ?></p>
                            <p class="text-[10px] text-outline mt-base">Dari <?= $total_orders; ?> pesanan terkumpul</p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-secondary">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Bagi Hasil Platform (10%)</p>
                            <p class="text-headline-sm font-bold text-secondary">Rp <?= number_format($platform_share, 0, ',', '.'); ?></p>
                            <p class="text-[10px] text-outline mt-base">Biaya administrasi sistem</p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-tertiary">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Payout Bersih Mitra (90%)</p>
                            <p class="text-headline-sm font-bold text-tertiary">Rp <?= number_format($net_share, 0, ',', '.'); ?></p>
                            <p class="text-[10px] text-outline mt-base">Ditransfer ke rekening mitra</p>
                        </div>
                    </div>
                    <div class="bento-card p-lg rounded-xl flex items-center gap-md border-l-4 border-l-slate-400">
                        <div>
                            <p class="text-label-sm text-on-surface-variant font-medium">Rata-rata Payout Toko</p>
                            <p class="text-headline-sm font-bold text-on-surface">Rp <?= number_format($active_count > 0 ? round($net_share / $active_count) : 0, 0, ',', '.'); ?></p>
                            <p class="text-[10px] text-outline mt-base">Payout per outlet aktif</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Statement Table -->
                <div class="bento-card rounded-xl overflow-hidden">
                    <div class="px-lg py-md border-b border-outline-variant flex justify-between items-center bg-slate-50">
                        <div>
                            <h2 class="text-headline-sm font-bold text-on-surface">Rincian Bagi Hasil per Mitra</h2>
                            <p class="text-body-xs text-on-surface-variant">Laporan rincian omset bruto, potongan platform 10%, dan transfer status.</p>
                        </div>
                        <span class="px-sm py-xs bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-full font-bold text-[11px]">Siklus Juni 2026</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low border-b border-outline-variant">
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider">Mitra Laundry</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-center">Total Orders</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right">Omset Bruto</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right">Komisi Platform (10%)</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-right">Payout Mitra (90%)</th>
                                    <th class="px-lg py-md text-label-sm text-on-surface-variant font-bold uppercase tracking-wider text-center">Status Transfer</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                <?php if (empty($mitra_list)): ?>
                                    <tr>
                                        <td colspan="6" class="px-lg py-12 text-center text-on-surface-variant">
                                            <span class="material-symbols-outlined text-outline text-[48px] mb-2">money_off</span>
                                            <p class="text-body-md font-semibold">Belum ada transaksi laporan keuangan terdaftar</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($mitra_list as $mitra): ?>
                                        <?php 
                                        $transfer_status = ($mitra['simulated_orders'] > 0 && $mitra['pending_transfers'] > 0) ? 'Proses' : 'Selesai';
                                        $status_badge = $transfer_status === 'Selesai' 
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                            : 'bg-amber-50 text-amber-700 border-amber-200';
                                        ?>
                                        <tr class="hover:bg-surface-container-low transition-colors">
                                            <td class="px-lg py-md">
                                                <div class="flex items-center gap-sm">
                                                    <img src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="" class="w-10 h-10 rounded object-cover border border-outline-variant">
                                                    <div>
                                                        <p class="text-body-sm font-bold text-on-surface leading-tight"><?= htmlspecialchars($mitra['nama_mitra']); ?></p>
                                                        <p class="text-[10px] text-outline">Tarif: Rp <?= number_format($mitra['harga_per_kg'], 0, ',', '.'); ?>/kg</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-lg py-md text-center text-body-sm font-semibold text-on-surface-variant"><?= $mitra['simulated_orders']; ?> Pcs</td>
                                            <td class="px-lg py-md text-right text-body-sm font-bold text-on-surface">Rp <?= number_format($mitra['simulated_gross'], 0, ',', '.'); ?></td>
                                            <td class="px-lg py-md text-right text-body-sm font-bold text-secondary">- Rp <?= number_format($mitra['simulated_platform'], 0, ',', '.'); ?></td>
                                            <td class="px-lg py-md text-right text-body-sm font-extrabold text-primary">Rp <?= number_format($mitra['simulated_net'], 0, ',', '.'); ?></td>
                                            <td class="px-lg py-md text-center">
                                                <span class="px-xs py-[2px] border rounded-full font-bold text-[10px] <?= $status_badge; ?>">
                                                    <?= $transfer_status; ?>
                                                </span>
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
            <p class="text-label-sm">© 2024 KosanLaundry Provincial Partnership Program. Freshness across the region.</p>
            <div class="flex gap-lg">
                <a class="text-label-sm hover:text-primary transition-colors" href="../bantuan/bantuan.php">Pusat Bantuan</a>
                <a class="text-label-sm hover:text-primary transition-colors" href="#">Kebijakan Kemitraan</a>
            </div>
        </footer>
    </main>

    <script>
        function downloadReport() {
            const toast = document.getElementById('toast');
            // Show toast
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.style.transform = 'translateY(-100px)';
                toast.style.opacity = '0';
            }, 3000);
        }
    </script>
</body>
</html>
