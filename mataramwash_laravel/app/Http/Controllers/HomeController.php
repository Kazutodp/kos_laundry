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
        $all_mitra = MitraLaundry::orderBy('rating', 'desc')->get()->map(function($m) {
            return [
                'nama' => $m->nama_mitra,
                'rating' => $m->rating,
                'alamat' => $m->alamat,
                'url' => route('mitra.show', $m->id),
                'tipe' => $m->icon_type
            ];
        });
        
        return view('welcome', compact('mitra', 'all_mitra'));
    }

    /**
     * Show details of a specific laundry outlet.
     */
    public function show($id)
    {
        $mitra = MitraLaundry::with('layanan')->findOrFail($id);
        
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
        
        // Calculate dynamic opening status based on WITA timezone (Asia/Makassar)
        date_default_timezone_set('Asia/Makassar');
        $currentTime = date('H:i');
        $isOpenNow = false;
        $jamBuka = $mitra->jam_buka ?? '08:00 - 21:00';
        
        if (strpos(strtolower($jamBuka), '24 hours') !== false || strpos(strtolower($jamBuka), '24 jam') !== false) {
            $isOpenNow = true;
        } elseif (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $jamBuka, $matches)) {
            $startTime = $matches[1];
            $endTime = $matches[2];
            if ($startTime <= $endTime) {
                $isOpenNow = ($currentTime >= $startTime && $currentTime <= $endTime);
            } else {
                $isOpenNow = ($currentTime >= $startTime || $currentTime <= $endTime);
            }
        } elseif (preg_match('/until\s*(\d{1,2}:\d{2})/i', $jamBuka, $matches)) {
            $startTime = '07:00';
            $endTime = $matches[1];
            $isOpenNow = ($currentTime >= $startTime && $currentTime <= $endTime);
        }
        $status_buka = ($mitra->status_buka == 1 && $isOpenNow);
        
        // Determine reason for being closed
        $tutup_reason = '';
        if (!$status_buka) {
            $tutup_reason = ($mitra->status_buka == 0) ? 'Dinonaktifkan oleh pengelola' : 'Di luar jam operasional';
        }
        
        return view('mitra.detail', compact(
            'mitra', 
            'config', 
            'is_self_service', 
            'delivery_label', 
            'delivery_advice', 
            'status_buka',
            'tutup_reason'
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

        // Fallback for localhost testing: update status when successfully redirected
        if ($order->status_pembayaran === 'pending') {
            $order->update([
                'status_pembayaran' => 'success',
                'status_order' => $order->status_order === 'Menunggu Pembayaran' ? 'Diproses' : $order->status_order
            ]);
        }

        return view('pembayaran_sukses', compact('order', 'reference'));
    }
}
