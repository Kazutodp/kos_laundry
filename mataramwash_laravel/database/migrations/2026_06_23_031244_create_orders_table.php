<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('mitra_laundry')->onDelete('cascade');
            $table->string('nama_pelanggan', 100);
            $table->string('layanan', 100);
            $table->decimal('berat_atau_qty', 5, 2)->default(0);
            $table->decimal('estimasi_berat', 5, 2)->nullable();
            $table->integer('tarif_per_kg');
            $table->integer('biaya_antar_jemput')->default(1500);
            $table->integer('total_harga');
            $table->string('foto_timbangan', 255)->nullable();
            $table->string('status_pembayaran', 20)->default('pending')->index();
            $table->string('status_transfer', 20)->default('Belum Selesai');
            $table->string('status_order', 50)->default('Menunggu Penjemputan');
            $table->text('alamat_antar_jemput')->nullable();
            $table->boolean('layanan_jemput')->default(true);
            $table->boolean('layanan_antar')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
