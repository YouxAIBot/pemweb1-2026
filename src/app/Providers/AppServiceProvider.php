<?php

namespace App\Providers;

use App\Models\HomepageNavItem;
use App\Models\HomepageSetting;
use App\Policies\ActivityPolicy;
use Filament\Actions\MountableAction;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityPolicy::class);
        Page::formActionsAlignment(Alignment::Right);
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);
        Page::$reportValidationErrorUsing = function (ValidationException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };

        View::composer('layouts.frontend', function ($view) {
            $defaults = [
                'homepageSettings' => (object) [
                    'site_name' => 'YoLearning',
                    'brand_text' => 'YoLearning',
                    'brand_initial' => 'Y',
                    'brand_logo_path' => null,
                    'meta_title' => 'YoLearning - Belajar Bahasa Interaktif',
                    'meta_description' => 'YoLearning adalah platform belajar bahasa berbasis quiz, progress, dan tantangan.',
                    'footer_left' => '© 2026 YoLearning. Semua progres belajar tersimpan rapi.',
                    'footer_right' => 'Belajar bahasa • Quiz • Tournament',
                    'cursor_glow_enabled' => true,
                    'cursor_glow_size' => 18,
                ],
                'homepageNavItems' => collect([
                    (object) ['label' => 'Home', 'url' => '#home', 'style' => 'link'],
                    (object) ['label' => 'Bahasa', 'url' => '#languages', 'style' => 'link'],
                    (object) ['label' => 'Tournament', 'url' => '#tournament', 'style' => 'link'],
                    (object) ['label' => 'Daftar', 'url' => '/register', 'style' => 'soft'],
                    (object) ['label' => 'Login', 'url' => '/login', 'style' => 'primary'],
                ]),
            ];

            try {
                if (Schema::hasTable('homepage_settings')) {
                    $defaults['homepageSettings'] = HomepageSetting::query()->first() ?? $defaults['homepageSettings'];
                }

                if (Schema::hasTable('homepage_nav_items')) {
                    $navItems = HomepageNavItem::query()
                        ->active()
                        ->orderBy('sort_order')
                        ->get();

                    if ($navItems->isNotEmpty()) {
                        $defaults['homepageNavItems'] = $navItems;
                    }
                }
            } catch (\Throwable) {
                // Keep frontend usable before the homepage migrations are executed.
            }

            $view->with($defaults);
        });

        MountableAction::configureUsing(function (MountableAction $action) {
            $action->modalFooterActionsAlignment(Alignment::Right);
        });
    }
}
