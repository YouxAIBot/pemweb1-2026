@extends('layouts.learning')

@section('title', 'Profil Pengguna - YoLearning')

@section('content')
@php
    $avatarUrl = $user->avatar_url ? asset('storage/' . $user->avatar_url) : null;
    $activePremium = $user->activePremium;
    $isPremium = $user->isPremium();
    $preference = $settings['preferences'] ?? [];
    $tabTitles = [
        'account' => ['eyebrow' => 'Account', 'title' => 'Account'],
        'edit-profile' => ['eyebrow' => 'Edit Profiles', 'title' => 'Profil Pengguna'],
        'preferences' => ['eyebrow' => 'Learning Settings', 'title' => 'Preferensi Belajar'],
        'progress' => ['eyebrow' => 'Progress', 'title' => 'Progress & Data'],
    ];
    $currentTitle = $tabTitles[$tab] ?? $tabTitles['account'];
@endphp
<div class="settings-page">
    <main class="settings-shell">
        <aside class="settings-sidebar">
            <a href="{{ route('dashboard') }}" class="settings-brand">
                <span>{{ $setting->brand_initial ?? 'Y' }}</span>
                <strong>{{ $setting->brand_text ?? 'YoLearning' }}</strong>
            </a>

            <div class="settings-profile-card">
                <div class="settings-avatar">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Foto profil">
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <h2>{{ $user->name }}</h2>
                    <p>Edit Profiles ✎</p>
                </div>
            </div>

            <div class="settings-nav-group">
                <span class="settings-nav-label">Account</span>
                <a href="{{ route('learning.profile.edit', ['tab' => 'account']) }}" class="settings-nav-item {{ $tab === 'account' ? 'active' : '' }}">Account Info</a>
                <a href="{{ route('learning.profile.edit', ['tab' => 'edit-profile']) }}" class="settings-nav-item {{ $tab === 'edit-profile' ? 'active' : '' }}">Edit Profiles</a>
                <a href="{{ route('learning.profile.edit', ['tab' => 'account']) }}#security" class="settings-nav-item">Password &amp; Security</a>
            </div>

            <div class="settings-nav-group">
                <span class="settings-nav-label">Learning</span>
                <a href="{{ route('learning.settings', ['tab' => 'preferences']) }}" class="settings-nav-item {{ $tab === 'preferences' ? 'active' : '' }}">Preferensi</a>
                <a href="{{ route('learning.settings', ['tab' => 'progress']) }}" class="settings-nav-item {{ $tab === 'progress' ? 'active' : '' }}">Progress &amp; Data</a>
            </div>

            <div class="settings-nav-group">
                <span class="settings-nav-label">Billing</span>
                <a href="{{ route('learning.profile.edit', ['tab' => 'account']) }}#subscription" class="settings-nav-item">Subscription</a>
            </div>
        </aside>

        <section class="settings-content">
            <div class="settings-topbar">
                <div>
                    <small>{{ $currentTitle['eyebrow'] }}</small>
                    <h1>{{ $currentTitle['title'] }}</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="settings-close">✕</a>
            </div>

            @if (session('learning_success'))
                <div class="settings-alert">{{ session('learning_success') }}</div>
            @endif

            @if ($tab === 'edit-profile')
                <div class="settings-edit-layout">
                    <form method="POST" action="{{ route('learning.profile.update') }}" enctype="multipart/form-data" class="settings-form-card">
                        @csrf
                        <input type="hidden" name="tab" value="edit-profile">

                        <div class="settings-hero-card">
                            <div>
                                <small>YoLearning Profile</small>
                                <h2>Bikin profil kamu lebih rapi</h2>
                                <p>Ubah nama, email, bio, foto profil, dan password tanpa elemen tambahan yang bikin ribet.</p>
                            </div>
                        </div>

                        <label class="settings-field">
                            <span>Display Name</span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Email</span>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Bio</span>
                            <textarea name="bio" rows="4" placeholder="Tulis bio singkat tentang kamu...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Avatar</span>
                            <input type="file" name="avatar" accept="image/*">
                            @error('avatar') <em>{{ $message }}</em> @enderror
                        </label>

                        <div class="settings-section-divider" id="security">Password &amp; Security</div>

                        <label class="settings-field">
                            <span>Password Lama</span>
                            <input type="password" name="current_password" autocomplete="current-password" placeholder="Isi jika ingin ganti password">
                            @error('current_password') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Password Baru</span>
                            <input type="password" name="password" autocomplete="new-password">
                            @error('password') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Konfirmasi Password Baru</span>
                            <input type="password" name="password_confirmation" autocomplete="new-password">
                        </label>

                        <button type="submit" class="settings-save-btn">Simpan Perubahan</button>
                    </form>

                    <aside class="settings-preview-panel">
                        <div class="preview-card">
                            <div class="preview-cover"></div>
                            <div class="preview-body">
                                <div class="preview-avatar">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="Avatar preview">
                                    @else
                                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <h3>{{ old('name', $user->name) }}</h3>
                                <strong>{{ strtok(old('email', $user->email), '@') }}</strong>
                                <p>{{ old('bio', $user->bio ?: 'Belum ada bio. Tambahkan bio singkat supaya profil kamu lebih personal.') }}</p>
                                <div class="preview-badge-row">
                                    <span>{{ $profile?->language?->name ?? 'Belum pilih bahasa' }}</span>
                                    <span>{{ ucfirst($profile?->ability_level ?? 'pemula') }}</span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            @elseif ($tab === 'preferences')
                <form method="POST" action="{{ route('learning.settings.preferences.update') }}" class="settings-form-card settings-preference-form">
                    @csrf

                    <div class="settings-hero-card">
                        <div>
                            <small>Preferensi Belajar</small>
                            <h2>Atur pengalaman belajar kamu</h2>
                            <p>Preferensi ini disimpan di akun kamu untuk audio, tampilan, target harian, dan privasi profil.</p>
                        </div>
                    </div>

                    <div class="settings-pref-grid">
                        <label class="settings-field">
                            <span>Target Belajar Harian</span>
                            <input type="number" name="daily_goal_minutes" min="5" max="180" value="{{ old('daily_goal_minutes', $preference['daily_goal_minutes'] ?? 10) }}" required>
                            @error('daily_goal_minutes') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Tema Tampilan</span>
                            <select name="theme_mode">
                                @foreach (['system' => 'Ikuti Sistem', 'dark' => 'Gelap', 'light' => 'Terang'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('theme_mode', $preference['theme_mode'] ?? 'system') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('theme_mode') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Jam Pengingat</span>
                            <input type="time" name="study_reminder_time" value="{{ old('study_reminder_time', $preference['study_reminder_time'] ?? '19:00') }}">
                            @error('study_reminder_time') <em>{{ $message }}</em> @enderror
                        </label>

                        <label class="settings-field">
                            <span>Voice Pilihan</span>
                            <input type="text" name="preferred_voice" value="{{ old('preferred_voice', $preference['preferred_voice'] ?? '') }}" placeholder="en-US-AriaNeural">
                            @error('preferred_voice') <em>{{ $message }}</em> @enderror
                        </label>
                    </div>

                    <div class="settings-toggle-grid">
                        @foreach ([
                            'sound_effects' => ['Efek Suara', 'Feedback benar/salah memakai suara ringan.'],
                            'autoplay_audio' => ['Autoplay Audio', 'Audio listening diputar otomatis jika browser mengizinkan.'],
                            'slow_audio_mode' => ['Mode Audio Lambat', 'Audio pembelajaran diputar lebih pelan untuk pemula.'],
                            'show_romanization' => ['Tampilkan Romanisasi', 'Huruf menampilkan cara baca.'],
                            'public_profile' => ['Profil Publik', 'Nama dan statistik dasar boleh muncul di leaderboard.'],
                            'study_reminder_enabled' => ['Pengingat Belajar', 'Aktifkan pengingat sesuai jam yang dipilih.'],
                        ] as $name => [$label, $description])
                            <label class="settings-toggle-card">
                                <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $preference[$name] ?? false))>
                                <span>
                                    <strong>{{ $label }}</strong>
                                    <small>{{ $description }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="settings-save-btn">Simpan Preferensi</button>
                </form>
            @elseif ($tab === 'progress')
                <div class="settings-section">
                    <h2>Ringkasan Progress</h2>
                    <div class="settings-metric-grid">
                        <div class="settings-metric-card"><span>Level Selesai</span><strong>{{ $progressSummary['levels_completed'] }} / {{ $progressSummary['levels_total'] }}</strong></div>
                        <div class="settings-metric-card"><span>Soal Terjawab</span><strong>{{ $progressSummary['questions_answered'] }}</strong></div>
                        <div class="settings-metric-card"><span>Jawaban Benar</span><strong>{{ $progressSummary['questions_correct'] }}</strong></div>
                        <div class="settings-metric-card"><span>Total XP</span><strong>{{ $profile?->total_xp ?? 0 }}</strong></div>
                    </div>
                </div>

                <div class="settings-section">
                    <h2>Bahasa Saya</h2>
                    <div class="settings-table-card">
                        @forelse ($languages as $language)
                            <div class="settings-row">
                                <span>{{ $language->flag_label ?: 'Lang' }}</span>
                                <strong>{{ $language->name }}</strong>
                                <a href="{{ route('learning.onboarding') }}">Kelola</a>
                            </div>
                        @empty
                            <div class="settings-row">
                                <span>Bahasa</span>
                                <strong>Belum ada bahasa aktif.</strong>
                                <a href="{{ route('learning.onboarding') }}">Pilih</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="settings-section">
                    <h2>Reset Progress</h2>
                    <form method="POST" action="{{ route('learning.settings.progress.reset') }}" class="settings-danger-card">
                        @csrf
                        <div>
                            <strong>Reset progress bahasa aktif</strong>
                            <p>Progress level dan jawaban soal untuk {{ $profile?->language?->name ?? 'bahasa aktif' }} akan dihapus. Data akun, premium, dan pembayaran tetap aman.</p>
                        </div>
                        <label class="settings-field">
                            <span>Ketik RESET</span>
                            <input type="text" name="confirmation" placeholder="RESET">
                            @error('confirmation') <em>{{ $message }}</em> @enderror
                        </label>
                        <button type="submit">Reset Progress</button>
                    </form>
                </div>
            @else
                <div class="settings-section">
                    <h2>Account Info</h2>
                    <div class="settings-table-card">
                        <div class="settings-row">
                            <span>Username</span>
                            <strong>{{ strtok($user->email, '@') }}</strong>
                            <a href="{{ route('learning.profile.edit', ['tab' => 'edit-profile']) }}">Edit</a>
                        </div>
                        <div class="settings-row">
                            <span>Email</span>
                            <strong>{{ $user->email }}</strong>
                            <a href="{{ route('learning.profile.edit', ['tab' => 'edit-profile']) }}">Edit</a>
                        </div>
                        <div class="settings-row">
                            <span>Bio</span>
                            <strong>{{ $user->bio ?: 'Belum ada bio.' }}</strong>
                            <a href="{{ route('learning.profile.edit', ['tab' => 'edit-profile']) }}">Edit</a>
                        </div>
                        <div class="settings-row">
                            <span>Bahasa Aktif</span>
                            <strong>{{ $profile?->language?->name ?? 'Belum memilih bahasa' }}</strong>
                            <a href="{{ route('learning.onboarding') }}">Ganti</a>
                        </div>
                    </div>
                </div>

                <div class="settings-section" id="security">
                    <h2>Password &amp; Security</h2>
                    <div class="settings-table-card">
                        <div class="settings-row">
                            <span>Password</span>
                            <strong>••••••••••••</strong>
                            <a href="{{ route('learning.profile.edit', ['tab' => 'edit-profile']) }}#security">Edit</a>
                        </div>
                        <div class="settings-row muted-row">
                            <span>Status keamanan</span>
                            <strong>{{ $user->email_verified_at ? 'Email terverifikasi' : 'Email belum diverifikasi' }}</strong>
                            <span></span>
                        </div>
                    </div>
                </div>

                <div class="settings-section" id="subscription">
                    <h2>Subscription</h2>
                    <div class="subscription-card">
                        <div>
                            <small>{{ $isPremium ? 'Premium Active' : 'Free Plan' }}</small>
                            <h3>{{ $isPremium ? 'Belajar tanpa iklan sedang aktif' : 'Akun kamu masih menggunakan paket gratis' }}</h3>
                            <p>
                                @if ($isPremium)
                                    Premium aktif sampai {{ $activePremium?->ends_at?->format('d M Y H:i') }}. Iklan saat masuk dan keluar soal otomatis tidak tampil.
                                @else
                                    Upgrade ke premium untuk pengalaman belajar yang lebih nyaman tanpa iklan saat masuk dan keluar soal.
                                @endif
                            </p>
                            <a href="{{ route('learning.premium') }}" class="subscription-action">{{ $isPremium ? 'Lihat Riwayat Pembayaran' : 'Upgrade Premium' }}</a>
                        </div>
                        <span class="subscription-pill">{{ $isPremium ? 'Aktif' : 'Gratis' }}</span>
                    </div>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    html, body { min-height:100%; overflow-y:auto; }
    .settings-page { min-height:100vh; padding:1rem; background:#080d18; }
    .settings-shell { width:min(1440px,100%); margin:0 auto; display:grid; grid-template-columns:280px minmax(0,1fr); gap:1.2rem; }
    .settings-sidebar, .settings-content { border:1px solid var(--border); border-radius:28px; background:rgba(18,24,38,.88); box-shadow:var(--shadow); }
    .settings-sidebar { padding:1rem; }
    .settings-content { padding:1.2rem; }
    .settings-brand { display:flex; align-items:center; gap:.8rem; font-weight:950; margin-bottom:1rem; }
    .settings-brand span { width:42px; height:42px; border-radius:14px; display:grid; place-items:center; background:linear-gradient(145deg,var(--cyan),var(--primary)); color:#07101f; }
    .settings-profile-card { display:flex; align-items:center; gap:.9rem; padding:1rem; border-radius:18px; background:rgba(255,255,255,.05); border:1px solid var(--border); margin-bottom:1rem; }
    .settings-profile-card h2 { font-size:1.15rem; margin-bottom:.15rem; }
    .settings-profile-card p { color:var(--muted); font-weight:800; }
    .settings-avatar { width:58px; height:58px; border-radius:20px; overflow:hidden; display:grid; place-items:center; background:linear-gradient(145deg,var(--cyan),var(--primary)); color:#07101f; font-weight:950; font-size:1.5rem; flex:0 0 auto; }
    .settings-avatar img { width:100%; height:100%; object-fit:cover; }
    .settings-nav-group { margin-top:1rem; display:grid; gap:.4rem; }
    .settings-nav-label { color:var(--muted); font-size:.82rem; font-weight:900; text-transform:uppercase; letter-spacing:.09em; margin:0 .55rem .2rem; }
    .settings-nav-item { display:flex; align-items:center; min-height:44px; padding:.78rem .9rem; border-radius:14px; color:var(--muted); font-weight:850; transition:.2s; }
    .settings-nav-item:hover, .settings-nav-item.active { background:#2a2d36; color:var(--text); }
    .settings-topbar { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1rem; }
    .settings-topbar small { color:var(--muted); font-weight:950; text-transform:uppercase; letter-spacing:.1em; }
    .settings-topbar h1 { margin-top:.3rem; font-size:clamp(1.7rem,4vw,2.4rem); letter-spacing:-.05em; }
    .settings-close { width:44px; height:44px; display:grid; place-items:center; border:1px solid var(--border); border-radius:14px; background:rgba(255,255,255,.05); font-size:1.2rem; }
    .settings-alert { border:1px solid rgba(73,211,139,.3); background:rgba(73,211,139,.11); color:#d8ffea; border-radius:16px; padding:.9rem 1rem; margin-bottom:1rem; font-weight:900; }
    .settings-section { margin-bottom:1.3rem; }
    .settings-section h2 { font-size:2rem; letter-spacing:-.05em; margin-bottom:1rem; }
    .settings-table-card, .settings-form-card, .subscription-card, .preview-card { border:1px solid var(--border); border-radius:24px; background:rgba(255,255,255,.04); }
    .settings-table-card { overflow:hidden; }
    .settings-row { display:grid; grid-template-columns:minmax(140px,180px) 1fr auto; gap:1rem; align-items:center; padding:1rem 1.1rem; border-bottom:1px solid rgba(255,255,255,.06); }
    .settings-row:last-child { border-bottom:0; }
    .settings-row span { color:var(--muted); font-weight:850; }
    .settings-row strong { font-weight:850; overflow-wrap:anywhere; }
    .settings-row a { display:inline-flex; align-items:center; justify-content:center; min-width:92px; padding:.75rem 1rem; border-radius:14px; background:rgba(255,255,255,.08); font-weight:900; }
    .muted-row { opacity:.9; }
    .subscription-card { padding:1.2rem; display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; }
    .subscription-card small { color:var(--cyan); font-weight:950; text-transform:uppercase; letter-spacing:.1em; }
    .subscription-card h3 { margin:.4rem 0; font-size:1.3rem; letter-spacing:-.04em; }
    .subscription-card p { color:var(--muted); font-weight:780; line-height:1.6; }
    .subscription-pill { padding:.65rem .9rem; border-radius:999px; background:rgba(102,232,247,.12); border:1px solid rgba(102,232,247,.24); color:var(--cyan); font-weight:950; }
    .subscription-action { display:inline-flex; margin-top:.85rem; border-radius:999px; background:linear-gradient(135deg,var(--cyan),var(--primary)); color:#07101f; padding:.72rem 1rem; font-weight:950; }
    .settings-edit-layout { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:1rem; align-items:start; }
    .settings-form-card { padding:1rem; }
    .settings-hero-card { border:1px solid rgba(116,88,255,.28); background:linear-gradient(90deg, rgba(84,49,160,.22), rgba(30,35,60,.2)); border-radius:22px; padding:1rem 1.1rem; margin-bottom:1rem; }
    .settings-hero-card small { color:var(--cyan); font-weight:950; text-transform:uppercase; letter-spacing:.1em; }
    .settings-hero-card h2 { margin:.4rem 0; font-size:1.55rem; letter-spacing:-.04em; }
    .settings-hero-card p { color:var(--muted); font-weight:780; line-height:1.6; }
    .settings-field { display:grid; gap:.45rem; margin-bottom:.9rem; }
    .settings-field span { color:var(--muted); font-size:.84rem; font-weight:900; }
    .settings-field input, .settings-field textarea, .settings-field select { width:100%; border:1px solid var(--border); border-radius:16px; background:rgba(255,255,255,.05); color:var(--text); padding:.9rem 1rem; outline:none; font-weight:850; }
    .settings-field select option { color:#111827; }
    .settings-field textarea { resize:vertical; min-height:100px; }
    .settings-field input:focus, .settings-field textarea:focus, .settings-field select:focus { border-color:rgba(102,232,247,.4); }
    .settings-field em { color:#ffb4c2; font-style:normal; font-weight:850; font-size:.82rem; }
    .settings-section-divider { margin:1rem 0 .85rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,.06); color:var(--cyan); font-weight:950; letter-spacing:.04em; }
    .settings-save-btn { border:0; border-radius:18px; background:linear-gradient(135deg,var(--cyan),var(--primary)); color:#07101f; padding:1rem 1.1rem; font-weight:950; cursor:pointer; width:100%; }
    .settings-pref-grid, .settings-metric-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.9rem; }
    .settings-toggle-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; margin:1rem 0; }
    .settings-toggle-card { display:flex; align-items:flex-start; gap:.75rem; border:1px solid var(--border); border-radius:18px; background:rgba(255,255,255,.04); padding:.9rem; cursor:pointer; }
    .settings-toggle-card input { width:18px; height:18px; margin-top:.15rem; accent-color:#66e8f7; }
    .settings-toggle-card strong { display:block; font-weight:950; }
    .settings-toggle-card small { display:block; color:var(--muted); font-weight:780; line-height:1.45; margin-top:.2rem; }
    .settings-metric-card { border:1px solid var(--border); border-radius:20px; background:rgba(255,255,255,.045); padding:1rem; }
    .settings-metric-card span { display:block; color:var(--muted); font-weight:850; margin-bottom:.35rem; }
    .settings-metric-card strong { font-size:1.55rem; letter-spacing:-.04em; }
    .settings-danger-card { display:grid; gap:1rem; border:1px solid rgba(255,107,138,.32); border-radius:22px; background:rgba(255,107,138,.08); padding:1rem; }
    .settings-danger-card strong { font-size:1.1rem; }
    .settings-danger-card p { color:var(--muted); line-height:1.6; font-weight:780; margin-top:.35rem; }
    .settings-danger-card button { border:0; border-radius:16px; background:#ff5d7d; color:#fff; padding:.9rem 1rem; font-weight:950; cursor:pointer; }
    .preview-card { overflow:hidden; position:sticky; top:1rem; }
    .preview-cover { height:118px; background:radial-gradient(circle at 20% 20%, rgba(102,232,247,.22), transparent 35%), linear-gradient(135deg, #2b0f72, #120824 55%, #2d1a74); }
    .preview-body { padding:1rem; margin-top:-38px; }
    .preview-avatar { width:76px; height:76px; border-radius:24px; overflow:hidden; border:4px solid rgba(18,24,38,.98); display:grid; place-items:center; background:linear-gradient(145deg,var(--cyan),var(--primary)); color:#07101f; font-weight:950; font-size:2rem; }
    .preview-avatar img { width:100%; height:100%; object-fit:cover; }
    .preview-body h3 { margin-top:.8rem; font-size:1.6rem; letter-spacing:-.05em; }
    .preview-body strong { display:block; color:#b8c1d7; margin-top:.2rem; }
    .preview-body p { margin-top:.7rem; color:var(--muted); line-height:1.6; font-weight:780; }
    .preview-badge-row { display:flex; flex-wrap:wrap; gap:.55rem; margin-top:1rem; }
    .preview-badge-row span { padding:.5rem .8rem; border-radius:999px; background:rgba(255,255,255,.08); color:var(--text); font-size:.78rem; font-weight:900; }
    @media (max-width:980px) {
        .settings-shell, .settings-edit-layout { grid-template-columns:1fr; }
        .preview-card { position:static; }
    }
    @media (max-width:640px) {
        .settings-page { padding:.7rem; }
        .settings-row { grid-template-columns:1fr; }
        .subscription-card, .settings-topbar { flex-direction:column; }
        .settings-pref-grid, .settings-toggle-grid, .settings-metric-grid { grid-template-columns:1fr; }
    }
</style>
@endpush
