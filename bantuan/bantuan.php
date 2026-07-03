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
    <title>Pusat Bantuan - MataramWash</title>
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
                <img alt="MataramWash Logo" class="h-10 w-10 object-contain" src="../Logo_MataramWash.png?v=3">
                <span class="">MataramWash</span>
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
                        <a href="../user/riwayat_pesanan.php" class="flex items-center gap-xs px-md py-sm text-body-md text-on-surface hover:bg-surface-container transition-colors">
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
                Cari jawaban untuk kendala Anda atau temukan panduan penggunaan fitur MataramWash.
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
                                <span>Bagaimana jika saya lupa kata sandi akun MataramWash?</span>
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
                                <span>Berapa biaya untuk layanan antar-jemput pakaian?</span>
                                <span class="material-symbols-outlined text-outline transition-transform duration-300" id="faq-icon-3">expand_more</span>
                            </button>
                            <div class="accordion-content max-h-0 overflow-hidden" id="faq-content-3">
                                <p class="p-lg pt-0 text-xs lg:text-sm text-on-surface-variant leading-relaxed">
                                    Layanan antar-jemput pakaian dikenakan tarif flat terjangkau sebesar Rp 1.500 saja per pesanan untuk memudahkan Anda tanpa perlu keluar kosan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Category: Kemitraan -->
                    <div class="space-y-sm pt-4" id="faq-mitra">
                        <h3 class="font-bold text-sm text-on-surface-variant tracking-wider uppercase">Kemitraan Laundry</h3>

                        <div class="border border-outline-variant/60 rounded-xl overflow-hidden bg-surface transition-all faq-box">
                            <button class="w-full flex justify-between items-center p-md lg:p-lg text-left font-bold text-on-surface text-sm lg:text-base hover:bg-surface-container-high/40 transition-colors" onclick="toggleFaq(4)">
                                <span>Bagaimana cara mendaftarkan laundry saya sebagai mitra MataramWash?</span>
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
                        Kami menjamin kerahasiaan data pribadi, kata sandi, dan riwayat detail pemesanan Anda di platform MataramWash.
                    </p>
                </div>
            </div>
            
        </div>
    </section>

</main>

