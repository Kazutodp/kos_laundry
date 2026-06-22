<?php
session_start();
require_once '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$order_id = intval($_GET['order_id'] ?? 0);
$reference = htmlspecialchars($_GET['reference'] ?? '');

if ($order_id <= 0) {
    die("ID Pesanan tidak valid.");
}

// Fetch order details & make sure it belongs to the current user
try {
    $stmt = $pdo->prepare("
        SELECT o.*, m.nama_mitra, m.alamat as alamat_mitra, m.no_telp as telp_mitra 
        FROM orders o
        JOIN mitra_laundry m ON o.mitra_id = m.id
        WHERE o.id = ? AND o.nama_pelanggan = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_nama']]);
    $order = $stmt->fetch();

    if (!$order) {
        die("Pesanan tidak ditemukan atau Anda tidak berwenang mengakses halaman ini.");
    }
} catch (PDOException $e) {
    die("Kesalahan database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pembayaran Sukses | MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "surface-bright": "#f9f9ff",
                    "surface-variant": "#dce2f3",
                    "on-surface": "#151c27",
                    "primary": "#0058be",
                    "primary-container": "#2170e4",
                    "on-primary": "#ffffff",
                    "outline-variant": "#c2c6d6",
                    "background": "#f9f9ff",
                    "on-background": "#151c27",
                    "surface-container": "#e7eefe",
                    "surface-container-low": "#f0f3ff",
                    "surface-container-lowest": "#ffffff"
            }
          }
        }
      }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9ff;
        }
        .material-symbols-outlined {
            vertical-align: middle;
        }
    </style>
</head>
<body class="text-on-background bg-background min-h-screen flex flex-col justify-between">

    <!-- Mini Header -->
    <header class="w-full bg-surface-container shadow-sm py-4 px-6 border-b border-outline-variant/30">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a class="flex items-center space-x-xs text-headline-md font-bold text-primary" href="../index.php">
                <img alt="MataramWash Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
                <span class="text-lg">MataramWash</span>
            </a>
            <a class="flex items-center space-x-1 text-label-md font-bold text-primary hover:underline" href="../index.php">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali</span>
            </a>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-grow flex items-center justify-center p-md py-xl">
        <div class="max-w-md w-full bg-white rounded-3xl border border-outline-variant/20 p-xl shadow-[0px_10px_35px_rgba(0,0,0,0.06)] space-y-lg text-center relative overflow-hidden">
            <!-- Top Gradient Accent -->
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

            <!-- Success Icon -->
            <div class="flex justify-center pt-md">
                <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center border-4 border-emerald-100 animate-bounce">
                    <span class="material-symbols-outlined text-emerald-500 text-[48px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
            </div>

            <!-- Header Titles -->
            <div class="space-y-xs">
                <h1 class="text-headline-md font-bold text-on-surface">Pembayaran Sukses!</h1>
                <p class="text-body-md text-slate-500">Terima kasih, pembayaran Anda telah berhasil kami terima.</p>
            </div>

            <!-- Receipt Detail Box -->
            <div class="bg-surface-container-low border border-outline-variant/30 rounded-2xl p-lg text-left space-y-md">
                <div class="flex justify-between border-b border-outline-variant/30 pb-sm">
                    <span class="text-label-sm text-slate-500 uppercase tracking-wider">Detail Transaksi</span>
                    <span class="text-body-sm font-bold text-slate-700">#<?= htmlspecialchars($order['id']); ?></span>
                </div>

                <div class="space-y-sm text-body-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Mitra Laundry</span>
                        <span class="font-semibold text-on-surface"><?= htmlspecialchars($order['nama_mitra']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Layanan</span>
                        <span class="font-semibold text-on-surface"><?= htmlspecialchars($order['layanan']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Jumlah / Qty</span>
                        <span class="font-semibold text-on-surface"><?= htmlspecialchars(floatval($order['berat_atau_qty'])); ?> x Rp <?= number_format($order['tarif_per_kg'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Biaya Antar-Jemput</span>
                        <span class="font-semibold text-on-surface">Rp <?= number_format($order['biaya_antar_jemput'], 0, ',', '.'); ?></span>
                    </div>
                    <?php if ($reference): ?>
                    <div class="flex justify-between border-t border-dashed border-outline-variant/30 pt-sm">
                        <span class="text-slate-500">ID Transaksi</span>
                        <span class="font-mono text-[11px] text-slate-600 truncate max-w-[200px]"><?= $reference; ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between border-t border-outline-variant/30 pt-md mt-sm">
                    <span class="text-body-md font-bold text-on-surface">Total Bayar</span>
                    <span class="text-body-lg font-extrabold text-primary">Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <!-- Follow up Alert -->
            <div class="p-md bg-sky-50 border border-sky-100 rounded-2xl flex items-start gap-sm text-left">
                <span class="material-symbols-outlined text-sky-500 mt-0.5" style="font-variation-settings: 'FILL' 1;">info</span>
                <div class="text-[12px] text-sky-800 leading-normal">
                    <strong>Pemberitahuan:</strong> Kurir dari <strong><?= htmlspecialchars($order['nama_mitra']); ?></strong> akan segera menghubungi Anda di nomor telepon terdaftar untuk koordinasi penjemputan cucian.
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-sm flex flex-col sm:flex-row gap-md justify-center">
                <a href="../index.php" class="w-full sm:w-auto px-lg py-sm bg-primary text-white rounded-xl font-bold flex items-center justify-center gap-xs shadow-md hover:brightness-110 active:scale-95 transition-all text-sm">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    Beranda
                </a>
                <a href="notifikasi.php" class="w-full sm:w-auto px-lg py-sm bg-slate-100 text-slate-700 rounded-xl font-bold flex items-center justify-center gap-xs hover:bg-slate-200 active:scale-95 transition-all text-sm border border-slate-200">
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                    Pantau Pesanan
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-4 text-center bg-surface-container border-t border-outline-variant/20 text-[11px] text-slate-400">
        © 2026 MataramWash. Semua pembayaran digital diproses secara aman oleh Midtrans.
    </footer>
</body>
</html>
