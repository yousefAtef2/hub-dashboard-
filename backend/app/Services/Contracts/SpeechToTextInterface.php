<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface SpeechToTextInterface
{
    /**
     * Transcribe an audio file into text.
     *
     * @param UploadedFile $audio
     * @param string $languageCode ISO code of the spoken language (e.g. "ar")
     * @return string The transcribed text
     */
    public function transcribe(UploadedFile $audio, string $languageCode): string;
}
