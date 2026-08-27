<?php

namespace App\Services\Providers;

use App\Services\Contracts\TranslatorInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiTranslator implements TranslatorInterface
{
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): string
    {
        $sourceLabel = config("translation.languages.$sourceLanguage.label", $sourceLanguage);
        $targetLabel = config("translation.languages.$targetLanguage.label", $targetLanguage);

        $response = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'temperature' => 0.2,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a professional real-time interpreter for a call center. "
                            . "Translate the user's message from {$sourceLabel} to {$targetLabel}. "
                            . "Preserve tone and meaning. Reply with ONLY the translated text, no quotes, no explanation.",
                    ],
                    ['role' => 'user', 'content' => $text],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Translation provider error: ' . $response->body());
        }

        return trim($response->json('choices.0.message.content', ''));
    }
}
