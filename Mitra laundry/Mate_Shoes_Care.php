<?php
$nama_mitra = "Mate Shoes Care";
$jam_operasional_html = '
<div class="flex justify-between p-xs hover:bg-surface-container/30 rounded-lg transition-colors">
    <span class="text-on-surface-variant">Senin - Minggu</span>
    <span class="font-bold text-on-surface">09:00 - 21:00</span>
</div>';

$custom_delivery_label = "Antar-Jemput";
$custom_delivery_advice = "Gratis ongkir < 2km";

$custom_tabs = [
    'kiloan' => 'Cuci Sepatu',
    'satuan' => 'Perawatan Khusus',
    'keunggulan' => 'Keunggulan'
];

$custom_grids_html = '
<!-- Grid 1: Cuci Sepatu -->
<div id="grid-kiloan" class="grid-content grid grid-cols-1 md:grid-cols-2 gap-md">
    <!-- Fast Clean -->
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
    <!-- Deep Clean -->
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
    <!-- Premium Deep Clean -->
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
    <!-- Unyellowing -->
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
    <!-- Repaint -->
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
    <!-- Leather Care -->
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
                <span><strong>Antar-Jemput Gratis (Sekitar Kekalik):</strong> Cukup pesan dari kosan, kami jemput dan antarkan kembali sepatu bersih Anda tanpa tambahan biaya ongkir untuk radius 2 km.</span>
            </li>
        </ul>
    </div>
</div>
';

include 'detail_template.php';
?>
