<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $notification = $request->all();

        $order_id = $notification['order_id'] ?? '';
        $status_code = $notification['status_code'] ?? '';
        $gross_amount = $notification['gross_amount'] ?? '';
        $transaction_status = $notification['transaction_status'] ?? '';
        $type = $notification['payment_type'] ?? '';
        $fraud_status = $notification['fraud_status'] ?? '';
        $signature_key = $notification['signature_key'] ?? '';

        if (empty($order_id) || empty($status_code) || empty($gross_amount) || empty($signature_key)) {
            return response()->json(['status' => 'error', 'message' => 'Missing notification details.'], 400);
        }

        // Get server key
        $serverKey = config('services.midtrans.server_key');
        if (empty($serverKey)) {
            $configFile = base_path('../admin/settings_config.json');
            if (file_exists($configFile)) {
                $loadedConfig = json_decode(file_get_contents($configFile), true);
                $serverKey = $loadedConfig['midtrans_server_key'] ?? '';
            }
        }

        // Verify signature
        $local_signature = hash('sha512', $order_id . $status_code . $gross_amount . $serverKey);

        if ($local_signature !== $signature_key) {
            return response()->json(['status' => 'error', 'message' => 'Signature mismatch. Request unauthorized.'], 401);
        }

        // Extract database order ID from Midtrans order_id (format: MW-[db_id]-[timestamp])
        $parts = explode('-', $order_id);
        $db_order_id = intval($parts[1] ?? 0);

        if ($db_order_id <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Invalid order ID mapping.'], 400);
        }

        $order = Order::find($db_order_id);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        // Determine payment status
        $status_pembayaran = 'pending';

        if ($transaction_status === 'capture') {
            if ($type === 'credit_card') {
                if ($fraud_status === 'challenge') {
                    $status_pembayaran = 'pending';
                } else {
                    $status_pembayaran = 'success';
                }
            }
        } elseif ($transaction_status === 'settlement') {
            $status_pembayaran = 'success';
        } elseif ($transaction_status === 'pending') {
            $status_pembayaran = 'pending';
        } elseif (in_array($transaction_status, ['deny', 'expire', 'cancel'])) {
            $status_pembayaran = 'failed';
        }

        // Update database
        try {
            $updateData = ['status_pembayaran' => $status_pembayaran];
            if ($status_pembayaran === 'success' && $order->status_order === 'Menunggu Pembayaran') {
                $updateData['status_order'] = 'Diproses';
            }
            $order->update($updateData);

            // Trigger WA notification to partner for successful payment
            if ($status_pembayaran === 'success') {
                try {
                    require_once base_path('../wa_helper.php');
                    notify_mitra_new_order($order->id, \DB::getPdo());
                } catch (\Exception $wa_ex) {
                    \Log::error('WA Notification failed: ' . $wa_ex->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated to ' . $status_pembayaran
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook database update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Database update failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
