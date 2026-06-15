@extends('layouts.learning')

@section('title', 'Selamat Datang - YoLearning')

@section('content')
<main class="welcome-gate" data-target-url="{{ $targetUrl }}">
    <div class="welcome-orb orb-one"></div>
    <div class="welcome-orb orb-two"></div>

    <section class="welcome-copy" aria-live="polite">
        <p class="welcome-kicker">YoLearning</p>
        <h1 class="welcome-line welcome-line-one">
            Selamat datang, {{ \Illuminate\Support\Str::limit($user->name, 24) }}
        </h1>
        <h2 class="welcome-line welcome-line-two">
            Ayo mulai petualanganmu
        </h2>
        <p class="welcome-caption">
            Menyiapkan dashboard belajar khusus untuk akun kamu.
        </p>
    </section>
</main>
@endsection

@push('scripts')
<script>
    const welcomeGate = document.querySelector('.welcome-gate');
    const targetUrl = welcomeGate?.dataset.targetUrl || '{{ route('dashboard') }}';

    window.setTimeout(() => {
        document.body.classList.add('welcome-leaving');
    }, 6200);

    window.setTimeout(() => {
        window.location.href = targetUrl;
    }, 6200);
</script>
@endpush
