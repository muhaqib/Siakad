<x-app-layout>
    <x-slot name="header">
        <span class="md:hidden">Detail Tagihan</span>
        <span class="hidden md:inline">Detail & Rincian Pembayaran</span>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('mahasiswa.payments.index') }}" class="btn-ghost-saas p-2 rounded-lg flex items-center justify-center" title="Kembali">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-siakad-dark dark:text-white hidden md:block">
                        Detail Tagihan: <span class="font-mono text-siakad-primary dark:text-blue-400">{{ $payment->invoice_number }}</span>
                    </h1>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">
                        {{ $payment->paymentType->name }} &bull; STIT Mambaul Hikmah
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('mahasiswa.payments.index') }}" class="btn-ghost-saas px-3.5 py-2 text-xs font-semibold rounded-lg">
                    Kembali ke Daftar Tagihan
                </a>
            </div>
        </div>

        <div class="max-w-3xl mx-auto space-y-6">
            <div class="card-saas p-6 dark:bg-gray-800 space-y-6">
                <!-- Header Info -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-siakad-light dark:border-gray-700 pb-4 gap-3">
                    <div>
                        <h3 class="text-base font-bold text-siakad-dark dark:text-white">{{ $payment->paymentType->name }}</h3>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono mt-0.5">No. Invoice: {{ $payment->invoice_number }}</p>
                    </div>
                    <div>
                        @if($payment->isPaid())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                LUNAS
                            </span>
                        @elseif($payment->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                MENUNGGU PEMBAYARAN BANK
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                BELUM LUNAS
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Mahasiswa Data Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Nama Mahasiswa</span>
                        <span class="font-bold text-siakad-dark dark:text-white text-sm">{{ $mahasiswa->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">NIM</span>
                        <span class="font-mono font-bold text-siakad-dark dark:text-white text-sm">{{ $mahasiswa->nim }}</span>
                    </div>
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Program Studi</span>
                        <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $mahasiswa->prodi->nama }}</span>
                    </div>
                    <div>
                        <span class="text-siakad-secondary dark:text-gray-400 block mb-0.5">Angkatan</span>
                        <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $mahasiswa->angkatan }}</span>
                    </div>
                </div>

                <!-- Financial Calculation Card -->
                <div class="p-4 rounded-xl bg-siakad-light/20 dark:bg-gray-900/50 border border-siakad-light dark:border-gray-700 space-y-2.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Nominal Tagihan:</span>
                        <span class="font-bold text-sm text-siakad-dark dark:text-white">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Nominal Terbayar:</span>
                        <span class="font-bold text-sm text-emerald-600 dark:text-emerald-400">Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($payment->isPaid())
                        <div class="flex justify-between border-t border-siakad-light dark:border-gray-700 pt-2.5">
                            <span class="text-siakad-secondary dark:text-gray-400">Tanggal Bayar:</span>
                            <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $payment->payment_date ? $payment->payment_date->format('d F Y') : ($payment->confirmed_at ? $payment->confirmed_at->format('d F Y') : '-') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-siakad-secondary dark:text-gray-400">Metode Pembayaran:</span>
                            <span class="font-semibold text-siakad-dark dark:text-gray-200">{{ $payment->payment_method ?? 'Tunai' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-siakad-secondary dark:text-gray-400">Verifikasi Pembayaran:</span>
                            @if($payment->isPaidViaMidtrans())
                                <span class="font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Otomatis via Midtrans
                                </span>
                            @else
                                <span class="font-semibold text-siakad-dark dark:text-gray-200">
                                    {{ $payment->confirmedBy->name ?? 'Staf Administrasi Keuangan' }}
                                </span>
                            @endif
                        </div>
                        @if($payment->isPaidViaMidtrans())
                            <div class="flex justify-between">
                                <span class="text-siakad-secondary dark:text-gray-400">Midtrans Order ID:</span>
                                <span class="font-mono text-xs font-semibold text-siakad-dark dark:text-gray-200">{{ $payment->midtrans_order_id }}</span>
                            </div>
                        @endif
                    @endif
                </div>

                @if(! $payment->isPaid())
                    <!-- Pilihan 2 Metode Pembayaran -->
                    <div class="border-t border-siakad-light dark:border-gray-700 pt-5 space-y-4">
                        <div>
                            <h4 class="font-bold text-sm text-siakad-dark dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-siakad-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Tersedia 2 Pilihan Jalur Pembayaran:
                            </h4>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">
                                Anda dapat memilih pembayaran online instan (Midtrans) atau pembayaran manual (Kasir Kampus / Transfer Bank).
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Opsi 1: Bayar Online via Midtrans -->
                            <div class="p-4 rounded-xl border-2 border-siakad-primary/30 dark:border-blue-500/40 bg-gradient-to-b from-blue-50/40 to-white dark:from-blue-950/20 dark:to-gray-800 flex flex-col justify-between space-y-3">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                            OPSI 1: ONLINE INSTAN
                                        </span>
                                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Otomatis Aktif</span>
                                    </div>
                                    <h5 class="text-sm font-bold text-siakad-dark dark:text-white">Bayar via Midtrans Snap</h5>
                                    <p class="text-xs text-siakad-secondary dark:text-gray-400 leading-relaxed">
                                        Bayar online via QRIS (GoPay, OVO, ShopeePay, DANA), Virtual Account (BCA, Mandiri, BNI, BRI, Permata), atau Kartu.
                                    </p>
                                    <div class="p-2 rounded-lg bg-white/80 dark:bg-gray-900/60 text-[11px] text-siakad-secondary dark:text-gray-400 space-y-1">
                                        <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                                            <span>✓</span>
                                            <span>Terverifikasi otomatis 24/7</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-medium">
                                            <span>✓</span>
                                            <span>Akses KRS langsung aktif seketika</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2 pt-2 border-t border-blue-100 dark:border-blue-900/50">
                                    <div class="flex items-center justify-between">
                                        <label for="input-nominal-{{ $payment->id }}" class="text-[11px] font-bold text-siakad-dark dark:text-gray-200 flex items-center gap-1.5">
                                            <span>Nominal yang Ingin Dibayar:</span>
                                            <span id="badge-mode-{{ $payment->id }}" class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Pelunasan Penuh</span>
                                        </label>
                                        <button 
                                            type="button"
                                            onclick="setNominalFull({{ $payment->id }}, {{ $payment->remaining_amount > 0 ? $payment->remaining_amount : $payment->amount }})"
                                            class="text-[10px] font-bold text-siakad-primary hover:underline dark:text-blue-400"
                                        >
                                            Set LUNAS
                                        </button>
                                    </div>
                                    <div class="relative rounded-lg shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                            <span class="text-xs font-bold text-siakad-secondary dark:text-gray-400">Rp</span>
                                        </div>
                                        <input 
                                            type="number" 
                                            id="input-nominal-{{ $payment->id }}"
                                            min="10000"
                                            max="{{ $payment->remaining_amount > 0 ? $payment->remaining_amount : $payment->amount }}"
                                            step="5000"
                                            value="{{ $payment->remaining_amount > 0 ? $payment->remaining_amount : $payment->amount }}"
                                            oninput="updateNominalPreview({{ $payment->id }}, {{ $payment->remaining_amount > 0 ? $payment->remaining_amount : $payment->amount }})"
                                            class="block w-full rounded-lg border border-siakad-light dark:border-gray-600 bg-white dark:bg-gray-900 py-1.5 pl-8 pr-3 text-xs font-bold text-siakad-dark dark:text-white focus:border-siakad-primary focus:ring-1 focus:ring-siakad-primary"
                                        />
                                    </div>
                                    <p id="hint-nominal-{{ $payment->id }}" class="text-[10px] text-siakad-secondary dark:text-gray-400">
                                        Bisa dicicil minimal Rp 10.000 s/d sisa tagihan.
                                    </p>
                                </div>

                                <button
                                    id="btn-pay-{{ $payment->id }}"
                                    onclick="payWithMidtrans({{ $payment->id }}, this)"
                                    class="w-full btn-primary-saas py-3 text-xs font-semibold rounded-xl flex items-center justify-center gap-2 shadow-sm hover:shadow transition disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <span id="btn-text-{{ $payment->id }}">Bayar Transfer melalui Midtrans (Rp {{ number_format($payment->remaining_amount > 0 ? $payment->remaining_amount : $payment->amount, 0, ',', '.') }})</span>
                                </button>
                            </div>

                            <!-- Opsi 2: Bayar via Kasir / Admin Kampus -->
                            <div class="p-4 rounded-xl border border-siakad-light dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 flex flex-col justify-between space-y-3">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            OPSI 2: MANUAL / ADMIN
                                        </span>
                                        <span class="text-[10px] text-siakad-secondary dark:text-gray-400">Loket Kampus</span>
                                    </div>
                                    <h5 class="text-sm font-bold text-siakad-dark dark:text-white">Bayar via Kasir / Transfer Bank</h5>
                                    <p class="text-xs text-siakad-secondary dark:text-gray-400 leading-relaxed">
                                        Bayar langsung di loket keuangan kampus secara tunai atau transfer rekening manual, lalu serahkan bukti bayar ke admin keuangan.
                                    </p>
                                    <div class="p-2 rounded-lg bg-white/80 dark:bg-gray-800/60 text-[11px] text-siakad-secondary dark:text-gray-400 space-y-1">
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <span>⏱</span>
                                            <span>Verifikasi manual oleh admin (1-2 hari kerja)</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <span>📄</span>
                                            <span>Bawa kuitansi fisik / bukti transfer ke TU</span>
                                        </div>
                                    </div>
                                </div>

                                <a
                                    href="https://wa.me/6281234567890?text=Halo%20Admin%20Keuangan,%20saya%20ingin%20konfirmasi%20pembayaran%20{{ urlencode($payment->name) }}%20(ID:%20{{ $payment->id }})"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="w-full btn-secondary-saas py-3 text-xs font-semibold rounded-xl flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.2.662.589 1.221.771 1.394.858.173.086.274.072.375-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                    </svg>
                                    Konfirmasi ke Admin Keuangan
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions Footer -->
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-siakad-light dark:border-gray-700">
                    <a href="{{ route('mahasiswa.payments.index') }}" class="btn-ghost-saas px-4 py-2 text-xs font-medium rounded-lg">
                        Kembali ke Daftar
                    </a>
                    @if($payment->isPaid())
                        <a href="{{ route('mahasiswa.payments.receipt', $payment->id) }}" target="_blank" class="btn-primary-saas px-4 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak Kwitansi Resmi
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

@if(! $payment->isPaid())
@push('scripts')
<script src="{{ config('midtrans.base_url.snap_js') }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
const MIDTRANS_CLIENT_KEY = '{{ config('midtrans.client_key') }}';
const SNAP_URL = '{{ config('midtrans.base_url.snap_js') }}';

function setNominalFull(paymentId, maxAmount) {
    const input = document.getElementById(`input-nominal-${paymentId}`);
    if (input) {
        input.value = maxAmount;
        updateNominalPreview(paymentId, maxAmount);
    }
}

function updateNominalPreview(paymentId, maxAmount) {
    const input = document.getElementById(`input-nominal-${paymentId}`);
    const badge = document.getElementById(`badge-mode-${paymentId}`);
    const btnText = document.getElementById(`btn-text-${paymentId}`);
    const hint = document.getElementById(`hint-nominal-${paymentId}`);

    if (!input) return;

    let val = parseInt(input.value) || 0;
    if (val > maxAmount) {
        val = maxAmount;
        input.value = maxAmount;
    }

    const formatted = new Intl.NumberFormat('id-ID').format(val);

    if (val >= maxAmount) {
        if (badge) {
            badge.textContent = 'Pelunasan Penuh';
            badge.className = 'px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
        }
        if (btnText) {
            btnText.textContent = `Bayar Transfer melalui Midtrans (Rp ${formatted})`;
        }
        if (hint) {
            hint.textContent = 'Pembayaran penuh akan melunasi seluruh sisa tagihan ini.';
        }
    } else {
        const remainingAfter = maxAmount - val;
        const formattedRem = new Intl.NumberFormat('id-ID').format(remainingAfter);
        if (badge) {
            badge.textContent = 'Cicilan';
            badge.className = 'px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
        }
        if (btnText) {
            btnText.textContent = `Bayar Cicilan via Midtrans (Rp ${formatted})`;
        }
        if (hint) {
            hint.textContent = `Sisa tagihan setelah cicilan ini: Rp ${formattedRem}`;
        }
    }
}

function ensureSnapLoaded() {
    return new Promise((resolve, reject) => {
        if (window.snap) {
            resolve(true);
            return;
        }

        const existingScript = document.querySelector(`script[src="${SNAP_URL}"]`);
        if (existingScript) {
            existingScript.onload = () => resolve(true);
            existingScript.onerror = () => reject(new Error('Gagal memuat modul pembayaran Midtrans.'));
            return;
        }

        const script = document.createElement('script');
        script.src = SNAP_URL;
        script.setAttribute('data-client-key', MIDTRANS_CLIENT_KEY);
        script.onload = () => resolve(true);
        script.onerror = () => reject(new Error('Gagal memuat modul pembayaran Midtrans.'));
        document.head.appendChild(script);
    });
}

async function payWithMidtrans(paymentId, btn) {
    const originalText = btn.innerHTML;
    const inputNominal = document.getElementById(`input-nominal-${paymentId}`);
    let amountToPay = inputNominal ? parseInt(inputNominal.value) : null;

    if (amountToPay && amountToPay < 10000) {
        alert('⚠️ Nominal pembayaran minimal adalah Rp 10.000');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `
        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        Menyiapkan pembayaran Midtrans...
    `;

    try {
        try {
            await ensureSnapLoaded();
        } catch (snapErr) {
            console.warn('Snap load warning:', snapErr);
        }

        const payload = {};
        if (amountToPay) {
            payload.amount = amountToPay;
        }

        const response = await fetch(`/mahasiswa/payments/${paymentId}/midtrans/token`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (! response.ok) {
            throw new Error(data.message || 'Gagal mendapatkan token pembayaran.');
        }

        btn.disabled = false;
        btn.innerHTML = originalText;

        if (typeof snap !== 'undefined') {
            snap.pay(data.snap_token, {
                onSuccess: function(result) {
                    window.location.href = `/mahasiswa/payments/${paymentId}/midtrans/finish`;
                },
                onPending: function(result) {
                    window.location.href = `/mahasiswa/payments/${paymentId}/midtrans/finish`;
                },
                onError: function(result) {
                    window.location.href = `/mahasiswa/payments/${paymentId}/midtrans/finish`;
                },
                onClose: function() {
                    console.info('Popup Snap ditutup oleh pengguna.');
                }
            });
        } else if (data.redirect_url) {
            // Fallback sesuai rekomendasi Midtrans docs jika popup diblokir browser
            window.location.href = data.redirect_url;
        } else {
            alert('Modul pembayaran Midtrans sedang disiapkan. Silakan coba klik tombol kembali.');
        }

    } catch (error) {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('⚠️ ' + error.message);
    }
}
</script>
@endpush
@endif
</x-app-layout>
