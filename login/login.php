<?php
session_start();
require_once '../db_connect.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Pendaftaran berhasil! Silakan masuk dengan akun Anda.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nama'];
                $_SESSION['email'] = $user['email'];

                header("Location: ../index.php");
                exit();
            } else {
                $error = 'Email atau kata sandi salah.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Masuk | KosanLaundry</title>
    <!-- Google Identity Services SDK -->
    <script async="" defer="" src="https://accounts.google.com/gsi/client"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-highest": "#dce2f3",
                        "on-error": "#ffffff",
                        "secondary-container": "#6df5e1",
                        "tertiary-container": "#a36700",
                        "tertiary": "#825100",
                        "on-surface": "#151c27",
                        "on-error-container": "#93000a",
                        "error": "#ba1a1a",
                        "on-tertiary-fixed": "#2a1700",
                        "inverse-primary": "#adc6ff",
                        "primary-fixed-dim": "#adc6ff",
                        "on-tertiary-fixed-variant": "#653e00",
                        "surface-bright": "#f9f9ff",
                        "secondary-fixed": "#71f8e4",
                        "outline": "#727785",
                        "tertiary-fixed-dim": "#ffb95f",
                        "surface-container-high": "#e2e8f8",
                        "on-tertiary-container": "#fffbff",
                        "on-primary-container": "#fefcff",
                        "on-secondary-fixed-variant": "#005048",
                        "secondary-fixed-dim": "#4fdbc8",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed": "#ffddb8",
                        "inverse-on-surface": "#ebf1ff",
                        "primary": "#0058be",
                        "surface": "#f9f9ff",
                        "error-container": "#ffdad6",
                        "surface-dim": "#d3daea",
                        "background": "#f9f9ff",
                        "primary-fixed": "#d8e2ff",
                        "on-background": "#151c27",
                        "secondary": "#006b5f",
                        "surface-container-low": "#f0f3ff",
                        "primary-container": "#2170e4",
                        "on-surface-variant": "#424754",
                        "surface-container": "#e7eefe",
                        "on-secondary": "#ffffff",
                        "on-primary": "#ffffff",
                        "surface-variant": "#dce2f3",
                        "outline-variant": "#c2c6d6",
                        "surface-tint": "#005ac2",
                        "inverse-surface": "#2a313d",
                        "on-secondary-container": "#006f64",
                        "surface-container-lowest": "#ffffff",
                        "on-primary-fixed-variant": "#004395",
                        "on-secondary-fixed": "#00201c",
                        "on-primary-fixed": "#001a42"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sm": "12px",
                        "container-margin": "20px",
                        "xs": "8px",
                        "md": "16px",
                        "base": "4px",
                        "gutter": "16px",
                        "xl": "32px",
                        "lg": "24px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "headline-lg": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "label-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "body-md": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "600" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "display-lg": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }]
                    }
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col">
<main class="flex-grow flex flex-col md:flex-row">
    <!-- Brand/Illustration Side -->
    <section class="hidden md:flex md:w-1/2 lg:w-3/5 bg-primary relative overflow-hidden items-center justify-center p-xl">
        <!-- Background Image with Opacity -->
        <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('laundry_bg.png');"></div>
        <!-- Background Decoration -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 -left-10 w-96 h-96 bg-primary-container rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 -right-10 w-80 h-80 bg-primary-container rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10 max-w-lg text-center">
            <div class="mb-lg inline-flex items-center justify-center p-md bg-white rounded-full shadow-lg">
                <img alt="KosanLaundry Logo" class="w-16 h-16 object-contain" src="../logo.png?v=3"/>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-on-primary mb-md leading-tight">
                Kesegaran Maksimal untuk Pakaian Anda.
            </h1>
            <p class="font-body-lg text-body-lg text-primary-fixed opacity-90 mb-xl">
                Hemat waktu dan nikmati kebersihan profesional langsung dari genggaman tangan Anda. Kami merawat setiap serat benang dengan cinta.
            </p>
            <div class="grid grid-cols-2 gap-md text-left">
                <div class="p-md glass-card rounded-xl">
                    <span class="material-symbols-outlined text-white mb-xs">schedule</span>
                    <p class="font-label-md text-label-md text-white">Pengerjaan Kilat</p>
                </div>
                <div class="p-md glass-card rounded-xl">
                    <span class="material-symbols-outlined text-white mb-xs">verified</span>
                    <p class="font-label-md text-label-md text-white">Higienis &amp; Bersih</p>
                </div>
            </div>
        </div>
        <!-- Subtle Texture -->
        <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>
    </section>
    <!-- Form Side -->
    <section class="flex-grow md:w-1/2 lg:w-2/5 flex flex-col justify-center px-container-margin py-xl bg-surface-container-lowest relative">
        <!-- Mobile Brand Identity -->
        <div class="md:hidden flex items-center gap-xs mb-xl">
            <img alt="Logo" class="w-10 h-10 object-contain" src="../logo.png?v=3"/>
            <span class="font-headline-md text-headline-md text-primary font-bold">KosanLaundry</span>
        </div>
        <div class="max-w-md w-full mx-auto">
            <header class="mb-lg">
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Selamat Datang</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Silakan masuk untuk mengelola pesanan laundry Anda.</p>
            </header>

            <?php if (!empty($error)): ?>
                <div class="p-md bg-error-container text-on-error-container rounded-xl flex items-center gap-xs font-label-sm border border-error mb-md animate-pulse">
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

            <!-- Login Form -->
            <form class="space-y-md" action="login.php" method="POST">
                <!-- Email -->
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface-variant block" for="email">Alamat Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline">mail</span>
                        <input class="w-full pl-[48px] pr-md py-sm bg-surface-container-low border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md text-on-surface" id="email" name="email" placeholder="contoh@email.com" required="" type="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"/>
                    </div>
                </div>
                <!-- Password -->
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface-variant block" for="password">Kata Sandi</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline">lock</span>
                        <input class="w-full pl-[48px] pr-12 py-sm bg-surface-container-low border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md text-on-surface" id="password" name="password" placeholder="••••••••" required="" type="password"/>
                        <button class="absolute right-md top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors" id="toggle-password" type="button">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                </div>
                <!-- Options -->
                <div class="flex items-center justify-between" id="options-container">
                    <label class="flex items-center gap-xs cursor-pointer group">
                        <input class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/>
                        <span class="font-label-sm text-label-sm text-on-surface-variant group-hover:text-on-surface transition-colors">Ingat Saya</span>
                    </label>
                    <a class="font-label-sm text-label-sm text-primary hover:underline font-semibold" href="forgot_password.php">Lupa Kata Sandi?</a>
                </div>
                <!-- Action Button -->
                <button class="w-full py-md bg-primary text-on-primary rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all shadow-md active:scale-[0.98]" type="submit">
                    <span id="submit-btn-text">Masuk Sekarang</span>
                </button>
                <!-- Divider -->
                <div class="relative flex items-center py-base">
                    <div class="flex-grow border-t border-outline-variant"></div>
                    <span class="flex-shrink mx-md font-label-sm text-label-sm text-outline">Atau masuk dengan</span>
                    <div class="flex-grow border-t border-outline-variant"></div>
                </div>
                <!-- Social Login Container -->
                <div class="w-full flex justify-center">
                    <div class="w-full" id="google-login-btn-container"></div>
                </div>
            </form>
            <footer class="mt-xl text-center space-y-md">
                <p class="font-label-md text-label-md text-on-surface-variant">
                    <span>Belum punya akun? </span><a class="text-primary font-bold hover:underline" href="daftar.php">Daftar Sekarang</a>
                </p>
                <div class="pt-md border-t border-outline-variant">
                    <a class="inline-flex items-center gap-xs font-label-sm text-label-sm text-outline hover:text-primary transition-colors" href="#">
                        <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                        Masuk sebagai Admin
                    </a>
                </div>
            </footer>
        </div>

    </section>
</main>

<script>
    window.onload = function () {
        google.accounts.id.initialize({
            client_id: "YOUR_GOOGLE_CLIENT_ID",
            callback: (response) => console.log(response)
        });
        google.accounts.id.renderButton(
            document.getElementById("google-login-btn-container"),
            { theme: "outline", size: "large", width: "100%", shape: "rectangular" }
        );
    }

    // Toggle passwords visibility
    const togglePasswordBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            const iconSpan = togglePasswordBtn.querySelector('.material-symbols-outlined');
            if (iconSpan) {
                iconSpan.textContent = isPassword ? 'visibility_off' : 'visibility';
            }
        });
    }

</script>
</body>
</html>
