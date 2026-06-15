@extends('layouts.learning')

@section('title', $level->title . ' - YoLearning')

@section('content')
<div class="app-frame">
    @include('frontend.learning.partials.sidebar')

    <main class="main-panel">
        <div class="main-topbar">
            <div>
                <h1>{{ $level->title }}</h1>
                <p>{{ $part->title }} • {{ $level->typeLabel() }} • {{ $level->xp_reward }} XP</p>
            </div>
            <a href="{{ route('learning.parts.show', $part) }}" class="logout-link">Peta Level</a>
        </div>

        <div class="content-area">
            <section class="hero-learning">
                <small>{{ $level->typeLabel() }}</small>
                <h2>{{ $level->title }}</h2>
                <p>{{ $level->description ?: 'Ini adalah preview latihan. Admin bisa mengatur tipe level, soal, pilihan jawaban, audio listening, dan pembahasan dari Filament.' }}</p>
            </section>

            <div class="questions-list">
                @forelse ($level->questions as $question)
                    <article class="question-card" style="animation-delay: {{ $loop->index * 0.08 }}s">
                        <small>{{ str($question->type)->headline() }} • {{ $question->points }} poin</small>
                        <h3>{{ $question->instruction ?: 'Jawab pertanyaan berikut' }}</h3>
                        <p>{{ $question->question_text }}</p>

                        @if ($question->audio_path)
                            <audio class="audio-player" controls src="{{ asset('storage/' . $question->audio_path) }}"></audio>
                        @endif

                        @if ($question->image_path)
                            <img src="{{ asset('storage/' . $question->image_path) }}" alt="Gambar soal" style="margin-top:.8rem;border-radius:18px;border:1px solid var(--border);max-height:260px;object-fit:cover;width:100%">
                        @endif

                        @if ($question->options->isNotEmpty())
                            <div class="option-list">
                                @foreach ($question->options as $option)
                                    <div class="option-chip {{ $option->is_correct ? 'correct' : '' }}">
                                        <span>{{ $option->option_text }}</span>
                                        @if ($option->audio_path)
                                            <audio controls src="{{ asset('storage/' . $option->audio_path) }}" style="max-width:190px;height:32px"></audio>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($question->explanation)
                            <p style="margin-top:.8rem"><strong style="color:var(--cyan)">Pembahasan:</strong> {{ $question->explanation }}</p>
                        @endif
                    </article>
                @empty
                    <article class="question-card">
                        <small>Belum ada soal</small>
                        <h3>Konten level belum dibuat</h3>
                        <p>Admin bisa menambahkan soal lewat LEARNING CMS → Questions. Untuk level listening, upload audio di field Audio Soal.</p>
                    </article>
                @endforelse
            </div>
        </div>
    </main>

    @include('frontend.learning.partials.right-panel')
</div>
@endsection
