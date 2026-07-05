<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\MitraLaundry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function process(Request $request)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login terlebih dahulu untuk melakukan pemesanan.'
            ]);
        }

        $user = Auth::user();

        // Validate Input
        $validated = $request->validate([
            'mitra_id' => 'required|integer',
            'layanan' => 'required|string',
            'qty' => 'required|numeric|min:0.01',
            'tarif_per_kg' => 'required|integer',
            'biaya_antar_jemput' => 'nullable|integer',
            'is_self_service' => 'nullable|boolean',
            'catatan' => 'nullable|string',
            'layanan_jemput' => 'nullable|integer',
            'layanan_antar' => 'nullable|integer',
            'alamat_antar_jemput' => 'nullable|string'
        ]);

        $mitra_id = $validated['mitra_id'];
        $layanan = $validated['layanan'];
        $qty = $validated['qty'];
        $tarif_per_kg = $validated['tarif_per_kg'];
        $biaya_antar_jemput = $validated['biaya_antar_jemput'] ?? 1500;
        
        $is_self_service = $validated['is_self_service'] ?? false;
        $catatan = $validated['catatan'] ?? null;
        $layanan_jemput = $validated['layanan_jemput'] ?? 0;
        $layanan_antar = $validated['layanan_antar'] ?? 0;
        $alamat_antar_jemput = $validated['alamat_antar_jemput'] ?? null;

        // Check if Midtrans Config is loaded
        $serverKey = config('services.midtrans.server_key');
        $environment = config('services.midtrans.environment', 'sandbox');

        if (empty($serverKey)) {
            // Attempt to load from JSON settings_config.json if config is empty
            $configFile = base_path('../admin/settings_config.json');
            if (file_exists($configFile)) {
                $loadedConfig = json_decode(file_get_contents($configFile), true);
                $serverKey = $loadedConfig['midtrans_server_key'] ?? '';
                $environment = $loadedConfig['midtrans_environment'] ?? 'sandbox';
            }
        }

        if ($is_self_service && empty($serverKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Integrasi pembayaran (Midtrans) belum dikonfigurasi oleh administrator.'
            ]);
        }

        // Calculate Total Price
        $estimasi_berat = $is_self_service ? 0.00 : $qty;
        $berat_atau_qty = $is_self_service ? $qty : 0.00;

        $harga_layanan = round(($is_self_service ? $berat_atau_qty : $estimasi_berat) * $tarif_per_kg);
        $total_harga = $harga_layanan + $biaya_antar_jemput;

        $status_order = $is_self_service ? 'Diproses' : 'Menunggu Penjemputan';

        // Save order to database
        try {
            $order = Order::create([
                'mitra_id' => $mitra_id,
                'nama_pelanggan' => $user->nama,
                'layanan' => $layanan,
                'berat_atau_qty' => $berat_atau_qty,
                'estimasi_berat' => $estimasi_berat,
                'tarif_per_kg' => $tarif_per_kg,
                'biaya_antar_jemput' => $biaya_antar_jemput,
                'total_harga' => $total_harga,
                'status_pembayaran' => 'pending',
                'status_transfer' => 'Proses',
                'status_order' => $status_order,
                'alamat_antar_jemput' => $alamat_antar_jemput,
                'layanan_jemput' => $layanan_jemput,
                'layanan_antar' => $layanan_antar,
                'catatan' => $catatan,
            ]);
            
            // Trigger WA notification to partner for regular order (weigh first / COD)
            try {
                require_once base_path('../wa_helper.php');
                notify_mitra_new_order($order->id, \DB::getPdo());
            } catch (\Exception $wa_ex) {
                \Log::error('WA Notification failed: ' . $wa_ex->getMessage());
            }
            
            if (!$is_self_service) {
                return response()->json([
                    'status' => 'success',
                    'flow' => 'timbang_dulu',
                    'order_id' => $order->id
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ]);
        }

        // Generate unique order ID for Midtrans (MW-[database-id]-[timestamp])
        $midtrans_order_id = 'MW-' . $order->id . '-' . time();

        $subtotal_layanan = $total_harga - $biaya_antar_jemput;

        // Prepare Midtrans Payload
        $payload = [
            'transaction_details' => [
                'order_id' => $midtrans_order_id,
                'gross_amount' => $total_harga
            ],
            'item_details' => [
                [
                    'id' => 'SVC-' . substr(md5($layanan), 0, 5),
                    'price' => $subtotal_layanan,
                    'quantity' => 1,
                    'name' => substr($layanan . ' (' . $qty . ' Mesin)', 0, 50)
                ],
                [
                    'id' => 'SHIPPING-FLAT',
                    'price' => $biaya_antar_jemput,
                    'quantity' => 1,
                    'name' => 'Biaya Antar-Jemput'
                ]
            ],
            'customer_details' => [
                'first_name' => $user->nama,
                'email' => $user->email,
                'phone' => $user->no_telp ?? ''
            ]
        ];

        // Midtrans Snap API Endpoint
        $endpoint = ($environment === 'production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $auth_header = 'Basic ' . base64_encode($serverKey . ':');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $auth_header,
            ])
            ->withoutVerifying() // handle SSL for local dev
            ->post($endpoint, $payload);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json([
                    'status' => 'success',
                    'token' => $result['token'],
                    'redirect_url' => $result['redirect_url'],
                    'order_id' => $order->id
                ]);
            } else {
                $result = $response->json();
                $error_msg = $result['error_messages'][0] ?? 'Gagal membuat invoice pembayaran.';
                return response()->json([
                    'status' => 'error',
                    'message' => 'Midtrans Error: ' . $error_msg,
                    'debug_http_code' => $response->status()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Koneksi ke Midtrans gagal: ' . $e->getMessage()
            ]);
        }
    }
}
