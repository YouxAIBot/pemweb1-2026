@extends('layouts.learning')

@section('title', 'Video Question - YoLearning')

@php
    function videoQuestionPublicUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $value = ltrim($value, '/');

        return str_starts_with($value, 'storage/')
            ? asset($value)
            : asset('storage/' . $value);
    }

    function videoQuestionYoutubeEmbed(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $parts = parse_url($url);
        $host = str_replace('www.', '', $parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $query = [];
        parse_str($parts['query'] ?? '', $query);

        $id = null;

        if ($host === 'youtu.be') {
            $id = explode('/', $path)[0] ?? null;
        }

        if (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
            $id = $query['v'] ?? null;

            if (! $id && str_starts_with($path, 'shorts/')) {
                $id = explode('/', $path)[1] ?? null;
            }

            if (! $id && str_starts_with($path, 'embed/')) {
                $id = explode('/', $path)[1] ?? null;
            }
        }

        return $id ? 'https://www.youtube.com/embed/' . urlencode($id) : null;
    }
@endphp

@section('content')
<div class="videoq-page">
    <header class="videoq-top">
        <a href="{{ route('learning.games') }}" class="videoq-back">&larr; Turnamen</a>
        <span class="videoq-chip">{{ $profile->language?->name ?? 'Bahasa aktif' }}</span>
    </header>

    <main class="videoq-shell">
        <section class="videoq-head">
            <small>Mode Kompetitif</small>
            <h1>Video Question</h1>
            <p>Tonton video pendek, jawab soal, lalu kumpulkan skor khusus mode video untuk bahasa aktif.</p>
        </section>

        @if ($result)
            <div class="videoq-result">
                <strong>Skor terakhir: {{ $result['score'] }}</strong>
                <span>{{ $result['correct_count'] }} / {{ $result['total_questions'] }} benar dalam {{ $result['duration_seconds'] }} detik.</span>
            </div>
        @endif

        <div class="videoq-layout">
            <section class="videoq-panel">
                @if ($questions->isEmpty())
                    <div class="videoq-empty">
                        <small>Belum ada soal video</small>
                        <h2>Konten Video Question belum dibuat</h2>
                        <p>Admin bisa menambahkan soal dengan jenis Video Question dari LEARNING CMS - Questions.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('learning.video-question.submit') }}" data-videoq-form>
                        @csrf
                        <input type="hidden" name="duration_seconds" value="0" data-duration-input>

                        <div class="videoq-question-list">
                            @foreach ($questions as $question)
                                @php
                                    $settings = $question->settings ?? [];
                                    $videoUrl = videoQuestionPublicUrl($settings['video_path'] ?? null) ?: ($settings['video_url'] ?? null);
                                    $embedUrl = videoQuestionYoutubeEmbed($videoUrl);
                                @endphp
                                <article class="videoq-card">
                                    <input type="hidden" name="question_ids[]" value="{{ $question->id }}">
                                    <div class="videoq-frame">
                                        @if ($embedUrl)
                                            <iframe src="{{ $embedUrl }}" title="Video question" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                        @elseif ($videoUrl)
                                            <video src="{{ $videoUrl }}" controls playsinline></video>
                                        @else
                                            <div class="videoq-no-video">Video belum tersedia</div>
                                        @endif
                                    </div>

                                    <div class="videoq-copy">
                                        <small>Soal {{ $loop->iteration }}</small>
                                        <h2>{{ $question->question_text }}</h2>
                                        @if ($question->instruction)
                                            <p>{{ $question->instruction }}</p>
                                        @endif
                                    </div>

                                    <div class="videoq-options">
                                        @foreach ($question->options as $option)
                                            <label>
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}">
                                                <span>{{ $option->option_text }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <button type="submit" class="videoq-submit">Simpan Skor</button>
                    </form>
                @endif
            </section>

            <aside class="videoq-side">
                <section class="videoq-side-card">
                    <small>Skor Terbaik Saya</small>
                    <h2>{{ $myBest?->score ?? 0 }}</h2>
                    <p>{{ $myBest ? (($myBest->correct_count ?? 0) . ' / ' . ($myBest->total_questions ?? 0) . ' benar') : 'Belum ada percobaan.' }}</p>
                </section>

                <section class="videoq-side-card">
                    <small>Leaderboard Video</small>
                    <div class="videoq-rank-list">
                        @forelse ($leaderboard as $row)
                            <div class="videoq-rank-row">
                                <span>#{{ $loop->iteration }}</span>
                                <strong>{{ $row->user?->name ?? 'User' }}</strong>
                                <em>{{ $row->score }}</em>
                            </div>
                        @empty
                            <p>Belum ada leaderboard.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
    html, body { min-height: 100%; overflow-y: auto; }
    .videoq-page { min-height: 100vh; padding: 1rem; background: #080d18; }
    .videoq-top, .videoq-shell { width: min(1180px, 100%); margin-inline: auto; }
    .videoq-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .videoq-back, .videoq-chip { display: inline-flex; border: 1px solid var(--border); border-radius: 999px; background: rgba(255,255,255,.055); padding: .72rem 1rem; font-weight: 950; }
    .videoq-shell { border: 1px solid var(--border); border-radius: 30px; background: rgba(18,24,38,.86); box-shadow: var(--shadow); padding: clamp(1rem,4vw,2rem); }
    .videoq-head { max-width: 760px; margin-bottom: 1rem; }
    .videoq-head small, .videoq-copy small, .videoq-side-card small, .videoq-empty small { color: var(--cyan); font-size: .76rem; font-weight: 950; letter-spacing: .13em; text-transform: uppercase; }
    .videoq-head h1 { margin: .45rem 0; font-size: clamp(2.1rem,6vw,4.2rem); letter-spacing: -.08em; line-height: .96; }
    .videoq-head p, .videoq-copy p, .videoq-side-card p, .videoq-rank-list p, .videoq-empty p { color: var(--muted); font-weight: 760; line-height: 1.6; }
    .videoq-result { display: flex; flex-wrap: wrap; gap: .6rem 1rem; justify-content: space-between; border: 1px solid rgba(73,211,139,.32); border-radius: 18px; background: rgba(73,211,139,.1); padding: .9rem 1rem; margin-bottom: 1rem; font-weight: 900; }
    .videoq-layout { display: grid; grid-template-columns: minmax(0,1fr) 310px; gap: 1rem; align-items: start; }
    .videoq-question-list { display: grid; gap: 1rem; }
    .videoq-card, .videoq-side-card, .videoq-empty { border: 1px solid var(--border); border-radius: 24px; background: rgba(255,255,255,.045); padding: 1rem; }
    .videoq-frame { width: 100%; aspect-ratio: 16 / 9; overflow: hidden; border: 1px solid rgba(255,255,255,.08); border-radius: 20px; background: #050812; }
    .videoq-frame iframe, .videoq-frame video { width: 100%; height: 100%; border: 0; display: block; object-fit: cover; }
    .videoq-no-video { height: 100%; display: grid; place-items: center; color: var(--muted); font-weight: 950; }
    .videoq-copy { margin-top: 1rem; }
    .videoq-copy h2 { margin: .35rem 0; font-size: clamp(1.2rem,3vw,1.8rem); letter-spacing: -.05em; }
    .videoq-options { display: grid; gap: .65rem; margin-top: .85rem; }
    .videoq-options label { display: flex; align-items: center; gap: .75rem; border: 1px solid rgba(255,255,255,.08); border-radius: 16px; background: rgba(255,255,255,.04); padding: .85rem; cursor: pointer; font-weight: 900; }
    .videoq-options input { width: 18px; height: 18px; accent-color: #66e8f7; }
    .videoq-submit { margin-top: 1rem; width: 100%; border: 0; border-radius: 999px; background: linear-gradient(135deg,var(--cyan),var(--primary)); color: #07101f; padding: 1rem; font-weight: 950; cursor: pointer; }
    .videoq-side { display: grid; gap: 1rem; position: sticky; top: 1rem; }
    .videoq-side-card h2 { margin: .35rem 0; font-size: 2.2rem; letter-spacing: -.07em; }
    .videoq-rank-list { display: grid; gap: .55rem; margin-top: .8rem; }
    .videoq-rank-row { display: grid; grid-template-columns: 42px 1fr auto; gap: .7rem; align-items: center; border-top: 1px solid rgba(255,255,255,.07); padding-top: .65rem; }
    .videoq-rank-row span { color: var(--cyan); font-weight: 950; }
    .videoq-rank-row em { color: var(--muted); font-style: normal; font-weight: 950; }
    @media (max-width: 920px) { .videoq-layout { grid-template-columns: 1fr; } .videoq-side { position: static; } }
    @media (max-width: 640px) { .videoq-top, .videoq-result { align-items: flex-start; flex-direction: column; } }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-videoq-form]');
    const durationInput = document.querySelector('[data-duration-input]');
    const startedAt = Date.now();

    form?.addEventListener('submit', () => {
        durationInput.value = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
    });
})();
</script>
@endpush