<!-- Footer -->
<footer class="w-full bg-slate-950 text-slate-400 border-t border-slate-900 mt-xl">
    <div class="max-w-7xl mx-auto px-gutter py-xl grid grid-cols-1 md:grid-cols-4 gap-xl">
        <!-- Brand Section -->
        <div class="space-y-md">
            <div class="flex items-center gap-xs">
                <img alt="MataramWash Logo" class="h-10 w-10 object-contain" src="../Logo_MataramWash.png?v=3">
                <span class="text-headline-sm font-headline-md font-bold text-white">MataramWash</span>
            </div>
            <p class="text-slate-400 font-body-md leading-relaxed">
                Freshness delivered to your doorstep. Solusi laundry cerdas dan praktis khusus mahasiswa & profesional di Mataram.
            </p>
            <div class="space-y-sm pt-xs text-label-sm">
                <div class="flex items-center space-x-xs">
                    <span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
                    <span class="text-slate-300">Mataram, Nusa Tenggara Barat</span>
                </div>
                <div class="flex items-center space-x-xs">
                    <span class="material-symbols-outlined text-[18px] text-primary">call</span>
                    <span class="text-slate-300">+62 823-4196-1954</span>
                </div>
                <div class="flex items-center space-x-xs">
                    <span class="material-symbols-outlined text-[18px] text-primary">mail</span>
                    <span class="text-slate-300">support@mataramwash.com</span>
                </div>
            </div>
        </div>

        <!-- Company Links -->
        <div class="space-y-md md:pl-lg">
            <h5 class="font-bold text-white font-label-md tracking-wider uppercase text-xs">Perusahaan</h5>
            <ul class="space-y-sm text-body-md">
                <li>
                    <a href="#" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Tentang Kami</a>
                </li>
                <li>
                    <a href="#" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Bantuan &amp; FAQ</a>
                </li>
                <li>
                    <a href="#" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Kontak Kami</a>
                </li>
                <li>
                    <a href="#" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Syarat &amp; Ketentuan</a>
                </li>
            </ul>
        </div>

        <!-- Service Links -->
        <div class="space-y-md">
            <h5 class="font-bold text-white font-label-md tracking-wider uppercase text-xs">Layanan Kami</h5>
            <ul class="space-y-sm text-body-md">
                <li>
                    <a href="../layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Laundry Kiloan</a>
                </li>
                <li>
                    <a href="../layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Laundry Satuan</a>
                </li>
                <li>
                    <a href="../layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Cuci Sepatu Premium</a>
                </li>
                <li>
                    <a href="../layanan/layanan.php" class="hover:text-primary transition-all duration-300 hover:translate-x-1 inline-block">Express Delivery</a>
                </li>
            </ul>
        </div>

        <!-- Follow & Payments -->
        <div class="space-y-md">
            <h5 class="font-bold text-white font-label-md tracking-wider uppercase text-xs">Ikuti Kami</h5>
            <div class="flex space-x-sm">
                <a class="w-9 h-9 rounded-full bg-slate-900 border border-slate-800 text-slate-300 hover:bg-primary hover:text-white hover:border-primary transition-all duration-300 flex items-center justify-center" href="#" aria-label="Instagram">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051C.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                </a>
                <a class="w-9 h-9 rounded-full bg-slate-900 border border-slate-800 text-slate-300 hover:bg-primary hover:text-white hover:border-primary transition-all duration-300 flex items-center justify-center" href="#" aria-label="TikTok">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.99-1.72-.08-.07-.17-.17-.25-.26v6.52c-.03 2.32-.83 4.67-2.61 6.13-1.89 1.56-4.52 2.1-6.91 1.62-2.73-.55-5.17-2.45-6.07-5.13-.99-2.94-.3-6.42 1.83-8.66 1.73-1.83 4.37-2.68 6.81-2.28v4.11c-1.12-.22-2.34-.05-3.32.54-.99.6-1.63 1.65-1.79 2.79-.27 1.93.99 3.88 2.89 4.26 1.43.29 2.99-.14 3.89-1.27.46-.57.69-1.29.69-2v-12.3c0-.02 0-.03-.01-.05z"/>
                    </svg>
                </a>
                <a class="w-9 h-9 rounded-full bg-slate-900 border border-slate-800 text-slate-300 hover:bg-primary hover:text-white hover:border-primary transition-all duration-300 flex items-center justify-center" href="#" aria-label="WhatsApp">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.248 8.477 3.517 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.66.986 3.278 1.488 5.339 1.49 5.485-.002 9.948-4.469 9.95-9.956.002-2.657-1.02-5.155-2.877-7.017C17.18 1.81 14.685.787 12.03.785 6.544.787 2.08 5.253 2.078 10.743c-.001 2.045.513 3.626 1.486 5.23L2.553 21.64l5.885-1.543-.791.757z"/>
                    </svg>
                </a>
            </div>
            <div class="space-y-xs pt-xs">
                <h6 class="text-white font-bold text-xs uppercase tracking-wider">Metode Pembayaran</h6>
                <div class="flex flex-wrap gap-xs">
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">MIDTRANS</span>
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">QRIS</span>
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">GOPAY</span>
                    <span class="px-2 py-1 text-[9px] font-bold rounded bg-slate-900 border border-slate-800 text-slate-400">OVO</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-slate-900 py-md text-xs text-slate-500 bg-slate-950">
        <div class="max-w-7xl mx-auto px-gutter flex flex-col md:flex-row justify-between items-center gap-xs">
            <span>&copy; 2026 MataramWash. Semua Hak Dilindungi.</span>
            <div class="flex space-x-md">
                <a href="#" class="hover:text-primary transition-colors">Kebijakan Privasi</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-primary transition-colors">Syarat &amp; Ketentuan</a>
            </div>
        </div>
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
