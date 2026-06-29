<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Since database design uses name instead of user_id, we map by nama_pelanggan
        $orders = Order::with('mitra')
            ->where('nama_pelanggan', $user->nama)
            ->orderBy('created_at', 'desc')
            ->get();

        // Load Midtrans environment details for display config
        $configFile = base_path('../admin/settings_config.json');
        $clientKey = '';
        if (file_exists($configFile)) {
            $loadedConfig = json_decode(file_get_contents($configFile), true);
            $clientKey = $loadedConfig['midtrans_client_key'] ?? '';
        }

        return view('orders.index', compact('orders', 'clientKey'));
    }

    public function getSnapToken($id)
    {
        $user = Auth::user();
        $order = Order::where('id', $id)
            ->where('nama_pelanggan', $user->nama)
            ->where('status_pembayaran', 'pending')
            ->where('status_order', 'Menunggu Pembayaran')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan atau belum ditimbang oleh outlet.'
            ], 404);
        }

        // Get server key
        $serverKey = config('services.midtrans.server_key');
        $environment = config('services.midtrans.environment', 'sandbox');

        if (empty($serverKey)) {
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
                'message' => 'Integrasi pembayaran (Midtrans) belum dikonfigurasi.'
            ]);
        }

        $is_self = (strpos(strtolower($order->layanan), 'self') !== false || strpos(strtolower($order->nama_mitra), 'washtra') !== false);
        if (!$is_self && floatval($order->berat_atau_qty) <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan belum ditimbang oleh outlet laundry. Silakan hubungi admin/mitra untuk melakukan penimbangan.'
            ]);
        }

        $midtrans_order_id = 'MW-' . $order->id . '-' . time();
        $total_harga = intval($order->total_harga);
        $tarif_per_kg = intval($order->tarif_per_kg);
        $qty = floatval($order->berat_atau_qty);
        $biaya_antar_jemput = intval($order->biaya_antar_jemput);

        $subtotal_layanan = $total_harga - $biaya_antar_jemput;

        // Prepare Midtrans Payload
        $payload = [
            'transaction_details' => [
                'order_id' => $midtrans_order_id,
                'gross_amount' => $total_harga
            ],
            'item_details' => [
                [
                    'id' => 'SVC-' . substr(md5($order->layanan), 0, 5),
                    'price' => $subtotal_layanan,
                    'quantity' => 1,
                    'name' => substr($order->layanan . ($is_self ? ' (' . $qty . ' Mesin)' : ' (' . $qty . ' Kg)'), 0, 50)
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

        // API Endpoint
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
            ->withoutVerifying()
            ->post($endpoint, $payload);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json([
                    'status' => 'success',
                    'token' => $result['token'],
                    'redirect_url' => $result['redirect_url']
                ]);
            } else {
                $result = $response->json();
                $error_msg = $result['error_messages'][0] ?? 'Gagal membuat invoice pembayaran.';
                return response()->json([
                    'status' => 'error',
                    'message' => 'Midtrans Error: ' . $error_msg
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
