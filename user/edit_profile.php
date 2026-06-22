<?php
session_start();
require_once '../db_connect.php';

// Cek apakah user sudah login, jika belum arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $telp = trim($_POST['telp'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $hapus_foto = $_POST['hapus_foto'] ?? '0';
    
    if (empty($nama)) {
        $error = 'Nama Lengkap wajib diisi.';
    } else {
        try {
            // Ambil path foto profil saat ini dari DB
            $stmt = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_current = $stmt->fetch();
            $foto_path = $user_current['foto_profil'] ?? null;
            
            // Proses hapus foto profil jika ditrigger
            if ($hapus_foto === '1') {
                if (!empty($user_current['foto_profil'])) {
                    $old_file = '../' . $user_current['foto_profil'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $foto_path = null;
            }
            
            // Proses upload foto profil baru (baik via Cropped Base64 atau Fallback File Upload)
            $cropped_data = $_POST['cropped_image_data'] ?? '';
            if (!empty($cropped_data)) {
                if (preg_match('/^data:image\/(\w+);base64,/', $cropped_data, $type)) {
                    $data = substr($cropped_data, strpos($cropped_data, ',') + 1);
                    $type = strtolower($type[1]); // png, jpeg, etc.
                    
                    if (in_array($type, ['jpg', 'jpeg', 'png'])) {
                        $data = base64_decode($data);
                        
                        if ($data !== false) {
                            $upload_dir = '../uploads/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            
                            $new_file_name = 'avatar_' . $user_id . '_' . time() . '.' . $type;
                            $dest_path = $upload_dir . $new_file_name;
                            
                            if (file_put_contents($dest_path, $data)) {
                                // Hapus file lama jika ada
                                if (!empty($user_current['foto_profil'])) {
                                    $old_file = '../' . $user_current['foto_profil'];
                                    if (file_exists($old_file)) {
                                        unlink($old_file);
                                    }
                                }
                                $foto_path = 'uploads/' . $new_file_name;
                            } else {
                                $error = 'Gagal menyimpan gambar hasil potong ke server.';
                            }
                        } else {
                            $error = 'Dekode base64 gambar gagal.';
                        }
                    } else {
                        $error = 'Format gambar hasil potong tidak didukung. Hanya JPG, JPEG, dan PNG.';
                    }
                } else {
                    $error = 'Format data gambar hasil potong tidak valid.';
                }
            } elseif (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
                // Fallback jika JavaScript cropper bermasalah (normal upload)
                $file_tmp = $_FILES['foto_profil']['tmp_name'];
                $file_name = $_FILES['foto_profil']['name'];
                $file_size = $_FILES['foto_profil']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                
                if (!in_array($file_ext, $allowed_extensions)) {
                    $error = 'Ekstensi file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.';
                } elseif ($file_size > 2 * 1024 * 1024) {
                    $error = 'Ukuran file terlalu besar. Maksimal 2MB.';
                } else {
                    $upload_dir = '../uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $new_file_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
                    $dest_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        if (!empty($user_current['foto_profil'])) {
                            $old_file = '../' . $user_current['foto_profil'];
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                        $foto_path = 'uploads/' . $new_file_name;
                    } else {
                        $error = 'Gagal mengunggah gambar ke server.';
                    }
                }
            }
            
            if (empty($error)) {
                // Update database
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, foto_profil = ?, no_telp = ?, jenis_kelamin = ?, alamat = ? WHERE id = ?");
                $stmt->execute([$nama, $foto_path, $telp, $gender, $alamat, $user_id]);
                
                // Update session
                $_SESSION['username'] = $nama;
                $_SESSION['profile_pic'] = $foto_path;
                
                $success = 'Profil Anda berhasil diperbarui!';
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }
}

// Ambil data user terbaru dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // Simpan ke session untuk memastikan data selalu sinkron
    $_SESSION['username'] = $user['nama'];
    $_SESSION['profile_pic'] = $user['foto_profil'];
} catch (PDOException $e) {
    $error = 'Gagal mengambil data profil: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Edit Profil | MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <!-- Cropper.js CSS & JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "tertiary-fixed": "#ffddb8",
                    "error": "#ba1a1a",
                    "surface-bright": "#f9f9ff",
                    "primary-container": "#2170e4",
                    "on-primary-container": "#fefcff",
                    "on-secondary-fixed-variant": "#005048",
                    "on-primary-fixed": "#001a42",
                    "on-error": "#ffffff",
                    "on-secondary-container": "#006f64",
                    "secondary-fixed": "#71f8e4",
                    "primary-fixed-dim": "#adc6ff",
                    "tertiary-fixed-dim": "#ffb95f",
                    "surface": "#f9f9ff",
                    "on-primary": "#ffffff",
                    "background": "#f9f9ff",
                    "tertiary": "#825100",
                    "on-error-container": "#93000a",
                    "surface-dim": "#d3daea",
                    "secondary-fixed-dim": "#4fdbc8",
                    "on-tertiary-fixed": "#2a1700",
                    "error-container": "#ffdad6",
                    "surface-variant": "#dce2f3",
                    "surface-container-highest": "#dce2f3",
                    "primary": "#0058be",
                    "surface-container-lowest": "#ffffff",
                    "outline": "#727785",
                    "on-secondary-fixed": "#00201c",
                    "on-secondary": "#ffffff",
                    "on-background": "#151c27",
                    "secondary": "#006b5f",
                    "on-tertiary": "#ffffff",
                    "surface-container-low": "#f0f3ff",
                    "primary-fixed": "#d8e2ff",
                    "outline-variant": "#c2c6d6",
                    "surface-container-high": "#e2e8f8",
                    "on-tertiary-fixed-variant": "#653e00",
                    "on-tertiary-container": "#fffbff",
                    "on-surface-variant": "#424754",
                    "inverse-primary": "#adc6ff",
                    "inverse-on-surface": "#ebf1ff",
                    "on-surface": "#151c27",
                    "inverse-surface": "#2a313d",
                    "tertiary-container": "#a36700",
                    "surface-container": "#e7eefe",
                    "secondary-container": "#6df5e1",
                    "on-primary-fixed-variant": "#004395",
                    "surface-tint": "#005ac2"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "md": "16px",
                    "xs": "8px",
                    "gutter": "16px",
                    "container-margin": "20px",
                    "base": "4px",
                    "sm": "12px",
                    "xl": "32px",
                    "lg": "24px"
            },
            "fontFamily": {
                    "display-lg": ["Inter"],
                    "body-md": ["Inter"],
                    "label-md": ["Inter"],
                    "headline-lg": ["Inter"],
                    "body-lg": ["Inter"],
                    "headline-md": ["Inter"],
                    "label-sm": ["Inter"],
                    "headline-lg-mobile": ["Inter"]
            },
            "fontSize": {
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}]
            }
          },
        },
      }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9f9ff; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-shadow { box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05); }
        .input-focus-ring:focus { border-color: #0058be; ring: 2px; ring-color: #0058be; }
        .transition-standard { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="text-on-background bg-background">

<!-- Mini Header for Brand -->
<header class="w-full bg-surface-container shadow-sm py-4 px-6 border-b border-outline-variant/30 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <a class="flex items-center space-x-xs text-headline-md font-headline-md font-bold text-primary" href="../index.php">
            <img alt="MataramWash Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
            <span class="text-lg">MataramWash</span>
        </a>
        <a class="flex items-center space-x-1 text-label-md font-bold text-primary hover:underline" href="../index.php">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
</header>

<!-- TopNavBar Shell -->
<main class="max-w-7xl mx-auto px-6 py-12 pt-10">
<div class="flex flex-col lg:flex-row gap-8">
<!-- Sidebar Navigation -->
<aside class="w-full lg:w-72 flex-shrink-0">
<div class="bg-surface-container-lowest rounded-xl p-4 custom-shadow border border-outline-variant/30 sticky top-28">
<div class="flex flex-col space-y-1">
<a class="flex items-center space-x-3 px-4 py-3 bg-primary-container text-on-primary-container rounded-lg font-bold transition-standard" href="edit_profile.php">
<span class="material-symbols-outlined">person</span>
<span class="text-label-md">Personal Info</span>
</a>
<a class="flex items-center space-x-3 px-4 py-3 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-standard" href="security.php">
<span class="material-symbols-outlined">lock</span>
<span class="text-label-md">Password &amp; Keamanan</span>
</a>
</div>
</div>
</aside>
<!-- Main Form Content -->
<section class="flex-grow">
<div class="bg-surface-container-lowest rounded-xl custom-shadow border border-outline-variant/30 overflow-hidden">
<!-- Header Form -->
<div class="p-8 border-b border-outline-variant/20">
<h1 class="text-headline-md font-headline-md text-on-surface mb-2">Edit Profil</h1>
<p class="text-body-md font-body-md text-on-surface-variant">Perbarui informasi pribadi dan alamat kos Anda untuk mempermudah penjemputan.</p>
</div>
<div class="p-8">

<?php if (!empty($error)): ?>
    <div class="p-md bg-error-container text-on-error-container rounded-xl flex items-center gap-xs font-label-sm border border-error mb-md">
        <span class="material-symbols-outlined text-error">error</span>
        <span><?= htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="p-md bg-secondary-container text-on-secondary-container rounded-xl flex items-center gap-xs font-label-sm border border-secondary mb-md">
        <span class="material-symbols-outlined text-secondary">check_circle</span>
        <span><?= htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<form class="space-y-10" action="edit_profile.php" method="POST" enctype="multipart/form-data">
<!-- Hidden deletion input -->
<input type="hidden" id="hapus-foto-input" name="hapus_foto" value="0">
<input type="hidden" id="cropped-image-data" name="cropped_image_data" value="">

<!-- Avatar Upload Section -->
<div class="flex flex-col items-center sm:flex-row sm:items-end space-y-4 sm:space-y-0 sm:space-x-8">
<div class="relative group">
<div class="w-32 h-32 rounded-full overflow-hidden border-4 border-surface-container-high custom-shadow bg-primary text-on-primary flex items-center justify-center font-bold text-4xl select-none" id="avatar-container">
    <?php if (!empty($user['foto_profil'])): ?>
        <img alt="User Avatar" id="avatar-img" class="w-full h-full object-cover" src="../<?= htmlspecialchars($user['foto_profil']); ?>">
    <?php else: ?>
        <span id="avatar-initial"><?= strtoupper(substr($user['nama'], 0, 1)); ?></span>
    <?php endif; ?>
</div>
<button onclick="triggerUpload()" class="absolute bottom-1 right-1 bg-primary text-white p-2 rounded-full shadow-lg hover:scale-105 transition-transform" type="button">
<span class="material-symbols-outlined text-sm">photo_camera</span>
</button>
<input type="file" id="foto-profil-input" name="foto_profil" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewImage(event)">
</div>
<div class="text-center sm:text-left">
<h3 class="text-label-md font-bold text-on-surface">Foto Profil</h3>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">JPG atau PNG, Maksimal 2MB.</p>
<div class="mt-3 flex space-x-3">
<button onclick="triggerUpload()" class="text-label-sm font-bold text-primary hover:underline" type="button">Ganti Foto</button>
<button onclick="deletePhoto()" class="text-label-sm font-bold text-error hover:underline" type="button">Hapus</button>
</div>
</div>
</div>
<!-- Personal Information Section -->
<div>
<div class="flex items-center space-x-2 mb-6">
<span class="material-symbols-outlined text-primary">account_circle</span>
<h2 class="text-headline-sm font-headline-md text-on-surface">Informasi Pribadi</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant">Nama Lengkap</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">person</span>
<input class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" type="text" name="nama" required value="<?= htmlspecialchars($user['nama']); ?>">
</div>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant flex items-center justify-between w-full">
    <span>Email</span>
    <span class="inline-flex items-center gap-0.5 bg-secondary-container/20 text-secondary text-[11px] font-bold px-2 py-0.5 rounded-full select-none">
        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">verified</span>
        Terverifikasi <?= !empty($user['google_id']) ? '(Google)' : '' ?>
    </span>
</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">mail</span>
<input class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface-container-high text-on-surface-variant/70 cursor-not-allowed text-body-md" disabled="" type="email" value="<?= htmlspecialchars($user['email']); ?>">
</div>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant flex items-center justify-between w-full">
    <span>Nomor Telepon</span>
    <?php if (!empty($user['no_telp'])): ?>
    <span class="inline-flex items-center gap-0.5 bg-secondary-container/20 text-secondary text-[11px] font-bold px-2 py-0.5 rounded-full select-none">
        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">verified</span>
        Terverifikasi
    </span>
    <?php else: ?>
    <span class="inline-flex items-center gap-0.5 bg-surface-container-high text-outline text-[11px] font-bold px-2 py-0.5 rounded-full select-none">
        <span class="material-symbols-outlined text-[14px]">error</span>
        Belum Diisi
    </span>
    <?php endif; ?>
</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">call</span>
<input class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" type="tel" name="telp" value="<?= htmlspecialchars($user['no_telp'] ?? ''); ?>">
</div>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant">Jenis Kelamin</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">wc</span>
<select name="gender" class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none appearance-none transition-standard text-body-md">
<option value="Laki-laki" <?= ($user['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
<option value="Perempuan" <?= ($user['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
<option value="Lainnya" <?= ($user['jenis_kelamin'] ?? '') === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">expand_more</span>
</div>
</div>
</div>
</div>
<!-- Address Section -->
<div class="pt-6">
<div class="flex items-center space-x-2 mb-6">
<span class="material-symbols-outlined text-primary">location_on</span>
<h2 class="text-headline-sm font-headline-md text-on-surface">Informasi Alamat Kos</h2>
</div>
<div class="space-y-6">

<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant">Alamat Lengkap</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-4 text-on-surface-variant">map</span>
<textarea name="alamat" class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" placeholder="Masukkan alamat lengkap penjemputan..." rows="3"><?= htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
</div>
</div>
</div>
</div>
<!-- Form Actions -->
<div class="flex flex-col sm:flex-row justify-end items-center space-y-4 sm:space-y-0 sm:space-x-4 pt-10 border-t border-outline-variant/20">
<button onclick="window.location.href='../index.php'" class="w-full sm:w-auto px-8 py-3 rounded-xl text-label-md font-bold text-on-surface-variant hover:bg-surface-container-high transition-standard" type="button">
                                    Batal
                                </button>
<button class="w-full sm:w-auto px-10 py-3 rounded-xl bg-primary text-white text-label-md font-bold custom-shadow hover:scale-[1.02] active:scale-95 transition-standard" type="submit">
                                    Simpan Perubahan
                                </button>
</div>
</form>
</div>
</div>
</section>
</div>
</main>

<script>
        function triggerUpload() {
            document.getElementById('foto-profil-input').click();
        }

        function deletePhoto() {
            if (confirm("Apakah Anda yakin ingin menghapus foto profil?")) {
                document.getElementById('hapus-foto-input').value = "1";
                // Submit form langsung untuk menghapus foto
                document.querySelector('form').submit();
            }
        }

        let cropper;

        function previewImage(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const modal = document.getElementById('cropper-modal');
                    const image = document.getElementById('cropper-image');
                    image.src = e.target.result;
                    
                    modal.classList.remove('hidden');
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    setTimeout(() => {
                        cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.8,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    }, 100);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function closeCropperModal() {
            document.getElementById('cropper-modal').classList.add('hidden');
            document.getElementById('foto-profil-input').value = ""; // Clear input to trigger event next time
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }

        function applyCrop() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300
                });
                const dataURL = canvas.toDataURL('image/jpeg', 0.9);
                
                // Set the hidden input value
                document.getElementById('cropped-image-data').value = dataURL;
                
                // Update avatar container preview
                const container = document.getElementById('avatar-container');
                container.innerHTML = `<img alt="User Avatar" id="avatar-img" class="w-full h-full object-cover" src="${dataURL}">`;
                
                // Close modal
                closeCropperModal();
            }
        }

        // Toggle focus state visuals
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.querySelector('.material-symbols-outlined')?.classList.add('text-primary');
            });
            input.addEventListener('blur', () => {
                input.parentElement.querySelector('.material-symbols-outlined')?.classList.remove('text-primary');
            });
        });
    </script>

    <!-- Modal Cropper -->
    <div id="cropper-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm hidden animate-fade-in">
        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl p-6 max-w-lg w-full mx-4 shadow-2xl space-y-4">
            <div class="flex justify-between items-center border-b border-outline-variant/20 pb-2">
                <h3 class="text-headline-sm font-bold text-on-surface">Sesuaikan & Potong Foto</h3>
                <button onclick="closeCropperModal()" class="text-on-surface-variant hover:text-error transition-colors" type="button">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="w-full h-80 bg-slate-900 rounded-xl overflow-hidden flex items-center justify-center relative">
                <img id="cropper-image" src="" alt="Source Image" class="max-w-full max-h-full block">
            </div>
            <!-- Cropper Controls -->
            <div class="flex justify-center items-center gap-4 py-2 text-on-surface-variant">
                <button onclick="cropper.zoom(0.1)" class="w-10 h-10 rounded-full bg-surface-container-high hover:bg-primary hover:text-white transition-colors flex items-center justify-center" type="button" title="Perbesar">
                    <span class="material-symbols-outlined text-lg">zoom_in</span>
                </button>
                <button onclick="cropper.zoom(-0.1)" class="w-10 h-10 rounded-full bg-surface-container-high hover:bg-primary hover:text-white transition-colors flex items-center justify-center" type="button" title="Perkecil">
                    <span class="material-symbols-outlined text-lg">zoom_out</span>
                </button>
                <button onclick="cropper.rotate(-90)" class="w-10 h-10 rounded-full bg-surface-container-high hover:bg-primary hover:text-white transition-colors flex items-center justify-center" type="button" title="Putar Kiri">
                    <span class="material-symbols-outlined text-lg">rotate_left</span>
                </button>
                <button onclick="cropper.rotate(90)" class="w-10 h-10 rounded-full bg-surface-container-high hover:bg-primary hover:text-white transition-colors flex items-center justify-center" type="button" title="Putar Kanan">
                    <span class="material-symbols-outlined text-lg">rotate_right</span>
                </button>
            </div>
            <div class="flex justify-end items-center space-x-3 pt-4 border-t border-outline-variant/20">
                <button onclick="closeCropperModal()" class="px-4 py-2 rounded-xl text-label-md font-bold text-on-surface-variant hover:bg-surface-container transition-colors" type="button">Batal</button>
                <button onclick="applyCrop()" class="px-6 py-2 rounded-xl bg-primary text-white text-label-md font-bold hover:bg-primary-container transition-colors" type="button">Potong & Terapkan</button>
            </div>
        </div>
    </div>
</body>
</html>
