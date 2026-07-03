<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Lupa Kata Sandi - MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "error": "#ba1a1a",
                    "on-primary-fixed": "#001a42",
                    "background": "#f9f9ff",
                    "inverse-primary": "#adc6ff",
                    "surface-container-lowest": "#ffffff",
                    "on-tertiary-fixed": "#2a1700",
                    "primary-fixed-dim": "#adc6ff",
                    "secondary": "#006b5f",
                    "on-tertiary-fixed-variant": "#653e00",
                    "inverse-surface": "#2a313d",
                    "inverse-on-surface": "#ebf1ff",
                    "surface": "#f9f9ff",
                    "secondary-fixed-dim": "#4fdbc8",
                    "on-secondary-fixed": "#00201c",
                    "surface-dim": "#d3daea",
                    "on-tertiary-container": "#fffbff",
                    "on-error-container": "#93000a",
                    "on-background": "#151c27",
                    "on-primary-container": "#fefcff",
                    "tertiary-fixed-dim": "#ffb95f",
                    "surface-tint": "#005ac2",
                    "surface-container-low": "#f0f3ff",
                    "secondary-container": "#6df5e1",
                    "on-surface-variant": "#424754",
                    "on-primary": "#ffffff",
                    "surface-variant": "#dce2f3",
                    "on-surface": "#151c27",
                    "on-secondary": "#ffffff",
                    "tertiary": "#825100",
                    "surface-bright": "#f9f9ff",
                    "primary-fixed": "#d8e2ff",
                    "primary": "#0058be",
                    "surface-container-highest": "#dce2f3",
                    "surface-container": "#e7eefe",
                    "on-primary-fixed-variant": "#004395",
                    "tertiary-container": "#a36700",
                    "surface-container-high": "#e2e8f8",
                    "tertiary-fixed": "#ffddb8",
                    "on-tertiary": "#ffffff",
                    "on-error": "#ffffff",
                    "on-secondary-fixed-variant": "#005048",
                    "error-container": "#ffdad6",
                    "primary-container": "#2170e4",
                    "on-secondary-container": "#006f64",
                    "secondary-fixed": "#71f8e4",
                    "outline-variant": "#c2c6d6",
                    "outline": "#727785"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "lg": "24px",
                    "xl": "32px",
                    "md": "16px",
                    "xs": "8px",
                    "container-margin": "20px",
                    "base": "4px",
                    "gutter": "16px",
                    "sm": "12px"
            },
            "fontFamily": {
                    "display-lg": ["Inter"],
                    "label-sm": ["Inter"],
                    "label-md": ["Inter"],
                    "headline-md": ["Inter"],
                    "headline-lg-mobile": ["Inter"],
                    "headline-lg": ["Inter"],
                    "body-lg": ["Inter"],
                    "body-md": ["Inter"]
            },
            "fontSize": {
                    "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                    "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9ff;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
        }
        .btn-press:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">
    <!-- Top Navigation Bar - Contextual for Transactional (Minimized) -->
    <header class="fixed top-0 w-full z-50 bg-transparent py-md px-lg">
        <div class="max-w-7xl mx-auto flex items-center justify-center md:justify-start gap-xs">
            <img alt="MataramWash Logo" class="h-10 w-10 object-contain" src="../Logo_MataramWash.png?v=3"/>
            <span class="text-headline-md font-headline-md font-bold text-primary">MataramWash</span>
        </div>
    </header>
    <!-- Main Content Area -->
    <main class="flex-grow flex items-center justify-center px-gutter py-xl mt-md">
        <!-- Abstract Background Decoration -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-primary-container/10 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[30%] h-[30%] bg-secondary-container/10 rounded-full blur-[100px]"></div>
        </div>
        <!-- Forgot Password Card -->
        <div class="glass-card w-full max-w-[440px] p-xl rounded-[16px] z-10 transition-all duration-300">
            <!-- Icon/Logo Context -->
            <div class="flex justify-center mb-xl">
                <div class="w-16 h-16 bg-primary-container rounded-full flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined !text-[32px]" style="font-variation-settings: 'FILL' 1;">lock_reset</span>
                </div>
            </div>
            <!-- Content -->
            <div class="text-center space-y-sm mb-xl">
                <h1 class="font-headline-md text-headline-md text-on-surface">Lupa Kata Sandi?</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Masukkan email Anda untuk menerima instruksi pengaturan ulang kata sandi.
                </p>
            </div>
            <!-- Form -->
            <form action="#" class="space-y-lg" id="forgotPasswordForm" method="POST" onsubmit="handleFormSubmit(event)">
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface-variant px-base" for="email">Alamat Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-md flex items-center pointer-events-none text-outline group-focus-within:text-primary transition-colors">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <input class="block w-full pl-[48px] pr-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none font-body-md text-body-md" id="email" name="email" placeholder="nama@email.com" required="" type="email"/>
                    </div>
                </div>
                <button class="btn-press w-full bg-primary text-on-primary py-md px-lg rounded-[16px] font-bold text-body-md flex items-center justify-center gap-xs shadow-md hover:bg-primary-container hover:text-on-primary-container transition-all duration-200" id="submitBtn" type="submit">
                    <span id="btnText">Kirim</span>
                    <span class="material-symbols-outlined" id="btnIcon">arrow_forward</span>
                    <div class="hidden animate-spin rounded-full h-5 w-5 border-2 border-on-primary border-t-transparent" id="loader"></div>
                </button>
            </form>
            <!-- Navigation Links -->
            <div class="mt-xl text-center">
                <a class="inline-flex items-center gap-xs font-label-md text-label-md text-primary hover:text-primary-container transition-colors group" href="login.php">
                    <span class="material-symbols-outlined !text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    <span>Kembali ke Login</span>
                </a>
            </div>
        </div>
        <!-- Feedback Toast (Hidden by default) -->
        <div class="fixed bottom-lg left-1/2 -translate-x-1/2 glass-card border-secondary text-on-surface py-md px-lg rounded-xl flex items-center gap-md opacity-0 translate-y-4 pointer-events-none transition-all duration-300 z-50" id="toast">
            <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="font-label-md text-label-md">Instruksi telah dikirim ke email Anda.</span>
        </div>
    </main>
    <!-- Footer - Minimum for focused screen -->
    <footer class="w-full py-lg px-gutter border-t border-outline-variant/10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-md opacity-60">
            <p class="font-label-sm text-label-sm text-on-surface-variant">© 2024 MataramWash. Freshness delivered.</p>
            <div class="flex gap-lg">
                <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary" href="../bantuan/bantuan.php">Bantuan</a>
                <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary" href="#">Privasi</a>
            </div>
        </div>
    </footer>
    <script>
        function handleFormSubmit(event) {
            event.preventDefault();
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            const loader = document.getElementById('loader');
            const submitBtn = document.getElementById('submitBtn');
            const toast = document.getElementById('toast');

            // Visual State: Loading
            btnText.textContent = 'Mengirim...';
            btnIcon.classList.add('hidden');
            loader.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');

            // Simulate API call
            setTimeout(() => {
                // Visual State: Success
                btnText.textContent = 'Berhasil Terkirim';
                loader.classList.add('hidden');
                
                // Show Toast
                toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
                
                // Reset after 3 seconds
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
                    btnText.textContent = 'Kirim';
                    btnIcon.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                }, 3000);
            }, 1500);
        }

        // Simple parallax-like motion for the card
        document.addEventListener('mousemove', (e) => {
            const card = document.querySelector('.glass-card');
            if (window.innerWidth > 768) {
                const xAxis = (window.innerWidth / 2 - e.pageX) / 50;
                const yAxis = (window.innerHeight / 2 - e.pageY) / 50;
                card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
            }
        });
    </script>
</body>
</html>
