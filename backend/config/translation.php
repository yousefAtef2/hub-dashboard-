<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Providers
    |--------------------------------------------------------------------------
    | Each pipeline stage (speech-to-text, translation, text-to-speech) has
    | its own provider binding, resolved in app/Providers/AppServiceProvider.php
    | Change the value here (or in .env) to switch providers without touching
    | any controller/service code.
    */
    'providers' => [
        'stt'  => env('STT_PROVIDER', 'openai'),
        'mt'   => env('MT_PROVIDER', 'openai'),
        'tts'  => env('TTS_PROVIDER', 'openai'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    | code  => ISO code used by the providers (STT/MT/TTS)
    | label => Display name
    | tts_voice => default voice id per provider (openai example voices)
    */
    'languages' => [
        'ar' => ['label' => 'Arabic',     'tts_voice' => 'alloy'],
        'en' => ['label' => 'English',    'tts_voice' => 'alloy'],
        'es' => ['label' => 'Spanish',    'tts_voice' => 'alloy'],
        'de' => ['label' => 'German',     'tts_voice' => 'alloy'],
        'fr' => ['label' => 'French',     'tts_voice' => 'alloy'],
        'it' => ['label' => 'Italian',    'tts_voice' => 'alloy'],
        'nl' => ['label' => 'Dutch',      'tts_voice' => 'alloy'],
        'ru' => ['label' => 'Russian',    'tts_voice' => 'alloy'],
        // Add more languages here — no other code changes needed.
    ],

    /*
    |--------------------------------------------------------------------------
    | Audio chunking
    |--------------------------------------------------------------------------
    */
    'max_audio_seconds' => 30, // safety limit per chunk sent from the browser
];
