<?php
$nama_mitra = 'Neko Laundry';
$jam_operasional_html = '<div class="w-full space-y-1">
    <div class="flex py-[2px] text-sm">
        <span class="text-on-surface-variant w-28 shrink-0 text-left">Senin - Sabtu 07</span>
        <span class="font-bold text-on-surface flex-1 text-left">00 - 21:00</span>
    </div>
    <div class="flex py-[2px] text-sm">
        <span class="text-on-surface-variant w-28 shrink-0 text-left">Minggu 08</span>
        <span class="font-bold text-on-surface flex-1 text-left">00 - 19</span>
    </div>
</div>';

// Custom pricing overrides matching the database base price
$custom_harga_lipat_reguler = 3000;
$custom_harga_pengeringan = 6000;
$custom_harga_setrika_reguler = 13000;
$custom_harga_setrika_saja = 7000;

// Custom Satuan pricing overrides
$custom_harga_satuan_jaket = 15000;
$custom_harga_satuan_selimut = 20000;
$custom_harga_satuan_bed_cover = 30000;

include 'detail_template.php';
?>
