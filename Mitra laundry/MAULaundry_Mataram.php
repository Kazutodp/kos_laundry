<?php
$nama_mitra = "MAULaundry Mataram";
$jam_operasional_html = '
<div class="flex justify-between p-xs hover:bg-surface-container/30 rounded-lg transition-colors">
    <span class="text-on-surface-variant">Senin - Minggu</span>
    <span class="font-bold text-on-surface">08:00 - 22:00</span>
</div>';

$custom_delivery_label = "Jemput-Antar";
$custom_delivery_advice = "Gratis ongkir < 3km";

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
    <!-- Bed Cover -->
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
    <!-- Selimut & Sprei -->
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
    <!-- Pakaian Satuan -->
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
                <span><strong>Layanan Antar Jemput Gratis:</strong> Nikmati kemudahan antar jemput cucian GRATIS ongkir untuk radius 3 km dari outlet kami.</span>
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

include 'detail_template.php';
?>
