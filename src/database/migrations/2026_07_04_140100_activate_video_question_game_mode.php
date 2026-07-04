<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('game_modes')) {
            return;
        }

        DB::table('game_modes')->updateOrInsert([
            'key' => 'video_question',
        ], [
            'title' => 'Video Question',
            'subtitle' => 'Soal dari video',
            'description' => 'User menonton video pendek lalu menjawab pertanyaan setelahnya.',
            'icon_label' => '▶',
            'route_name' => 'learning.video-question',
            'button_label' => 'Mulai Video',
            'status' => 'active',
            'sort_order' => 4,
            'is_active' => true,
            'settings' => json_encode([]),
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('game_modes')) {
            return;
        }

        DB::table('game_modes')
            ->where('key', 'video_question')
            ->update([
                'route_name' => null,
                'button_label' => 'Segera Hadir',
                'status' => 'coming_soon',
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
};
