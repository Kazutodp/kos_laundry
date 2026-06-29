<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of all orders.
     */
    public function index()
    {
        $orders = Order::with('mitra')->orderBy('created_at', 'desc')->get();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Handle weight recording and scale digital photo upload.
     */
    public function timbang(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'real_weight' => 'required|numeric|min:0.05',
            'foto_timbangan' => 'required|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $order = Order::findOrFail($request->order_id);
        
        try {
            $foto_path = $order->foto_timbangan;

            if ($request->hasFile('foto_timbangan')) {
                $file = $request->file('foto_timbangan');
                $ext = $file->getClientOriginalExtension();
                $new_file_name = 'timbangan_' . $order->id . '_' . time() . '.' . $ext;
                
                // Destination directories
                $laravel_upload_path = public_path('uploads/timbangan');
                $root_upload_path = base_path('../uploads/timbangan');
                
                // Ensure directories exist
                if (!File::isDirectory($laravel_upload_path)) {
                    File::makeDirectory($laravel_upload_path, 0777, true, true);
                }
                if (!File::isDirectory($root_upload_path)) {
                    File::makeDirectory($root_upload_path, 0777, true, true);
                }

                // Delete old files if they exist
                if ($foto_path) {
                    if (File::exists(public_path($foto_path))) {
                        File::delete(public_path($foto_path));
                    }
                    if (File::exists(base_path('../' . $foto_path))) {
                        File::delete(base_path('../' . $foto_path));
                    }
                }

                // Move file to Laravel upload path
                $file->move($laravel_upload_path, $new_file_name);
                
                // Set path in DB format
                $foto_path = 'uploads/timbangan/' . $new_file_name;

                // Copy to PHP Native upload directory
                File::copy($laravel_upload_path . '/' . $new_file_name, base_path('../' . $foto_path));
            }

            // Recalculate price
            $total_harga = round($request->real_weight * $order->tarif_per_kg) + $order->biaya_antar_jemput;

            // Update order
            $order->update([
                'berat_atau_qty' => $request->real_weight,
                'total_harga' => $total_harga,
                'foto_timbangan' => $foto_path,
                'status_order' => 'Menunggu Pembayaran'
            ]);

            return redirect()->back()->with('success', 'Berat riil berhasil diperbarui dan status diubah menjadi Menunggu Pembayaran.');

        } catch (\Exception $e) {
            Log::error('Laravel Admin Timbang failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui timbangan: ' . $e->getMessage());
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status_order' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        try {
            $status_transfer = ($request->status_order === 'Selesai') ? 'Selesai' : 'Proses';

            $updateData = [
                'status_order' => $request->status_order,
                'status_transfer' => $status_transfer,
            ];

            // If status is updated to active processing, ready, or finished, auto-mark payment as success
            if (in_array($request->status_order, ['Diproses', 'Siap Diantar', 'Selesai'])) {
                $updateData['status_pembayaran'] = 'success';
            }

            $order->update($updateData);

            return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui menjadi: ' . $request->status_order);
        } catch (\Exception $e) {
            Log::error('Laravel Admin Update Status failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}
