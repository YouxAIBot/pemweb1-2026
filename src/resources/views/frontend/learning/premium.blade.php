@extends('layouts.learning')

@section('title', 'Toko Premium - YoLearning')

@section('content')
<div class="premium-page">
    <header class="premium-top">
        <a href="{{ route('dashboard') }}" class="premium-back">Kembali</a>
        <a href="{{ route('learning.profile.edit', ['tab' => 'account']) }}#subscription" class="premium-back premium-ghost">Profile</a>
    </header>

    <main class="premium-shell">
        <section class="premium-head">
            <small>YoLearning Premium</small>
            <h1>Toko Premium YoLearning.</h1>
            <p>Pilih pembayaran otomatis lewat Midtrans atau kirim bukti transfer manual. Data akun dan progress belajar tetap aman.</p>
        </section>

        @if (session('learning_success'))
            <div class="premium-alert">{{ session('learning_success') }}</div>
        @endif
        @if (session('learning_error'))
            <div class="premium-alert premium-danger">{{ session('learning_error') }}</div>
        @endif

        <section class="premium-status">
            <div>
                <small>Status Akun</small>
                @if ($activePremium)
                    <h2>Premium aktif sampai {{ $activePremium->ends_at->format('d M Y H:i') }}</h2>
                    <p>Paket: {{ $activePremium->package?->name ?? 'Premium' }}. Iklan level otomatis dinonaktifkan selama masa premium aktif.</p>
                @else
                    <h2>Akun kamu masih gratis</h2>
                    <p>User gratis tetap bisa belajar, tetapi akan melihat iklan 15 detik sebelum masuk dan setelah menyelesaikan level.</p>
                @endif
            </div>
            <span>{{ $activePremium ? 'Premium' : 'Gratis' }}</span>
        </section>

        <section class="premium-grid">
            <div class="premium-packages">
                @forelse ($packages as $package)
                    <article class="premium-package">
                        <div class="premium-package-head">
                            <div>
                                <small>{{ $package->duration_days }} hari</small>
                                <h2>{{ $package->name }}</h2>
                            </div>
                            <strong>{{ $package->formattedPrice() }}</strong>
                        </div>
                        <p>{{ $package->description }}</p>
                        <ul>
                            @foreach (($package->benefits ?? []) as $benefit)
                                <li>{{ $benefit }}</li>
                            @endforeach
                        </ul>

                        <div class="premium-payment-note premium-midtrans-note">
                            <b>Pembayaran Otomatis</b>
                            <span>Bayar langsung lewat Midtrans. Popup pembayaran akan menampilkan QRIS, e-wallet, virtual account, atau metode lain yang aktif di dashboard Midtrans.</span>
                            <em>Jika pembayaran sukses, status premium akan aktif otomatis setelah notifikasi Midtrans diterima sistem.</em>
                        </div>

                        @if ($midtransEnabled)
                            <form method="POST" action="{{ route('learning.premium.payments.midtrans') }}" class="premium-form" data-midtrans-form>
                                @csrf
                                <input type="hidden" name="premium_package_id" value="{{ $package->id }}">
                                <button type="submit" class="midtrans-button" data-midtrans-button>Bayar Sekarang via Midtrans</button>
                                <em class="premium-error" data-midtrans-message hidden></em>
                            </form>
                        @else
                            <div class="premium-midtrans-disabled">
                                <b>Midtrans belum aktif di aplikasi.</b>
                                <span>Pastikan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` sudah diisi di VPS, lalu jalankan ulang cache config Laravel.</span>
                            </div>
                        @endif

                        <div class="premium-divider"><span>atau upload manual</span></div>

                        <form method="POST" action="{{ route('learning.premium.payments.store') }}" enctype="multipart/form-data" class="premium-form">
                            @csrf
                            <input type="hidden" name="premium_package_id" value="{{ $package->id }}">

                            <div class="premium-payment-note">
                                <b>Pembayaran Manual</b>
                                <span>Transfer sesuai nominal ke rekening admin, lalu upload bukti pembayaran.</span>
                                <em>Contoh rekening: BCA 1234567890 a.n. YoLearning</em>
                            </div>

                            <label>
                                Bukti Pembayaran
                                <input type="file" name="payment_proof" accept="image/*,.pdf" required>
                                @error('payment_proof') <em class="premium-error">{{ $message }}</em> @enderror
                            </label>

                            <label>
                                Catatan untuk Admin
                                <textarea name="note" rows="3" placeholder="Opsional, contoh: transfer dari rekening atas nama ...">{{ old('note') }}</textarea>
                                @error('note') <em class="premium-error">{{ $message }}</em> @enderror
                            </label>

                            <button type="submit">Kirim Bukti Pembayaran</button>
                        </form>
                    </article>
                @empty
                    <article class="premium-package">
                        <small>Belum ada paket</small>
                        <h2>Paket premium belum dibuat</h2>
                        <p>Admin bisa menambahkan paket melalui menu Premium Packages di admin panel.</p>
                    </article>
                @endforelse
            </div>

            <aside class="premium-history">
                <small>Riwayat Pembayaran</small>
                <div class="premium-history-list">
                    @forelse ($payments as $payment)
                        <div class="premium-history-row">
                            <div>
                                <b>{{ $payment->payment_code }}</b>
                                <span>{{ $payment->package?->name ?? 'Paket Premium' }} - {{ $payment->formattedAmount() }}</span>
                            </div>
                            <em class="status-{{ $payment->payment_status }}">{{ \App\Models\PremiumPayment::STATUSES[$payment->payment_status] ?? $payment->payment_status }}</em>
                        </div>
                    @empty
                        <p class="premium-muted">Belum ada riwayat pembayaran.</p>
                    @endforelse
                </div>
            </aside>
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    html, body { min-height:100%; overflow-y:auto; }
    .premium-page { min-height:100vh; padding:1rem; background:#080d18; }
    .premium-top, .premium-shell { width:min(1220px,100%); margin-inline:auto; }
    .premium-top { display:flex; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
    .premium-back { display:inline-flex; align-items:center; justify-content:center; min-height:44px; border:1px solid var(--border); border-radius:14px; background:rgba(255,255,255,.06); padding:.7rem 1rem; font-weight:950; }
    .premium-ghost { color:var(--muted); }
    .premium-shell { border:1px solid var(--border); border-radius:28px; background:rgba(18,24,38,.88); box-shadow:var(--shadow); padding:clamp(1rem,4vw,2rem); }
    .premium-head { max-width:780px; margin-bottom:1rem; }
    .premium-head small, .premium-status small, .premium-package small, .premium-history small { color:var(--cyan); font-weight:950; letter-spacing:.13em; text-transform:uppercase; font-size:.74rem; }
    .premium-head h1 { margin:.45rem 0; font-size:clamp(2rem,5vw,4rem); letter-spacing:-.08em; line-height:.96; }
    .premium-head p, .premium-status p, .premium-package p, .premium-muted { color:var(--muted); font-weight:760; line-height:1.6; }
    .premium-alert { border:1px solid rgba(73,211,139,.32); background:rgba(73,211,139,.1); border-radius:16px; padding:.9rem 1rem; margin:1rem 0; font-weight:900; }
    .premium-danger { border-color:rgba(255,107,138,.35); background:rgba(255,107,138,.1); }
    .premium-status { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; border:1px solid var(--border); border-radius:22px; background:rgba(255,255,255,.045); padding:1rem; margin-bottom:1rem; }
    .premium-status h2 { margin:.35rem 0; font-size:clamp(1.2rem,3vw,1.8rem); letter-spacing:-.05em; }
    .premium-status span { border:1px solid rgba(102,232,247,.25); border-radius:999px; background:rgba(102,232,247,.09); color:var(--cyan); padding:.6rem .85rem; font-weight:950; white-space:nowrap; }
    .premium-grid { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:1rem; align-items:start; }
    .premium-packages { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1rem; }
    .premium-package, .premium-history { border:1px solid var(--border); border-radius:22px; background:rgba(255,255,255,.045); padding:1rem; }
    .premium-package-head { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; }
    .premium-package h2 { margin:.35rem 0; font-size:1.55rem; letter-spacing:-.05em; }
    .premium-package strong { font-size:1.25rem; white-space:nowrap; }
    .premium-package ul { display:grid; gap:.55rem; margin:.9rem 0; padding-left:1.1rem; color:#dce7f8; font-weight:800; }
    .premium-form { display:grid; gap:.8rem; margin-top:1rem; }
    .premium-payment-note { display:grid; gap:.25rem; border:1px solid rgba(102,232,247,.18); border-radius:16px; background:rgba(102,232,247,.06); padding:.85rem; }
    .premium-midtrans-note { margin-top:1rem; border-color:rgba(250,204,21,.24); background:rgba(250,204,21,.07); }
    .premium-midtrans-disabled { display:grid; gap:.25rem; border:1px solid rgba(255,107,138,.28); border-radius:12px; background:rgba(255,107,138,.08); color:#ffe7ec; padding:.85rem; margin-top:.8rem; font-weight:850; }
    .premium-midtrans-disabled span { color:var(--muted); line-height:1.5; }
    .premium-payment-note b { color:#eaffff; }
    .premium-payment-note span, .premium-payment-note em { color:var(--muted); font-style:normal; font-weight:800; line-height:1.45; }
    .premium-form label { display:grid; gap:.4rem; color:var(--muted); font-weight:900; font-size:.86rem; }
    .premium-form input, .premium-form textarea { width:100%; border:1px solid var(--border); border-radius:15px; background:rgba(255,255,255,.06); color:var(--text); padding:.82rem; outline:none; font-weight:850; }
    .premium-form button { border:0; border-radius:999px; background:linear-gradient(135deg,var(--cyan),var(--primary)); color:#07101f; padding:.9rem 1rem; font-weight:950; cursor:pointer; }
    .premium-divider { display:flex; align-items:center; gap:.7rem; margin:1rem 0 .2rem; color:var(--muted); font-size:.78rem; font-weight:950; text-transform:uppercase; }
    .premium-divider::before, .premium-divider::after { content:''; height:1px; flex:1; background:rgba(255,255,255,.12); }
    .premium-form .midtrans-button { background:linear-gradient(135deg,#facc15,#fb7185); color:#140c05; }
    .premium-form button:disabled { opacity:.7; cursor:not-allowed; }
    .premium-error { color:#ffb4c2; font-style:normal; }
    .premium-error[hidden] { display:none; }
    .premium-history { position:sticky; top:1rem; }
    .premium-history-list { display:grid; gap:.7rem; margin-top:.85rem; }
    .premium-history-row { display:grid; gap:.55rem; border:1px solid rgba(255,255,255,.07); border-radius:16px; padding:.8rem; background:rgba(255,255,255,.035); }
    .premium-history-row b { display:block; }
    .premium-history-row span { color:var(--muted); font-size:.84rem; font-weight:800; }
    .premium-history-row em { width:max-content; border-radius:999px; padding:.4rem .6rem; font-style:normal; font-size:.76rem; font-weight:950; background:rgba(255,255,255,.08); }
    .status-approved, .status-paid { color:#b9ffd8; }
    .status-pending { color:#fff3a8; }
    .status-rejected, .status-expired { color:#ffb4c2; }
    @media (max-width:920px){ .premium-grid{grid-template-columns:1fr;} .premium-history{position:static;} .premium-status{flex-direction:column;} }
</style>
@endpush

@if ($midtransEnabled && filled($midtransClientKey))
    @push('scripts')
        <script src="{{ $midtransSnapScriptUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
        <script>
            (() => {
                const forms = document.querySelectorAll('[data-midtrans-form]');

                if (!forms.length) {
                    return;
                }

                const resetButton = (button) => {
                    button.disabled = false;
                    button.textContent = button.dataset.originalText || 'Bayar QRIS/DANA via Midtrans';
                };

                forms.forEach((form) => {
                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        const button = form.querySelector('[data-midtrans-button]');
                        const message = form.querySelector('[data-midtrans-message]');
                        button.dataset.originalText = button.dataset.originalText || button.textContent;
                        button.disabled = true;
                        button.textContent = 'Membuka pembayaran...';
                        message.hidden = true;
                        message.textContent = '';

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: new FormData(form),
                            });

                            const payload = await response.json().catch(() => ({}));

                            if (!response.ok || !payload.snap_token) {
                                throw new Error(payload.message || 'Gagal membuka pembayaran Midtrans.');
                            }

                            if (!window.snap) {
                                throw new Error('Snap Midtrans belum siap. Coba muat ulang halaman.');
                            }

                            window.snap.pay(payload.snap_token, {
                                onSuccess: () => {
                                    window.location.href = "{{ route('learning.premium') }}?payment=success";
                                },
                                onPending: () => {
                                    window.location.href = "{{ route('learning.premium') }}?payment=pending";
                                },
                                onError: () => {
                                    message.textContent = 'Pembayaran gagal diproses. Kamu masih bisa mencoba lagi atau upload bukti manual.';
                                    message.hidden = false;
                                    resetButton(button);
                                },
                                onClose: () => {
                                    message.textContent = 'Popup pembayaran ditutup. Klik tombol Midtrans lagi untuk melanjutkan pembayaran.';
                                    message.hidden = false;
                                    resetButton(button);
                                },
                            });
                        } catch (error) {
                            message.textContent = error.message || 'Terjadi kesalahan saat membuka pembayaran.';
                            message.hidden = false;
                            resetButton(button);
                        }
                    });
                });
            })();
        </script>
    @endpush
@endif
