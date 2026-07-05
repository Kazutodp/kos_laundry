<?php
// mitra/layanan.php
session_start();
require_once '../db_connect.php';

// Authentication Check
if (!isset($_SESSION['mitra_logged_in']) || !isset($_SESSION['mitra_id'])) {
    header("Location: login.php");
    exit();
}

$mitra_id = $_SESSION['mitra_id'];

// Fetch Partner Profile Data from DB
$stmt = $pdo->prepare("SELECT * FROM mitra_laundry WHERE id = ?");
$stmt->execute([$mitra_id]);
$mitra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mitra) {
    unset($_SESSION['mitra_logged_in']);
    header("Location: login.php");
    exit();
}

// Load custom pricing from the partner file if it exists
$file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
$file_path = '../Mitra laundry/' . $file_name;

// Default values
$harga_pengeringan = 6000;
$harga_setrika_reguler = 13000;
$harga_setrika_saja = 7000;
$harga_satuan_jaket = 15000;
$harga_satuan_selimut = 20000;
$harga_satuan_bed_cover = 30000;

if (file_exists($file_path)) {
    $content = file_get_contents($file_path);
    if (preg_match('/\$custom_harga_pengeringan\s*=\s*(\d+)/', $content, $matches)) {
        $harga_pengeringan = (int)$matches[1];
    }
    if (preg_match('/\$custom_harga_setrika_reguler\s*=\s*(\d+)/', $content, $matches)) {
        $harga_setrika_reguler = (int)$matches[1];
    }
    if (preg_match('/\$custom_harga_setrika_saja\s*=\s*(\d+)/', $content, $matches)) {
        $harga_setrika_saja = (int)$matches[1];
    }
    if (preg_match('/\$custom_harga_satuan_jaket\s*=\s*(\d+)/', $content, $matches)) {
        $harga_satuan_jaket = (int)$matches[1];
    }
    if (preg_match('/\$custom_harga_satuan_selimut\s*=\s*(\d+)/', $content, $matches)) {
        $harga_satuan_selimut = (int)$matches[1];
    }
    if (preg_match('/\$custom_harga_satuan_bed_cover\s*=\s*(\d+)/', $content, $matches)) {
        $harga_satuan_bed_cover = (int)$matches[1];
    }
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect DB values
    $harga_per_kg = (int)($_POST['harga_per_kg'] ?? 0);
    $no_telp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $jam_buka = trim($_POST['jam_buka'] ?? '');
    $fasilitas = isset($_POST['fasilitas']) ? implode(',', $_POST['fasilitas']) : '';
    $keunggulan_lainnya = trim($_POST['keunggulan_lainnya'] ?? '');

    // Collect file custom pricing values
    $in_harga_pengeringan = (int)($_POST['harga_pengeringan'] ?? 6000);
    $in_harga_setrika_reguler = (int)($_POST['harga_setrika_reguler'] ?? 13000);
    $in_harga_setrika_saja = (int)($_POST['harga_setrika_saja'] ?? 7000);
    $in_harga_satuan_jaket = (int)($_POST['harga_satuan_jaket'] ?? 15000);
    $in_harga_satuan_selimut = (int)($_POST['harga_satuan_selimut'] ?? 20000);
    $in_harga_satuan_bed_cover = (int)($_POST['harga_satuan_bed_cover'] ?? 30000);

    if ($harga_per_kg <= 0 || $in_harga_pengeringan <= 0 || $in_harga_setrika_reguler <= 0 || $in_harga_setrika_saja <= 0) {
        $error = 'Semua tarif harga harus berupa angka positif.';
    } else {
        try {
            // 1. Update Database
            $stmt_update = $pdo->prepare("UPDATE mitra_laundry SET harga_per_kg = ?, no_telp = ?, alamat = ?, jam_buka = ?, fasilitas = ?, keunggulan_lainnya = ? WHERE id = ?");
            $stmt_update->execute([
                $harga_per_kg,
                $no_telp,
                $alamat,
                $jam_buka,
                $fasilitas,
                $keunggulan_lainnya,
                $mitra_id
            ]);

            // 2. Update the Partner PHP Template File dynamically
            if (file_exists($file_path)) {
                $file_content = file_get_contents($file_path);

                // Helper local function to replace or prepend PHP variables
                function update_var(&$content, $name, $value) {
                    $pattern = '/\$' . preg_quote($name) . '\s*=\s*[^;]+;/s';
                    $replacement = '$' . $name . ' = ' . (int)$value . ';';
                    if (preg_match($pattern, $content)) {
                        $content = preg_replace($pattern, $replacement, $content);
                    } else {
                        // Find include and prepend
                        $include_pattern = '/include\s+[\'"]detail_template\.php[\'"];/i';
                        if (preg_match($include_pattern, $content)) {
                            $content = preg_replace($include_pattern, $replacement . "\n" . 'include \'detail_template.php\';', $content);
                        } else {
                            $content = str_replace('<?php', "<?php\n" . $replacement, $content);
                        }
                    }
                }

                // Update jam operasional HTML representation in the PHP file
                $jam_operasional_html_content = "<div class=\"w-full space-y-1\">\n";
                $lines = explode("\n", str_replace("\r", "", $jam_buka));
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    if (strpos($line, ':') !== false) {
                        $parts = explode(':', $line, 2);
                        $day = trim($parts[0]);
                        $time = trim($parts[1]);
                    } else {
                        $day = 'Jam Kerja';
                        $time = $line;
                    }
                    $jam_operasional_html_content .= "    <div class=\"flex py-[2px] text-sm\">\n" .
                                                     "        <span class=\"text-on-surface-variant w-28 shrink-0 text-left\">" . htmlspecialchars($day) . "</span>\n" .
                                                     "        <span class=\"font-bold text-on-surface flex-1 text-left\">" . htmlspecialchars($time) . "</span>\n" .
                                                     "    </div>\n";
                }
                $jam_operasional_html_content .= "</div>";

                // Replace jam operasional variable
                $pattern_jam = '/\$jam_operasional_html\s*=\s*[^;]+;/s';
                $replacement_jam = '$jam_operasional_html = ' . var_export($jam_operasional_html_content, true) . ';';
                if (preg_match($pattern_jam, $file_content)) {
                    $file_content = preg_replace($pattern_jam, $replacement_jam, $file_content);
                }

                // Replace pricing variables
                update_var($file_content, 'custom_harga_lipat_reguler', $harga_per_kg);
                update_var($file_content, 'custom_harga_pengeringan', $in_harga_pengeringan);
                update_var($file_content, 'custom_harga_setrika_reguler', $in_harga_setrika_reguler);
                update_var($file_content, 'custom_harga_setrika_saja', $in_harga_setrika_saja);
                update_var($file_content, 'custom_harga_satuan_jaket', $in_harga_satuan_jaket);
                update_var($file_content, 'custom_harga_satuan_selimut', $in_harga_satuan_selimut);
                update_var($file_content, 'custom_harga_satuan_bed_cover', $in_harga_satuan_bed_cover);

                file_put_contents($file_path, $file_content);
            }

            $success = 'Pengaturan layanan & harga berhasil diperbarui.';
            
            // Reload the local variables to match updated state
            $harga_pengeringan = $in_harga_pengeringan;
            $harga_setrika_reguler = $in_harga_setrika_reguler;
            $harga_setrika_saja = $in_harga_setrika_saja;
            $harga_satuan_jaket = $in_harga_satuan_jaket;
            $harga_satuan_selimut = $in_harga_satuan_selimut;
            $harga_satuan_bed_cover = $in_harga_satuan_bed_cover;
            
            // Refresh DB state
            $stmt->execute([$mitra_id]);
            $mitra = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Kelola Layanan | <?= htmlspecialchars($mitra['nama_mitra']); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: "#0058be",
              secondary: "#00a389",
              dark: "#0f172a"
            },
            fontFamily: {
              sans: ["Inter", "sans-serif"]
            }
          }
        }
      }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col">

    <!-- Top Navbar -->
    <nav class="sticky top-0 z-40 bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="p-2 hover:bg-slate-100 rounded-xl transition-colors flex items-center justify-center text-slate-500 hover:text-slate-900">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Portal Mitra</span>
                <h1 class="text-md font-extrabold text-slate-900 leading-tight font-headline">Kelola Layanan & Tarif</h1>
            </div>
        </div>
        
        <div class="text-xs font-semibold text-slate-400 bg-slate-100 px-3.5 py-2 rounded-xl">
            Outlet: <span class="font-bold text-slate-900"><?= htmlspecialchars($mitra['nama_mitra']); ?></span>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-6 space-y-6">

        <!-- Notification Banner Alerts -->
        <?php if (!empty($success)): ?>
            <div class="p-4 bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-500/20 flex items-center gap-3 animate-fade-in">
                <span class="material-symbols-outlined text-[24px]">check_circle</span>
                <span class="text-sm font-bold"><?= htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="p-4 bg-rose-600 text-white rounded-2xl shadow-lg shadow-rose-600/20 flex items-center gap-3 animate-fade-in">
                <span class="material-symbols-outlined text-[24px]">error</span>
                <span class="text-sm font-bold"><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Container -->
        <form action="" method="POST" class="space-y-6">
            
            <!-- SECTION 1: PROFIL & INFORMASI TOKO -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="material-symbols-outlined text-primary p-2 bg-blue-50 rounded-xl text-[24px]">storefront</span>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-md">Profil & Kontak Outlet</h2>
                        <p class="text-xs text-slate-400">Detail alamat, telepon, dan jam buka toko Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Telephone Input -->
                    <div class="space-y-2">
                        <label for="no_telp" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">No. Telepon Kemitraan</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-slate-400">call</span>
                            <input type="text" id="no_telp" name="no_telp" value="<?= htmlspecialchars($mitra['no_telp']); ?>" placeholder="Contoh: 082341961954" required
                                   class="w-full pl-12 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>

                    <!-- Hours of Operation Input -->
                    <div class="space-y-2">
                        <label for="jam_buka" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Jam Operasional (Teks)</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-4 text-[20px] text-slate-400">schedule</span>
                            <textarea id="jam_buka" name="jam_buka" rows="2" placeholder="Contoh:&#10;Senin - Minggu: 07:00 - 22:00" required
                                      class="w-full pl-12 pr-4 py-3 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none"><?= htmlspecialchars($mitra['jam_buka']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Address Input -->
                <div class="space-y-2">
                    <label for="alamat" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Alamat Lengkap Outlet</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-4 text-[20px] text-slate-400">location_on</span>
                        <textarea id="alamat" name="alamat" rows="2" placeholder="Contoh: Jl. Majapahit No.88C, Kekalik Jaya, Kec. Sekarbela, Kota Mataram" required
                                  class="w-full pl-12 pr-4 py-3 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none"><?= htmlspecialchars($mitra['alamat']); ?></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Facilities Checkboxes -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Fasilitas Outlet</label>
                        <?php 
                        $selected_facilities = explode(',', $mitra['fasilitas'] ?? ''); 
                        ?>
                        <div class="bg-slate-50/50 border border-slate-200 rounded-2xl p-4 space-y-3">
                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="fasilitas[]" value="wifi" <?= in_array('wifi', $selected_facilities) ? 'checked' : ''; ?> 
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                                <span>Wifi Gratis (Free Wi-Fi)</span>
                            </label>
                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="fasilitas[]" value="ac" <?= in_array('ac', $selected_facilities) ? 'checked' : ''; ?> 
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                                <span>Ruang Tunggu AC</span>
                            </label>
                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="fasilitas[]" value="parkir" <?= in_array('parkir', $selected_facilities) ? 'checked' : ''; ?> 
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                                <span>Parkir Luas & Aman</span>
                            </label>
                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="fasilitas[]" value="air" <?= in_array('air', $selected_facilities) ? 'checked' : ''; ?> 
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                                <span>Air Minum Gratis (Dispenser)</span>
                            </label>
                            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="fasilitas[]" value="antar" <?= in_array('antar', $selected_facilities) ? 'checked' : ''; ?> 
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                                <span>Layanan Antar-Jemput</span>
                            </label>
                        </div>
                    </div>

                    <!-- Other Advantages Input -->
                    <div class="space-y-2">
                        <label for="keunggulan_lainnya" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Keunggulan Utama (Satu per baris)</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-4 text-[20px] text-slate-400">workspace_premium</span>
                            <textarea id="keunggulan_lainnya" name="keunggulan_lainnya" rows="6" 
                                      placeholder="Contoh:&#10;Proses Cepat: Selesai hanya dalam waktu 90 menit.&#10;Higienis: Pakaian Anda tidak dicampur dengan pelanggan lain."
                                      class="w-full pl-12 pr-4 py-3 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none"><?= htmlspecialchars($mitra['keunggulan_lainnya']); ?></textarea>
                        </div>
                        <p class="text-[10px] text-slate-400">Tulis keunggulan outlet Anda (format: "Judul: Deskripsi singkat") satu per baris.</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: TARIF LAYANAN KILOAN -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="material-symbols-outlined text-secondary p-2 bg-emerald-50 rounded-xl text-[24px]">payments</span>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-md">Tarif Layanan Kiloan</h2>
                        <p class="text-xs text-slate-400">Sesuaikan tarif cuci per kg di outlet Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Harga Cuci Lipat Reguler -->
                    <div class="space-y-2">
                        <label for="harga_per_kg" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Cuci Lipat Reguler (Harga Dasar / kg) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga_per_kg" name="harga_per_kg" value="<?= $mitra['harga_per_kg']; ?>" required min="1"
                                   class="w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>

                    <!-- Harga Pengeringan -->
                    <div class="space-y-2">
                        <label for="harga_pengeringan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Layanan Pengeringan (/ kg) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga_pengeringan" name="harga_pengeringan" value="<?= $harga_pengeringan; ?>" required min="1"
                                   class="w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>

                    <!-- Harga Setrika Reguler -->
                    <div class="space-y-2">
                        <label for="harga_setrika_reguler" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Cuci Setrika Reguler (/ kg) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga_setrika_reguler" name="harga_setrika_reguler" value="<?= $harga_setrika_reguler; ?>" required min="1"
                                   class="w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>

                    <!-- Harga Setrika Saja -->
                    <div class="space-y-2">
                        <label for="harga_setrika_saja" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Setrika Saja (/ kg) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga_setrika_saja" name="harga_setrika_saja" value="<?= $harga_setrika_saja; ?>" required min="1"
                                   class="w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: TARIF LAYANAN SATUAN -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="material-symbols-outlined text-amber-600 p-2 bg-amber-50 rounded-xl text-[24px]">dry_cleaning</span>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-md">Tarif Layanan Satuan</h2>
                        <p class="text-xs text-slate-400">Tentukan tarif khusus untuk item satuan pakaian tertentu</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Harga Satuan Jaket -->
                    <div class="space-y-2">
                        <label for="harga_satuan_jaket" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Cuci Jaket (/ pcs) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga_satuan_jaket" name="harga_satuan_jaket" value="<?= $harga_satuan_jaket; ?>" required min="1"
                                   class="w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>

                    <!-- Harga Satuan Selimut -->
                    <div class="space-y-2">
                        <label for="harga_satuan_selimut" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Cuci Selimut (/ pcs) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga_satuan_selimut" name="harga_satuan_selimut" value="<?= $harga_satuan_selimut; ?>" required min="1"
                                   class="w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>

                    <!-- Harga Satuan Bed Cover -->
                    <div class="space-y-2">
                        <label for="harga_satuan_bed_cover" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Cuci Bed Cover (/ pcs) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga_satuan_bed_cover" name="harga_satuan_bed_cover" value="<?= $harga_satuan_bed_cover; ?>" required min="1"
                                   class="w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm transition-all outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3.5">
                <a href="dashboard.php" class="text-sm font-bold text-slate-500 hover:bg-slate-200/50 px-5 py-3 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-gradient-to-r from-primary to-blue-600 hover:brightness-110 active:scale-[0.98] text-white py-3.5 px-6 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                    <span>Simpan Pengaturan</span>
                    <span class="material-symbols-outlined text-[18px]">save</span>
                </button>
            </div>
            
        </form>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-4 px-6 text-center text-xs text-slate-400 mt-12">
        © 2026 MataramWash Portal Kemitraan. Pengaturan Mandiri Layanan & Tarif Outlet.
    </footer>

</body>
</html>
