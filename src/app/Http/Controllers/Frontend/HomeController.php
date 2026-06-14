<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $languages = [
            [
                'name' => 'Mandarin',
                'slug' => 'mandarin',
                'accent' => '你好',
                'description' => 'Grammar, listening, dan percakapan dasar untuk pemula.',
                'status' => 'Tersedia',
            ],
            [
                'name' => 'Korea',
                'slug' => 'korea',
                'accent' => '안녕',
                'description' => 'Latihan hangul, kosakata, dan dialog sehari-hari.',
                'status' => 'Tersedia',
            ],
            [
                'name' => 'Jepang',
                'slug' => 'jepang',
                'accent' => 'こんにちは',
                'description' => 'Materi hiragana, frasa dasar, dan budaya praktis.',
                'status' => 'Segera',
            ],
            [
                'name' => 'Inggris',
                'slug' => 'inggris',
                'accent' => 'Hello',
                'description' => 'Vocabulary, grammar, listening, dan speaking practice.',
                'status' => 'Segera',
            ],
            [
                'name' => 'Arab',
                'slug' => 'arab',
                'accent' => 'مرحبا',
                'description' => 'Belajar huruf, kosakata, dan kalimat harian.',
                'status' => 'Segera',
            ],
            [
                'name' => 'Prancis',
                'slug' => 'prancis',
                'accent' => 'Bonjour',
                'description' => 'Frasa populer, pengucapan, dan percakapan ringan.',
                'status' => 'Segera',
            ],
        ];

        return view('frontend.home', compact('languages'));
    }
}
