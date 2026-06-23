<?php

namespace App\Http\Controllers;

use App\Models\MitraLaundry;
use App\Models\Order;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the landing page with laundry outlets.
     */
    public function index()
    {
        $mitra = MitraLaundry::where('status_buka', 1)->orderBy('rating', 'desc')->take(8)->get();
        
        // Ambil semua mitra untuk autocomplete search
        $all_mitra = MitraLaundry::orderBy('rating', 'desc')->get();
        
        return view('welcome', compact('mitra', 'all_mitra'));
    }

    /**
     * Show details of a specific laundry outlet.
     */
    public function show($id)
    {
        $mitra = MitraLaundry::findOrFail($id);
        
        // Load configuration (if settings file exists)
        $configFile = base_path('../admin/settings_config.json');
        $config = [
            'midtrans_environment' => 'sandbox',
            'midtrans_client_key' => ''
        ];

        if (file_exists($configFile)) {
            $loadedConfig = json_decode(file_get_contents($configFile), true);
            if ($loadedConfig) {
                $config = array_merge($config, $loadedConfig);
            }
        }

        // Default configurations
        $is_self_service = (strpos(strtolower($mitra->nama_mitra), 'washtra') !== false);
        $delivery_label = $is_self_service ? 'Layanan Mandiri' : 'Jemput-Antar';
        $delivery_advice = $is_self_service ? 'Cuci Mandiri di Toko' : 'Biaya antar-jemput Rp 1.500';
        
        $harga_per_kg = $mitra->harga_per_kg;
        $pricing = [
            'lipat_reguler' => $harga_per_kg,
            'setrika_reguler' => $harga_per_kg + 2000,
            'setrika_saja' => max(3000, $harga_per_kg - 2000),
            'express_lipat' => $harga_per_kg + 4000,
            'express_setrika' => $harga_per_kg + 6000,
            'pengeringan' => null,
            'satuan_jaket' => 15000,
            'satuan_selimut' => 20000,
            'satuan_bed_cover' => 30000
        ];

        $custom_tabs = null;
        $custom_grids_html = null;

        // ID 7: LAUNDRY LOMBOK (Specific pricing overrides)
        if ($id == 7) {
            $pricing['lipat_reguler'] = 7000;
            $pricing['pengeringan'] = 6000;
            $pricing['setrika_reguler'] = 13000;
            $pricing['setrika_saja'] = 7000;
            $pricing['express_lipat'] = 7000 + 4000;
            $pricing['express_setrika'] = 7000 + 6000;
        }

        // ID 8: MAULaundry Mataram (Custom Tabs and Grids)
        if ($id == 8) {
            $delivery_label = "Jemput-Antar";
            $delivery_advice = "Biaya antar-jemput Rp 1.500";
            
            $custom_tabs = [
                'self' => 'Self Service',
                'kiloan' => 'Kiloan (Drop Off)',
                'satuan' => 'Cuci Satuan',
                'keunggulan' => 'Keunggulan'
            ];

            $custom_grids_html = '
            <!-- Grid 1: Self Service -->
            <div id="grid-self" class="grid-content grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Kering (Wash & Dry)</h3>
                            <span class="text-primary font-bold text-lg">Rp 25.000/7kg</span>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Mencuci dan mengeringkan pakaian secara mandiri dengan kapasitas mesin hingga 7kg. Selesai cepat ± 90 menit.</p>
                    </div>
                    <button onclick="openOrderModal(\'Self Service Wash & Dry (7kg)\', 25000, \'load\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Basah (Wash Only)</h3>
                            <span class="text-primary font-bold text-lg">Rp 10.000/7kg</span>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Hanya layanan mencuci tanpa pengeringan dengan mesin komersil kapasitas 7kg.</p>
                    </div>
                    <button onclick="openOrderModal(\'Self Service Wash Only (7kg)\', 10000, \'load\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <h3 class="font-headline-md text-[20px] text-on-surface">Pengeringan Reguler</h3>
                            <span class="text-primary font-bold text-lg">Rp 15.000/50mnt</span>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Layanan mesin dryer komersil selama 50 menit, menjamin cucian basah Anda kering total 100%.</p>
                    </div>
                    <button onclick="openOrderModal(\'Self Service Dryer (50 menit)\', 15000, \'load\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <h3 class="font-headline-md text-[20px] text-on-surface">Pengeringan Cepat</h3>
                            <span class="text-primary font-bold text-lg">Rp 3.000/10mnt</span>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Layanan mesin dryer komersil durasi pendek selama 10 menit untuk sedikit pakaian atau melengkapi pengeringan.</p>
                    </div>
                    <button onclick="openOrderModal(\'Self Service Dryer (10 menit)\', 3000, \'load\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Grid 2: Kiloan Drop Off -->
            <div id="grid-kiloan" class="grid-content hidden grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between md:col-span-2">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <div>
                                <h3 class="font-headline-md text-[20px] text-on-surface">Cuci Kering Lipat (Drop Off)</h3>
                                <p class="text-[12px] text-primary font-semibold mt-xs">Gratis Parfum & Detergen 50ml pertama</p>
                            </div>
                            <div class="text-right">
                                <span class="text-primary font-bold text-lg block">Rp 34.000/7kg</span>
                                <span class="text-label-sm text-on-surface-variant text-[12px]">+ Rp 4.850/kg selanjutnya</span>
                            </div>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Tinggal antar cucian kotor Anda (atau request layanan antar-jemput), staf kami yang akan memproses pencucian, pengeringan, dan pelipatan rapi. Hemat waktu dan tenaga!</p>
                    </div>
                    <button onclick="openOrderModal(\'Cuci Kering Lipat Drop Off (7kg)\', 34000, \'load\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Grid 3: Satuan -->
            <div id="grid-satuan" class="grid-content hidden grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant flex flex-col justify-between">
                    <div>
                        <h3 class="font-headline-md text-[18px] text-primary font-bold mb-md">Bed Cover Satuan</h3>
                        <div class="space-y-sm text-body-md text-on-surface-variant mb-lg">
                            <div class="flex justify-between"><span>Ukuran S (120x200)</span><strong>Rp 27.000</strong></div>
                            <div class="flex justify-between"><span>Ukuran M (160x200)</span><strong>Rp 30.000</strong></div>
                            <div class="flex justify-between"><span>Ukuran L (180x200)</span><strong>Rp 33.000</strong></div>
                            <div class="flex justify-between"><span>Ukuran XL (200x200)</span><strong>Rp 36.000</strong></div>
                        </div>
                    </div>
                    <button onclick="openOrderModal(\'Cuci Satuan Bed Cover\', 27000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant flex flex-col justify-between">
                    <div>
                        <h3 class="font-headline-md text-[18px] text-primary font-bold mb-md">Selimut & Sprei</h3>
                        <div class="space-y-sm text-body-md text-on-surface-variant mb-lg">
                            <div class="flex justify-between"><span>Selimut Tipis</span><strong>Rp 25.000</strong></div>
                            <div class="flex justify-between"><span>Selimut Sedang</span><strong>Rp 27.000</strong></div>
                            <div class="flex justify-between"><span>Selimut Tebal</span><strong>Rp 29.000</strong></div>
                            <div class="flex justify-between"><span>Sprei Set (Sprei + Bantal Guling)</span><strong>Rp 28.000</strong></div>
                            <div class="flex justify-between"><span>Sprei Non-set</span><strong>Rp 25.000</strong></div>
                        </div>
                    </div>
                    <button onclick="openOrderModal(\'Cuci Satuan Selimut/Sprei\', 25000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant flex flex-col justify-between md:col-span-2">
                    <div>
                        <h3 class="font-headline-md text-[18px] text-primary font-bold mb-md">Pakaian Satuan Premium</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-lg gap-y-sm text-body-md text-on-surface-variant mb-lg">
                            <div class="flex justify-between border-b border-outline-variant/30 pb-xs"><span>Jas / Almamater</span><strong>Rp 25.000</strong></div>
                            <div class="flex justify-between border-b border-outline-variant/30 pb-xs"><span>Jaket / Blazer / Kemeja</span><strong>Rp 20.000</strong></div>
                            <div class="flex justify-between border-b border-outline-variant/30 pb-xs"><span>Dress Pesta</span><strong>Rp 25.000</strong></div>
                            <div class="flex justify-between border-b border-outline-variant/30 pb-xs"><span>Kaos / Celana per pcs</span><strong>Rp 15.000</strong></div>
                        </div>
                    </div>
                    <button onclick="openOrderModal(\'Cuci Satuan Premium\', 15000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Grid 4: Keunggulan -->
            <div id="grid-keunggulan" class="grid-content hidden space-y-md">
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant shadow-sm space-y-sm">
                    <h3 class="font-headline-md text-[20px] text-primary font-bold">Kenapa Memilih MAULaundry?</h3>
                    <ul class="space-y-sm text-body-md text-on-surface-variant">
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Smart Laundry Express 90 Menit:</strong> Proses pencucian dan pengeringan super cepat menggunakan mesin komersil modern.</span>
                        </li>
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Opsi Fleksibel (Self Service / Drop Off):</strong> Bisa cuci mandiri lebih hemat atau titipkan pakaian Anda agar staf kami yang memproses.</span>
                        </li>
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Layanan Antar Jemput Murah:</strong> Nikmati kemudahan antar jemput cucian dengan tarif flat Rp 1.500 saja.</span>
                        </li>
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Hasil Terjamin Maksimal:</strong> Pakaian dijamin Bersih, Wangi, Rapi, wangi parfum tahan lama dan detergen 50ml pertama gratis.</span>
                        </li>
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Perawatan Sesuai Jenis Pakaian:</strong> Sangat cocok untuk baju harian, sprei & selimut tebal, baju kerja, hingga pakaian berbahan lembut (delicate clothes).</span>
                        </li>
                    </ul>
                </div>
            </div>
            ';
        }

        // ID 9: Mate Shoes Care (Custom Tabs and Grids for Shoe Care)
        if ($id == 9) {
            $delivery_label = "Antar-Jemput";
            $delivery_advice = "Biaya antar-jemput Rp 1.500";
            
            $custom_tabs = [
                'kiloan' => 'Cuci Sepatu',
                'satuan' => 'Perawatan Khusus',
                'keunggulan' => 'Keunggulan'
            ];

            $custom_grids_html = '
            <!-- Grid 1: Cuci Sepatu -->
            <div id="grid-kiloan" class="grid-content grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <h3 class="font-headline-md text-[20px] text-on-surface">Fast Clean</h3>
                            <span class="text-primary font-bold text-lg">Rp 20.000/pasang</span>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Pembersihan cepat khusus bagian luar sepatu (midsole & outsole) secara manual. Selesai dalam 30-45 menit. Cocok untuk pembersihan instan harian.</p>
                    </div>
                    <button onclick="openOrderModal(\'Fast Clean Sepatu\', 20000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <h3 class="font-headline-md text-[20px] text-on-surface">Deep Clean</h3>
                            <span class="text-primary font-bold text-lg">Rp 30.000/pasang</span>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Pembersihan menyeluruh luar dan dalam meliputi outsole, midsole, upper, insole, hingga tali sepatu. Menghilangkan kotoran membandel dan bau tidak sedap.</p>
                    </div>
                    <button onclick="openOrderModal(\'Deep Clean Sepatu\', 30000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant hover:shadow-md transition-shadow flex flex-col justify-between md:col-span-2">
                    <div>
                        <div class="flex justify-between items-start mb-sm">
                            <h3 class="font-headline-md text-[20px] text-on-surface">Premium Deep Clean (Special Materials)</h3>
                            <span class="text-primary font-bold text-lg">Rp 45.000/pasang</span>
                        </div>
                        <p class="text-on-surface-variant text-body-md mb-lg">Pembersihan mendalam khusus untuk sepatu berbahan sensitif dan mewah seperti suede, nubuck, genuine leather (kulit asli), canvas halus, atau knit premium. Menggunakan bahan pembersih khusus (premium cleaner) dan sikat bulu kuda halus agar tekstur/warna sepatu tidak rusak.</p>
                    </div>
                    <button onclick="openOrderModal(\'Premium Deep Clean Sepatu\', 45000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Grid 2: Perawatan Khusus -->
            <div id="grid-satuan" class="grid-content hidden grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant flex flex-col justify-between">
                    <div>
                        <h3 class="font-headline-md text-[18px] text-primary font-bold mb-md">Unyellowing (Midsole Treatment)</h3>
                        <div class="space-y-sm text-body-md text-on-surface-variant mb-lg">
                            <p class="text-on-surface-variant text-body-md mb-md">Treatment khusus menggunakan bahan kimia aman untuk menghilangkan noda kuning/oksidasi pada sol karet (midsole) sepatu Anda. Mengembalikan warna putih bersih semula.</p>
                            <div class="flex justify-between border-t border-outline-variant/30 pt-xs"><span>Biaya Treatment</span><strong>Rp 50.000/pasang</strong></div>
                        </div>
                    </div>
                    <button onclick="openOrderModal(\'Unyellowing Sepatu\', 50000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant flex flex-col justify-between">
                    <div>
                        <h3 class="font-headline-md text-[18px] text-primary font-bold mb-md">Repaint (Pewarnaan Ulang)</h3>
                        <div class="space-y-sm text-body-md text-on-surface-variant mb-lg">
                            <p class="text-on-surface-variant text-body-md mb-md">Pengecatan ulang sepatu canvas, suede, atau leather yang warnanya sudah pudar agar kembali pekat dan terlihat segar kembali seperti baru. Menggunakan cat premium khusus sepatu.</p>
                            <div class="flex justify-between border-t border-outline-variant/30 pt-xs"><span>Biaya Treatment</span><strong>Rp 75.000/pasang</strong></div>
                        </div>
                    </div>
                    <button onclick="openOrderModal(\'Repaint Sepatu\', 75000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant flex flex-col justify-between md:col-span-2">
                    <div>
                        <h3 class="font-headline-md text-[18px] text-primary font-bold mb-md">Leather Care & Conditioning</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-lg gap-y-sm text-body-md text-on-surface-variant mb-lg">
                            <p class="text-on-surface-variant text-body-md md:col-span-2">Perawatan khusus sepatu berbahan kulit asli (leather) maupun sintetis. Meliputi deep cleaning luar-dalam, pemberian pelembab leather conditioner, dan semir premium untuk mengembalikan kelenturan kulit agar tidak pecah-pecah.</p>
                            <div class="flex justify-between border-t border-outline-variant/30 pt-xs md:col-span-2"><span>Biaya Treatment</span><strong>Rp 40.000/pasang</strong></div>
                        </div>
                    </div>
                    <button onclick="openOrderModal(\'Leather Care & Conditioning\', 40000, \'pcs\')" class="w-full bg-primary text-on-primary py-sm rounded-xl font-bold flex items-center justify-center gap-sm active:scale-[0.98] transition-transform">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span> Pesan Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Grid 3: Keunggulan -->
            <div id="grid-keunggulan" class="grid-content hidden space-y-md">
                <div class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant shadow-sm space-y-sm">
                    <h3 class="font-headline-md text-[20px] text-primary font-bold">Kenapa Memilih Mate Shoes Care?</h3>
                    <ul class="space-y-sm text-body-md text-on-surface-variant">
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Peralatan & Cairan Pembersih Khusus:</strong> Kami menggunakan sikat bulu kuda halus (horsehair brush) untuk bahan sensitif, sikat nilon untuk sol, dan cleaner khusus sepatu ramah lingkungan serta anti-bakteri.</span>
                        </li>
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Shoes Care Specialist:</strong> Sepatu dikerjakan manual secara detail oleh spesialis perawatan sepatu untuk memastikan keamanan lem dan ketahanan bahan sepatu Anda.</span>
                        </li>
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Garansi Bersih Maksimal:</strong> Kami berkomitmen memberikan kualitas terbaik. Jika Anda merasa kurang puas dengan kebersihan hasil cucian, kami bersedia mencuci ulang gratis!</span>
                        </li>
                        <li class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-secondary mt-[2px]">check_circle</span>
                            <span><strong>Layanan Antar-Jemput Murah:</strong> Cukup pesan dari kosan, kami jemput dan antarkan kembali sepatu bersih Anda dengan biaya ongkir flat Rp 1.500 saja.</span>
                        </li>
                    </ul>
                </div>
            </div>
            ';
        }

        return view('mitra.detail', compact(
            'mitra', 
            'config', 
            'is_self_service', 
            'delivery_label', 
            'delivery_advice', 
            'pricing', 
            'custom_tabs', 
            'custom_grids_html'
        ));
    }

    /**
     * Show payment success page.
     */
    public function success(Request $request)
    {
        $orderId = intval($request->query('order_id', 0));
        $reference = $request->query('reference', '');

        if ($orderId <= 0) {
            abort(404, 'ID Pesanan tidak valid.');
        }

        $order = Order::with('mitra')->findOrFail($orderId);

        return view('pembayaran_sukses', compact('order', 'reference'));
    }
}
