<?php
session_start();
require_once '../db_connect.php';

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard_admin.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['admin_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'ID Admin dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'];

                header("Location: dashboard_admin.php");
                exit();
            } else {
                $error = 'ID Admin atau password salah.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html><html class="light" lang="id"><head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Admin Login | KosanLaundry Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-primary-container": "#fefcff",
                        "on-primary": "#ffffff",
                        "background": "#f9f9ff",
                        "on-secondary-fixed-variant": "#005048",
                        "error": "#ba1a1a",
                        "on-secondary-fixed": "#00201c",
                        "tertiary-container": "#a36700",
                        "tertiary": "#825100",
                        "surface": "#f9f9ff",
                        "outline-variant": "#c2c6d6",
                        "on-primary-fixed": "#001a42",
                        "tertiary-fixed-dim": "#ffb95f",
                        "secondary-fixed-dim": "#4fdbc8",
                        "surface-bright": "#f9f9ff",
                        "primary": "#3b82f6",
                        "on-secondary": "#ffffff",
                        "primary-fixed": "#d8e2ff",
                        "surface-dim": "#d3daea",
                        "secondary-container": "#6df5e1",
                        "primary-container": "#2170e4",
                        "surface-container": "#e7eefe",
                        "secondary-fixed": "#71f8e4",
                        "secondary": "#006b5f",
                        "on-tertiary-container": "#fffbff",
                        "surface-container-high": "#e2e8f8",
                        "on-tertiary": "#ffffff",
                        "inverse-on-surface": "#ebf1ff",
                        "surface-container-lowest": "#ffffff",
                        "tertiary-fixed": "#ffddb8",
                        "on-tertiary-fixed-variant": "#653e00",
                        "on-surface": "#151c27",
                        "on-background": "#151c27",
                        "on-tertiary-fixed": "#2a1700",
                        "surface-tint": "#005ac2",
                        "on-secondary-container": "#006f64",
                        "on-surface-variant": "#424754",
                        "on-primary-fixed-variant": "#004395",
                        "outline": "#727785",
                        "on-error-container": "#93000a",
                        "surface-container-highest": "#dce2f3",
                        "surface-variant": "#dce2f3",
                        "primary-fixed-dim": "#adc6ff",
                        "inverse-primary": "#adc6ff",
                        "on-error": "#ffffff",
                        "surface-container-low": "#f0f3ff",
                        "error-container": "#ffdad6",
                        "inverse-surface": "#2a313d"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sm": "12px",
                        "gutter": "16px",
                        "xs": "8px",
                        "container-margin": "20px",
                        "md": "16px",
                        "xl": "32px",
                        "lg": "24px",
                        "base": "4px"
                    },
                    "fontFamily": {
                        "headline-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "headline-lg": ["Inter"],
                        "label-sm": ["Inter"],
                        "label-md": ["Inter"],
                        "body-md": ["Inter"]
                    },
                    "fontSize": {
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                        "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
                    }
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .bg-pattern {
            background-image: radial-gradient(circle at 2px 2px, #e5e7eb 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col items-center justify-center p-md sm:p-xl bg-pattern">
<main class="w-full max-w-5xl flex flex-col lg:flex-row bg-surface-container-lowest rounded-2xl overflow-hidden shadow-2xl ring-1 ring-outline-variant min-h-[700px]">
<!-- Left Side: Illustration/Brand -->
<section class="hidden lg:flex lg:w-1/2 flex-col justify-between p-xl bg-surface-container-low relative overflow-hidden border-r border-outline-variant">
<div class="relative z-10">
<div class="mb-xl">
<img alt="KosanLaundry Logo" class="h-16 w-16 object-contain" src="../logo.png?v=3">
<div class="mt-base font-bold text-primary text-headline-md">KosanLaundry</div>
</div>
<h1 class="text-headline-lg font-headline-lg text-primary mb-md leading-tight">Admin Portal Access</h1>
<p class="text-body-lg text-on-surface-variant max-w-sm">
                Pintu gerbang manajemen aman untuk operasional dan orkestrasi logistik KosanLaundry.
            </p>
</div>
<!-- Dashboard-style Illustration -->
<div class="relative z-10 flex-grow flex items-center justify-center py-xl">
<div class="w-full grid grid-cols-2 gap-md opacity-20">
<div class="h-32 bg-primary rounded-xl"></div>
<div class="h-32 bg-secondary rounded-xl mt-xl"></div>
<div class="h-32 bg-tertiary rounded-xl -mt-md"></div>
<div class="h-32 bg-primary-container rounded-xl"></div>
</div>
</div>
<div class="relative z-10 flex items-center gap-xs text-label-md text-on-surface-variant font-medium">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">security</span>
<span>Security Level: Enhanced High-Trust Zone</span>
</div>
<!-- Decor -->
<div class="absolute -bottom-10 -left-10 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
</section>
<!-- Right Side: Login Form -->
<section class="flex-grow p-xl md:p-16 flex flex-col justify-center bg-surface-container-lowest">
<div class="mb-xl">
<!-- Mobile Logo -->
<div class="lg:hidden flex flex-col items-center mb-xl">
<img alt="KosanLaundry Logo" class="h-12 w-12 object-contain" src="../logo.png?v=3">
<span class="font-bold text-primary mt-xs">KosanLaundry</span>
</div>
<h2 class="text-headline-md font-headline-lg text-on-surface">Masuk Panel Admin</h2>
<p class="text-body-md text-on-surface-variant mt-xs">Identitas internal diperlukan untuk akses kontrol.</p>
</div>
<?php if (!empty($error)): ?>
<div class="p-md bg-error-container text-on-error-container rounded-xl flex items-center gap-xs font-label-sm border border-error mb-md animate-pulse">
    <span class="material-symbols-outlined text-error">error</span>
    <span><?= htmlspecialchars($error); ?></span>
</div>
<?php endif; ?>

<form class="space-y-lg" action="admin.php" method="POST">
<!-- Admin ID Field -->
<div class="space-y-xs">
<label class="text-label-md font-label-md text-on-surface-variant flex items-center gap-xs" for="admin_id">
<span class="material-symbols-outlined text-[18px]">person</span>
                    Username / ID Admin
                </label>
<div class="relative">
<input class="w-full px-md py-md rounded-lg border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none" id="admin_id" name="admin_id" placeholder="Masukkan ID Staf" type="text" value="<?= isset($_POST['admin_id']) ? htmlspecialchars($_POST['admin_id']) : '' ?>">
</div>
</div>
<!-- Password Field -->
<div class="space-y-xs">
<div class="flex justify-between items-center">
<label class="text-label-md font-label-md text-on-surface-variant flex items-center gap-xs" for="password">
<span class="material-symbols-outlined text-[18px]">lock</span>
                        Password
                    </label>
<button class="text-label-sm text-primary hover:underline font-semibold" id="toggle-password" type="button">Tampilkan</button>
</div>
<div class="relative">
<input class="w-full px-md py-md rounded-lg border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none" id="password" name="password" placeholder="••••••••" type="password">
</div>
</div>
<!-- Primary Action Button -->
<button class="w-full bg-primary text-on-primary py-md rounded-xl font-headline-md text-headline-md hover:brightness-110 transition-all active:scale-[0.98] shadow-lg shadow-primary/20" type="submit">
                Masuk ke Panel Admin
            </button>
<!-- Forgot Password -->
<div class="text-center">
<button class="text-label-md text-on-surface-variant hover:text-primary transition-colors group" type="button">
                    Lupa Password Admin? 
                    <span class="font-bold underline decoration-primary/30 group-hover:decoration-primary">Hubungi Tim IT</span>
</button>
</div>
</form>
<div class="mt-xl pt-xl border-t border-outline-variant flex flex-col gap-lg">
<!-- Disclaimer Box -->
<div class="p-md rounded-xl bg-error/5 border border-error/10 flex gap-sm items-start">
<span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">warning</span>
<p class="text-label-sm text-on-surface-variant leading-relaxed">
<span class="font-bold text-error">Akses Terbatas:</span> Halaman ini hanya untuk staf KosanLaundry yang berwenang. Segala percobaan akses ilegal akan dicatat dan diproses secara hukum.
                </p>
</div>
</div>
</section>
</main>
<footer class="mt-xl text-center">
<p class="text-label-sm text-on-surface-variant opacity-60">
        © 2024 KosanLaundry Operations Dept. • Freshness Security Protocol v4.2
    </p>
</footer>
<script>
    // Toggle Password Visibility
    const toggleBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    
    toggleBtn.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleBtn.textContent = type === 'password' ? 'Tampilkan' : 'Sembunyikan';
    });

    // Simple visual feedback on focus
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.closest('.space-y-xs').querySelector('label').classList.add('text-primary');
        });
        input.addEventListener('blur', () => {
            input.closest('.space-y-xs').querySelector('label').classList.remove('text-primary');
        });
    });

    // Form Animation and Submission
    document.querySelector('form').addEventListener('submit', (e) => {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> Memverifikasi...';
        btn.classList.add('opacity-80', 'cursor-not-allowed');
        
        setTimeout(() => {
            form.submit();
        }, 1500);
    });
</script>
</body></html>
