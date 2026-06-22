<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class EdgeTtsLocalService
{
    public function generateToPublicStorage(
        string $text,
        string $voice = 'en-US-AriaNeural',
        string $directory = 'learning/audio/generated/edge-tts',
        ?string $fileName = null
    ): array {
        $directory = trim($directory, '/');
        $fileName = $fileName
            ? Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.mp3'
            : now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.mp3';

        $relativePath = $directory . '/' . $fileName;
        $absolutePath = Storage::disk('public')->path($relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        $process = new Process([
            'edge-tts',
            '--text',
            $text,
            '--voice',
            $voice,
            '--write-media',
            $absolutePath,
        ]);

        $process->setTimeout(90);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Edge TTS gagal. Pastikan edge-tts sudah terinstall di container. Error: ' . $process->getErrorOutput()
            );
        }

        if (! file_exists($absolutePath) || filesize($absolutePath) === 0) {
            throw new RuntimeException('Edge TTS tidak menghasilkan file audio.');
        }

        return [
            'path' => $relativePath,
            'url' => Storage::disk('public')->url($relativePath),
        ];
    }
}
