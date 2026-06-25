<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('nama', 100);
            $table->timestamp('created_at')->useCurrent();
        });

        // Seed default admin (username: admin, password: admin123)
        DB::table('admins')->insert([
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'nama' => 'Administrator'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
