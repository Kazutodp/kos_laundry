<?php
// wa_helper.php
require_once __DIR__ . '/db_connect.php';

function kirim_whatsapp($target, $message) {
    // Fonnte API Token
    $token = "YOUR_FONNTE_TOKEN"; 
    
    // Format target phone number to start with 62 instead of 0 or +
    $target = preg_replace('/[^0-9]/', '', $target);
    if (strpos($target, '0') === 0) {
        $target = '62' . substr($target, 1);
    }
    
    // Log target and message for easy local verification
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    $log_file = $log_dir . '/whatsapp.log';
    $log_entry = date('[Y-m-d H:i:s]') . " Target: {$target} | Message: " . str_replace("\n", " [NL] ", $message) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    // Perform API Request (Fonnte API Gateway)
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'target' => $target,
        'message' => $message,
        'countryCode' => '62',
      ),
      CURLOPT_HTTPHEADER => array(
        'Authorization: ' . $token
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function notify_mitra_new_order($order_id, $pdo) {
    try {
        // Fetch order details
        $stmt_order = $pdo->prepare("SELECT o.*, m.nama_mitra, m.no_telp FROM orders o JOIN mitra_laundry m ON o.mitra_id = m.id WHERE o.id = ?");
        $stmt_order->execute([$order_id]);
        $order = $stmt_order->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) return false;
        
        $nama_mitra = $order['nama_mitra'];
        $no_telp_mitra = $order['no_telp'];
        $nama_pelanggan = $order['nama_pelanggan'];
        $layanan = $order['layanan'];
        $total_harga = $order['total_harga'];
        $alamat = $order['alamat_antar_jemput'];
        
        if (empty($no_telp_mitra)) return false;
        
        // Construct message
        if ($order['status_pembayaran'] === 'success') {
            $message = "Halo *{$nama_mitra}*,\n\nAda pesanan laundry baru masuk (Sudah Lunas Online) dari *{$nama_pelanggan}*!\n" .
                       "Layanan: {$layanan}\n" .
                       "Total Bayar: Rp " . number_format($total_harga, 0, ',', '.') . "\n" .
                       "Status: Siap diproses.\n\n" .
                       "Silakan cek dashboard mitra Anda untuk detail pesanan.";
        } else {
            // Regular (weigh later / COD)
            $message = "Halo *{$nama_mitra}*,\n\nAda pesanan laundry baru masuk dari *{$nama_pelanggan}*!\n" .
                       "Layanan: {$layanan}\n" .
                       "Alamat Jemput: {$alamat}\n" .
                       "Status: Menunggu berat timbangan.\n\n" .
                       "Mohon segera lakukan penjemputan/proses dan perbarui berat cucian di dashboard mitra Anda.";
        }
        
        return kirim_whatsapp($no_telp_mitra, $message);
    } catch (Exception $e) {
        // Log error
        $log_dir = __DIR__ . '/logs';
        if (!is_dir($log_dir)) mkdir($log_dir, 0777, true);
        file_put_contents($log_dir . '/whatsapp_errors.log', date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}
?>
