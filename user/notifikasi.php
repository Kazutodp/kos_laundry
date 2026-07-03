<?php
session_start();
require_once '../db_connect.php';

// Cek apakah user sudah login, jika belum arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Notifikasi - MataramWash</title>
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
                    "on-error-container": "#93000a",
                    "on-error": "#ffffff",
                    "primary-fixed": "#d8e2ff",
                    "error-container": "#ffdad6",
                    "surface-container": "#e7eefe",
                    "surface-container-lowest": "#ffffff",
                    "on-secondary-fixed-variant": "#005048",
                    "inverse-on-surface": "#ebf1ff",
                    "tertiary-fixed-dim": "#ffb95f",
                    "secondary": "#006b5f",
                    "on-tertiary-fixed-variant": "#653e00",
                    "on-secondary-container": "#006f64",
                    "secondary-fixed": "#71f8e4",
                    "on-tertiary": "#ffffff",
                    "on-tertiary-container": "#fffbff",
                    "surface-dim": "#d3daea",
                    "secondary-fixed-dim": "#4fdbc8",
                    "inverse-surface": "#2a313d",
                    "on-secondary-fixed": "#00201c",
                    "on-surface-variant": "#424754",
                    "background": "#f9f9ff",
                    "on-tertiary-fixed": "#2a1700",
                    "outline-variant": "#c2c6d6",
                    "surface-container-highest": "#dce2f3",
                    "primary-container": "#2170e4",
                    "error": "#ba1a1a",
                    "on-primary-fixed": "#001a42",
                    "on-surface": "#151c27",
                    "secondary-container": "#6df5e1",
                    "tertiary-fixed": "#ffddb8",
                    "surface": "#f9f9ff",
                    "outline": "#727785",
                    "surface-tint": "#005ac2",
                    "on-primary": "#ffffff",
                    "surface-container-low": "#f0f3ff",
                    "on-primary-container": "#fefcff",
                    "on-primary-fixed-variant": "#004395",
                    "tertiary": "#825100",
                    "on-secondary": "#ffffff",
                    "on-background": "#151c27",
                    "primary": "#0058be",
                    "primary-fixed-dim": "#adc6ff",
                    "inverse-primary": "#adc6ff",
                    "surface-container-high": "#e2e8f8",
                    "tertiary-container": "#a36700"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "base": "4px",
                    "xs": "8px",
                    "gutter": "16px",
                    "lg": "24px",
                    "xl": "32px",
                    "container-margin": "20px",
                    "sm": "12px",
                    "md": "16px"
            },
            "fontFamily": {
                    "label-sm": ["Inter"],
                    "display-lg": ["Inter"],
                    "label-md": ["Inter"],
                    "headline-md": ["Inter"],
                    "headline-lg-mobile": ["Inter"],
                    "headline-lg": ["Inter"],
                    "body-md": ["Inter"],
                    "body-lg": ["Inter"]
            },
            "fontSize": {
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .unread-dot::after {
            content: '';
            position: absolute;
            top: 12px;
            right: 12px;
            width: 8px;
            height: 8px;
            background-color: #0058be; /* primary */
            border-radius: 50%;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9ff;
        }
        .custom-shadow {
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
        }
        .transition-standard {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="text-on-background bg-background">

<!-- Mini Header for Brand -->
<header class="w-full bg-surface-container shadow-sm py-4 px-6 border-b border-outline-variant/30 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <a class="flex items-center space-x-xs text-headline-md font-headline-md font-bold text-primary" href="../index.php">
            <img alt="MataramWash Logo" class="h-8 w-8 object-contain" src="../Logo_MataramWash.png?v=3">
            <span class="text-lg">MataramWash</span>
        </a>
        <a class="flex items-center space-x-1 text-label-md font-bold text-primary hover:underline" href="../index.php">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
</header>

<main class="max-w-4xl mx-auto px-6 py-12 pt-10">
    <!-- Header Section -->
    <header class="mb-xl">
        <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Notifikasi</h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Pantau pembaruan pesanan dan promo menarik untuk Anda.</p>
    </header>

    <!-- Notifications List -->
    <div id="notifications-list" class="space-y-md">
        <!-- Item 1: Status Update (Unread) -->
        <div class="relative group bg-white p-lg rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] cursor-pointer unread-dot">
            <div class="flex gap-lg items-start">
                <div class="w-12 h-12 rounded-xl bg-primary-container/10 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-primary text-[28px]">laundry</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-base">
                        <h3 class="font-body-lg font-semibold text-on-surface">Pesanan #KL-9921 Sedang Dicuci di Laundry Bersih Jaya</h3>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">2 jam yang lalu</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        Pakaian Anda saat ini sedang dalam proses pencucian profesional. Estimasi selesai adalah sore ini pukul 17:00.
                    </p>
                </div>
            </div>
        </div>

        <!-- Item 3: Courier Tracking (New/Highlight) -->
        <div class="relative group bg-white p-lg rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] cursor-pointer unread-dot">
            <div class="flex gap-lg items-start">
                <div class="w-12 h-12 rounded-xl bg-secondary-container/20 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-secondary text-[28px]">local_shipping</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-base">
                        <h3 class="font-body-lg font-semibold text-on-surface">Kurir Menuju Lokasi Penjemputan</h3>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">3 jam yang lalu</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">Kurir Laundry Express dalam perjalanan menuju lokasi Anda untuk penjemputan.</p>
                </div>
            </div>
        </div>

        <!-- Item 2: Promo Info -->
        <div class="relative group bg-surface-container-lowest p-lg rounded-xl border border-outline-variant/20 transition-all hover:bg-white cursor-pointer">
            <div class="flex gap-lg items-start">
                <div class="w-12 h-12 rounded-xl bg-tertiary-container/10 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-tertiary text-[28px]">local_offer</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-base">
                        <h3 class="font-body-lg font-semibold text-on-surface">Promo Spesial di Cuci Cepat!</h3>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">1 hari yang lalu</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">Diskon 20% untuk semua layanan hari ini.</p>
                    <button class="mt-md text-primary font-label-md text-label-md hover:underline flex items-center gap-xs">
                        Klaim Voucher <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Item 4: Completion -->
        <div class="relative group bg-white p-lg rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20 transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] cursor-pointer unread-dot">
            <div class="flex gap-lg items-start">
                <div class="w-12 h-12 rounded-xl bg-primary-container/10 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-primary text-[28px]">delivery_dining</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-base">
                        <h3 class="font-body-lg font-semibold text-on-surface">Pesanan #KL-9877 Siap Diantar</h3>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Baru saja</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">Pesanan #KL-9877 Siap Diantar oleh Kurir FreshClean. Pastikan Anda berada di lokasi.</p>
                </div>
            </div>
        </div>

        <!-- Item 5: Done State 1 -->
        <div class="relative group bg-surface-container-lowest p-lg rounded-xl border border-outline-variant/20 transition-all hover:bg-white cursor-pointer opacity-80">
            <div class="flex gap-lg items-start">
                <div class="w-12 h-12 rounded-xl bg-on-secondary-fixed-variant/10 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-on-secondary-fixed-variant text-[28px]">check_circle</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-base">
                        <h3 class="font-body-lg font-semibold text-on-surface">Pesanan #KL-9855 Telah Selesai</h3>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">2 hari yang lalu</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        Pesanan Anda telah berhasil diantar dan diterima. Terima kasih telah mempercayakan cucian Anda kepada MataramWash!
                    </p>
                </div>
            </div>
        </div>

        <!-- Item 6: Done State 2 -->
        <div class="relative group bg-surface-container-lowest p-lg rounded-xl border border-outline-variant/20 transition-all hover:bg-white cursor-pointer opacity-80">
            <div class="flex gap-lg items-start">
                <div class="w-12 h-12 rounded-xl bg-on-secondary-fixed-variant/10 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-on-secondary-fixed-variant text-[28px]">store</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-base">
                        <h3 class="font-body-lg font-semibold text-on-surface">Cucian Siap Diambil</h3>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">3 hari yang lalu</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">Cucian Anda dari Bintang Laundry telah selesai dan siap diambil.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State Check (Hidden by default) -->
    <div class="hidden flex-col items-center justify-center py-24 text-center" id="empty-state">
        <div class="w-24 h-24 bg-surface-variant/30 rounded-full flex items-center justify-center mb-lg">
            <span class="material-symbols-outlined text-on-surface-variant text-5xl">notifications_off</span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Belum ada notifikasi</h2>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-sm mt-xs">Semua pembaruan tentang pesanan dan promo Anda akan muncul di sini.</p>
    </div>

    <!-- Action Button -->
    <div class="mt-xl flex justify-center">
        <button id="clear-all-btn" class="px-xl py-md bg-surface-container-high text-on-surface-variant rounded-full font-label-md text-label-md hover:bg-surface-variant transition-colors flex items-center gap-md">
            <span class="material-symbols-outlined">delete_sweep</span>
            Bersihkan Semua Notifikasi
        </button>
    </div>
</main>

<script>
    document.querySelectorAll('.group').forEach(item => {
        item.addEventListener('click', function() {
            // Mock reading interaction
            this.classList.remove('unread-dot');
            this.classList.replace('bg-white', 'bg-surface-container-lowest');
            this.style.opacity = '0.7';
        });
    });

    // Simple clear all mock
    const clearBtn = document.getElementById('clear-all-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (confirm('Hapus semua notifikasi?')) {
                const notificationsList = document.getElementById('notifications-list');
                const emptyState = document.getElementById('empty-state');
                
                if (notificationsList) notificationsList.style.display = 'none';
                if (emptyState) {
                    emptyState.classList.remove('hidden');
                    emptyState.style.display = 'flex';
                }
                clearBtn.parentElement.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>
