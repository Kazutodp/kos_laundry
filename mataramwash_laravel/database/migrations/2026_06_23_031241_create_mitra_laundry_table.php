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
        Schema::create('mitra_laundry', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mitra', 100);
            $table->string('foto_toko', 255)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('alamat');
            $table->string('no_telp', 20)->nullable();
            $table->decimal('rating', 2, 1)->default(0.0);
            $table->integer('harga_per_kg');
            $table->string('jam_buka', 50)->nullable();
            $table->boolean('status_buka')->default(true);
            $table->string('icon_type', 50)->default('kiloan');
            $table->boolean('is_rekomendasi')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_laundry');
    }
};
