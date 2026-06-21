<?php

namespace App\Filament\Admin\Pages;

use App\Models\LearningLevel;
use App\Models\LearningQuestion;
use App\Models\LearningQuestionOption;
use App\Services\Integrations\OpenAIQuestionGeneratorService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Throwable;

class AiQuestionGenerator extends Page
{
    protected static ?string $navigationGroup = 'LEARNING CMS';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'AI Question Generator';

    protected static ?string $title = 'AI Question Generator';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.admin.pages.ai-question-generator';

    public ?int $learningLevelId = null;

    public string $questionType = 'multiple_choice';

    public int $questionCount = 3;

    public string $targetLanguage = 'English';

    public string $difficulty = 'beginner';

    public ?string $topic = null;

    public ?string $notes = null;

    public ?string $generatedJson = null;

    public array $generatedPayload = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->email === 'admin@admin.com'));
    }

    public function generate(): void
    {
        $this->validate([
            'learningLevelId' => ['required', 'integer', 'exists:learning_levels,id'],
            'questionType' => ['required', 'in:multiple_choice,word_match,listening,real_case,mixed'],
            'questionCount' => ['required', 'integer', 'min:1', 'max:10'],
            'targetLanguage' => ['required', 'string', 'max:80'],
            'difficulty' => ['required', 'string', 'max:40'],
            'topic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $payload = app(OpenAIQuestionGeneratorService::class)->generate([
                'question_type' => $this->questionType,
                'question_count' => $this->questionCount,
                'target_language' => $this->targetLanguage,
                'difficulty' => $this->difficulty,
                'topic' => $this->topic,
                'notes' => $this->notes,
            ]);

            $this->generatedPayload = $payload;
            $this->generatedJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            Notification::make()
                ->title('Soal berhasil digenerate')
                ->body('Preview dulu hasilnya, lalu klik Simpan ke Database.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Gagal generate soal')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function saveGenerated(): void
    {
        $this->validate([
            'learningLevelId' => ['required', 'integer', 'exists:learning_levels,id'],
        ]);

        $questions = Arr::get($this->generatedPayload, 'questions', []);

        if (empty($questions)) {
            Notification::make()
                ->title('Belum ada soal untuk disimpan')
                ->warning()
                ->send();

            return;
        }

        $saved = 0;

        foreach ($questions as $index => $item) {
            $type = $item['type'] ?? $this->questionType;
            $settings = $this->normalizeSettings($type, $item['settings'] ?? []);

            $question = LearningQuestion::create([
                'learning_level_id' => $this->learningLevelId,
                'type' => $type,
                'instruction' => $item['instruction'] ?? 'Jawab soal berikut.',
                'question_text' => $item['question_text'] ?? ('Generated question ' . ($index + 1)),
                'correct_answer' => $item['correct_answer'] ?? '',
                'explanation' => $item['explanation'] ?? '',
                'points' => max((int) ($item['points'] ?? 10), 1),
                'time_limit' => max((int) ($item['time_limit'] ?? 0), 0) ?: null,
                'sort_order' => $this->nextSortOrder() + $index,
                'is_active' => true,
                'settings' => $settings,
            ]);

            foreach (($item['options'] ?? []) as $optionIndex => $option) {
                if (blank($option['text'] ?? null)) {
                    continue;
                }

                LearningQuestionOption::create([
                    'learning_question_id' => $question->id,
                    'option_text' => $option['text'],
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
                    'sort_order' => $optionIndex + 1,
                    'settings' => [],
                ]);
            }

            $saved += 1;
        }

        Notification::make()
            ->title($saved . ' soal berhasil disimpan')
            ->success()
            ->send();

        $this->generatedPayload = [];
        $this->generatedJson = null;
    }

    public function getLevelsProperty()
    {
        return LearningLevel::query()
            ->with('part.language')
            ->orderBy('learning_part_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function getOpenaiConfiguredProperty(): bool
    {
        return filled(config('services.openai.api_key'));
    }

    private function nextSortOrder(): int
    {
        return (int) LearningQuestion::query()
            ->where('learning_level_id', $this->learningLevelId)
            ->max('sort_order') + 1;
    }

    private function normalizeSettings(string $type, array $settings): array
    {
        if ($type === 'listening') {
            return [
                'story_button_label' => 'Mulai',
                'listening_flow' => collect($settings['listening_flow'] ?? [])->map(function (array $item) {
                    if (($item['type'] ?? 'story') === 'question') {
                        return [
                            'type' => 'question',
                            'question_text' => $item['question_text'] ?? '',
                            'question_audio_path' => '',
                            'options' => collect($item['options'] ?? [])->map(fn ($option) => [
                                'text' => $option['text'] ?? '',
                                'is_correct' => (bool) ($option['is_correct'] ?? false),
                            ])->values()->all(),
                            'explanation' => $item['explanation'] ?? '',
                        ];
                    }

                    return [
                        'type' => 'story',
                        'story_text' => $item['story_text'] ?? '',
                        'story_audio_path' => '',
                    ];
                })->values()->all(),
            ];
        }

        if ($type === 'word_match') {
            return [
                'word_pairs' => $settings['word_pairs'] ?? [],
            ];
        }

        if ($type === 'real_case') {
            return [
                'scenario_context' => $settings['scenario_context'] ?? '',
                'ideal_response' => $settings['ideal_response'] ?? '',
            ];
        }

        return $settings;
    }
}
