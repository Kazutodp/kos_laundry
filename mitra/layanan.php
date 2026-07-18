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

$success = '';
$error = '';

// Helper function to extract coordinates from Google Maps Link
function get_coords_from_google_maps($url) {
    $url = trim($url);
    if (empty($url)) return null;

    // Follow short link redirect if maps.app.goo.gl or g.co is found
    if (strpos($url, 'maps.app.goo.gl') !== false || strpos($url, 'g.co') !== false) {
        $headers = @get_headers($url, 1);
        if ($headers) {
            $location = '';
            if (isset($headers['Location'])) {
                $location = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
            } elseif (isset($headers['location'])) {
                $location = is_array($headers['location']) ? end($headers['location']) : $headers['location'];
            }
            if ($location) {
                $url = $location;
            }
        }
    }
    
    // Pattern 1: @latitude,longitude
    if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
        return ['latitude' => $matches[1], 'longitude' => $matches[2]];
    }
    
    // Pattern 2: q=latitude,longitude
    if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
        return ['latitude' => $matches[1], 'longitude' => $matches[2]];
    }
    
    // Pattern 3: ll=latitude,longitude
    if (preg_match('/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
        return ['latitude' => $matches[1], 'longitude' => $matches[2]];
    }
    
    return null;
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. UPDATE PROFILE & LOCATION & FOTO TOKO
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $no_telp = trim($_POST['no_telp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $jam_buka = trim($_POST['jam_buka'] ?? '');
        $google_maps_link = trim($_POST['google_maps_link'] ?? '');
        $fasilitas = isset($_POST['fasilitas']) ? implode(',', $_POST['fasilitas']) : '';
        $keunggulan_lainnya = trim($_POST['keunggulan_lainnya'] ?? '');
        
        $latitude = $mitra['latitude'];
        $longitude = $mitra['longitude'];

        // Coordinate extraction from maps link
        if (!empty($google_maps_link)) {
            $coords = get_coords_from_google_maps($google_maps_link);
            if ($coords) {
                $latitude = $coords['latitude'];
                $longitude = $coords['longitude'];
            }
        }

        // Image upload handling
        $foto_toko = $mitra['foto_toko'];
        if (isset($_FILES['foto_toko']) && $_FILES['foto_toko']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_toko']['tmp_name'];
            $file_name = basename($_FILES['foto_toko']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($file_ext, $allowed_exts)) {
                $error = 'Format file gambar tidak didukung (gunakan JPG, PNG, atau WEBP).';
            } else {
                $new_file_name = 'mitra_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    // Delete old photo if exists and is not default
                    if (!empty($mitra['foto_toko']) && file_exists('../' . $mitra['foto_toko']) && $mitra['foto_toko'] !== 'uploads/mitra_1.png') {
                        @unlink('../' . $mitra['foto_toko']);
                    }
                    $foto_toko = 'uploads/' . $new_file_name;
                } else {
                    $error = 'Gagal mengunggah foto toko.';
                }
            }
        }

        if (empty($error)) {
            try {
                // Update mitra_laundry in DB
                $stmt_up = $pdo->prepare("UPDATE mitra_laundry SET no_telp = ?, alamat = ?, jam_buka = ?, google_maps_link = ?, latitude = ?, longitude = ?, foto_toko = ?, fasilitas = ?, keunggulan_lainnya = ? WHERE id = ?");
                $stmt_up->execute([
                    $no_telp,
                    $alamat,
                    $jam_buka,
                    $google_maps_link,
                    $latitude,
                    $longitude,
                    $foto_toko,
                    $fasilitas,
                    $keunggulan_lainnya,
                    $mitra_id
                ]);

                // Sync the template file (opening hours, location, name, etc.)
                $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
                $file_path = '../Mitra laundry/' . $file_name;
                if (file_exists($file_path)) {
                    $file_content = file_get_contents($file_path);
                    
                    // Rebuild operasional html
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

                    $pattern_jam = '/\$jam_operasional_html\s*=\s*[^;]+;/s';
                    $replacement_jam = '$jam_operasional_html = ' . var_export($jam_operasional_html_content, true) . ';';
                    if (preg_match($pattern_jam, $file_content)) {
                        $file_content = preg_replace($pattern_jam, $replacement_jam, $file_content);
                    }
                    file_put_contents($file_path, $file_content);
                }

                $success = 'Profil outlet berhasil diperbarui.';
                
                // Refresh DB state
                $stmt->execute([$mitra_id]);
                $mitra = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }

    // 2. ADD CUSTOM SERVICE
    elseif (isset($_POST['action']) && $_POST['action'] === 'add_service') {
        $nama_layanan = trim($_POST['nama_layanan'] ?? '');
        $harga = (int)($_POST['harga'] ?? 0);
        $detail = trim($_POST['detail'] ?? '');
        $kategori = trim($_POST['kategori'] ?? 'kiloan');

        if (empty($nama_layanan) || $harga <= 0) {
            $error = 'Nama layanan dan harga positif wajib diisi.';
        } else {
            try {
                $stmt_add = $pdo->prepare("INSERT INTO `mitra_layanan` (mitra_id, nama_layanan, harga, detail, kategori) VALUES (?, ?, ?, ?, ?)");
                $stmt_add->execute([$mitra_id, $nama_layanan, $harga, $detail, $kategori]);
                $success = 'Layanan baru berhasil ditambahkan.';
            } catch (Exception $e) {
                $error = 'Gagal menambahkan layanan: ' . $e->getMessage();
            }
        }
    }

    // 3. DELETE CUSTOM SERVICE
    elseif (isset($_POST['action']) && $_POST['action'] === 'delete_service') {
        $service_id = (int)($_POST['service_id'] ?? 0);
        if ($service_id > 0) {
            try {
                $stmt_del = $pdo->prepare("DELETE FROM `mitra_layanan` WHERE id = ? AND mitra_id = ?");
                $stmt_del->execute([$service_id, $mitra_id]);
                if ($stmt_del->rowCount() > 0) {
                    $success = 'Layanan berhasil dihapus.';
                } else {
                    $error = 'Layanan tidak ditemukan atau tidak berhak dihapus.';
                }
            } catch (Exception $e) {
                $error = 'Gagal menghapus layanan: ' . $e->getMessage();
            }
        }
    }

    // 4. EDIT CUSTOM SERVICE
    elseif (isset($_POST['action']) && $_POST['action'] === 'edit_service') {
        $service_id = (int)($_POST['service_id'] ?? 0);
        $nama_layanan = trim($_POST['nama_layanan'] ?? '');
        $harga = (int)($_POST['harga'] ?? 0);
        $detail = trim($_POST['detail'] ?? '');
        $kategori = trim($_POST['kategori'] ?? 'kiloan');

        if ($service_id > 0 && !empty($nama_layanan) && $harga > 0) {
            try {
                $stmt_edit = $pdo->prepare("UPDATE `mitra_layanan` SET nama_layanan = ?, harga = ?, detail = ?, kategori = ? WHERE id = ? AND mitra_id = ?");
                $stmt_edit->execute([$nama_layanan, $harga, $detail, $kategori, $service_id, $mitra_id]);
                $success = 'Layanan berhasil diperbarui.';
            } catch (Exception $e) {
                $error = 'Gagal memperbarui layanan: ' . $e->getMessage();
            }
        } else {
            $error = 'Nama layanan dan harga positif wajib diisi.';
        }
    }
}

// Fetch all services of this partner
$stmt_services = $pdo->prepare("SELECT * FROM `mitra_layanan` WHERE `mitra_id` = ? ORDER BY `kategori` ASC, `id` ASC");
$stmt_services->execute([$mitra_id]);
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);
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
    <style>
        .modal {
            transition: opacity 0.25s ease;
        }
        body.modal-active {
            overflow: hidden;
        }
    </style>
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
                <h1 class="text-md font-extrabold text-slate-900 leading-tight font-headline">Kelola Profil & Layanan</h1>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Settings Link -->
            <a href="settings.php" class="flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 px-3.5 py-2 rounded-xl transition-all border border-transparent hover:border-slate-200" title="Pengaturan Akun">
                <span class="material-symbols-outlined text-[18px]">settings</span>
                Pengaturan
            </a>
            <div class="text-xs font-semibold text-slate-400 bg-slate-100 px-3.5 py-2 rounded-xl">
                Outlet: <span class="font-bold text-slate-900"><?= htmlspecialchars($mitra['nama_mitra']); ?></span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-5xl w-full mx-auto p-6 space-y-6">

        <!-- Notification Alerts -->
        <?php if (!empty($success)): ?>
            <div class="p-4 bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-500/20 flex items-center gap-3">
                <span class="material-symbols-outlined text-[24px]">check_circle</span>
                <span class="text-sm font-bold"><?= htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="p-4 bg-rose-600 text-white rounded-2xl shadow-lg shadow-rose-600/20 flex items-center gap-3">
                <span class="material-symbols-outlined text-[24px]">error</span>
                <span class="text-sm font-bold"><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- LEFT PANEL: PROFILE & MEDIA -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-5">
                    <h2 class="text-md font-extrabold text-slate-900 border-b border-slate-50 pb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[22px]">image</span>
                        <span>Foto Toko</span>
                    </h2>
                    
                    <!-- Foto Toko Preview -->
                    <div class="relative group rounded-2xl overflow-hidden aspect-video border border-slate-200 bg-slate-50 flex items-center justify-center">
                        <?php if (!empty($mitra['foto_toko'])): ?>
                            <img id="foto_preview" src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="Foto Toko" class="w-full h-full object-cover">
                        <?php else: ?>
                            <img id="foto_preview" src="../uploads/mitra_1.png" alt="Foto Toko" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Ganti Foto Toko</label>
                            <input type="file" name="foto_toko" accept="image/*" onchange="previewImage(event)"
                                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-primary hover:file:bg-blue-100 cursor-pointer">
                        </div>

                        <hr class="border-slate-100 my-4">

                        <!-- No Telp -->
                        <div class="space-y-1">
                            <label for="no_telp" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">No. Telepon / WhatsApp</label>
                            <input type="text" id="no_telp" name="no_telp" value="<?= htmlspecialchars($mitra['no_telp']); ?>" required
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none">
                        </div>

                        <!-- Jam Buka -->
                        <div class="space-y-1">
                            <label for="jam_buka" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Jam Operasional</label>
                            <textarea id="jam_buka" name="jam_buka" rows="2" required
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none"><?= htmlspecialchars($mitra['jam_buka']); ?></textarea>
                        </div>

                        <!-- Alamat -->
                        <div class="space-y-1">
                            <label for="alamat" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Alamat Lengkap</label>
                            <textarea id="alamat" name="alamat" rows="2" required
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none"><?= htmlspecialchars($mitra['alamat']); ?></textarea>
                        </div>

                        <!-- Google Maps Link -->
                        <div class="space-y-1">
                            <label for="google_maps_link" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Link Google Maps</label>
                            <input type="url" id="google_maps_link" name="google_maps_link" value="<?= htmlspecialchars($mitra['google_maps_link'] ?? ''); ?>" placeholder="https://maps.google.com/..."
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none">
                            <span class="text-[9px] text-slate-400 block font-medium leading-tight">Tempel tautan lokasi Google Maps untuk menyinkronkan koordinat peta toko secara otomatis saat disimpan.</span>
                        </div>

                        <!-- Fasilitas Checkboxes -->
                        <div class="space-y-2 pt-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Fasilitas Toko</label>
                            <?php $selected_facilities = explode(',', $mitra['fasilitas'] ?? ''); ?>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-2">
                                <label class="flex items-center gap-3 text-xs font-semibold text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="fasilitas[]" value="wifi" <?= in_array('wifi', $selected_facilities) ? 'checked' : ''; ?> class="rounded border-slate-300 text-primary">
                                    <span>Wifi Gratis</span>
                                </label>
                                <label class="flex items-center gap-3 text-xs font-semibold text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="fasilitas[]" value="ac" <?= in_array('ac', $selected_facilities) ? 'checked' : ''; ?> class="rounded border-slate-300 text-primary">
                                    <span>Ruang Tunggu AC</span>
                                </label>
                                <label class="flex items-center gap-3 text-xs font-semibold text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="fasilitas[]" value="parkir" <?= in_array('parkir', $selected_facilities) ? 'checked' : ''; ?> class="rounded border-slate-300 text-primary">
                                    <span>Parkir Luas</span>
                                </label>
                                <label class="flex items-center gap-3 text-xs font-semibold text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="fasilitas[]" value="air" <?= in_array('air', $selected_facilities) ? 'checked' : ''; ?> class="rounded border-slate-300 text-primary">
                                    <span>Air Minum Gratis</span>
                                </label>
                                <label class="flex items-center gap-3 text-xs font-semibold text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="fasilitas[]" value="antar" <?= in_array('antar', $selected_facilities) ? 'checked' : ''; ?> class="rounded border-slate-300 text-primary">
                                    <span>Antar Jemput</span>
                                </label>
                            </div>
                        </div>

                        <!-- Keunggulan Lainnya -->
                        <div class="space-y-1">
                            <label for="keunggulan_lainnya" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Keunggulan Toko (Satu per baris)</label>
                            <textarea id="keunggulan_lainnya" name="keunggulan_lainnya" rows="3" placeholder="Contoh:&#10;Proses Cepat: Selesai hanya dalam waktu 90 menit."
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none"><?= htmlspecialchars($mitra['keunggulan_lainnya']); ?></textarea>
                        </div>

                        <button type="submit" class="w-full bg-primary hover:brightness-110 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-md transition-all flex items-center justify-center gap-2 mt-2">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>Simpan Profil Outlet</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- RIGHT PANEL: DYNAMIC SERVICES MANAGEMENT -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-6">
                    <div class="flex justify-between items-center border-b border-slate-50 pb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary text-[24px]">local_laundry_service</span>
                            <div>
                                <h2 class="text-md font-extrabold text-slate-900">Daftar Layanan Toko</h2>
                                <p class="text-xs text-slate-400">Atur menu, harga, dan rincian produk laundry Anda</p>
                            </div>
                        </div>
                        <button onclick="toggleModal()" class="bg-secondary hover:brightness-105 active:scale-[0.98] text-white px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Tambah Layanan</span>
                        </button>
                    </div>

                    <!-- Services Table -->
                    <?php if (empty($services)): ?>
                        <div class="text-center py-12 space-y-3">
                            <span class="material-symbols-outlined text-[64px] text-slate-200">grid_off</span>
                            <p class="text-sm font-semibold text-slate-400">Belum ada layanan yang didaftarkan.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-bold text-xs uppercase">
                                        <th class="py-3 px-4">Nama Layanan</th>
                                        <th class="py-3 px-4">Kategori Tab</th>
                                        <th class="py-3 px-4">Tarif Harga</th>
                                        <th class="py-3 px-4">Detail Keterangan</th>
                                        <th class="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($services as $srv): ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-4 px-4 font-bold text-slate-950"><?= htmlspecialchars($srv['nama_layanan']); ?></td>
                                            <td class="py-4 px-4">
                                                <?php if ($srv['kategori'] === 'self'): ?>
                                                    <span class="inline-block px-2.5 py-1 text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded-full">Self Service</span>
                                                <?php elseif ($srv['kategori'] === 'kiloan'): ?>
                                                    <span class="inline-block px-2.5 py-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full">Kiloan Drop-Off</span>
                                                <?php elseif ($srv['kategori'] === 'satuan'): ?>
                                                    <span class="inline-block px-2.5 py-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-full">Satuan</span>
                                                <?php else: ?>
                                                    <span class="inline-block px-2.5 py-1 text-[10px] font-bold text-purple-700 bg-purple-50 border border-purple-100 rounded-full">Express</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-4 font-bold text-primary">Rp <?= number_format($srv['harga'], 0, ',', '.'); ?></td>
                                            <td class="py-4 px-4 text-xs text-slate-500 max-w-[200px] truncate" title="<?= htmlspecialchars($srv['detail']); ?>">
                                                <?= htmlspecialchars($srv['detail']); ?>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <!-- Edit Button -->
                                                    <button type="button" 
                                                            data-id="<?= $srv['id']; ?>"
                                                            data-nama="<?= htmlspecialchars($srv['nama_layanan']); ?>"
                                                            data-harga="<?= $srv['harga']; ?>"
                                                            data-kategori="<?= htmlspecialchars($srv['kategori']); ?>"
                                                            data-detail="<?= htmlspecialchars($srv['detail']); ?>"
                                                            onclick="openEditModal(this)"
                                                            class="p-2 bg-blue-50 hover:bg-blue-100 text-primary rounded-xl transition-colors inline-flex items-center justify-center border border-blue-100" 
                                                            title="Edit Layanan">
                                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                                    </button>
                                                    
                                                    <!-- Delete Button -->
                                                    <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?');" class="inline">
                                                        <input type="hidden" name="action" value="delete_service">
                                                        <input type="hidden" name="service_id" value="<?= $srv['id']; ?>">
                                                        <button type="submit" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition-colors inline-flex items-center justify-center border border-rose-100" title="Hapus Layanan">
                                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- ADD SERVICE MODAL DIALOG -->
    <div id="add_modal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50">
        <div class="modal-overlay absolute w-full h-full bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal()"></div>
        
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-3xl shadow-2xl z-50 overflow-y-auto transform scale-95 transition-transform duration-300">
            <div class="modal-content py-6 text-left px-6 space-y-4">
                <!-- Title -->
                <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                    <h3 class="text-md font-extrabold text-slate-950 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-secondary">add_circle</span>
                        <span>Tambah Layanan Baru</span>
                    </h3>
                    <button class="modal-close cursor-pointer z-50 text-slate-400 hover:text-slate-600" onclick="toggleModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add_service">

                    <div class="space-y-1">
                        <label for="nama_layanan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Layanan *</label>
                        <input type="text" id="nama_layanan" name="nama_layanan" required placeholder="Contoh: Cuci Setrika Ekspres"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="harga" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Tarif Harga (Rp) *</label>
                            <input type="number" id="harga" name="harga" required min="100" placeholder="Contoh: 15000"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none">
                        </div>

                        <div class="space-y-1">
                            <label for="kategori" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori Tab *</label>
                            <select id="kategori" name="kategori" required
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none cursor-pointer">
                                <option value="kiloan">Cuci Kiloan</option>
                                <option value="satuan">Cuci Satuan</option>
                                <option value="express">Cuci Express</option>
                                <option value="self">Self Service</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="detail" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Keterangan / Detail Layanan</label>
                        <textarea id="detail" name="detail" rows="3" placeholder="Contoh: Pengerjaan 6 jam selesai. Pakaian disetrika rapi menggunakan setrika uap."
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-xs font-bold hover:bg-slate-50 transition-colors" onclick="toggleModal()">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-secondary text-white text-xs font-bold hover:brightness-105 transition-all shadow-md">Simpan Layanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT SERVICE MODAL DIALOG -->
    <div id="edit_modal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50">
        <div class="modal-overlay absolute w-full h-full bg-slate-900/50 backdrop-blur-sm" onclick="toggleEditModal()"></div>
        
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-3xl shadow-2xl z-50 overflow-y-auto transform scale-95 transition-transform duration-300">
            <div class="modal-content py-6 text-left px-6 space-y-4">
                <!-- Title -->
                <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                    <h3 class="text-md font-extrabold text-slate-950 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary">edit_document</span>
                        <span>Edit Layanan</span>
                    </h3>
                    <button class="modal-close cursor-pointer z-50 text-slate-400 hover:text-slate-600" onclick="toggleEditModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="edit_service">
                    <input type="hidden" name="service_id" id="edit_service_id">

                    <div class="space-y-1">
                        <label for="edit_nama_layanan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Layanan *</label>
                        <input type="text" id="edit_nama_layanan" name="nama_layanan" required placeholder="Contoh: Cuci Setrika Ekspres"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="edit_harga" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Tarif Harga (Rp) *</label>
                            <input type="number" id="edit_harga" name="harga" required min="100" placeholder="Contoh: 15000"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none">
                        </div>

                        <div class="space-y-1">
                            <label for="edit_kategori" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori Tab *</label>
                            <select id="edit_kategori" name="kategori" required
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none cursor-pointer">
                                <option value="kiloan">Cuci Kiloan</option>
                                <option value="satuan">Cuci Satuan</option>
                                <option value="express">Cuci Express</option>
                                <option value="self">Self Service</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="edit_detail" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Keterangan / Detail Layanan</label>
                        <textarea id="edit_detail" name="detail" rows="3" placeholder="Contoh: Pengerjaan 6 jam selesai. Pakaian disetrika rapi menggunakan setrika uap."
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm outline-none"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-xs font-bold hover:bg-slate-50 transition-colors" onclick="toggleEditModal()">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:brightness-110 transition-all shadow-md">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-4 px-6 text-center text-xs text-slate-400 mt-12">
        © 2026 MataramWash Portal Kemitraan. Pengaturan Mandiri Layanan & Profil Outlet.
    </footer>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('foto_preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function toggleModal() {
            const body = document.querySelector('body');
            const modal = document.querySelector('#add_modal');
            const modalContainer = modal.querySelector('.modal-container');
            
            modal.classList.toggle('opacity-0');
            modal.classList.toggle('pointer-events-none');
            body.classList.toggle('modal-active');
            
            if (modal.classList.contains('pointer-events-none')) {
                modalContainer.classList.remove('scale-100');
                modalContainer.classList.add('scale-95');
            } else {
                modalContainer.classList.remove('scale-95');
                modalContainer.classList.add('scale-100');
            }
        }

        function openEditModal(button) {
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const harga = button.getAttribute('data-harga');
            const kategori = button.getAttribute('data-kategori');
            const detail = button.getAttribute('data-detail');
            
            document.getElementById('edit_service_id').value = id;
            document.getElementById('edit_nama_layanan').value = nama;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('edit_kategori').value = kategori;
            document.getElementById('edit_detail').value = detail;
            
            toggleEditModal();
        }

        function toggleEditModal() {
            const body = document.querySelector('body');
            const modal = document.querySelector('#edit_modal');
            const modalContainer = modal.querySelector('.modal-container');
            
            modal.classList.toggle('opacity-0');
            modal.classList.toggle('pointer-events-none');
            body.classList.toggle('modal-active');
            
            if (modal.classList.contains('pointer-events-none')) {
                modalContainer.classList.remove('scale-100');
                modalContainer.classList.add('scale-95');
            } else {
                modalContainer.classList.remove('scale-95');
                modalContainer.classList.add('scale-100');
            }
        }
    </script>
</body>
</html>
