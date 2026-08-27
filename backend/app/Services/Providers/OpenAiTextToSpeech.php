<?php

namespace App\Services\Providers;

use App\Services\Contracts\TextToSpeechInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiTextToSpeech implements TextToSpeechInterface
{
    public function synthesize(string $text, string $languageCode): string
    {
        $voice = config("translation.languages.$languageCode.tts_voice", 'alloy');

        $response = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/audio/speech', [
                'model' => 'tts-1',
                'voice' => $voice,
                'input' => $text,
                'response_format' => 'mp3',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('TTS provider error: ' . $response->body());
        }

        return $response->body(); // raw mp3 binary
    }
}
