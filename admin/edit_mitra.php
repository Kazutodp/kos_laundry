<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

$success = false;
$error = '';

// Get partner data to edit
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: manajemen_mitra.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM mitra_laundry WHERE id = ?");
    $stmt->execute([$id]);
    $mitra = $stmt->fetch();
    
    if (!$mitra) {
        header("Location: manajemen_mitra.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: manajemen_mitra.php");
    exit();
}

// Load custom pricing from the partner file if it exists
$file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
$file_path = '../Mitra laundry/' . $file_name;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_mitra = trim($_POST['nama_mitra'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $google_maps_link = trim($_POST['google_maps_link'] ?? '');
    
    // Attempt coordinate extraction if Google Maps link is provided
    if (!empty($google_maps_link)) {
        $extracted_coords = get_coords_from_google_maps($google_maps_link);
        if ($extracted_coords) {
            $latitude = $extracted_coords['latitude'];
            $longitude = $extracted_coords['longitude'];
        }
    }
    $alamat = trim($_POST['alamat'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');
    $harga_per_kg = trim($_POST['harga_per_kg'] ?? '');
    $jam_buka = trim($_POST['jam_buka'] ?? '');
    $icon_type = $_POST['icon_type'] ?? 'kiloan';
    $status_buka = isset($_POST['status_buka']) ? 1 : 0;
    $is_rekomendasi = isset($_POST['is_rekomendasi']) ? 1 : 0;
    $rating = trim($_POST['rating'] ?? '5.0');
    
    // Custom Pricing Overrides
    $harga_pengeringan = (int)($_POST['harga_pengeringan'] ?? 6000);
    $harga_setrika_reguler = (int)($_POST['harga_setrika_reguler'] ?? 13000);
    $harga_setrika_saja = (int)($_POST['harga_setrika_saja'] ?? 7000);
    $harga_satuan_jaket = (int)($_POST['harga_satuan_jaket'] ?? 15000);
    $harga_satuan_selimut = (int)($_POST['harga_satuan_selimut'] ?? 20000);
    $harga_satuan_bed_cover = (int)($_POST['harga_satuan_bed_cover'] ?? 30000);

    // Validation
    if (empty($nama_mitra) || empty($latitude) || empty($longitude) || empty($alamat) || empty($harga_per_kg)) {
        $error = 'Nama Mitra, Koordinat (Latitude & Longitude), Alamat, dan Harga wajib diisi.';
    } else {
        // File Upload
        $foto_toko = $mitra['foto_toko']; // keep old by default
        
        if (isset($_FILES['foto_toko']) && $_FILES['foto_toko']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_toko']['tmp_name'];
            $file_name = basename($_FILES['foto_toko']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate extension
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($file_ext, $allowed_exts)) {
                $error = 'Format file gambar tidak didukung (gunakan JPG, PNG, atau WEBP).';
            } else {
                // Create unique name
                $new_file_name = 'mitra_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    // Delete old photo if not a default/placeholder photo
                    if (!empty($mitra['foto_toko']) && file_exists('../' . $mitra['foto_toko']) && $mitra['foto_toko'] !== 'uploads/mitra_1.png') {
                        @unlink('../' . $mitra['foto_toko']);
                    }
                    $foto_toko = 'uploads/' . $new_file_name;
                } else {
                    $error = 'Gagal mengunggah foto toko baru ke server.';
                }
            }
        }

        if (empty($error)) {
            try {
                // Update database
                $stmt = $pdo->prepare("UPDATE mitra_laundry SET nama_mitra = ?, foto_toko = ?, latitude = ?, longitude = ?, alamat = ?, no_telp = ?, rating = ?, harga_per_kg = ?, jam_buka = ?, status_buka = ?, icon_type = ?, is_rekomendasi = ? WHERE id = ?");
                $stmt->execute([
                    $nama_mitra,
                    $foto_toko,
                    $latitude,
                    $longitude,
                    $alamat,
                    $no_telp,
                    $rating,
                    $harga_per_kg,
                    $jam_buka,
                    $status_buka,
                    $icon_type,
                    $is_rekomendasi,
                    $id
                ]);

                // Manage template files
                $old_slug = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
                $new_slug = str_replace(' ', '_', $nama_mitra) . '.php';
                
                // If name changed, delete old detail page file
                if ($old_slug !== $new_slug && file_exists('../Mitra laundry/' . $old_slug)) {
                    @unlink('../Mitra laundry/' . $old_slug);
                }

                // Write/Overwrite detail page template
                $detail_dir = '../Mitra laundry/';
                if (!is_dir($detail_dir)) {
                    mkdir($detail_dir, 0777, true);
                }

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
                    $jam_operasional_html_content .= "    <div class=\"flex justify-between p-xs hover:bg-surface-container/30 rounded-lg transition-colors text-sm\">\n" .
                                                     "        <span class=\"text-on-surface-variant w-24 shrink-0 text-left\">" . htmlspecialchars($day) . "</span>\n" .
                                                     "        <span class=\"font-bold text-on-surface flex-1 text-left\">" . htmlspecialchars($time) . "</span>\n" .
                                                     "    </div>\n";
                }
                $jam_operasional_html_content .= "</div>";

                $file_content = "<?php\n" .
                    "\$nama_mitra = " . var_export($nama_mitra, true) . ";\n" .
                    "\$jam_operasional_html = " . var_export($jam_operasional_html_content, true) . ";\n\n" .
                    "// Custom pricing overrides matching the database base price\n" .
                    "\$custom_harga_lipat_reguler = " . (int)$harga_per_kg . ";\n" .
                    "\$custom_harga_pengeringan = " . $harga_pengeringan . ";\n" .
                    "\$custom_harga_setrika_reguler = " . $harga_setrika_reguler . ";\n" .
                    "\$custom_harga_setrika_saja = " . $harga_setrika_saja . ";\n\n" .
                    "// Custom Satuan pricing overrides\n" .
                    "\$custom_harga_satuan_jaket = " . $harga_satuan_jaket . ";\n" .
                    "\$custom_harga_satuan_selimut = " . $harga_satuan_selimut . ";\n" .
                    "\$custom_harga_satuan_bed_cover = " . $harga_satuan_bed_cover . ";\n\n" .
                    "include 'detail_template.php';\n" .
                    "?>\n";

                file_put_contents($detail_dir . $new_slug, $file_content);

                header("Location: manajemen_mitra.php?status=updated");
                exit();
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan ke database: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Edit Mitra Laundry | MataramWash Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "primary-fixed": "#d8e2ff",
                    "surface-container-high": "#e2e8f8",
                    "on-tertiary-fixed": "#2a1700",
                    "error-container": "#ffdad6",
                    "primary-fixed-dim": "#adc6ff",
                    "on-surface": "#151c27",
                    "primary": "#3b82f6",
                    "tertiary": "#825100",
                    "on-secondary": "#ffffff",
                    "error": "#ba1a1a",
                    "inverse-primary": "#adc6ff",
                    "background": "#f9f9ff",
                    "on-background": "#151c27",
                    "secondary": "#006b5f",
                    "primary-container": "#2170e4",
                    "on-secondary-fixed": "#00201c",
                    "surface-variant": "#dce2f3",
                    "on-tertiary": "#ffffff",
                    "on-error-container": "#93000a",
                    "on-surface-variant": "#424754",
                    "on-primary-fixed": "#001a42",
                    "tertiary-fixed-dim": "#ffb95f",
                    "surface-container-highest": "#dce2f3",
                    "on-secondary-container": "#006f64",
                    "tertiary-fixed": "#ffddb8",
                    "surface-bright": "#f9f9ff",
                    "surface-container": "#e7eefe",
                    "secondary-container": "#6df5e1",
                    "on-primary-fixed-variant": "#004395",
                    "tertiary-container": "#a36700",
                    "on-primary": "#ffffff",
                    "on-secondary-fixed-variant": "#005048",
                    "on-tertiary-fixed-variant": "#653e00",
                    "inverse-surface": "#2a313d",
                    "outline-variant": "#c2c6d6",
                    "outline": "#727785",
                    "on-primary-container": "#fefcff",
                    "secondary-fixed-dim": "#4fdbc8",
                    "secondary-fixed": "#71f8e4",
                    "surface-container-low": "#f0f3ff",
                    "inverse-on-surface": "#ebf1ff",
                    "surface-tint": "#005ac2",
                    "surface-container-lowest": "#ffffff",
                    "surface": "#f9f9ff",
                    "on-tertiary-container": "#fffbff",
                    "on-error": "#ffffff",
                    "surface-dim": "#d3daea"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "container-margin": "20px",
                    "xl": "32px",
                    "sm": "12px",
                    "gutter": "16px",
                    "base": "4px",
                    "md": "16px",
                    "lg": "24px",
                    "xs": "8px"
            }
          }
        }
      }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .bento-card {
            background-color: #ffffff;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex overflow-hidden">
    <!-- SideNavBar -->
    <!-- SideNavBar -->
<aside class="hidden lg:flex flex-col h-screen sticky top-0 p-md space-y-md bg-slate-900 border-r border-slate-800 w-64 shrink-0 text-slate-300">
<div class="flex items-center gap-xs px-xs py-sm border-b border-slate-800">
<img alt="MataramWash Logo" class="h-8 w-8 object-contain brightness-110 filter" src="../logo.png?v=3">
<span class="text-headline-sm font-headline-md font-extrabold text-white">MataramWash</span>
</div>
<div class="flex flex-col gap-xs py-md border-b border-slate-800">
<p class="px-md text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-xs">Main Menu</p>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="dashboard_admin.php">
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">dashboard</span>
<span class="text-label-md font-label-md">Dashboard</span>
</a>
<a class="flex items-center gap-sm px-md py-sm bg-blue-600 text-white rounded-xl font-bold border-l-4 border-blue-400 shadow-lg shadow-blue-900/30 transition-all duration-200" href="manajemen_mitra.php">
<span class="material-symbols-outlined text-[20px]">group</span>
<span class="text-label-md font-label-md">Manajemen Mitra</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="kelola_pesanan.php">
<span class="material-symbols-outlined text-[20px]">local_laundry_service</span>
<span class="text-label-md font-label-md">Kelola Pesanan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="operational_area.php">
<span class="material-symbols-outlined text-[20px]">map</span>
<span class="text-label-md font-label-md">Wilayah Operasional</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="analitik_kemitraan.php">
<span class="material-symbols-outlined text-[20px]">analytics</span>
<span class="text-label-md font-label-md">Analitik Kemitraan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="financial_statements.php">
<span class="material-symbols-outlined text-[20px]">payments</span>
<span class="text-label-md font-label-md">Laporan Keuangan</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="settings.php">
<span class="material-symbols-outlined text-[20px]">settings</span>
<span class="text-label-md font-label-md">Settings</span>
</a>
</div>
<div class="flex-grow"></div>
<div class="flex flex-col gap-xs py-md">
<a class="flex items-center gap-sm px-md py-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all duration-200" href="../bantuan/bantuan.php">
<span class="material-symbols-outlined text-[20px]">help</span>
<span class="text-label-md font-label-md">Help Center</span>
</a>
<a class="flex items-center gap-sm px-md py-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl transition-all duration-200" href="logout_admin.php">
<span class="material-symbols-outlined text-[20px] text-rose-400">logout</span>
<span class="text-label-md font-label-md text-rose-400 font-bold">Logout</span>
</a>
</div>
</aside>

    <!-- Main Content Canvas -->
    <main class="flex-grow flex flex-col h-screen overflow-hidden">
        <!-- TopAppBar -->
        <header class="sticky top-0 w-full z-40 flex justify-between items-center px-lg py-md bg-white border-b border-slate-100 max-w-none">
            <div class="flex items-center gap-md flex-1">
                <div class="lg:hidden">
                    <span class="material-symbols-outlined text-on-surface-variant">menu</span>
                </div>
                <div class="text-headline-sm font-bold text-on-surface">Edit Mitra Laundry</div>
            </div>
            <div class="flex items-center gap-md">
                <div class="flex items-center gap-sm">
                    <p class="text-label-md font-bold leading-none hidden sm:block"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?></p>
                    <img alt="Admin profile" class="w-9 h-9 rounded-full object-cover border-2 border-slate-100 shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVvTfpl6gmSbn7utdVTjVT1ZrHaIbCt76OBU9jA9oc3rue19H1ElhbliNLU8FUVfCMZWMCOXO6ZI0EBlE68GvL7TdpDcdz05FrUqtzRUVrrTQKcC_MwtAKGFkV_XAbFOxIpl3JRF93_22IuQMGYGKqzXSHUZRnab8I7P_AWzrPQKLrh9PmQd4pqpbRW8v-5sKU_uUJt1jpvrX5bWXDDQshtNQtM9DcfB5GsKwZW-zFy6P6DnFBWUY_oCDubbBHW4BXb1p5RWiXyyg">
                </div>
            </div>
        </header>

        <!-- Scrollable Area -->
        <div class="flex-grow overflow-y-auto custom-scrollbar p-lg">
            <div class="max-w-5xl mx-auto space-y-lg">
                <!-- Back link & title -->
                <div class="flex items-center gap-xs">
                    <a href="manajemen_mitra.php" class="text-primary hover:underline flex items-center font-bold text-label-md gap-base">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        <span>Kembali ke Manajemen Mitra</span>
                    </a>
                </div>
                <div>
                    <h1 class="text-headline-lg font-headline-lg text-on-surface">Edit Profil Mitra</h1>
                    <p class="text-body-md text-on-surface-variant">Perbarui detail profil, operasional, dan lokasi fisik mitra laundry.</p>
                </div>

                <!-- Response Alert -->
                <?php if (!empty($error)): ?>
                    <div class="p-md bg-error-container text-on-error-container rounded-xl border border-error flex items-center gap-md shadow-sm">
                        <span class="material-symbols-outlined text-error text-2xl">error</span>
                        <div>
                            <p class="font-bold">Gagal Menyimpan Perubahan</p>
                            <p class="text-xs"><?= htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Grid -->
                <form action="edit_mitra.php?id=<?= $id; ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
                    <!-- Left and middle sections: Main Inputs -->
                    <div class="lg:col-span-2 space-y-lg">
                        <div class="bento-card p-xl rounded-xl space-y-md">
                            <h2 class="text-headline-sm font-bold text-primary flex items-center gap-xs">
                                <span class="material-symbols-outlined">storefront</span>
                                <span>Informasi Outlet</span>
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                                <div class="space-y-xs">
                                    <label for="nama_mitra" class="text-label-md font-bold text-on-surface-variant">Nama Mitra *</label>
                                    <input type="text" id="nama_mitra" name="nama_mitra" required value="<?= htmlspecialchars($mitra['nama_mitra']); ?>" placeholder="Contoh: KosanFresh Laundry" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                                <div class="space-y-xs">
                                    <label for="no_telp" class="text-label-md font-bold text-on-surface-variant">No. Telepon / WhatsApp</label>
                                    <input type="text" id="no_telp" name="no_telp" value="<?= htmlspecialchars($mitra['no_telp']); ?>" placeholder="Contoh: 081234567890" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                            </div>
                            <div class="space-y-xs">
                                <label for="alamat" class="text-label-md font-bold text-on-surface-variant">Alamat Lengkap *</label>
                                <textarea id="alamat" name="alamat" rows="3" required class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white"><?= htmlspecialchars($mitra['alamat']); ?></textarea>
                            </div>
                        </div>

                        <div class="bento-card p-xl rounded-xl space-y-md">
                            <h2 class="text-headline-sm font-bold text-primary flex items-center gap-xs">
                                <span class="material-symbols-outlined">local_laundry_service</span>
                                <span>Operasional &amp; Tarif</span>
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                                <div class="space-y-xs">
                                    <label for="icon_type" class="text-label-md font-bold text-on-surface-variant">Tipe Layanan *</label>
                                    <select id="icon_type" name="icon_type" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white cursor-pointer">
                                        <option value="kiloan" <?= $mitra['icon_type'] === 'kiloan' ? 'selected' : ''; ?>>Laundry Baju (Kiloan)</option>
                                        <option value="express" <?= $mitra['icon_type'] === 'express' ? 'selected' : ''; ?>>Laundry Kilat (Express)</option>
                                        <option value="eco" <?= $mitra['icon_type'] === 'eco' ? 'selected' : ''; ?>>Eco Friendly Wash</option>
                                        <option value="sepatu" <?= $mitra['icon_type'] === 'sepatu' ? 'selected' : ''; ?>>Cuci Sepatu &amp; Tas</option>
                                        <option value="satuan" <?= $mitra['icon_type'] === 'satuan' ? 'selected' : ''; ?>>Dry Cleaning &amp; Satuan</option>
                                    </select>
                                </div>
                                <div class="space-y-xs">
                                    <label for="harga_per_kg" class="text-label-md font-bold text-on-surface-variant">Harga per kg/pasang (Rp) *</label>
                                    <input type="number" id="harga_per_kg" name="harga_per_kg" required min="0" value="<?= htmlspecialchars($mitra['harga_per_kg']); ?>" placeholder="Contoh: 7000" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                                <div class="space-y-xs">
                                    <label for="rating" class="text-label-md font-bold text-on-surface-variant">Rating</label>
                                    <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" value="<?= htmlspecialchars($mitra['rating']); ?>" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                                <div class="space-y-xs">
                                    <label for="jam_buka" class="text-label-md font-bold text-on-surface-variant">Jam Operasional</label>
                                    <textarea id="jam_buka" name="jam_buka" rows="3" placeholder="Contoh:&#10;Senin - Sabtu: 07:00 - 21:00&#10;Minggu: 08:00 - 18:00" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white resize-none"><?= htmlspecialchars($mitra['jam_buka']); ?></textarea>
                                </div>
                                <div class="flex flex-col gap-sm pt-xs justify-center">
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="status_buka" value="1" <?= $mitra['status_buka'] == 1 ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        <span class="ml-3 text-label-md font-bold text-on-surface-variant">Status: Buka &amp; Aktif</span>
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="is_rekomendasi" value="1" <?= $mitra['is_rekomendasi'] == 1 ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        <span class="ml-3 text-label-md font-bold text-on-surface-variant">Tampilkan di Rekomendasi Beranda</span>
                                    </label>
                            </div>
                        </div>

                        <!-- Custom Pricing Bento Card -->
                        <div class="bento-card p-xl rounded-xl space-y-md">
                            <h2 class="text-headline-sm font-bold text-primary flex items-center gap-xs">
                                <span class="material-symbols-outlined">payments</span>
                                <span>Tarif Kustom Layanan (Opsional)</span>
                            </h2>
                            <p class="text-xs text-on-surface-variant">Kustomisasi tarif jika ingin berbeda dari tarif kelipatan dasar sistem.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                                <div class="space-y-xs">
                                    <label for="harga_pengeringan" class="text-label-md font-bold text-on-surface-variant">Harga Pengeringan (Rp)</label>
                                    <input type="number" id="harga_pengeringan" name="harga_pengeringan" value="<?= htmlspecialchars($harga_pengeringan); ?>" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                                <div class="space-y-xs">
                                    <label for="harga_setrika_reguler" class="text-label-md font-bold text-on-surface-variant">Harga Setrika Reguler (Rp)</label>
                                    <input type="number" id="harga_setrika_reguler" name="harga_setrika_reguler" value="<?= htmlspecialchars($harga_setrika_reguler); ?>" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                                <div class="space-y-xs">
                                    <label for="harga_setrika_saja" class="text-label-md font-bold text-on-surface-variant">Harga Setrika Saja (Rp)</label>
                                    <input type="number" id="harga_setrika_saja" name="harga_setrika_saja" value="<?= htmlspecialchars($harga_setrika_saja); ?>" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                                <div class="space-y-xs">
                                    <label for="harga_satuan_jaket" class="text-label-md font-bold text-on-surface-variant">Harga Satuan Jaket (Rp)</label>
                                    <input type="number" id="harga_satuan_jaket" name="harga_satuan_jaket" value="<?= htmlspecialchars($harga_satuan_jaket); ?>" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                                <div class="space-y-xs">
                                    <label for="harga_satuan_selimut" class="text-label-md font-bold text-on-surface-variant">Harga Satuan Selimut (Rp)</label>
                                    <input type="number" id="harga_satuan_selimut" name="harga_satuan_selimut" value="<?= htmlspecialchars($harga_satuan_selimut); ?>" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                                <div class="space-y-xs">
                                    <label for="harga_satuan_bed_cover" class="text-label-md font-bold text-on-surface-variant">Harga Satuan Bed Cover (Rp)</label>
                                    <input type="number" id="harga_satuan_bed_cover" name="harga_satuan_bed_cover" value="<?= htmlspecialchars($harga_satuan_bed_cover); ?>" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                            </div>
                        </div>

                        <div class="bento-card p-xl rounded-xl space-y-md">
                            <h2 class="text-headline-sm font-bold text-primary flex items-center gap-xs">
                                <span class="material-symbols-outlined">map</span>
                                <span>Koordinat Geografis (Peta) *</span>
                            </h2>
                            <p class="text-xs text-on-surface-variant">Koordinat sangat penting untuk pencarian laundry terdekat berdasarkan jarak.</p>
                            <div class="space-y-xs">
                                <label for="google_maps_link" class="text-label-md font-bold text-on-surface-variant">Link Google Maps (Opsional)</label>
                                <input type="url" id="google_maps_link" name="google_maps_link" placeholder="https://www.google.com/maps/place/..." class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                <div id="link_info" class="text-[11px] font-semibold text-outline">Tempel link Google Maps di sini untuk mengisi koordinat secara otomatis.</div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                                <div class="space-y-xs">
                                    <label for="latitude" class="text-label-md font-bold text-on-surface-variant">Latitude *</label>
                                    <input type="text" id="latitude" name="latitude" required value="<?= htmlspecialchars($mitra['latitude']); ?>" placeholder="-8.590000" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                                <div class="space-y-xs">
                                    <label for="longitude" class="text-label-md font-bold text-on-surface-variant">Longitude *</label>
                                    <input type="text" id="longitude" name="longitude" required value="<?= htmlspecialchars($mitra['longitude']); ?>" placeholder="116.100000" class="w-full rounded-xl border-outline-variant focus:ring-primary focus:border-primary text-body-md py-2.5 px-md bg-white">
                                </div>
                            </div>
                            <div class="flex justify-end pt-xs">
                                <button type="button" onclick="generateSimulatedCoords()" class="text-primary font-bold text-label-md hover:underline flex items-center gap-xs cursor-pointer select-none">
                                    <span class="material-symbols-outlined text-[18px]">my_location</span>
                                    <span>Simulasikan Koordinat (Mataram)</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right section: Image upload & Actions -->
                    <div class="space-y-lg">
                        <!-- Image Upload Card -->
                        <div class="bento-card p-xl rounded-xl space-y-md">
                            <h2 class="text-headline-sm font-bold text-primary flex items-center gap-xs">
                                <span class="material-symbols-outlined">image</span>
                                <span>Foto Toko</span>
                            </h2>
                            <div class="space-y-md">
                                <div class="border-2 border-dashed border-outline-variant rounded-2xl p-md text-center flex flex-col items-center justify-center bg-slate-50 cursor-pointer relative min-h-[220px] transition-colors hover:bg-slate-100/50" onclick="document.getElementById('foto_toko').click()">
                                    <input type="file" id="foto_toko" name="foto_toko" accept="image/*" class="hidden" onchange="previewImage(event)">
                                    
                                    <!-- Initial preview showing current photo -->
                                    <div id="upload-placeholder" class="space-y-xs <?= !empty($mitra['foto_toko']) ? 'hidden' : ''; ?>">
                                        <span class="material-symbols-outlined text-outline text-4xl">cloud_upload</span>
                                        <p class="text-label-md font-bold text-on-surface-variant">Pilih foto toko baru</p>
                                        <p class="text-[10px] text-outline">JPG, PNG, atau WEBP (Maks. 2MB)</p>
                                    </div>
                                    <img id="image-preview" class="absolute inset-0 w-full h-full object-cover rounded-2xl <?= empty($mitra['foto_toko']) ? 'hidden' : ''; ?>" src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="Preview Foto">
                                </div>
                                <button type="button" id="remove-preview-btn" onclick="removePreview(event)" class="w-full py-xs border border-error text-error rounded-xl text-label-md font-bold hover:bg-error-container/10 transition-all <?= empty($mitra['foto_toko']) ? 'hidden' : ''; ?>">Hapus Foto</button>
                            </div>
                        </div>

                        <!-- Actions Card -->
                        <div class="bento-card p-xl rounded-xl space-y-md bg-surface-container-low/50">
                            <button type="submit" class="w-full bg-primary text-on-primary py-3 rounded-xl font-bold flex items-center justify-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all cursor-pointer">
                                <span class="material-symbols-outlined">save</span>
                                <span>Simpan Perubahan</span>
                            </button>
                            <a href="manajemen_mitra.php" class="w-full border border-outline-variant bg-white text-on-surface-variant py-3 rounded-xl font-bold flex items-center justify-center gap-sm hover:bg-surface-container-high active:scale-95 transition-all text-center">
                                <span class="material-symbols-outlined">close</span>
                                <span>Batal</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Image preview functionality
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            const removeBtn = document.getElementById('remove-preview-btn');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    removeBtn.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removePreview(event) {
            event.stopPropagation();
            const input = document.getElementById('foto_toko');
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            const removeBtn = document.getElementById('remove-preview-btn');

            input.value = '';
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            removeBtn.classList.add('hidden');
        }

        // Simulate Mataram Geolocation coordinates
        function generateSimulatedCoords() {
            // Mataram center is roughly -8.59, 116.10
            // Random variation of +/- 0.015
            const lat = (-8.59 + (Math.random() - 0.5) * 0.03).toFixed(6);
            const lng = (116.10 + (Math.random() - 0.5) * 0.03).toFixed(6);

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }

        // Live Google Maps link parsing
        const mapsLinkInput = document.getElementById('google_maps_link');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const linkInfo = document.getElementById('link_info');

        if (mapsLinkInput) {
            mapsLinkInput.addEventListener('input', function() {
                const url = this.value.trim();
                if (!url) {
                    latInput.readOnly = false;
                    lngInput.readOnly = false;
                    latInput.classList.remove('bg-slate-100');
                    lngInput.classList.remove('bg-slate-100');
                    linkInfo.textContent = 'Tempel link Google Maps di sini untuk mengisi koordinat secara otomatis.';
                    linkInfo.className = 'text-[11px] font-semibold text-outline';
                    return;
                }
                
                // Set to readonly to indicate auto-calculated values
                latInput.readOnly = true;
                lngInput.readOnly = true;
                latInput.classList.add('bg-slate-100');
                lngInput.classList.add('bg-slate-100');
                
                // Regex for @lat,lng
                let match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
                if (match) {
                    latInput.value = match[1];
                    lngInput.value = match[2];
                    linkInfo.className = 'text-[11px] font-bold text-emerald-600 flex items-center gap-xs mt-xs';
                    linkInfo.innerHTML = '<span class="material-symbols-outlined text-[14px]">check_circle</span> Koordinat berhasil diekstrak!';
                    return;
                }
                
                // Regex for q=lat,lng
                match = url.match(/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/);
                if (match) {
                    latInput.value = match[1];
                    lngInput.value = match[2];
                    linkInfo.className = 'text-[11px] font-bold text-emerald-600 flex items-center gap-xs mt-xs';
                    linkInfo.innerHTML = '<span class="material-symbols-outlined text-[14px]">check_circle</span> Koordinat berhasil diekstrak!';
                    return;
                }
                
                // If short link
                if (url.includes('maps.app.goo.gl') || url.includes('g.co')) {
                    linkInfo.className = 'text-[11px] font-bold text-amber-600 flex items-center gap-xs mt-xs';
                    linkInfo.innerHTML = '<span class="material-symbols-outlined text-[14px]">info</span> Link pendek terdeteksi. Koordinat akan diekstrak otomatis saat disimpan.';
                    return;
                }
                
                // If unrecognized format, allow manual edits as fallback
                latInput.readOnly = false;
                lngInput.readOnly = false;
                latInput.classList.remove('bg-slate-100');
                lngInput.classList.remove('bg-slate-100');
                linkInfo.className = 'text-[11px] font-bold text-error flex items-center gap-xs mt-xs';
                linkInfo.innerHTML = '<span class="material-symbols-outlined text-[14px]">error</span> Format link tidak dikenali. Silakan isi koordinat secara manual.';
            });
        }
    </script>
</body>
</html>
