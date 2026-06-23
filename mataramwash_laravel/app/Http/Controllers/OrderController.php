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
            'biaya_antar_jemput' => 'nullable|integer'
        ]);

        $mitra_id = $validated['mitra_id'];
        $layanan = $validated['layanan'];
        $qty = $validated['qty'];
        $tarif_per_kg = $validated['tarif_per_kg'];
        $biaya_antar_jemput = $validated['biaya_antar_jemput'] ?? 1500;

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

        if (empty($serverKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Integrasi pembayaran (Midtrans) belum dikonfigurasi oleh administrator.'
            ]);
        }

        // Calculate Total Price
        $harga_layanan = round($qty * $tarif_per_kg);
        $total_harga = $harga_layanan + $biaya_antar_jemput;

        // Save order to database
        try {
            $order = Order::create([
                'mitra_id' => $mitra_id,
                'nama_pelanggan' => $user->nama,
                'layanan' => $layanan,
                'berat_atau_qty' => $qty,
                'tarif_per_kg' => $tarif_per_kg,
                'biaya_antar_jemput' => $biaya_antar_jemput,
                'total_harga' => $total_harga,
                'status_pembayaran' => 'pending',
                'status_transfer' => 'Proses',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ]);
        }

        // Generate unique order ID for Midtrans (MW-[database-id]-[timestamp])
        $midtrans_order_id = 'MW-' . $order->id . '-' . time();

        // Prepare Midtrans Payload
        $payload = [
            'transaction_details' => [
                'order_id' => $midtrans_order_id,
                'gross_amount' => $total_harga
            ],
            'item_details' => [
                [
                    'id' => 'SVC-' . substr(md5($layanan), 0, 5),
                    'price' => $tarif_per_kg,
                    'quantity' => $qty,
                    'name' => substr($layanan, 0, 50)
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
