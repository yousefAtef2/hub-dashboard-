<?php

namespace App\Services\Contracts;

interface TranslatorInterface
{
    /**
     * Translate text from one language to another.
     *
     * @param string $text
     * @param string $sourceLanguage ISO code (e.g. "ar")
     * @param string $targetLanguage ISO code (e.g. "en")
     * @return string Translated text
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): string;
}
