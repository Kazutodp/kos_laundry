<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Riwayat Pesanan - MataramWash</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <!-- Midtrans Snap.js -->
    @if (config('services.midtrans.environment') === 'production')
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    @endif

    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "surface-bright": "#f9f9ff",
                    "surface-variant": "#dce2f3",
                    "on-surface": "#151c27",
                    "primary": "#0058be",
                    "primary-container": "#2170e4",
                    "on-primary": "#ffffff",
                    "outline-variant": "#c2c6d6",
                    "background": "#f9f9ff",
                    "on-background": "#151c27",
                    "surface-container": "#e7eefe",
                    "surface-container-low": "#f0f3ff",
                    "surface-container-lowest": "#ffffff",
                    "secondary": "#006b5f",
                    "on-secondary": "#ffffff",
                    "secondary-container": "#6df5e1",
                    "on-secondary-container": "#006f64"
            },
            "spacing": {
                    "xl": "32px",
                    "base": "4px",
                    "gutter": "16px",
                    "container-margin": "20px",
                    "xs": "8px",
                    "sm": "12px",
                    "md": "16px",
                    "lg": "24px"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            }
          }
        }
      }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9ff;
        }
        .material-symbols-outlined {
            vertical-align: middle;
        }
    </style>
</head>
<body class="text-on-background bg-background min-h-screen flex flex-col justify-between">

    <!-- Mini Header -->
    <header class="w-full bg-surface-container shadow-sm py-4 px-6 border-b border-outline-variant/30 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a class="flex items-center space-x-xs text-headline-md font-bold text-primary" href="{{ route('home') }}">
                <img alt="MataramWash Logo" class="h-8 w-8 object-contain" src="{{ asset('logo.png?v=3') }}">
                <span class="text-lg">MataramWash</span>
            </a>
            <a class="flex items-center space-x-1 text-label-md font-bold text-primary hover:underline" href="{{ route('home') }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-grow max-w-4xl w-full mx-auto p-md py-xl">
        <header class="mb-xl flex justify-between items-center">
            <div>
                <h1 class="text-headline-lg font-bold text-on-surface tracking-tight text-3xl">Riwayat Pesanan</h1>
                <p class="text-body-md text-slate-500 mt-xs">Pantau progres cuci dan selesaikan tagihan laundry Anda di sini.</p>
            </div>
            <button onclick="window.location.reload()" class="flex items-center gap-xs px-md py-sm bg-white border border-outline-variant/50 rounded-xl hover:bg-slate-50 text-slate-600 transition-colors text-sm font-semibold shadow-sm">
                <span class="material-symbols-outlined text-[18px]">refresh</span> Refresh
            </button>
        </header>

        @if ($orders->isEmpty())
            <div class="bg-white rounded-3xl border border-outline-variant/20 p-xl text-center space-y-md shadow-sm">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 mx-auto">
                    <span class="material-symbols-outlined text-slate-400 text-[48px]">local_laundry_service</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-on-surface">Belum ada pesanan</h3>
                    <p class="text-sm text-slate-400 mt-xs">Semua laundry yang Anda pesan akan tercatat di halaman ini.</p>
                </div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-xs px-lg py-sm bg-primary text-white rounded-xl font-bold shadow-md hover:brightness-110 active:scale-95 transition-all text-sm mx-auto">
                    Pesan Layanan Sekarang
                </a>
            </div>
        @else
            <div class="space-y-lg">
                @foreach ($orders as $order) 
                    @php
                        $is_self = (strpos(strtolower($order->layanan), 'self') !== false || strpos(strtolower($order->mitra->nama_mitra), 'washtra') !== false);
                        $is_satuan = (strpos(strtolower($order->layanan), 'sepatu') !== false || strpos(strtolower($order->layanan), 'shoes') !== false || strpos(strtolower($order->mitra->nama_mitra), 'shoes') !== false);
                        $is_kiloan = !$is_self && !$is_satuan;
                        
                        // Style class based on order status
                        $order_status = $order->status_order ?? 'Menunggu Penjemputan';
                        $status_badge_class = 'bg-slate-100 text-slate-700';
                        if ($order_status === 'Menunggu Timbangan') {
                            $status_badge_class = 'bg-amber-50 text-amber-700 border border-amber-200';
                        } elseif ($order_status === 'Menunggu Pembayaran') {
                            $status_badge_class = 'bg-sky-50 text-sky-700 border border-sky-200 font-bold animate-pulse';
                        } elseif ($order_status === 'Diproses') {
                            $status_badge_class = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
                        } elseif ($order_status === 'Siap Diantar') {
                            $status_badge_class = 'bg-teal-50 text-teal-700 border border-teal-200';
                        } elseif ($order_status === 'Selesai') {
                            $status_badge_class = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                        }
                        
                        // Style class based on payment status
                        $pay_status = $order->status_pembayaran;
                        $pay_badge_class = 'bg-slate-100 text-slate-500';
                        if ($pay_status === 'success') {
                            $pay_badge_class = 'bg-emerald-500 text-white font-bold';
                        } elseif ($pay_status === 'pending') {
                            $pay_badge_class = 'bg-amber-500 text-white font-bold';
                        } elseif ($pay_status === 'failed') {
                            $pay_badge_class = 'bg-rose-500 text-white font-bold';
                        }
                    @endphp
                    <div class="bg-white rounded-3xl border border-outline-variant/30 p-lg shadow-sm hover:shadow-md transition-all duration-200 flex flex-col md:flex-row gap-lg items-start">
                        <!-- Partner Thumbnail -->
                        <img alt="Outlet logo" class="w-16 h-16 rounded-2xl object-cover border border-slate-100 flex-shrink-0" src="{{ asset($order->mitra->foto_toko ?: 'uploads/mitra_1.png') }}">
                        
                        <!-- Order Details -->
                        <div class="flex-1 space-y-md w-full">
                            <div class="flex flex-wrap justify-between items-start gap-xs">
                                <div>
                                    <h3 class="text-lg font-bold text-on-surface">{{ $order->mitra->nama_mitra }}</h3>
                                    <p class="text-sm text-primary font-semibold mt-base">{{ $order->layanan }}</p>
                                </div>
                                <div class="flex gap-xs items-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $status_badge_class }}">
                                        {{ $order_status }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $pay_badge_class }}">
                                        Pay: {{ strtoupper($pay_status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <hr class="border-outline-variant/20">

                            <!-- Details breakdown -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md text-xs text-slate-600">
                                <div class="space-y-sm">
                                    <div>
                                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">ID Transaksi / Tanggal</span>
                                        <span class="font-medium text-slate-800">#{{ $order->id }} &bull; {{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                                    </div>
                                    @if ($is_kiloan)
                                    <div>
                                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Berat Pakaian</span>
                                        <span class="font-medium text-slate-800">
                                            Estimasi: <strong>{{ floatval($order->estimasi_berat) }} Kg</strong> 
                                            @if (floatval($order->berat_atau_qty) > 0)
                                                &bull; Asli: <strong class="text-emerald-600">{{ floatval($order->berat_atau_qty) }} Kg</strong>
                                            @endif
                                        </span>
                                    </div>
                                    @elseif ($is_self)
                                    <div>
                                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Jumlah Slot Mesin</span>
                                        <span class="font-medium text-slate-800"><strong>{{ floatval($order->berat_atau_qty) }} Mesin</strong></span>
                                    </div>
                                    @elseif ($is_satuan)
                                    <div>
                                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Jumlah Pakaian/Barang</span>
                                        <span class="font-medium text-slate-800"><strong>{{ floatval($order->estimasi_berat) }} Pasang Sepatu</strong></span>
                                    </div>
                                    @endif
                                </div>

                                <div class="space-y-sm">
                                    <div>
                                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Pilihan Distribusi</span>
                                        <span class="font-medium text-slate-800">
                                            @php
                                                $distribution = [];
                                                if ($order->layanan_jemput) $distribution[] = 'Jemput';
                                                if ($order->layanan_antar) $distribution[] = 'Antar';
                                                if (empty($distribution)) $distribution[] = 'Ambil Mandiri';
                                                echo implode(' - ', $distribution);
                                            @endphp
                                        </span>
                                    </div>
                                    @if (!empty($order->alamat_antar_jemput))
                                    <div>
                                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Alamat Pengiriman</span>
                                        <span class="font-medium text-slate-800 truncate block max-w-xs" title="{{ $order->alamat_antar_jemput }}">
                                            {{ $order->alamat_antar_jemput }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if (!empty($order->catatan))
                            <div class="p-xs bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-500">
                                <strong>Catatan:</strong> "{{ $order->catatan }}"
                            </div>
                            @endif

                            <!-- Bottom interactive details -->
                            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-md pt-sm border-t border-dashed border-outline-variant/30">
                                <div class="text-left">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Tagihan</span>
                                    <span class="text-lg font-black text-primary">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                                    @if (!$is_self && floatval($order->berat_atau_qty) > 0)
                                        <span class="text-[10px] text-slate-400 block">({{ floatval($order->berat_atau_qty) }}kg x Rp {{ number_format($order->tarif_per_kg, 0, ',', '.') }} + Rp {{ number_format($order->biaya_antar_jemput, 0, ',', '.') }})</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-md">
                                    <!-- Scale photo proof button -->
                                    @if (!empty($order->foto_timbangan))
                                        <button onclick="viewTimbangan('{{ asset($order->foto_timbangan) }}')" class="flex items-center gap-xs text-xs font-bold text-secondary hover:text-emerald-700 transition-colors py-sm px-md border border-emerald-200 bg-emerald-50/50 rounded-xl shadow-xs">
                                            <span class="material-symbols-outlined text-[16px]">image</span> Lihat Foto Timbangan
                                        </button>
                                    @endif

                                    <!-- Pay button -->
                                    @if ($pay_status === 'pending' && ($order_status === 'Menunggu Pembayaran' || $is_self || $is_satuan))
                                        <button onclick="payOrder({{ $order->id }}, this)" class="bg-primary text-on-primary py-sm px-lg rounded-xl font-bold shadow-md hover:brightness-110 active:scale-95 transition-all text-xs flex items-center gap-xs">
                                            <span class="material-symbols-outlined text-[16px]">payments</span> Bayar Sekarang
                                        </button>
                                    @elseif ($pay_status === 'pending' && $is_kiloan && ($order_status === 'Menunggu Penjemputan' || $order_status === 'Menunggu Timbangan'))
                                        <span class="text-[11px] text-amber-600 bg-amber-50 px-md py-sm rounded-xl border border-amber-100 flex items-center gap-xs font-semibold">
                                            <span class="material-symbols-outlined text-[14px]">schedule</span> Menunggu timbangan resmi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <!-- Photo Zoom Modal -->
    <div id="timbangan-modal" class="fixed inset-0 z-[100] hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-md">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl relative flex flex-col">
            <div class="p-lg border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Bukti Timbangan Digital</h3>
                <button onclick="closeTimbangan()" class="material-symbols-outlined text-slate-400 hover:text-slate-600 text-2xl">close</button>
            </div>
            <div class="p-lg flex justify-center bg-slate-900">
                <img id="modal-image" src="" alt="Foto Timbangan" class="max-h-[50vh] object-contain rounded-xl">
            </div>
            <div class="p-lg text-center text-xs text-slate-500 bg-slate-50">
                Foto ini diunggah secara resmi oleh mitra laundry kami saat menimbang pakaian Anda di outlet.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="w-full py-4 text-center bg-surface-container border-t border-outline-variant/20 text-[11px] text-slate-400 mt-xl">
        © 2026 MataramWash. Semua hak dilindungi.
    </footer>

    <script>
        function viewTimbangan(imageUrl) {
            document.getElementById('modal-image').src = imageUrl;
            document.getElementById('timbangan-modal').classList.remove('hidden');
        }

        function closeTimbangan() {
            document.getElementById('timbangan-modal').classList.add('hidden');
            document.getElementById('modal-image').src = '';
        }

        function payOrder(orderId, btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px] mr-xs">sync</span> Processing...';
            btn.disabled = true;

            // Call backend to generate Snap token
            fetch('/orders/' + orderId + '/token')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Trigger Snap Popup
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            window.location.href = '{{ route("pembayaran.sukses") }}?order_id=' + orderId + '&reference=' + result.order_id;
                        },
                        onPending: function(result) {
                            alert('Pembayaran pending. Silakan selesaikan pembayaran Anda.');
                            window.location.reload();
                        },
                        onError: function(result) {
                            alert('Pembayaran gagal. Silakan coba kembali.');
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        },
                        onClose: function() {
                            alert('Anda menutup popup pembayaran sebelum menyelesaikan transaksi.');
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    });
                } else {
                    alert(data.message || 'Gagal menghasilkan token pembayaran.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal menghubungi server pembayaran.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
