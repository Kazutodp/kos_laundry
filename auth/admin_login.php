<!DOCTYPE html>

<html class="light" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Login | KosanLaundry Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "tertiary": "#825100",
                    "on-tertiary-fixed": "#2a1700",
                    "on-primary": "#ffffff",
                    "on-error": "#ffffff",
                    "on-tertiary": "#ffffff",
                    "on-error-container": "#93000a",
                    "inverse-surface": "#2a313d",
                    "error": "#ba1a1a",
                    "inverse-primary": "#adc6ff",
                    "surface-variant": "#dce2f3",
                    "primary-container": "#2170e4",
                    "on-primary-fixed-variant": "#004395",
                    "on-secondary-container": "#006f64",
                    "surface-tint": "#005ac2",
                    "surface-container-low": "#f0f3ff",
                    "tertiary-fixed-dim": "#ffb95f",
                    "surface-container-highest": "#dce2f3",
                    "inverse-on-surface": "#ebf1ff",
                    "on-secondary-fixed": "#00201c",
                    "secondary": "#006b5f",
                    "tertiary-fixed": "#ffddb8",
                    "surface-dim": "#d3daea",
                    "outline": "#727785",
                    "primary-fixed-dim": "#adc6ff",
                    "surface-container-high": "#e2e8f8",
                    "primary-fixed": "#d8e2ff",
                    "secondary-container": "#6df5e1",
                    "on-tertiary-fixed-variant": "#653e00",
                    "outline-variant": "#c2c6d6",
                    "on-tertiary-container": "#fffbff",
                    "surface": "#f9f9ff",
                    "on-secondary-fixed-variant": "#005048",
                    "tertiary-container": "#a36700",
                    "on-primary-container": "#fefcff",
                    "surface-bright": "#f9f9ff",
                    "on-surface": "#151c27",
                    "error-container": "#ffdad6",
                    "secondary-fixed-dim": "#4fdbc8",
                    "on-background": "#151c27",
                    "secondary-fixed": "#71f8e4",
                    "primary": "#0058be",
                    "background": "#f9f9ff",
                    "on-primary-fixed": "#001a42",
                    "surface-container": "#e7eefe",
                    "surface-container-lowest": "#ffffff",
                    "on-secondary": "#ffffff",
                    "on-surface-variant": "#424754"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "8px",
                    "xl": "12px",
                    "2xl": "16px",
                    "full": "9999px"
            },
            "spacing": {
                    "sm": "12px",
                    "lg": "24px",
                    "base": "4px",
                    "md": "16px",
                    "xl": "32px",
                    "container-margin": "20px",
                    "gutter": "16px",
                    "xs": "8px"
            },
            "fontFamily": {
                    "headline-md": ["Inter"],
                    "label-sm": ["Inter"],
                    "display-lg": ["Inter"],
                    "body-md": ["Inter"],
                    "headline-lg": ["Inter"],
                    "label-md": ["Inter"],
                    "headline-lg-mobile": ["Inter"],
                    "body-lg": ["Inter"]
            },
            "fontSize": {
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}]
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
        .admin-glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
        }
        .bg-pattern {
            background-image: radial-gradient(circle at 2px 2px, #e5e7eb 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col items-center justify-center p-md sm:p-xl bg-pattern">
<!-- Security Overlay / Branding Container -->
<main class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 bg-surface-container-lowest rounded-2xl overflow-hidden shadow-xl ring-1 ring-outline-variant">
<!-- Left Side: Visual/Context Area -->
<section class="hidden lg:flex flex-col justify-between p-xl bg-surface-container-low relative overflow-hidden">
<div class="relative z-10">
<img alt="KosanLaundry Logo" class="h-10 w-auto mb-xl" src="../logo.png"/>
<h1 class="text-headline-lg font-headline-lg text-primary mb-md leading-tight">Admin Portal Access</h1>
<p class="text-body-lg text-on-surface-variant max-w-xs">
                    Secure management gateway for KosanLaundry operations and logistics orchestration.
                </p>
</div>
<!-- Dashboard Abstract Illustration (CSS Pattern) -->
<div class="absolute bottom-0 right-0 w-full h-1/2 opacity-10 pointer-events-none">
<div class="grid grid-cols-3 gap-2 p-md h-full">
<div class="bg-primary rounded-lg h-32 w-full"></div>
<div class="bg-secondary rounded-lg h-48 w-full mt-10"></div>
<div class="bg-tertiary rounded-lg h-24 w-full"></div>
</div>
</div>
<div class="relative z-10 flex items-center gap-xs text-label-md text-on-surface-variant">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified_user</span>
<span>Security Level: Enhanced High-Trust Zone</span>
</div>
</section>
<!-- Right Side: Login Form -->
<section class="p-xl md:p-24 flex flex-col justify-center bg-surface-container-lowest">
<div class="mb-xl text-center lg:text-left">
<!-- Mobile Logo -->
<img alt="KosanLaundry Logo" class="h-8 w-auto mb-md lg:hidden mx-auto" src="../logo.png"/>
<h2 class="text-headline-md font-headline-md text-on-surface">Masuk Panel Admin</h2>
<p class="text-label-md text-on-surface-variant mt-xs">Identitas internal diperlukan untuk akses.</p>
<?php if (isset($_GET['error'])): ?>
<div class="mb-md p-sm rounded-xl border border-error/20 bg-error-container text-on-error-container flex items-center gap-xs font-label-md text-label-md">
    <span class="material-symbols-outlined text-[20px]">error</span>
    <span><?= htmlspecialchars($_GET['error']); ?></span>
</div>
<?php endif; ?>
<form action="admin_login_process.php" class="space-y-lg" method="POST">
<!-- Admin ID Field -->
<div class="space-y-xs">
<label class="text-label-md font-label-md text-on-surface-variant flex items-center gap-xs" for="admin_id">
<span class="material-symbols-outlined text-[18px]">person</span>
                        Username / ID Admin
                    </label>
<div class="relative">
<input class="w-full px-md py-md rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none" id="admin_id" name="username" placeholder="Masukkan ID Staf" type="text" required />
</div>
</div>
<!-- Password Field -->
<div class="space-y-xs">
<div class="flex justify-between items-center">
<label class="text-label-md font-label-md text-on-surface-variant flex items-center gap-xs" for="password">
<span class="material-symbols-outlined text-[18px]">lock</span>
                            Password
                        </label>
<button class="text-label-sm text-primary hover:underline font-label-sm" type="button">Sembunyikan</button>
</div>
<div class="relative">
<input class="w-full px-md py-md rounded-xl border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none" id="password" name="password" placeholder="••••••••" type="password" required />
</div>
</div>
<!-- Submit Button -->
<button class="w-full bg-primary text-on-primary py-md rounded-xl font-headline-md text-headline-md hover:bg-primary-container transition-all active:scale-[0.98] shadow-lg shadow-primary/10" type="submit">
                    Masuk ke Panel Admin
                </button>
<!-- Help & Support -->
<div class="pt-md flex flex-col gap-sm">
<button class="text-label-md text-on-surface-variant hover:text-primary transition-colors flex items-center justify-center gap-xs group" type="button">
<span class="material-symbols-outlined text-[18px]">contact_support</span>
                        Lupa Password Admin? 
                        <span class="font-bold underline decoration-primary/30 group-hover:decoration-primary">Hubungi Tim IT</span>
</button>
</div>
</form>
<div class="mt-xl pt-xl border-t border-outline-variant flex flex-col gap-md">
<!-- Switch to User Login -->
<a class="flex items-center justify-center gap-xs py-sm px-md rounded-lg border border-outline-variant text-label-md font-label-md text-on-surface-variant hover:bg-surface-container-low transition-colors" href="login.html">
<span class="material-symbols-outlined text-[18px]">switch_account</span>
                    Bukan Admin? Kembali ke Login User
                </a>
<!-- Disclaimer -->
<div class="p-md rounded-xl bg-error-container/20 flex gap-sm items-start">
<span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">warning</span>
<p class="text-label-sm text-on-surface-variant leading-relaxed">
<span class="font-bold text-error">Akses Terbatas:</span> Halaman ini hanya untuk staf KosanLaundry yang berwenang. Segala percobaan akses ilegal akan dicatat dan diproses secara hukum.
                    </p>
</div>
</div>
</section>
</main>
<!-- Footer Copyright -->
<footer class="mt-xl text-center">
<p class="text-label-sm text-on-surface-variant opacity-60">
            © 2024 KosanLaundry Operations Dept. • Freshness Security Protocol v4.2
        </p>
</footer>
<script>
        // Simple Interaction for Input States
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.previousElementSibling.classList.add('text-primary');
            });
            input.addEventListener('blur', () => {
                input.parentElement.previousElementSibling.classList.remove('text-primary');
            });
        });

        // Form Animation on submit
        document.querySelector('form').addEventListener('submit', (e) => {
            const btn = e.target.querySelector('button[type="submit"]');
            setTimeout(() => {
                btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> Memverifikasi...';
                btn.classList.add('opacity-80', 'cursor-not-allowed');
            }, 10);
        });

        // Toggle password visibility
        const toggleBtn = document.querySelector('button[type="button"]');
        const passwordInput = document.getElementById('password');
        if (toggleBtn && passwordInput) {
            toggleBtn.textContent = 'Tampilkan';
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.textContent = isPassword ? 'Sembunyikan' : 'Tampilkan';
            });
        }
    </script>
</body></html>
