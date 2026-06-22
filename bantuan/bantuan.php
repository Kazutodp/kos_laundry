<?php
// bantuan/bantuan.php
session_start();
require_once '../db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$dashboard_url = "../dashboard.php";
$login_url = "../login/login.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pusat Bantuan - KosanLaundry</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-surface-variant": "#424754",
                    "surface-container-low": "#f0f3ff",
                    "tertiary-container": "#a36700",
                    "on-background": "#151c27",
                    "surface-bright": "#f9f9ff",
                    "outline-variant": "#c2c6d6",
                    "surface-container-lowest": "#ffffff",
                    "secondary-container": "#6df5e1",
                    "outline": "#727785",
                    "on-tertiary-fixed": "#2a1700",
                    "inverse-surface": "#2a313d",
                    "on-primary-fixed-variant": "#004395",
                    "surface-container": "#e7eefe",
                    "primary": "#0058be",
                    "on-secondary": "#ffffff",
                    "on-primary-fixed": "#001a42",
                    "primary-container": "#2170e4",
                    "background": "#f9f9ff",
                    "on-tertiary": "#ffffff",
                    "surface-variant": "#dce2f3",
                    "secondary": "#006b5f",
                    "secondary-fixed": "#71f8e4",
                    "error-container": "#ffdad6",
                    "surface": "#f9f9ff",
                    "surface-dim": "#d3daea",
                    "on-surface": "#151c27",
                    "error": "#ba1a1a",
                    "inverse-on-surface": "#ebf1ff",
                    "on-error": "#ffffff",
                    "tertiary-fixed-dim": "#ffb95f",
                    "secondary-fixed-dim": "#4fdbc8",
                    "surface-container-highest": "#dce2f3",
                    "tertiary": "#825100",
                    "on-error-container": "#93000a",
                    "surface-tint": "#005ac2",
                    "on-primary-container": "#fefcff",
                    "on-tertiary-container": "#fffbff",
                    "on-secondary-container": "#006f64",
                    "on-secondary-fixed-variant": "#005048",
                    "surface-container-high": "#e2e8f8",
                    "on-primary": "#ffffff",
                    "inverse-primary": "#adc6ff",
                    "primary-fixed": "#d8e2ff",
                    "primary-fixed-dim": "#adc6ff",
                    "on-secondary-fixed": "#00201c",
                    "on-tertiary-fixed-variant": "#653e00",
                    "tertiary-fixed": "#ffddb8"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "xl": "32px",
                    "base": "4px",
                    "gutter": "16px",
                    "container-margin": "20px",
                    "xs": "8px",
                    "sm": "12px",
                    "md": "16px",
                    "lg": "24px"
            },
            "fontFamily": {
                    "label-md": ["Inter"],
                    "headline-md": ["Inter"],
                    "body-lg": ["Inter"],
                    "display-lg": ["Inter"],
                    "headline-lg": ["Inter"],
                    "label-sm": ["Inter"],
                    "headline-lg-mobile": ["Inter"],
                    "body-md": ["Inter"]
            },
            "fontSize": {
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
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
        .bento-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 88, 190, 0.08);
        }
        .accordion-content {
            transition: max-height 0.3s ease-out, padding 0.3s ease-out;
        }
        .guide-item {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md overflow-x-hidden">

<!-- TopNavBar -->
<nav class="sticky top-0 w-full z-50 bg-surface shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-gutter py-md flex justify-between items-center">
        <div class="flex items-center space-x-md lg:space-x-lg">
            <a class="flex items-center space-x-xs text-headline-md font-headline-md font-bold text-primary" href="../index.php">
                <img alt="KosanLaundry Logo" class="h-10 w-10 object-contain" src="../logo.png?v=3">
                <span class="">KosanLaundry</span>
            </a>
        </div>
        <div class="flex items-center space-x-md">
            <!-- Desktop Nav -->
            <div class="hidden md:flex space-x-lg items-center mr-lg">
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../index.php">Beranda</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../layanan/layanan.php">Layanan</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors font-label-md" href="../lokasi/locations.php">Lokasi</a>
                <a class="text-primary font-bold border-b-2 border-primary pb-1 font-label-md" href="#">Bantuan</a>
            </div>
            
            <?php if ($is_logged_in): ?>
                <!-- Profile Indicator with Hover Dropdown -->
                <div class="relative group" id="profile-dropdown-container">
                    <button class="flex items-center justify-center w-10 h-10 rounded-full border border-outline-variant focus:outline-none select-none overflow-hidden bg-primary text-on-primary font-bold text-body-lg shadow-sm hover:scale-105 transition-all">
                        <?php if (!empty($_SESSION['profile_pic'])): ?>
                            <img src="../<?= htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-xs w-48 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-xs z-50 transform origin-top-right scale-95 opacity-0 pointer-events-none group-hover:scale-100 group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200">
                        <a href="../user/edit_profile.php" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">edit</span>
                            <span>Edit Profil</span>
                        </a>
                        <a href="#" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">history</span>
                            <span>Riwayat Pesanan</span>
                        </a>
                        <a href="../user/notifikasi.php" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-outline">notifications</span>
                            <span>Notifikasi</span>
                        </a>
                        <div class="border-t border-outline-variant my-xs"></div>
                        <a href="../logout.php" class="flex items-center gap-xs px-md py-sm text-body-md text-error hover:bg-error-container/10 transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-error">logout</span>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center space-x-xs sm:space-x-sm">
                    <button onclick="window.location.href='<?= $login_url; ?>'" class="px-lg py-xs border-2 border-primary text-primary rounded-xl font-bold hover:bg-primary-fixed transition-all active:scale-95 duration-150 text-sm">Masuk</button>
                    <button onclick="window.location.href='../login/daftar.php'" class="px-lg py-xs bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-all active:scale-95 duration-150 text-sm shadow-sm">Daftar</button>
                </div>
            <?php endif; ?>
            <button class="md:hidden flex items-center" id="mobile-menu-btn">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
    
    <!-- Mobile Navigation Menu -->
    <div class="hidden md:hidden w-full bg-surface border-t border-outline-variant py-md px-gutter space-y-md" id="mobile-menu">
        <a class="block text-on-surface-variant hover:text-primary transition-colors font-label-md py-xs" href="../index.php">Beranda</a>
        <a class="block text-on-surface-variant hover:text-primary transition-colors font-label-md py-xs" href="../layanan/layanan.php">Layanan</a>
        <a class="block text-on-surface-variant hover:text-primary transition-colors font-label-md py-xs" href="../lokasi/locations.php">Lokasi</a>
        <a class="block text-primary font-bold font-label-md py-xs" href="#">Bantuan</a>
    </div>
</nav>

<main class="min-h-screen pb-20">

    <!-- Hero Search Section -->
    <section class="relative bg-gradient-to-br from-surface-container-low to-background py-20 px-container-margin overflow-hidden border-b border-outline-variant/25">
        <div class="max-w-3xl mx-auto text-center space-y-lg relative z-10">
            <div class="inline-flex items-center space-x-xs px-md py-xs bg-primary-container/10 border border-primary-container/20 rounded-full text-primary font-label-sm mx-auto">
                <span class="material-symbols-outlined text-[18px]">support_agent</span>
                <span>Pusat Layanan Bantuan</span>
            </div>
            <h1 class="text-display-lg text-primary font-display-lg leading-tight md:text-5xl">
                Ada yang Bisa Kami Bantu?
            </h1>
            <p class="text-body-md text-on-surface-variant max-w-xl mx-auto">
                Cari jawaban untuk kendala Anda atau temukan panduan penggunaan fitur KosanLaundry.
            </p>
            <!-- Large Help Search Bar -->
            <div class="relative max-w-xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400">search</span>
                </div>
                <input type="text" id="help-search-input" placeholder="Ketik kendala Anda (contoh: cara bayar, klaim baju)..." class="block w-full pl-12 pr-12 py-4 border border-outline-variant rounded-2xl bg-surface-container-lowest text-body-md placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-md" oninput="searchHelpTopics()">
                <!-- Reset search button -->
                <button id="clear-help-search" class="absolute inset-y-0 right-0 pr-4 flex items-center text-outline hover:text-primary hidden" onclick="clearHelpSearch()">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        </div>
        <!-- Decorative Circle Blurs -->
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-primary-fixed-dim/15 rounded-full blur-3xl opacity-55"></div>
        <div class="absolute -bottom-40 -right-40 w-80 h-80 bg-secondary-fixed-dim/15 rounded-full blur-3xl opacity-55"></div>
    </section>



    <section class="py-16 px-container-margin max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-3 gap-xl items-start">
            
            <!-- Left 2 Columns: Popular Guides & FAQs -->
            <div class="lg:col-span-2 space-y-xl">
                
                <!-- Popular Guides -->
                <div class="space-y-md" id="popular-guides-section">
                    <h2 class="text-headline-lg font-headline-lg text-primary text-xl lg:text-2xl flex items-center gap-xs">
                        <span class="material-symbols-outlined">auto_stories</span>
                        <span>Panduan Populer</span>
                    </h2>
                    
                    <div class="space-y-sm" id="guides-list">
                        <!-- Guide 1 -->
                        <div class="guide-item p-md bg-surface-container-lowest border border-outline-variant/60 rounded-xl hover:shadow-sm">
                            <h4 class="font-bold text-on-surface text-sm lg:text-base">Cara Melakukan Pemesanan Laundry Pertama</h4>
                            <p class="text-xs lg:text-sm text-on-surface-variant mt-1 leading-relaxed">
                                Masuk ke dashboard akun Anda, klik 'Pesan', masukkan detail pakaian, tentukan alamat penjemputan, lalu pilih salah satu rekomendasi mitra terdekat.
                            </p>
                        </div>
                        <!-- Guide 2 -->
                        <div class="guide-item p-md bg-surface-container-lowest border border-outline-variant/60 rounded-xl hover:shadow-sm">
                            <h4 class="font-bold text-on-surface text-sm lg:text-base">Mekanisme Klaim Pakaian Rusak atau Hilang</h4>
                            <p class="text-xs lg:text-sm text-on-surface-variant mt-1 leading-relaxed">
                                Kirim foto pakaian rusak dan nota transaksi ke CS WhatsApp dalam kurun waktu 1x24 jam sejak pakaian diantar untuk klaim asuransi dari mitra.
                            </p>
                        </div>
                        <!-- Guide 3 -->
                        <div class="guide-item p-md bg-surface-container-lowest border border-outline-variant/60 rounded-xl hover:shadow-sm">
                            <h4 class="font-bold text-on-surface text-sm lg:text-base">Panduan Pembayaran Menggunakan Qris</h4>
                            <p class="text-xs lg:text-sm text-on-surface-variant mt-1 leading-relaxed">
                                Setelah memesan, pilih opsi pembayaran QRIS di sistem. Scan kode QR yang tampil menggunakan aplikasi e-wallet (OVO, GoPay, ShopeePay) atau M-Banking Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Accordion FAQs categorized -->
                <div class="space-y-lg pt-6" id="faq-section">
                    <h2 class="text-headline-lg font-headline-lg text-primary text-xl lg:text-2xl flex items-center gap-xs">
                        <span class="material-symbols-outlined">help_center</span>
                        <span>Pertanyaan yang Sering Diajukan (FAQ)</span>
                    </h2>

                    <!-- FAQ Category: Pesanan -->
                    <div class="space-y-sm" id="faq-pesanan">
                        <h3 class="font-bold text-sm text-on-surface-variant tracking-wider uppercase">Pesanan &amp; Pembayaran</h3>
                        
                        <div class="border border-outline-variant/60 rounded-xl overflow-hidden bg-surface transition-all faq-box">
                            <button class="w-full flex justify-between items-center p-md lg:p-lg text-left font-bold text-on-surface text-sm lg:text-base hover:bg-surface-container-high/40 transition-colors" onclick="toggleFaq(0)">
                                <span>Bagaimana cara memantau status pengerjaan laundry saya?</span>
                                <span class="material-symbols-outlined text-outline transition-transform duration-300" id="faq-icon-0">expand_more</span>
                            </button>
                            <div class="accordion-content max-h-0 overflow-hidden" id="faq-content-0">
                                <p class="p-lg pt-0 text-xs lg:text-sm text-on-surface-variant leading-relaxed">
                                    Anda dapat masuk ke profil Anda dan membuka tab 'Riwayat Pesanan' untuk melihat update status secara real-time dari mitra (mulai dari Dijemput, Diproses, hingga Selesai &amp; Siap Diantar).
                                </p>
                            </div>
                        </div>

                        <div class="border border-outline-variant/60 rounded-xl overflow-hidden bg-surface transition-all faq-box">
                            <button class="w-full flex justify-between items-center p-md lg:p-lg text-left font-bold text-on-surface text-sm lg:text-base hover:bg-surface-container-high/40 transition-colors" onclick="toggleFaq(1)">
                                <span>Metode pembayaran apa saja yang didukung?</span>
                                <span class="material-symbols-outlined text-outline transition-transform duration-300" id="faq-icon-1">expand_more</span>
                            </button>
                            <div class="accordion-content max-h-0 overflow-hidden" id="faq-content-1">
                                <p class="p-lg pt-0 text-xs lg:text-sm text-on-surface-variant leading-relaxed">
                                    Kami mendukung pembayaran tunai (Cash on Delivery/COD) saat penjemputan pakaian, serta pembayaran non-tunai melalui QRIS (GoPay, OVO, ShopeePay, Dana, LinkAja) dan transfer bank virtual account.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Category: Akun -->
                    <div class="space-y-sm pt-4" id="faq-akun">
                        <h3 class="font-bold text-sm text-on-surface-variant tracking-wider uppercase">Akun &amp; Keamanan</h3>

                        <div class="border border-outline-variant/60 rounded-xl overflow-hidden bg-surface transition-all faq-box">
                            <button class="w-full flex justify-between items-center p-md lg:p-lg text-left font-bold text-on-surface text-sm lg:text-base hover:bg-surface-container-high/40 transition-colors" onclick="toggleFaq(2)">
                                <span>Bagaimana jika saya lupa kata sandi akun KosanLaundry?</span>
                                <span class="material-symbols-outlined text-outline transition-transform duration-300" id="faq-icon-2">expand_more</span>
                            </button>
                            <div class="accordion-content max-h-0 overflow-hidden" id="faq-content-2">
                                <p class="p-lg pt-0 text-xs lg:text-sm text-on-surface-variant leading-relaxed">
                                    Pada halaman Login, Anda dapat mengeklik link 'Lupa Password'. Masukkan email Anda yang terdaftar, dan kami akan mengirimkan instruksi beserta link tautan reset kata sandi baru ke email Anda.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Category: Antar-Jemput -->
                    <div class="space-y-sm pt-4" id="faq-pengiriman">
                        <h3 class="font-bold text-sm text-on-surface-variant tracking-wider uppercase">Antar-Jemput</h3>

                        <div class="border border-outline-variant/60 rounded-xl overflow-hidden bg-surface transition-all faq-box">
                            <button class="w-full flex justify-between items-center p-md lg:p-lg text-left font-bold text-on-surface text-sm lg:text-base hover:bg-surface-container-high/40 transition-colors" onclick="toggleFaq(3)">
                                <span>Berapa batas radius pengantaran untuk gratis ongkir?</span>
                                <span class="material-symbols-outlined text-outline transition-transform duration-300" id="faq-icon-3">expand_more</span>
                            </button>
                            <div class="accordion-content max-h-0 overflow-hidden" id="faq-content-3">
                                <p class="p-lg pt-0 text-xs lg:text-sm text-on-surface-variant leading-relaxed">
                                    Batas radius pengantaran gratis ongkos kirim adalah 3 km dari lokasi outlet mitra laundry yang Anda pilih. Di atas radius 3 km, tarif pengantaran akan dikenakan biaya tambahan per kilometer secara otomatis.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Category: Kemitraan -->
                    <div class="space-y-sm pt-4" id="faq-mitra">
                        <h3 class="font-bold text-sm text-on-surface-variant tracking-wider uppercase">Kemitraan Laundry</h3>

                        <div class="border border-outline-variant/60 rounded-xl overflow-hidden bg-surface transition-all faq-box">
                            <button class="w-full flex justify-between items-center p-md lg:p-lg text-left font-bold text-on-surface text-sm lg:text-base hover:bg-surface-container-high/40 transition-colors" onclick="toggleFaq(4)">
                                <span>Bagaimana cara mendaftarkan laundry saya sebagai mitra KosanLaundry?</span>
                                <span class="material-symbols-outlined text-outline transition-transform duration-300" id="faq-icon-4">expand_more</span>
                            </button>
                            <div class="accordion-content max-h-0 overflow-hidden" id="faq-content-4">
                                <p class="p-lg pt-0 text-xs lg:text-sm text-on-surface-variant leading-relaxed">
                                    Silakan kunjungi menu Kemitraan di kaki halaman utama, isi formulir data outlet laundry Anda (alamat, foto mesin, kontak), lalu staf kami akan memproses kunjungan survei fisik ke outlet Anda dalam 3 hari kerja.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty search result alert -->
                <div id="no-help-results" class="text-center py-12 bg-surface-container-lowest border border-outline-variant/60 rounded-2xl hidden">
                    <span class="material-symbols-outlined text-outline text-5xl mb-2">search_off</span>
                    <p class="text-on-surface-variant font-bold">Tidak ada panduan atau pertanyaan bantuan yang cocok.</p>
                </div>
            </div>

            <!-- Right Column: Contact Card / WhatsApp CS (Hubungi CS) -->
            <div class="space-y-lg lg:sticky lg:top-28">
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-xl shadow-sm space-y-md">
                    <div class="w-12 h-12 bg-[#25d366]/10 text-[#25d366] rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl">chat</span>
                    </div>
                    <h3 class="text-headline-md font-bold text-on-surface text-lg">Hubungi Customer Service</h3>
                    <p class="text-xs lg:text-sm text-on-surface-variant leading-relaxed">
                        Punya kendala mendesak atau butuh respon langsung mengenai cucian Anda? Tim CS kami siap merespon pertanyaan Anda melalui WhatsApp.
                    </p>
                    
                    <div class="pt-sm space-y-xs text-xs text-on-surface-variant">
                        <div class="flex items-center gap-xs">
                            <span class="material-symbols-outlined text-sm text-outline">schedule</span>
                            <span>Jam Operasional: 08:00 - 22:00</span>
                        </div>
                        <div class="flex items-center gap-xs">
                            <span class="material-symbols-outlined text-sm text-outline">verified</span>
                            <span>Respon Cepat: Rata-rata 10 menit</span>
                        </div>
                    </div>
                    
                    <a href="https://wa.me/628983887223" target="_blank" class="w-full py-sm bg-[#25d366] text-white rounded-xl font-bold hover:bg-[#20ba5a] hover:shadow-lg transition-all active:scale-95 duration-150 shadow-md text-center flex items-center justify-center gap-xs">
                        <!-- Custom WhatsApp SVG Icon -->
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 2.568 1.405 4.312 1.406 5.37 0 9.734-4.368 9.737-9.738.002-2.6-1.01-5.047-2.85-6.89C15.998 2.09 13.555 1.08 10.96 1.08c-5.377 0-9.743 4.369-9.745 9.741-.001 1.77.466 2.77 1.42 4.385l-.974 3.556 3.65-.958z"/>
                        </svg>
                        <span>Hubungi via WhatsApp</span>
                    </a>
                </div>

                <!-- Security Notice Card -->
                <div class="bg-primary/5 border border-primary/20 rounded-2xl p-lg space-y-sm text-center">
                    <span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
                    <h4 class="font-bold text-primary text-sm">Keamanan Transaksi Anda</h4>
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        Kami menjamin kerahasiaan data pribadi, kata sandi, dan riwayat detail pemesanan Anda di platform KosanLaundry.
                    </p>
                </div>
            </div>
            
        </div>
    </section>

</main>

<!-- Footer -->
<footer class="w-full py-xl px-gutter grid grid-cols-1 md:grid-cols-4 gap-lg bg-surface-container-highest">
    <div class="space-y-md">
        <div class="flex items-center gap-xs">
            <img alt="KosanLaundry Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
            <span class="text-headline-sm font-headline-md font-bold text-primary">KosanLaundry</span>
        </div>
        <p class="text-on-surface-variant font-body-md">Freshness delivered to your doorstep. Laundry solusi cerdas untuk hidup lebih produktif.</p>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Company</h5>
        <ul class="space-y-xs">
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Privacy Policy</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Terms of Service</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">Contact Us</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="#">FAQ</a></li>
        </ul>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Layanan</h5>
        <ul class="space-y-xs">
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="../layanan/layanan.php">Laundry Kiloan</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="../layanan/layanan.php">Laundry Satuan</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="../layanan/layanan.php">Cuci Sepatu</a></li>
            <li><a class="text-on-surface-variant hover:text-primary transition-colors font-label-sm" href="../layanan/layanan.php">Dry Cleaning</a></li>
        </ul>
    </div>
    <div class="space-y-md">
        <h5 class="font-bold text-on-surface font-label-md">Follow Us</h5>
        <div class="flex space-x-md">
            <a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all" href="#">
                <span class="material-symbols-outlined text-[20px]">share</span>
            </a>
            <a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all" href="#">
                <span class="material-symbols-outlined text-[20px]">public</span>
            </a>
        </div>
        <p class="text-label-sm text-on-surface-variant opacity-80 mt-lg">© 2026 KosanLaundry. Freshness delivered to your doorstep.</p>
    </div>
</footer>

<script>
    // Search Help Topics Logic
    function searchHelpTopics() {
        const query = document.getElementById('help-search-input').value.toLowerCase().trim();
        const clearBtn = document.getElementById('clear-help-search');
        
        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        const guideItems = document.querySelectorAll('.guide-item');
        const faqBoxes = document.querySelectorAll('.faq-box');
        const categoriesContainer = document.getElementById('categories-container');
        const popularGuidesSection = document.getElementById('popular-guides-section');
        const faqSection = document.getElementById('faq-section');
        const noResultsAlert = document.getElementById('no-help-results');

        let matchCount = 0;

        // 1. Search in guides
        guideItems.forEach(item => {
            const text = item.innerText.toLowerCase();
            if (text.includes(query)) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // 2. Search in FAQs
        faqBoxes.forEach(item => {
            const text = item.innerText.toLowerCase();
            if (text.includes(query)) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (query.length > 0) {
            // Hide category grid when searching to make it look clean
            if (categoriesContainer) categoriesContainer.classList.add('hidden');
        } else {
            if (categoriesContainer) categoriesContainer.classList.remove('hidden');
            // Show all items
            guideItems.forEach(i => i.style.display = 'block');
            faqBoxes.forEach(i => i.style.display = 'block');
            noResultsAlert.classList.add('hidden');
            return;
        }

        // Show/hide empty state message
        if (matchCount === 0) {
            noResultsAlert.classList.remove('hidden');
            popularGuidesSection.classList.add('hidden');
            faqSection.classList.add('hidden');
        } else {
            noResultsAlert.classList.add('hidden');
            popularGuidesSection.classList.remove('hidden');
            faqSection.classList.remove('hidden');
        }
    }

    function clearHelpSearch() {
        document.getElementById('help-search-input').value = '';
        searchHelpTopics();
    }

    // Scroll to specific section ID on page
    function scrollToSection(id) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // If it is an accordion and has a box, highlight it
            const faqBox = el.querySelector('.faq-box');
            if (faqBox) {
                faqBox.classList.add('ring-2', 'ring-primary/20', 'border-primary');
                setTimeout(() => {
                    faqBox.classList.remove('ring-2', 'ring-primary/20', 'border-primary');
                }, 2000);
            }
        }
    }

    // Toggle FAQ Accordion
    function toggleFaq(index) {
        const content = document.getElementById('faq-content-' + index);
        const icon = document.getElementById('faq-icon-' + index);
        
        if (content.style.maxHeight && content.style.maxHeight !== '0px') {
            content.style.maxHeight = '0px';
            content.style.paddingTop = '0px';
            content.style.paddingBottom = '0px';
            icon.style.transform = 'rotate(0deg)';
        } else {
            // Close other items
            document.querySelectorAll('.accordion-content').forEach((pane, idx) => {
                pane.style.maxHeight = '0px';
                pane.style.paddingTop = '0px';
                pane.style.paddingBottom = '0px';
                const otherIcon = document.getElementById('faq-icon-' + idx);
                if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
            });
            
            content.style.maxHeight = content.scrollHeight + 'px';
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // Mobile Navbar Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>
</body>
</html>
