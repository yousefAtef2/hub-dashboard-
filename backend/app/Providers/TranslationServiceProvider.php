<?php

namespace App\Providers;

use App\Services\Contracts\SpeechToTextInterface;
use App\Services\Contracts\TextToSpeechInterface;
use App\Services\Contracts\TranslatorInterface;
use App\Services\Providers\OpenAiSpeechToText;
use App\Services\Providers\OpenAiTextToSpeech;
use App\Services\Providers\OpenAiTranslator;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Map provider name (from config/translation.php) => concrete class.
     * To add a new provider (Google, Azure, ElevenLabs...):
     *   1. Implement the matching interface in app/Services/Providers/
     *   2. Add an entry below
     *   3. Set STT_PROVIDER / MT_PROVIDER / TTS_PROVIDER in .env
     */
    public function register(): void
    {
        $this->app->bind(SpeechToTextInterface::class, function () {
            return match (config('translation.providers.stt')) {
                'openai' => new OpenAiSpeechToText(),
                default  => new OpenAiSpeechToText(),
            };
        });

        $this->app->bind(TranslatorInterface::class, function () {
            return match (config('translation.providers.mt')) {
                'openai' => new OpenAiTranslator(),
                default  => new OpenAiTranslator(),
            };
        });

        $this->app->bind(TextToSpeechInterface::class, function () {
            return match (config('translation.providers.tts')) {
                'openai' => new OpenAiTextToSpeech(),
                default  => new OpenAiTextToSpeech(),
            };
        });
    }
}
