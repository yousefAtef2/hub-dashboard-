<?php

namespace App\Services\Contracts;

interface TextToSpeechInterface
{
    /**
     * Synthesize speech audio from text.
     *
     * @param string $text
     * @param string $languageCode ISO code of the target language (e.g. "en")
     * @return string Binary audio content (mp3)
     */
    public function synthesize(string $text, string $languageCode): string;
}
