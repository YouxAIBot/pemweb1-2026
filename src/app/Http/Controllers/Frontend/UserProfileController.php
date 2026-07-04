<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use App\Models\LearningLanguage;
use App\Models\UserLevelProgress;
use App\Models\UserQuestionProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['account', 'edit-profile', 'preferences', 'progress'], true)
            ? $request->query('tab')
            : 'account';

        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();
        $settings = $this->settingsFor($profile?->settings ?? []);
        $selectedLanguageIds = collect(data_get($profile?->settings, 'enabled_languages', []))
            ->merge([$profile?->learning_language_id])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $languages = LearningLanguage::query()
            ->when($selectedLanguageIds, fn ($query) => $query->whereIn('id', $selectedLanguageIds))
            ->orderBy('sort_order')
            ->get();

        $progressSummary = $this->progressSummary($user->id, $profile?->learning_language_id);

        return view('frontend.learning.profile', [
            'setting' => DashboardSetting::query()->first() ?? new DashboardSetting(['brand_text' => 'YoLearning', 'brand_initial' => 'Y']),
            'user' => $user,
            'profile' => $profile,
            'tab' => $tab,
            'settings' => $settings,
            'languages' => $languages,
            'progressSummary' => $progressSummary,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:220'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
        ]);

        if (filled($data['password'] ?? null)) {
            if (! filled($data['current_password'] ?? null) || ! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak sesuai.'])->withInput();
            }
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'bio' => $data['bio'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $payload['avatar_url'] = $request->file('avatar')->store('profile-photos', 'public');
        }

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        return back()->with('learning_success', 'Profil berhasil diperbarui.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $profile = $request->user()->learningProfile;

        if (! $profile) {
            return redirect()->route('learning.onboarding');
        }

        $data = $request->validate([
            'daily_goal_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'theme_mode' => ['required', 'in:system,dark,light'],
            'sound_effects' => ['nullable', 'boolean'],
            'autoplay_audio' => ['nullable', 'boolean'],
            'slow_audio_mode' => ['nullable', 'boolean'],
            'show_romanization' => ['nullable', 'boolean'],
            'public_profile' => ['nullable', 'boolean'],
            'study_reminder_enabled' => ['nullable', 'boolean'],
            'study_reminder_time' => ['nullable', 'date_format:H:i'],
            'preferred_voice' => ['nullable', 'string', 'max:80'],
        ]);

        $settings = $this->settingsFor($profile->settings ?? []);
        $settings['preferences'] = array_merge($settings['preferences'], [
            'daily_goal_minutes' => (int) $data['daily_goal_minutes'],
            'theme_mode' => $data['theme_mode'],
            'sound_effects' => $request->boolean('sound_effects'),
            'autoplay_audio' => $request->boolean('autoplay_audio'),
            'slow_audio_mode' => $request->boolean('slow_audio_mode'),
            'show_romanization' => $request->boolean('show_romanization'),
            'public_profile' => $request->boolean('public_profile'),
            'study_reminder_enabled' => $request->boolean('study_reminder_enabled'),
            'study_reminder_time' => $data['study_reminder_time'] ?? '19:00',
            'preferred_voice' => $data['preferred_voice'] ?: null,
        ]);

        $profile->forceFill(['settings' => $settings])->save();

        return redirect()
            ->route('learning.settings', ['tab' => 'preferences'])
            ->with('learning_success', 'Preferensi belajar berhasil disimpan.');
    }

    public function resetProgress(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->learning_language_id) {
            return redirect()->route('learning.onboarding');
        }

        $data = $request->validate([
            'confirmation' => ['required', 'string', 'in:RESET'],
        ], [
            'confirmation.in' => 'Ketik RESET untuk mengonfirmasi reset progress.',
        ]);

        DB::transaction(function () use ($user, $profile) {
            $levelIds = $profile->language?->levels()->pluck('learning_levels.id') ?? collect();
            $questionIds = $profile->language?->questions()->pluck('learning_questions.id') ?? collect();

            UserLevelProgress::query()
                ->where('user_id', $user->id)
                ->whereIn('learning_level_id', $levelIds)
                ->delete();

            UserQuestionProgress::query()
                ->where('user_id', $user->id)
                ->whereIn('learning_question_id', $questionIds)
                ->delete();

            $firstPart = $profile->language?->parts()->active()->orderBy('sort_order')->first();
            $firstLevel = $firstPart?->levels()->active()->orderBy('sort_order')->first();
            $settings = $profile->settings ?? [];

            if ($profile->learning_language_id) {
                unset($settings['language_profiles'][(string) $profile->learning_language_id]);
            }

            $profile->forceFill([
                'current_part_id' => $firstPart?->id,
                'current_level_id' => $firstLevel?->id,
                'total_xp' => 0,
                'streak' => 0,
                'settings' => $settings,
            ])->save();

            if ($firstLevel) {
                UserLevelProgress::firstOrCreate([
                    'user_id' => $user->id,
                    'learning_level_id' => $firstLevel->id,
                ], [
                    'status' => 'available',
                ]);
            }
        });

        return redirect()
            ->route('learning.settings', ['tab' => 'progress'])
            ->with('learning_success', 'Progress bahasa aktif berhasil direset.');
    }

    private function settingsFor(array $settings): array
    {
        $settings['preferences'] = array_merge([
            'daily_goal_minutes' => 10,
            'theme_mode' => 'system',
            'sound_effects' => true,
            'autoplay_audio' => true,
            'slow_audio_mode' => false,
            'show_romanization' => true,
            'public_profile' => false,
            'study_reminder_enabled' => false,
            'study_reminder_time' => '19:00',
            'preferred_voice' => null,
        ], $settings['preferences'] ?? []);

        return $settings;
    }

    private function progressSummary(int $userId, ?int $languageId): array
    {
        if (! $languageId) {
            return [
                'levels_total' => 0,
                'levels_completed' => 0,
                'questions_answered' => 0,
                'questions_correct' => 0,
            ];
        }

        $levelIds = LearningLanguage::query()
            ->find($languageId)
            ?->levels()
            ->pluck('learning_levels.id') ?? collect();

        $questionIds = LearningLanguage::query()
            ->find($languageId)
            ?->questions()
            ->pluck('learning_questions.id') ?? collect();

        return [
            'levels_total' => $levelIds->count(),
            'levels_completed' => UserLevelProgress::query()
                ->where('user_id', $userId)
                ->whereIn('learning_level_id', $levelIds)
                ->where('status', 'completed')
                ->count(),
            'questions_answered' => UserQuestionProgress::query()
                ->where('user_id', $userId)
                ->whereIn('learning_question_id', $questionIds)
                ->count(),
            'questions_correct' => UserQuestionProgress::query()
                ->where('user_id', $userId)
                ->whereIn('learning_question_id', $questionIds)
                ->where('is_correct', true)
                ->count(),
        ];
    }
}
