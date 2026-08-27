<?php

namespace App\Services\Providers;

use App\Services\Contracts\SpeechToTextInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiSpeechToText implements SpeechToTextInterface
{
    public function transcribe(UploadedFile $audio, string $languageCode): string
    {
        $response = Http::withToken(config('services.openai.key'))
            ->attach('file', file_get_contents($audio->getRealPath()), $audio->getClientOriginalName())
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model'    => 'whisper-1',
                'language' => $languageCode,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('STT provider error: ' . $response->body());
        }

        return trim($response->json('text', ''));
    }
}
