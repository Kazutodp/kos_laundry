<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

require_once '../db_connect.php';

// Handle AJAX toggle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    header('Content-Type: application/json');
    if (!isset($_POST['id']) || !isset($_POST['field']) || !isset($_POST['value'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }
    $id = (int)$_POST['id'];
    $field = $_POST['field']; // 'status_buka' or 'is_rekomendasi'
    $value = (int)$_POST['value'] ? 1 : 0;

    if ($field !== 'status_buka' && $field !== 'is_rekomendasi') {
        echo json_encode(['success' => false, 'message' => 'Invalid field']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE mitra_laundry SET $field = ? WHERE id = ?");
        $stmt->execute([$value, $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

$success_message = '';
$error_message = '';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Fetch partner details first to get the template name
        $stmt = $pdo->prepare("SELECT nama_mitra FROM mitra_laundry WHERE id = ?");
        $stmt->execute([$id]);
        $mitra = $stmt->fetch();
        
        if ($mitra) {
            $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
            $file_path = '../Mitra laundry/' . $file_name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $del_stmt = $pdo->prepare("DELETE FROM mitra_laundry WHERE id = ?");
            $del_stmt->execute([$id]);
            
            header("Location: manajemen_mitra.php?status=deleted");
            exit();
        } else {
            $error_message = 'Mitra tidak ditemukan.';
        }
    } catch (PDOException $e) {
        $error_message = 'Gagal menghapus mitra: ' . $e->getMessage();
    }
}

if (isset($_GET['status']) && $_GET['status'] === 'deleted') {
    $success_message = 'Mitra laundry berhasil dihapus dari sistem.';
} elseif (isset($_GET['status']) && $_GET['status'] === 'updated') {
    $success_message = 'Data mitra laundry berhasil diperbarui!';
} elseif (isset($_GET['status']) && $_GET['status'] === 'added') {
    $success_message = 'Mitra laundry baru berhasil ditambahkan!';
}

// Fetch all partners
try {
    $stmt = $pdo->query("SELECT * FROM mitra_laundry ORDER BY created_at DESC");
    $raw_mitras = $stmt->fetchAll();
    
    // Filter to only include active partners whose files exist
    $all_mitras = [];
    foreach ($raw_mitras as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../Mitra laundry/' . $file_name)) {
            $all_mitras[] = $mitra;
        }
    }
} catch (PDOException $e) {
    $all_mitras = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Manajemen Mitra | MataramWash Admin</title>
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
<img alt="MataramWash Logo" class="h-8 w-8 object-contain brightness-110 filter" src="../Logo_MataramWash.png?v=3">
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
                <div class="text-headline-sm font-bold text-on-surface">Manajemen Mitra</div>
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
            <div class="max-w-7xl mx-auto space-y-lg">
                
                <!-- Breadcrumbs & Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-md">
                    <div>
                        <h1 class="text-headline-lg font-headline-lg text-on-surface">Daftar Mitra Laundry</h1>
                        <p class="text-body-md text-on-surface-variant">Kelola status, wilayah, rekomendasi, dan data lengkap seluruh mitra aktif.</p>
                    </div>
                    <a href="tambah_mitra.php" class="bg-primary text-on-primary px-lg py-sm rounded-xl font-bold flex items-center gap-sm shadow-md hover:brightness-110 active:scale-95 transition-all w-fit">
                        <span class="material-symbols-outlined">person_add</span>
                        Tambah Mitra Baru
                    </a>
                </div>

                <!-- Response Alert Messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="p-md bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-center gap-md shadow-sm">
                        <span class="material-symbols-outlined text-emerald-600 text-2xl">check_circle</span>
                        <p class="text-body-md font-medium"><?= htmlspecialchars($success_message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="p-md bg-error-container text-on-error-container rounded-xl border border-error flex items-center gap-md shadow-sm">
                        <span class="material-symbols-outlined text-error text-2xl">error</span>
                        <p class="text-body-md font-medium"><?= htmlspecialchars($error_message); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Table Bento Card -->
                <div class="bento-card rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low border-b border-outline-variant">
                                    <th class="pl-md pr-1 py-2.5 text-[11px] text-slate-700 font-semibold uppercase tracking-wide">No</th>
                                    <th class="px-md py-2.5 text-[11px] text-slate-700 font-semibold uppercase tracking-wide">Mitra</th>
                                    <th class="px-md py-2.5 text-[11px] text-slate-700 font-semibold uppercase tracking-wide">Kontak & Alamat</th>
                                    <th class="px-md py-2.5 text-[11px] text-slate-700 font-semibold uppercase tracking-wide">Layanan & Tarif</th>
                                    <th class="px-md py-2.5 text-[11px] text-slate-700 font-semibold uppercase tracking-wide">Status & Rekomendasi</th>
                                    <th class="px-md py-2.5 text-[11px] text-slate-700 font-semibold uppercase tracking-wide text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                <?php if (empty($all_mitras)): ?>
                                    <tr>
                                        <td colspan="6" class="px-md py-12 text-center text-on-surface-variant">
                                            <span class="material-symbols-outlined text-outline text-[48px] mb-2">storefront_off</span>
                                            <p class="text-body-md font-semibold">Belum ada mitra laundry yang terdaftar</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($all_mitras as $mitra): ?>
                                        <tr class="hover:bg-surface-container-low transition-colors">
                                            <!-- Column 0: No -->
                                            <td class="pl-md pr-1 py-sm text-[12px] text-slate-500 font-medium text-center"><?= $no++; ?></td>
                                            <!-- Column 1: Photo and Name -->
                                            <td class="px-md py-sm">
                                                <div class="flex items-center gap-sm">
                                                    <img src="../<?= htmlspecialchars($mitra['foto_toko']); ?>" alt="<?= htmlspecialchars($mitra['nama_mitra']); ?>" class="w-10 h-10 rounded-lg object-cover border border-outline-variant shrink-0">
                                                    <div>
                                                        <p class="text-[13px] font-bold text-on-surface leading-tight"><?= htmlspecialchars($mitra['nama_mitra']); ?></p>
                                                        <span class="inline-flex items-center gap-[2px] px-1.5 py-[1px] bg-amber-50 text-amber-700 border border-amber-200 rounded text-[10px] font-bold mt-0.5">
                                                            <span class="material-symbols-outlined text-[10px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                                            <span><?= htmlspecialchars($mitra['rating']); ?></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Column 2: Contact Info -->
                                            <td class="px-md py-sm">
                                                <div class="space-y-0.5">
                                                    <p class="flex items-center gap-xs text-[12px] font-medium text-on-surface">
                                                        <span class="material-symbols-outlined text-[14px] text-primary">call</span>
                                                        <span><?= htmlspecialchars($mitra['no_telp']); ?></span>
                                                    </p>
                                                    <p class="flex items-center gap-xs max-w-[220px] text-[12px] text-slate-500" title="<?= htmlspecialchars($mitra['alamat']); ?>">
                                                        <span class="material-symbols-outlined text-[14px] text-slate-400 shrink-0">location_on</span>
                                                        <span class="truncate"><?= htmlspecialchars($mitra['alamat']); ?></span>
                                                    </p>
                                                </div>
                                            </td>
                                            <!-- Column 3: Services & Rates -->
                                            <td class="px-md py-sm">
                                                <div class="space-y-0.5">
                                                    <p class="flex items-center gap-xs text-[12px] font-semibold text-on-surface">
                                                        <span class="material-symbols-outlined text-[14px] text-secondary">
                                                            <?= $mitra['icon_type'] === 'sepatu' ? 'footprint' : ($mitra['icon_type'] === 'express' ? 'bolt' : 'local_laundry_service'); ?>
                                                        </span>
                                                        <span>
                                                            <?php 
                                                            if ($mitra['icon_type'] === 'sepatu') {
                                                                echo 'Special Shoe';
                                                            } elseif ($mitra['icon_type'] === 'express') {
                                                                echo 'Layanan Express';
                                                            } else {
                                                                echo 'Kiloan & Satuan';
                                                            }
                                                            ?>
                                                        </span>
                                                    </p>
                                                    <p class="text-[12px] font-bold text-primary pl-[18px]">
                                                        Rp <?= number_format($mitra['harga_per_kg'], 0, ',', '.'); ?><span class="text-slate-400 font-normal">/kg</span>
                                                    </p>
                                                </div>
                                            </td>
                                            <!-- Column 4: Status & Settings -->
                                            <td class="px-md py-sm">
                                                <div class="space-y-1">
                                                    <!-- Toggle Status Buka -->
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" onchange="toggleMitra(<?= $mitra['id']; ?>, 'status_buka', this.checked)" <?= $mitra['status_buka'] == 1 ? 'checked' : ''; ?> class="sr-only peer">
                                                        <div class="w-8 h-[18px] bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-[14px] after:w-[14px] after:transition-all peer-checked:bg-primary"></div>
                                                        <span class="ml-2 text-[11px] font-medium text-slate-600">Buka / Aktif</span>
                                                    </label>
                                                    
                                                    <!-- Toggle Rekomendasi -->
                                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" onchange="toggleMitra(<?= $mitra['id']; ?>, 'is_rekomendasi', this.checked)" <?= $mitra['is_rekomendasi'] == 1 ? 'checked' : ''; ?> class="sr-only peer">
                                                        <div class="w-8 h-[18px] bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-[14px] after:w-[14px] after:transition-all peer-checked:bg-emerald-500"></div>
                                                        <span class="ml-2 text-[11px] font-medium text-slate-600">Rekomendasi</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <!-- Column 5: Actions -->
                                            <td class="px-md py-sm text-center">
                                                <div class="flex items-center justify-center gap-xs">
                                                    <a href="edit_mitra.php?id=<?= $mitra['id']; ?>" title="Edit Mitra" class="p-1.5 border border-outline-variant text-primary hover:bg-blue-50 hover:border-primary rounded-lg transition-all active:scale-95 flex items-center justify-center">
                                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                                    </a>
                                                    <button onclick="confirmDelete(<?= $mitra['id']; ?>, '<?= htmlspecialchars($mitra['nama_mitra'], ENT_QUOTES); ?>')" title="Hapus Mitra" class="p-1.5 border border-outline-variant text-error hover:bg-rose-50 hover:border-error rounded-lg transition-all active:scale-95 flex items-center justify-center">
                                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <!-- Footer -->
<footer class="w-full py-md px-lg bg-slate-50 border-t border-slate-100 flex justify-between items-center text-slate-400">
<p class="text-label-sm">© 2024 MataramWash Provincial Partnership Program. Freshness across the region.</p>
<div class="flex gap-lg">
<a class="text-label-sm hover:text-blue-600 transition-colors" href="../bantuan/bantuan.php">Pusat Bantuan</a>
<a class="text-label-sm hover:text-blue-600 transition-colors" href="#">Kebijakan Kemitraan</a>
</div>
</footer>
    </main>

    <script>
        function confirmDelete(id, name) {
            if (confirm(`Apakah Anda yakin ingin menghapus mitra "${name}"?\nTindakan ini akan menghapus data dari database dan menghapus file detail template.`)) {
                window.location.href = `manajemen_mitra.php?action=delete&id=${id}`;
            }
        }

        function toggleMitra(id, field, isChecked) {
            const val = isChecked ? 1 : 0;
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('id', id);
            formData.append('field', field);
            formData.append('value', val);

            fetch('manajemen_mitra.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memperbarui status: ' + (data.message || 'Error tidak diketahui'));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi server.');
                location.reload();
            });
        }
    </script>
</body>
</html>
