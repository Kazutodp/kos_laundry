<?php
session_start();
// Cek apakah user sudah login, jika belum arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Edit Profil | KosanLaundry</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
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
            <img alt="KosanLaundry Logo" class="h-8 w-8 object-contain" src="../logo.png?v=3">
            <span class="text-lg">KosanLaundry</span>
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
<a class="flex items-center space-x-3 px-4 py-3 bg-primary-container text-on-primary-container rounded-lg font-bold transition-standard" href="#">
<span class="material-symbols-outlined">person</span>
<span class="text-label-md">Personal Info</span>
</a>
<a class="flex items-center space-x-3 px-4 py-3 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-standard" href="#">
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
<form class="space-y-10">
<!-- Avatar Upload Section -->
<div class="flex flex-col items-center sm:flex-row sm:items-end space-y-4 sm:space-y-0 sm:space-x-8">
<div class="relative group">
<div class="w-32 h-32 rounded-full overflow-hidden border-4 border-surface-container-high custom-shadow bg-primary text-on-primary flex items-center justify-center font-bold text-4xl select-none">
    <?php if (!empty($_SESSION['profile_pic'])): ?>
        <img alt="User Large Avatar" class="w-full h-full object-cover" src="<?= htmlspecialchars($_SESSION['profile_pic']); ?>">
    <?php else: ?>
        <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
    <?php endif; ?>
</div>
<button class="absolute bottom-1 right-1 bg-primary text-white p-2 rounded-full shadow-lg hover:scale-105 transition-transform" type="button">
<span class="material-symbols-outlined text-sm">photo_camera</span>
</button>
</div>
<div class="text-center sm:text-left">
<h3 class="text-label-md font-bold text-on-surface">Foto Profil</h3>
<p class="text-label-sm font-label-sm text-on-surface-variant mt-1">JPG atau PNG, Maksimal 2MB.</p>
<div class="mt-3 flex space-x-3">
<button class="text-label-sm font-bold text-primary hover:underline" type="button">Ganti Foto</button>
<button class="text-label-sm font-bold text-error hover:underline" type="button">Hapus</button>
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
<input class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" type="text" value="<?= htmlspecialchars($_SESSION['username']); ?>">
</div>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant">Email</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">mail</span>
<input class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface-container-high text-on-surface-variant/70 cursor-not-allowed text-body-md" disabled="" type="email" value="<?= htmlspecialchars($_SESSION['email']); ?>">
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-primary text-label-sm font-bold hover:underline" type="button">Ubah</button>
</div>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant">Nomor Telepon</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">call</span>
<input class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" type="tel" value="+62 812 3456 7890">
</div>
</div>
<div class="space-y-2">
<label class="text-label-md font-bold text-on-surface-variant">Jenis Kelamin</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">wc</span>
<select class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none appearance-none transition-standard text-body-md">
<option selected="" value="Laki-laki">Laki-laki</option>
<option value="Perempuan">Perempuan</option>
<option value="Lainnya">Lainnya</option>
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
<textarea class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-standard text-body-md" placeholder="Masukkan alamat lengkap penjemputan..." rows="3"></textarea>
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
        // Simple micro-interaction for form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin mr-2">sync</span> Menyimpan...';
            btn.disabled = true;
            btn.classList.add('opacity-80');
            
            setTimeout(() => {
                btn.innerHTML = '<span class="material-symbols-outlined mr-2">check_circle</span> Berhasil!';
                btn.classList.replace('bg-primary', 'bg-secondary');
                
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.replace('bg-secondary', 'bg-primary');
                    btn.disabled = false;
                    btn.classList.remove('opacity-80');
                }, 2000);
            }, 1500);
        });

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
</body>
</html>
