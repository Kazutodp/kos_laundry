<?php
// mitra/dashboard.php
session_start();
require_once '../db_connect.php';

// Authentication Check
if (!isset($_SESSION['mitra_logged_in']) || !isset($_SESSION['mitra_id'])) {
    header("Location: login.php");
    exit();
}

$mitra_id = $_SESSION['mitra_id'];

// Handle Store Status Toggle (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_store') {
    header('Content-Type: application/json');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
    try {
        $stmt = $pdo->prepare("UPDATE mitra_laundry SET status_buka = ? WHERE id = ?");
        $stmt->execute([$status, $mitra_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    header('Content-Type: application/json');
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $new_status = trim($_POST['status_transfer'] ?? ''); // Column 'status_transfer' handles order workflow status in this DB

    if ($order_id > 0 && !empty($new_status)) {
        try {
            // Verify order belongs to this partner
            $check_stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND mitra_id = ?");
            $check_stmt->execute([$order_id, $mitra_id]);
            if ($check_stmt->fetch()) {
                $stmt = $pdo->prepare("UPDATE orders SET status_transfer = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan atau bukan milik Anda.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    }
    exit();
}

// Handle Check New Orders (AJAX Polling)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_new_orders') {
    header('Content-Type: application/json');
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    try {
        // Query for successful orders with ID higher than last known ID
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE mitra_id = ? AND id > ? AND status_pembayaran = 'success' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$mitra_id, $last_id]);
        $new_order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'new_order' => $new_order ? true : false,
            'latest_id' => $new_order ? (int)$new_order['id'] : $last_id
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Fetch Partner Profile Data
$stmt = $pdo->prepare("SELECT * FROM mitra_laundry WHERE id = ?");
$stmt->execute([$mitra_id]);
$mitra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mitra) {
    // Session invalid if partner deleted
    unset($_SESSION['mitra_logged_in']);
    header("Location: login.php");
    exit();
}

// Statistics Calculations
try {
    // 1. Get unique list of months/years that have orders for this partner (for the dropdown filter)
    $stmt_months = $pdo->prepare("SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as ym FROM orders WHERE mitra_id = ? ORDER BY ym DESC");
    $stmt_months->execute([$mitra_id]);
    $available_months = $stmt_months->fetchAll(PDO::FETCH_COLUMN);

    // Get selected filter month from GET parameter
    $selected_month = trim($_GET['filter_month'] ?? 'all');
    if ($selected_month !== 'all' && !preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
        $selected_month = 'all';
    }

    // 2. Orders today (always shows today's count)
    $stmt_today = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE mitra_id = ? AND DATE(created_at) = CURRENT_DATE");
    $stmt_today->execute([$mitra_id]);
    $orders_today = $stmt_today->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    // 3. Calculate This Month's Net Revenue (Kalender Berjalan)
    $this_month_ym = date('Y-m');
    $stmt_this_month = $pdo->prepare("SELECT SUM(total_harga) as rev, COUNT(*) as count FROM orders WHERE mitra_id = ? AND status_pembayaran = 'success' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt_this_month->execute([$mitra_id, $this_month_ym]);
    $this_month_data = $stmt_this_month->fetch(PDO::FETCH_ASSOC);
    $revenue_this_month = $this_month_data['rev'] ?? 0;
    $payout_this_month = $revenue_this_month * 0.9;
    $orders_this_month = $this_month_data['count'] ?? 0;

    // Calculate Last Month's Net Revenue (Bulan Lalu)
    $last_month_ym = date('Y-m', strtotime('first day of last month'));
    $stmt_last_month = $pdo->prepare("SELECT SUM(total_harga) as rev, COUNT(*) as count FROM orders WHERE mitra_id = ? AND status_pembayaran = 'success' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt_last_month->execute([$mitra_id, $last_month_ym]);
    $last_month_data = $stmt_last_month->fetch(PDO::FETCH_ASSOC);
    $revenue_last_month = $last_month_data['rev'] ?? 0;
    $payout_last_month = $revenue_last_month * 0.9;
    $orders_last_month = $last_month_data['count'] ?? 0;

    // 4. Calculate Payout Bersih and successful orders based on SELECTED filter
    if ($selected_month === 'all') {
        $stmt_payout = $pdo->prepare("SELECT SUM(total_harga) as total_revenue FROM orders WHERE mitra_id = ? AND status_pembayaran = 'success'");
        $stmt_payout->execute([$mitra_id]);
        
        $stmt_success = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE mitra_id = ? AND status_pembayaran = 'success'");
        $stmt_success->execute([$mitra_id]);
        
        $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE mitra_id = ? ORDER BY id DESC");
        $stmt_orders->execute([$mitra_id]);
    } else {
        $stmt_payout = $pdo->prepare("SELECT SUM(total_harga) as total_revenue FROM orders WHERE mitra_id = ? AND status_pembayaran = 'success' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt_payout->execute([$mitra_id, $selected_month]);
        
        $stmt_success = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE mitra_id = ? AND status_pembayaran = 'success' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt_success->execute([$mitra_id, $selected_month]);
        
        $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE mitra_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ? ORDER BY id DESC");
        $stmt_orders->execute([$mitra_id, $selected_month]);
    }
    
    $revenue_data = $stmt_payout->fetch(PDO::FETCH_ASSOC);
    $total_revenue = $revenue_data['total_revenue'] ?? 0;
    $payout_bersih = $total_revenue * 0.9;
    $total_success_orders = $stmt_success->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

    // Get absolute highest order ID of ALL orders for polling checkpointing
    $stmt_latest_checkpoint = $pdo->prepare("SELECT MAX(id) as max_id FROM orders WHERE mitra_id = ?");
    $stmt_latest_checkpoint->execute([$mitra_id]);
    $latest_order_id = (int)($stmt_latest_checkpoint->fetch(PDO::FETCH_ASSOC)['max_id'] ?? 0);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Indonesian Month Names Helper Function
function getIndonesianMonthName($ym) {
    if (!$ym || $ym === 'all') return 'Semua Waktu';
    $parts = explode('-', $ym);
    if (count($parts) !== 2) return $ym;
    $year = $parts[0];
    $month = (int)$parts[1];
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return ($monthNames[$month] ?? $parts[1]) . ' ' . $year;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Dashboard <?= htmlspecialchars($mitra['nama_mitra']); ?> | MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: "#0058be",
              secondary: "#00a389",
              dark: "#0f172a"
            },
            fontFamily: {
              sans: ["Inter", "sans-serif"]
            }
          }
        }
      }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col">

    <!-- Top Navbar -->
    <nav class="sticky top-0 z-40 bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-50 rounded-xl flex items-center justify-center">
                <img alt="MataramWash Logo" class="h-8 w-8 object-contain" src="../Logo_MataramWash.png">
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Portal Mitra</span>
                <h1 class="text-md font-extrabold text-slate-900 leading-tight"><?= htmlspecialchars($mitra['nama_mitra']); ?></h1>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <!-- Store Status Toggle Card -->
            <div class="flex items-center bg-slate-50 border border-slate-200/60 px-4 py-2 rounded-xl shadow-inner gap-3">
                <span class="text-xs font-bold text-slate-500">Status Toko:</span>
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" id="store-toggle" onchange="toggleStore(this.checked)" <?= $mitra['status_buka'] == 1 ? 'checked' : ''; ?> class="sr-only peer">
                    <div class="w-9 h-[20px] bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-[16px] after:w-[16px] after:transition-all peer-checked:bg-secondary"></div>
                    <span id="store-status-text" class="ml-2 text-xs font-bold <?= $mitra['status_buka'] == 1 ? 'text-secondary' : 'text-slate-500'; ?>">
                        <?= $mitra['status_buka'] == 1 ? 'BUKA' : 'TUTUP'; ?>
                    </span>
                </label>
            </div>

            <!-- Logout Link -->
            <a href="logout.php" class="flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 px-3.5 py-2 rounded-xl transition-all border border-transparent hover:border-rose-100">
                <span class="material-symbols-outlined text-[18px]">logout</span>
                Keluar
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-6 space-y-6">

        <!-- Banner & Notification Indicator Alert -->
        <div id="new-order-alert" class="hidden p-4 bg-amber-500 text-white rounded-2xl shadow-lg shadow-amber-500/20 flex items-center justify-between animate-bounce">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[28px] animate-pulse">notifications_active</span>
                <div>
                    <h3 class="font-bold text-sm">Pesanan Baru Masuk!</h3>
                    <p class="text-xs opacity-90">Ada pemesanan terbaru yang baru saja dibayar oleh pelanggan.</p>
                </div>
            </div>
            <button onclick="dismissAlert()" class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                Muat Ulang Halaman
            </button>
        </div>

        <!-- Dashboard Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Stat 1: Payout Bersih -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 relative overflow-hidden group hover:border-primary/20 transition-all">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary/20 text-[40px] group-hover:scale-110 transition-transform">payments</span>
                </div>
                <div class="p-3 bg-blue-50 text-primary rounded-xl">
                    <span class="material-symbols-outlined text-[28px] block">payments</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Payout Bersih Anda (90%)</p>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Rp <?= number_format($payout_bersih, 0, ',', '.'); ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Periode: <span class="font-bold text-primary"><?= getIndonesianMonthName($selected_month); ?></span></p>
                </div>
            </div>

            <!-- Stat 2: Pesanan Hari Ini -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 relative overflow-hidden group hover:border-secondary/20 transition-all">
                <div class="absolute top-0 right-0 w-24 h-24 bg-secondary/5 rounded-bl-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-secondary/20 text-[40px] group-hover:scale-110 transition-transform">today</span>
                </div>
                <div class="p-3 bg-emerald-50 text-secondary rounded-xl">
                    <span class="material-symbols-outlined text-[28px] block">today</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pesanan Hari Ini</p>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight"><?= $orders_today; ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Total masuk tanggal <?= date('d M Y'); ?></p>
                </div>
            </div>

            <!-- Stat 3: Total Sukses -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 relative overflow-hidden group hover:border-amber-500/20 transition-all">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-bl-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-500/20 text-[40px] group-hover:scale-110 transition-transform">check_circle</span>
                </div>
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <span class="material-symbols-outlined text-[28px] block">check_circle</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Pesanan Sukses</p>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight"><?= $total_success_orders; ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Periode: <span class="font-bold text-amber-600"><?= getIndonesianMonthName($selected_month); ?></span></p>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison Banner -->
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-6 text-white border border-slate-800/80 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <!-- Subtle glow accents -->
            <div class="absolute w-40 h-40 bg-primary/10 rounded-full blur-3xl -top-20 -left-20"></div>
            <div class="absolute w-40 h-40 bg-secondary/10 rounded-full blur-3xl -bottom-20 -right-20"></div>
            
            <div class="space-y-1 relative z-10">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Laporan Perbandingan Bulanan</span>
                <h3 class="text-lg font-bold">Ringkasan Pendapatan Bersih</h3>
                <p class="text-xs text-slate-400">Membandingkan hasil bulan ini dengan bulan kemarin (setelah potongan platform 10%)</p>
            </div>
            
            <div class="grid grid-cols-2 gap-8 md:gap-16 relative z-10">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Bulan Ini (<?= getIndonesianMonthName($this_month_ym); ?>)</span>
                    <div class="text-xl font-extrabold text-secondary">Rp <?= number_format($payout_this_month, 0, ',', '.'); ?></div>
                    <div class="text-[10px] text-slate-400 font-medium"><?= $orders_this_month; ?> Transaksi Sukses</div>
                </div>
                <div class="space-y-1 border-l border-slate-700/60 pl-8">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Bulan Lalu (<?= getIndonesianMonthName($last_month_ym); ?>)</span>
                    <div class="text-xl font-extrabold text-slate-300">Rp <?= number_format($payout_last_month, 0, ',', '.'); ?></div>
                    <div class="text-[10px] text-slate-400 font-medium"><?= $orders_last_month; ?> Transaksi Sukses</div>
                </div>
            </div>
        </div>

        <!-- Orders Table Container -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-slate-900 text-lg leading-tight">Daftar Transaksi Masuk</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Memantau proses pengerjaan cucian pelanggan secara real-time</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Month Filter Dropdown -->
                    <form method="GET" action="" class="flex items-center gap-2">
                        <label for="filter_month" class="text-xs font-bold text-slate-500 whitespace-nowrap">Filter Bulan:</label>
                        <select name="filter_month" id="filter_month" onchange="this.form.submit()" 
                                class="text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 py-1.5 pl-3 pr-8 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="all" <?= $selected_month === 'all' ? 'selected' : ''; ?>>Semua Waktu</option>
                            <?php foreach ($available_months as $m): ?>
                                <option value="<?= $m; ?>" <?= $selected_month === $m ? 'selected' : ''; ?>>
                                    <?= getIndonesianMonthName($m); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200/50">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        Auto Refresh Aktif
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-center w-12">No</th>
                            <th class="px-6 py-4">ID Pesanan</th>
                            <th class="px-6 py-4">Pelanggan</th>
                            <th class="px-6 py-4">Layanan</th>
                            <th class="px-6 py-4 text-right">Tarif</th>
                            <th class="px-6 py-4 text-center">Jumlah</th>
                            <th class="px-6 py-4 text-right">Total Bayar</th>
                            <th class="px-6 py-4">Status Pemrosesan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-2">inbox</span>
                                    <p class="text-sm font-semibold">Belum ada pesanan masuk untuk outlet Anda.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $no = 1; 
                            foreach ($orders as $order): 
                                $is_success = ($order['status_pembayaran'] === 'success');
                            ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 text-center font-semibold text-slate-400 text-xs"><?= $no++; ?></td>
                                    <td class="px-6 py-4 font-bold text-slate-900 text-xs">#<?= $order['id']; ?></td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-slate-950 text-xs block"><?= htmlspecialchars($order['nama_pelanggan']); ?></span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5"><?= date('d M H:i', strtotime($order['created_at'])); ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-semibold text-slate-700"><?= htmlspecialchars($order['layanan']); ?></td>
                                    <td class="px-6 py-4 text-right text-xs font-medium">Rp <?= number_format($order['tarif_per_kg'], 0, ',', '.'); ?></td>
                                    <td class="px-6 py-4 text-center text-xs font-bold text-slate-900"><?= floatval($order['berat_atau_qty']); ?></td>
                                    <td class="px-6 py-4 text-right text-xs font-bold text-slate-950">Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="px-6 py-4">
                                        <?php if (!$is_success): ?>
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-400 border border-slate-200/50 rounded-full text-[10px] font-extrabold uppercase select-none">Menunggu Pembayaran</span>
                                        <?php else: ?>
                                            <!-- Render drop down status selector if paid -->
                                            <select onchange="updateOrderStatus(<?= $order['id']; ?>, this.value)" 
                                                    class="text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 py-1 px-2.5 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                                <option value="Selesai" <?= $order['status_transfer'] === 'Selesai' ? 'selected' : ''; ?>>✓ Selesai</option>
                                                <option value="Sedang Dicuci" <?= $order['status_transfer'] === 'Sedang Dicuci' ? 'selected' : ''; ?>>🧺 Sedang Dicuci</option>
                                                <option value="Sedang Dikeringkan" <?= $order['status_transfer'] === 'Sedang Dikeringkan' ? 'selected' : ''; ?>>🔥 Sedang Dikeringkan</option>
                                                <option value="Menunggu Penjemputan" <?= $order['status_transfer'] === 'Menunggu Penjemputan' ? 'selected' : ''; ?>>🚚 Menunggu Penjemputan</option>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <!-- Quick detail or visual link -->
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-bold <?= $is_success ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'; ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $is_success ? 'bg-emerald-500' : 'bg-rose-500'; ?>"></span>
                                            <?= $is_success ? 'LUNAS' : 'PENDING'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-4 px-6 text-center text-xs text-slate-400">
        © 2024 MataramWash Portal Kemitraan. Mempermudah Pengelolaan Operasional Laundry Anda.
    </footer>

    <!-- Audio element alternative / Web Audio API Helper -->
    <script>
        // Store current highest ID
        let currentLatestId = <?= $latest_order_id; ?>;

        // Function to create audio alert using Web Audio API (no external file needed)
        function playNotificationSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                
                // First beep
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
                gain1.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start();
                osc1.stop(audioCtx.currentTime + 0.3);
                
                // Second beep
                setTimeout(() => {
                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(1320, audioCtx.currentTime); // E6 note
                    gain2.gain.setValueAtTime(0.1, audioCtx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.start();
                    osc2.stop(audioCtx.currentTime + 0.4);
                }, 150);
            } catch(e) {
                console.error("Audio Notification failed: ", e);
            }
        }

        // Toggle Store Status
        function toggleStore(isChecked) {
            const val = isChecked ? 1 : 0;
            const statusText = document.getElementById('store-status-text');
            
            const formData = new FormData();
            formData.append('action', 'toggle_store');
            formData.append('status', val);

            fetch('dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusText.innerText = isChecked ? 'BUKA' : 'TUTUP';
                    statusText.className = `ml-2 text-xs font-bold ${isChecked ? 'text-secondary' : 'text-slate-500'}`;
                } else {
                    alert('Gagal memperbarui status toko.');
                    location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Kesalahan jaringan.');
                location.reload();
            });
        }

        // Update Order Process Workflow Status
        function updateOrderStatus(orderId, newStatus) {
            const formData = new FormData();
            formData.append('action', 'update_order_status');
            formData.append('order_id', orderId);
            formData.append('status_transfer', newStatus);

            fetch('dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memperbarui status pesanan: ' + (data.message || 'Error'));
                    location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Kesalahan jaringan.');
                location.reload();
            });
        }

        // Polling loop to check for new orders
        function checkNewOrders() {
            fetch(`dashboard.php?action=check_new_orders&last_id=${currentLatestId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.new_order) {
                        // Play sound
                        playNotificationSound();
                        // Show warning banner
                        document.getElementById('new-order-alert').classList.remove('hidden');
                        // Update tracking ID to prevent looping alarms
                        currentLatestId = data.latest_id;
                    }
                })
                .catch(err => console.error("Polling error: ", err));
        }

        // Start Polling every 10 seconds
        setInterval(checkNewOrders, 10000);

        // Dismiss warning banner & reload
        function dismissAlert() {
            location.reload();
        }
    </script>
</body>
</html>
