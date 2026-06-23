<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\GoogleAuthController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/mitra/{id}', [HomeController::class, 'show'])->name('mitra.show');

Route::post('/webhook/midtrans', [WebhookController::class, 'handle']);
Route::post('/login/google', [GoogleAuthController::class, 'login']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/orders/process', [OrderController::class, 'process'])->name('orders.process');
    Route::get('/pembayaran-sukses', [HomeController::class, 'success'])->name('pembayaran.sukses');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
