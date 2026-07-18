<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

$success_message = '';
$error_message = '';

// Handle Order Delete/Hide Action (Soft vs Hard Delete for Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order_admin') {
    header('Content-Type: application/json');
    $order_id = intval($_POST['order_id'] ?? 0);
    $type = trim($_POST['type'] ?? 'hide'); // 'hide' or 'permanent'
    
    if ($order_id > 0) {
        try {
            if ($type === 'permanent') {
                // Hard Delete: remove row completely from database
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            } else {
                // Soft Delete: hide from admin
                $stmt = $pdo->prepare("UPDATE orders SET is_hidden_admin = 1 WHERE id = ?");
            }
            $stmt->execute([$order_id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID pesanan tidak valid.']);
    }
    exit();
}

// Fetch all orders (excluding hidden ones for admin)
try {
    $stmt = $pdo->query("
        SELECT o.*, m.nama_mitra 
        FROM orders o
        JOIN mitra_laundry m ON o.mitra_id = m.id
        WHERE o.is_hidden_admin = 0
        ORDER BY o.created_at DESC
    ");
    $all_orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_orders = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Kelola Pesanan | MataramWash Admin</title>
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
            vertical-align: middle;
        }
        .bento-card {
            background-color: #ffffff;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(226, 232, 240, 0.8);
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
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex overflow-hidden">

<!-- SideNavBar -->
<aside class="hidden lg:flex flex-col h-screen sticky top-0 p-md space-y-md bg-slate-900 border-r border-slate-800 w-64 shrink-0 text-slate-300">
    <div class="flex items-center gap-xs px-xs py-sm border-b border-slate-800">
        <img alt="MataramWash Logo" class="h-8 w-8 object-contain brightness-110 filter" src="../Logo_MataramWash.png?v=3">
        <span class="text-headline-sm font-headline-md font-extrabold text-white">MataramWash</span>
    </div>
    <div class="flex flex-col gap-xs py-md border-b border-slate-800">
        <p class="px-md text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-xs">Main Menu</p>
        <a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="dashboard_admin.php">
            <span class="material-symbols-outlined text-[20px]">dashboard</span>
            <span class="text-label-md font-label-md">Dashboard</span>
        </a>
        <a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="manajemen_mitra.php">
            <span class="material-symbols-outlined text-[20px]">group</span>
            <span class="text-label-md font-label-md">Manajemen Mitra</span>
        </a>
        <a class="flex items-center gap-sm px-md py-sm bg-blue-600 text-white rounded-xl font-bold border-l-4 border-blue-400 shadow-lg shadow-blue-900/30 transition-all duration-200" href="kelola_pesanan.php">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">local_laundry_service</span>
            <span class="text-label-md font-label-md">Kelola Pesanan</span>
        </a>
        <a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="operational_area.php">
            <span class="material-symbols-outlined text-[20px]">map</span>
            <span class="text-label-md font-label-md">Wilayah Operasional</span>
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

<!-- Main Canvas -->
<main class="flex-grow flex flex-col h-screen overflow-hidden">
    <!-- Header -->
    <header class="sticky top-0 w-full z-40 flex justify-between items-center px-lg py-md bg-white border-b border-slate-100 max-w-none">
        <div class="flex items-center gap-md flex-1">
            <h1 class="text-headline-sm font-extrabold text-slate-800 text-[20px]">Kelola Pesanan Laundry</h1>
        </div>
        <div class="flex items-center gap-sm">
            <p class="text-label-md font-extrabold text-slate-800 leading-none"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator'); ?></p>
            <p class="text-label-sm text-slate-400 leading-tight">Admin</p>
        </div>
    </header>

    <!-- Scrollable Area -->
    <div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
        <div class="max-w-7xl mx-auto space-y-lg">
            
            <!-- Alert Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="p-md bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm flex items-center gap-sm shadow-sm">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <span><?= htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="p-md bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm flex items-center gap-sm shadow-sm">
                    <span class="material-symbols-outlined text-rose-600">error</span>
                    <span><?= htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Table of Orders -->
            <div class="bento-card rounded-2xl overflow-hidden shadow-xs">
                <div class="p-lg border-b border-slate-100 bg-white">
                    <h3 class="text-base font-bold text-slate-800">Daftar Transaksi Masuk</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse bg-white">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="pl-lg pr-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs">No</th>
                                <th class="px-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs">ID</th>
                                <th class="px-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs">Pelanggan</th>
                                <th class="px-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs">Outlet / Layanan</th>
                                <th class="px-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs">Berat / Estimasi</th>
                                <th class="px-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs">Biaya & Payment</th>
                                <th class="px-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs">Status Order</th>
                                <th class="px-md py-sm text-label-sm text-slate-500 font-bold uppercase tracking-wider text-xs text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body" class="divide-y divide-slate-100 text-slate-700 text-sm">
                            <?php if (empty($all_orders)): ?>
                                <tr>
                                    <td colspan="8" class="px-md py-xl text-center text-slate-400">Belum ada pesanan terdaftar di sistem.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($all_orders as $order): 
                                    $is_self = (strpos(strtolower($order['layanan']), 'self') !== false || strpos(strtolower($order['nama_mitra']), 'washtra') !== false);
                                    $is_satuan = (strpos(strtolower($order['layanan']), 'sepatu') !== false || strpos(strtolower($order['layanan']), 'shoes') !== false || strpos(strtolower($order['nama_mitra']), 'shoes') !== false);
                                    $is_kiloan = !$is_self && !$is_satuan;
                                    
                                    $order_status = $order['status_order'] ?? 'Menunggu Penjemputan';
                                    $pay_status = $order['status_pembayaran'];
                                ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="pl-lg pr-md py-md text-slate-500 font-medium text-center"><?= $no++; ?></td>
                                        <td class="px-md py-md font-bold text-slate-800">#<?= $order['id']; ?></td>
                                        <td class="px-md py-md">
                                            <p class="font-bold text-slate-800"><?= htmlspecialchars($order['nama_pelanggan']); ?></p>
                                            <?php if (!empty($order['alamat_antar_jemput'])): ?>
                                                <p class="text-xs text-slate-500 truncate max-w-[200px]" title="<?= htmlspecialchars($order['alamat_antar_jemput']); ?>">
                                                    Alamat: <?= htmlspecialchars($order['alamat_antar_jemput']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-md py-md">
                                            <p class="font-bold text-slate-800"><?= htmlspecialchars($order['nama_mitra']); ?></p>
                                            <p class="text-xs text-blue-600 font-semibold"><?= htmlspecialchars($order['layanan']); ?></p>
                                        </td>
                                        <td class="px-md py-md">
                                            <?php if ($is_kiloan): ?>
                                                <p class="text-xs text-slate-400">Estimasi: <?= floatval($order['estimasi_berat']); ?> kg</p>
                                                <p class="text-sm font-semibold text-slate-800">Real: <span class="<?= floatval($order['berat_atau_qty']) > 0 ? 'text-emerald-600' : 'text-slate-400'; ?>"><?= floatval($order['berat_atau_qty']) ?: '-'; ?> kg</span></p>
                                            <?php elseif ($is_self): ?>
                                                <p class="text-sm font-bold text-slate-800"><?= floatval($order['berat_atau_qty']); ?> Slot</p>
                                            <?php elseif ($is_satuan): ?>
                                                <p class="text-sm font-bold text-slate-800"><?= floatval($order['estimasi_berat']); ?> Pasang</p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-md py-md">
                                            <p class="font-bold text-slate-800">Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></p>
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold mt-1 text-white <?= $pay_status === 'success' ? 'bg-emerald-500' : ($pay_status === 'pending' ? 'bg-amber-500' : 'bg-rose-500'); ?>">
                                                <?= strtoupper($pay_status); ?>
                                            </span>
                                        </td>
                                        <td class="px-md py-md">
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold <?= $order_status === 'Menunggu Pembayaran' ? 'bg-sky-100 text-sky-800 font-bold animate-pulse' : ($order_status === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : ($order_status === 'Dibatalkan' ? 'bg-rose-100 text-rose-800 font-bold' : 'bg-slate-100 text-slate-700')); ?>">
                                                <?= $order_status; ?>
                                            </span>
                                        </td>
                                        <td class="px-md py-md text-center space-x-xs align-middle">
                                            <?php 
                                            // Prepare order details data as array
                                            $order_data = [
                                                'id' => $order['id'],
                                                'nama_pelanggan' => $order['nama_pelanggan'],
                                                'nama_mitra' => $order['nama_mitra'],
                                                'layanan' => $order['layanan'],
                                                'is_kiloan' => $is_kiloan,
                                                'is_self' => $is_self,
                                                'estimasi_berat' => floatval($order['estimasi_berat']),
                                                'berat_atau_qty' => floatval($order['berat_atau_qty']),
                                                'total_harga' => intval($order['total_harga']),
                                                'status_pembayaran' => $order['status_pembayaran'],
                                                'status_order' => $order['status_order'],
                                                'alamat_antar_jemput' => $order['alamat_antar_jemput'],
                                                'catatan' => $order['catatan'],
                                                'foto_timbangan' => $order['foto_timbangan']
                                            ];
                                            $order_json = htmlspecialchars(json_encode($order_data), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <div class="inline-flex items-center gap-2">
                                                <!-- Detail Button -->
                                                <button onclick='openDetailModal(<?= $order_json; ?>)' 
                                                        class="text-xs bg-blue-50 text-blue-600 border border-blue-200 font-bold py-1.5 px-3 rounded-lg shadow-xs hover:bg-blue-100 transition-colors">
                                                    Detail
                                                </button>
                                                
                                                <!-- Delete Button -->
                                                <button onclick="confirmDeleteAdmin(<?= $order['id']; ?>, '<?= htmlspecialchars($order['nama_pelanggan'], ENT_QUOTES); ?>')" 
                                                        class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg border border-transparent hover:border-rose-100 transition-all active:scale-95 flex items-center justify-center" 
                                                        title="Hapus / Sembunyikan Pesanan">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
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
</main>

<!-- Detail Modal -->
<div id="detail-modal" class="fixed inset-0 z-[100] hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-md">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl relative flex flex-col">
        <div class="p-lg border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div>
                <h3 class="font-extrabold text-slate-900 text-lg leading-tight">Detail Pesanan</h3>
                <p class="text-xs text-slate-500 mt-0.5" id="detail-order-id">Pesanan #</p>
            </div>
            <button onclick="closeDetailModal()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 text-2xl">close</button>
        </div>
        <div class="p-lg space-y-md overflow-y-auto max-h-[70vh] text-xs">
            <div class="grid grid-cols-2 gap-md border-b border-slate-100 pb-md">
                <div>
                    <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Pelanggan</span>
                    <span class="block text-slate-800 font-extrabold text-sm" id="detail-customer-name">-</span>
                </div>
                <div>
                    <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Outlet Laundry</span>
                    <span class="block text-slate-800 font-extrabold text-sm" id="detail-mitra-name">-</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-md border-b border-slate-100 pb-md">
                <div>
                    <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Layanan</span>
                    <span class="block text-blue-600 font-bold text-sm" id="detail-service-name">-</span>
                </div>
                <div>
                    <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Berat / Qty</span>
                    <span class="block text-slate-800 font-bold text-sm" id="detail-weight">-</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-md border-b border-slate-100 pb-md">
                <div>
                    <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Total Tagihan</span>
                    <span class="block text-slate-950 font-extrabold text-sm" id="detail-total-price">-</span>
                </div>
                <div>
                    <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Status Pembayaran</span>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold text-white" id="detail-pay-status">-</span>
                </div>
            </div>

            <div class="border-b border-slate-100 pb-md">
                <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Status Pemrosesan</span>
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold" id="detail-order-status">-</span>
            </div>

            <div class="border-b border-slate-100 pb-md">
                <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Alamat Penjemputan / Pengantaran</span>
                <span class="block text-slate-700 leading-relaxed" id="detail-address">-</span>
            </div>

            <div>
                <span class="block text-slate-400 uppercase font-bold tracking-wider mb-1">Catatan Pelanggan</span>
                <span class="block text-slate-700 italic" id="detail-notes">-</span>
            </div>

            <div id="detail-photo-section" class="hidden pt-md border-t border-slate-100">
                <span class="block text-slate-400 uppercase font-bold tracking-wider mb-2">Foto Bukti Timbangan</span>
                <div class="relative rounded-2xl overflow-hidden aspect-video border border-slate-200 bg-slate-50 flex items-center justify-center max-w-sm">
                    <img id="detail-photo-img" src="" alt="Foto Timbangan" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openDetailModal(order) {
        document.getElementById('detail-order-id').innerText = 'Pesanan #' + order.id;
        document.getElementById('detail-customer-name').innerText = order.nama_pelanggan;
        document.getElementById('detail-mitra-name').innerText = order.nama_mitra;
        document.getElementById('detail-service-name').innerText = order.layanan;
        
        let weightText = '';
        if (order.is_kiloan) {
            weightText = 'Estimasi: ' + order.estimasi_berat + ' kg, Real: ' + (order.berat_atau_qty > 0 ? order.berat_atau_qty + ' kg' : '-');
        } else if (order.is_self) {
            weightText = order.berat_atau_qty + ' Slot';
        } else {
            weightText = order.estimasi_berat + ' Pasang';
        }
        document.getElementById('detail-weight').innerText = weightText;
        document.getElementById('detail-total-price').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(order.total_harga);
        
        const payBadge = document.getElementById('detail-pay-status');
        payBadge.innerText = order.status_pembayaran.toUpperCase();
        payBadge.className = 'inline-block px-2 py-0.5 rounded text-[10px] font-bold text-white ' + 
            (order.status_pembayaran === 'success' ? 'bg-emerald-500' : (order.status_pembayaran === 'pending' ? 'bg-amber-500' : 'bg-rose-500'));
            
        const orderBadge = document.getElementById('detail-order-status');
        orderBadge.innerText = order.status_order;
        orderBadge.className = 'inline-block px-2.5 py-1 rounded-full text-xs font-bold ' + 
            (order.status_order === 'Menunggu Pembayaran' ? 'bg-sky-100 text-sky-800' : (order.status_order === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : (order.status_order === 'Dibatalkan' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700')));
            
        document.getElementById('detail-address').innerText = order.alamat_antar_jemput || 'Ambil Mandiri (Tidak ada layanan antar-jemput)';
        document.getElementById('detail-notes').innerText = order.catatan || 'Tidak ada catatan tambahan.';
        
        const photoSection = document.getElementById('detail-photo-section');
        if (order.foto_timbangan) {
            document.getElementById('detail-photo-img').src = '../' + order.foto_timbangan;
            photoSection.classList.remove('hidden');
        } else {
            photoSection.classList.add('hidden');
        }
        
        document.getElementById('detail-modal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
    }
    
    // Delete Confirmation Modal Controls for Admin
    let selectedDeleteOrderId = null;
    let selectedRowElement = null;

    function confirmDeleteAdmin(orderId, customerName) {
        selectedDeleteOrderId = orderId;
        selectedRowElement = event.currentTarget.closest('tr');
        document.getElementById('delete-modal-subtitle').innerText = 'Pesanan #' + orderId;
        document.getElementById('delete-modal-name').innerText = customerName;
        
        const modal = document.getElementById('delete-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.firstElementChild.classList.remove('scale-95');
            modal.firstElementChild.classList.add('scale-100');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('delete-modal');
        modal.firstElementChild.classList.remove('scale-100');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            selectedDeleteOrderId = null;
            selectedRowElement = null;
        }, 150);
    }

    function executeDeleteAdmin() {
        if (!selectedDeleteOrderId || !selectedRowElement) return;
        
        const type = document.querySelector('input[name="delete_type"]:checked').value;
        const btn = document.getElementById('confirm-delete-btn');
        btn.disabled = true;
        btn.innerHTML = '<span>Memproses...</span>';

        const formData = new FormData();
        formData.append('action', 'delete_order_admin');
        formData.append('order_id', selectedDeleteOrderId);
        formData.append('type', type);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                
                // Fade out and remove row smoothly
                selectedRowElement.style.transition = 'all 0.4s ease';
                selectedRowElement.style.opacity = '0';
                selectedRowElement.style.transform = 'translateX(50px)';
                
                setTimeout(() => {
                    selectedRowElement.remove();
                    // Check if table is empty
                    const tableBody = document.getElementById('orders-table-body');
                    const remainingRows = tableBody.querySelectorAll('tr');
                    if (remainingRows.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="8" class="px-md py-xl text-center text-slate-400">Belum ada pesanan terdaftar di sistem.</td>
                            </tr>
                        `;
                    } else {
                        // Re-index No. column
                        let idx = 1;
                        tableBody.querySelectorAll('tr').forEach(r => {
                            const numCell = r.querySelector('td:first-child');
                            if (numCell) numCell.innerText = idx++;
                        });
                    }
                }, 400);
            } else {
                alert('Gagal menghapus pesanan: ' + (data.message || 'Error'));
                btn.disabled = false;
                btn.innerHTML = '<span>Hapus Pesanan</span><span class="material-symbols-outlined text-[16px]">check</span>';
            }
        })
        .catch(() => {
            alert('Kesalahan jaringan.');
            btn.disabled = false;
            btn.innerHTML = '<span>Hapus Pesanan</span><span class="material-symbols-outlined text-[16px]">check</span>';
        });
    }
</script>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative overflow-hidden transform scale-95 transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center gap-3.5 mb-4 text-rose-600">
            <div class="p-2.5 bg-rose-50 rounded-xl">
                <span class="material-symbols-outlined text-[26px]">delete_forever</span>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-lg leading-tight">Hapus Pesanan</h3>
                <p class="text-xs text-slate-500 mt-0.5" id="delete-modal-subtitle">Pesanan #</p>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-4 text-slate-600 text-xs leading-relaxed">
            <p>Bagaimana Anda ingin menghapus pesanan dari pelanggan <span class="font-bold text-slate-900" id="delete-modal-name"></span>?</p>
            
            <div class="grid grid-cols-1 gap-3 mt-2">
                <!-- Option 1: Hide -->
                <label class="relative flex items-start gap-3 p-3 bg-slate-50 border border-slate-200/60 hover:border-blue-500/40 rounded-2xl cursor-pointer select-none transition-all">
                    <input type="radio" name="delete_type" value="hide" checked class="mt-0.5 text-blue-600 border-slate-300 focus:ring-blue-500">
                    <div>
                        <span class="block font-bold text-slate-900 text-xs">Sembunyikan dari Tampilan</span>
                        <span class="block text-[10px] text-slate-400 mt-0.5">Pesanan disembunyikan dari tabel ini, tetapi datanya tetap tersimpan di database.</span>
                    </div>
                </label>

                <!-- Option 2: Permanent -->
                <label class="relative flex items-start gap-3 p-3 bg-rose-50/20 border border-rose-100 hover:border-rose-300 rounded-2xl cursor-pointer select-none transition-all">
                    <input type="radio" name="delete_type" value="permanent" class="mt-0.5 text-rose-600 border-rose-200 focus:ring-rose-500">
                    <div>
                        <span class="block font-bold text-rose-950 text-xs">Hapus Permanen</span>
                        <span class="block text-[10px] text-rose-400 mt-0.5">Menghapus pesanan secara permanen dari database. Data ini tidak dapat dipulihkan kembali.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2.5 mt-6">
            <button onclick="closeDeleteModal()" class="text-xs font-bold text-slate-500 hover:bg-slate-100 px-4 py-2.5 rounded-xl transition-all">
                Batal
            </button>
            <button id="confirm-delete-btn" onclick="executeDeleteAdmin()" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5 shadow-md shadow-rose-600/10">
                <span>Hapus Pesanan</span>
                <span class="material-symbols-outlined text-[16px]">check</span>
            </button>
        </div>
    </div>
</div>
</body>
</html>
