<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardDailyMission;
use App\Models\DashboardMenu;
use App\Models\DashboardSetting;
use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\UserLearningProfile;
use App\Models\UserLevelProgress;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LearningDashboardController extends Controller
{
    public function welcome(Request $request): View
    {
        $profile = $request->user()->learningProfile;
        $targetUrl = $profile?->onboarding_completed_at
            ? route('dashboard')
            : route('learning.onboarding');

        return view('frontend.learning.welcome', [
            'setting' => $this->setting(),
            'targetUrl' => $targetUrl,
            'user' => $request->user(),
        ]);
    }

    public function onboarding(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if ($profile?->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        return view('frontend.learning.onboarding', [
            'setting' => $this->setting(),
            'languages' => $this->languages(),
            'abilityOptions' => $this->abilityOptions(),
        ]);
    }

    public function storeOnboarding(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'learning_language_id' => ['required', 'exists:learning_languages,id'],
            'ability_level' => ['required', 'in:beginner,intermediate,master'],
        ], [
            'learning_language_id.required' => 'Pilih satu bahasa terlebih dahulu.',
            'ability_level.required' => 'Pilih tingkat kemampuan terlebih dahulu.',
        ]);

        $language = LearningLanguage::query()->active()->findOrFail($data['learning_language_id']);
        $startMap = [
            'beginner' => 1,
            'intermediate' => 3,
            'master' => 5,
        ];

        $targetIndex = $startMap[$data['ability_level']] ?? 1;
        $part = $language->parts()->active()->orderBy('sort_order')->first();
        $level = null;

        if ($part) {
            $level = $part->levels()
                ->active()
                ->orderBy('sort_order')
                ->skip(max($targetIndex - 1, 0))
                ->first()
                ?? $part->levels()->active()->orderByDesc('sort_order')->first();
        }

        $profile = UserLearningProfile::updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'learning_language_id' => $language->id,
            'current_part_id' => $part?->id,
            'current_level_id' => $level?->id,
            'ability_level' => $data['ability_level'],
            'start_level_number' => $targetIndex,
            'start_part_number' => 1,
            'onboarding_completed_at' => now(),
        ]);

        if ($level) {
            $this->syncProgressForUser($request->user()->id, $language, $level);
        }

        return redirect()->route('dashboard')->with('learning_success', 'Petualangan belajar berhasil disiapkan.');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $profile = $request->user()->learningProfile()->with(['language.parts.levels', 'currentPart', 'currentLevel'])->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $language = $profile->language;
        $parts = collect();

        if ($language) {
            $parts = $language->parts()
                ->active()
                ->with(['levels' => fn ($query) => $query->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get();
        }

        $progressByLevel = $this->progressByLevel($request->user()->id, $parts->pluck('levels')->flatten()->pluck('id')->all());

        return view('frontend.learning.dashboard', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'language' => $language,
            'parts' => $parts,
            'progressByLevel' => $progressByLevel,
            'menus' => $this->menus(),
            'missions' => $this->missions(),
            'friends' => $this->friends($request->user()),
        ]);
    }

    public function showPart(Request $request, LearningPart $part): View|RedirectResponse
    {
        $profile = $request->user()->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        if ((int) $part->learning_language_id !== (int) $profile->learning_language_id) {
            return redirect()->route('dashboard')->with('learning_error', 'Bagian ini bukan bagian dari bahasa yang sedang kamu pelajari.');
        }

        $part->load(['language', 'levels' => fn ($query) => $query->active()->withCount('questions')->orderBy('sort_order')]);
        $progressByLevel = $this->progressByLevel($request->user()->id, $part->levels->pluck('id')->all());

        return view('frontend.learning.part-map', [
            'setting' => $this->setting(),
            'profile' => $profile->load(['language', 'currentLevel']),
            'part' => $part,
            'levels' => $part->levels,
            'progressByLevel' => $progressByLevel,
            'menus' => $this->menus(),
            'missions' => $this->missions(),
            'friends' => $this->friends($request->user()),
        ]);
    }

    public function showLevel(Request $request, LearningPart $part, LearningLevel $level): View|RedirectResponse
    {
        $profile = $request->user()->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        if ((int) $level->learning_part_id !== (int) $part->id || (int) $part->learning_language_id !== (int) $profile->learning_language_id) {
            abort(404);
        }

        $level->load(['part.language', 'questions' => fn ($query) => $query->active()->with('options')->orderBy('sort_order')]);

        UserLevelProgress::firstOrCreate([
            'user_id' => $request->user()->id,
            'learning_level_id' => $level->id,
        ], [
            'status' => 'in_progress',
        ]);

        return view('frontend.learning.level-show', [
            'setting' => $this->setting(),
            'profile' => $profile->load('language'),
            'part' => $part,
            'level' => $level,
            'menus' => $this->menus(),
            'missions' => $this->missions(),
            'friends' => $this->friends($request->user()),
        ]);
    }

    private function abilityOptions(): array
    {
        return [
            'beginner' => ['label' => 'Pemula', 'description' => 'Mulai dari Level 1 Bagian 1.', 'target' => 'Level 1 • Bagian 1'],
            'intermediate' => ['label' => 'Paham', 'description' => 'Langsung ke latihan bagian tengah.', 'target' => 'Level 1 • Bagian 3'],
            'master' => ['label' => 'Master', 'description' => 'Masuk ke tantangan lebih lanjut.', 'target' => 'Level 1 • Bagian 5'],
        ];
    }

    private function setting(): DashboardSetting
    {
        try {
            if (Schema::hasTable('dashboard_settings')) {
                return DashboardSetting::current();
            }
        } catch (\Throwable) {
            // Keep page usable before migrations run.
        }

        return DashboardSetting::current();
    }

    private function languages()
    {
        try {
            if (Schema::hasTable('learning_languages')) {
                $languages = LearningLanguage::query()->active()->orderBy('sort_order')->get();

                if ($languages->isNotEmpty()) {
                    return $languages;
                }
            }
        } catch (\Throwable) {
            // Fallback below.
        }

        return collect([
            new LearningLanguage(['id' => 1, 'name' => 'Inggris', 'slug' => 'inggris', 'native_name' => 'Hello', 'flag_label' => 'EN', 'description' => 'Grammar, listening, dan real-case practice.']),
            new LearningLanguage(['id' => 2, 'name' => 'Mandarin', 'slug' => 'mandarin', 'native_name' => '你好', 'flag_label' => 'CN', 'description' => 'Percakapan dasar dan listening pemula.']),
        ]);
    }

    private function menus()
    {
        try {
            if (Schema::hasTable('dashboard_menus')) {
                $menus = DashboardMenu::query()->active()->orderBy('sort_order')->get();

                if ($menus->isNotEmpty()) {
                    return $menus;
                }
            }
        } catch (\Throwable) {
            // fallback below
        }

        return collect([
            (object) ['label' => 'Bahasa', 'url' => route('dashboard'), 'icon_label' => '文'],
            (object) ['label' => 'Huruf', 'url' => '#', 'icon_label' => 'Aa'],
            (object) ['label' => 'Toko', 'url' => '#', 'icon_label' => '◈'],
            (object) ['label' => 'Misi', 'url' => '#', 'icon_label' => '✓'],
            (object) ['label' => 'Turnamen & Games', 'url' => '#', 'icon_label' => '⚡'],
            (object) ['label' => 'Pengaturan', 'url' => '#', 'icon_label' => '⚙'],
        ]);
    }

    private function missions()
    {
        try {
            if (Schema::hasTable('dashboard_daily_missions')) {
                $missions = DashboardDailyMission::query()->active()->orderBy('sort_order')->get();

                if ($missions->isNotEmpty()) {
                    return $missions;
                }
            }
        } catch (\Throwable) {
            // fallback below
        }

        return collect([
            (object) ['title' => 'Kerjakan 5 soal', 'target' => 5, 'default_progress' => 2, 'unit_label' => 'soal'],
            (object) ['title' => 'Belajar 10 menit', 'target' => 10, 'default_progress' => 3, 'unit_label' => 'menit'],
            (object) ['title' => 'Kerjakan 20 soal', 'target' => 20, 'default_progress' => 2, 'unit_label' => 'soal'],
        ]);
    }

    private function friends(User $user)
    {
        try {
            return User::query()
                ->whereKeyNot($user->id)
                ->latest()
                ->take(6)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function progressByLevel(int $userId, array $levelIds)
    {
        if (empty($levelIds)) {
            return collect();
        }

        return UserLevelProgress::query()
            ->where('user_id', $userId)
            ->whereIn('learning_level_id', $levelIds)
            ->get()
            ->keyBy('learning_level_id');
    }

    private function syncProgressForUser(int $userId, LearningLanguage $language, LearningLevel $currentLevel): void
    {
        $language->load(['parts.levels']);

        foreach ($language->parts as $part) {
            foreach ($part->levels as $level) {
                $status = 'locked';

                if ($level->id === $currentLevel->id) {
                    $status = 'available';
                }

                if ($part->sort_order < $currentLevel->part->sort_order || ($part->id === $currentLevel->learning_part_id && $level->sort_order < $currentLevel->sort_order)) {
                    $status = 'completed';
                }

                UserLevelProgress::updateOrCreate([
                    'user_id' => $userId,
                    'learning_level_id' => $level->id,
                ], [
                    'status' => $status,
                    'best_score' => $status === 'completed' ? 100 : 0,
                    'completed_at' => $status === 'completed' ? now() : null,
                ]);
            }
        }
    }
}
