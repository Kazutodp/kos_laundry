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
            $table->decimal('berat_atau_qty', 5, 2);
            $table->integer('tarif_per_kg');
            $table->integer('biaya_antar_jemput')->default(1500);
            $table->integer('total_harga');
            $table->string('status_pembayaran', 20)->default('pending')->index(); // Changed default to pending for payment gateway integration
            $table->string('status_transfer', 20)->default('Belum Selesai');
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
