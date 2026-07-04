<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\LanguageLetterResource;
use App\Filament\Admin\Resources\LearningLevelResource;
use App\Filament\Admin\Resources\LearningPartResource;
use App\Filament\Admin\Resources\LearningQuestionResource;
use App\Models\LearningLanguage;
use Filament\Pages\Page;

class LanguageWorkspace extends Page
{
    protected static ?string $navigationGroup = 'LEARNING CMS';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Language Workspace';

    protected static ?string $title = 'Language Workspace';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.language-workspace';

    public ?int $languageId = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->email === 'admin@admin.com'));
    }

    public function mount(): void
    {
        $this->languageId = $this->languageId ?: LearningLanguage::query()->orderBy('sort_order')->value('id');
    }

    public function getLanguagesProperty()
    {
        return LearningLanguage::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    public function getSelectedLanguageProperty(): ?LearningLanguage
    {
        return $this->languageId ? LearningLanguage::query()->find($this->languageId) : null;
    }

    public function getStatsProperty(): array
    {
        $language = $this->selectedLanguage;

        if (! $language) {
            return [
                'parts' => 0,
                'levels' => 0,
                'questions' => 0,
                'letters' => 0,
            ];
        }

        return [
            'parts' => $language->parts()->count(),
            'levels' => $language->levels()->count(),
            'questions' => $language->questions()->count(),
            'letters' => $language->letters()->count(),
        ];
    }

    public function getPartsProperty()
    {
        return $this->selectedLanguage
            ? $this->selectedLanguage->parts()->withCount('levels')->take(12)->get()
            : collect();
    }

    public function resourceUrl(string $resource): string
    {
        if (! $this->languageId) {
            return '#';
        }

        return match ($resource) {
            'parts' => LearningPartResource::getUrl('index') . '?tableFilters[learning_language_id][value]=' . $this->languageId,
            'levels' => LearningLevelResource::getUrl('index') . '?tableFilters[learning_language_id][value]=' . $this->languageId,
            'questions' => LearningQuestionResource::getUrl('index') . '?tableFilters[learning_language_id][value]=' . $this->languageId,
            'letters' => LanguageLetterResource::getUrl('index') . '?tableFilters[learning_language_id][value]=' . $this->languageId,
            default => '#',
        };
    }
}
