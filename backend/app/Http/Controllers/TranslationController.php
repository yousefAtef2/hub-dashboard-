<?php

namespace App\Http\Controllers;

use App\Events\TranslationReady;
use App\Services\Contracts\SpeechToTextInterface;
use App\Services\Contracts\TextToSpeechInterface;
use App\Services\Contracts\TranslatorInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TranslationController extends Controller
{
    public function __construct(
        private SpeechToTextInterface $stt,
        private TranslatorInterface $translator,
        private TextToSpeechInterface $tts,
    ) {}

    /**
     * POST /api/rooms/{roomId}/pipeline
     *
     * Full pipeline: receives an audio chunk from participant A,
     * transcribes it, translates it into participant B's language,
     * synthesizes the translated speech, stores the audio file, and
     * broadcasts the result to the room over WebSocket so participant B
     * hears/reads it in (near) real time.
     *
     * Form fields:
     *  - audio            (file, required)      raw recorded chunk (webm/mp3/wav)
     *  - speaker_id        (string, required)
     *  - source_language   (string, required)    ISO code, e.g. "ar"
     *  - target_language   (string, required)    ISO code, e.g. "en"
     */
    public function pipeline(Request $request, string $roomId)
    {
        $validated = $request->validate([
            'audio'            => ['required', 'file', 'max:20480'], // 20MB safety cap
            'speaker_id'       => ['required', 'string'],
            'source_language'  => ['required', 'string', 'in:' . implode(',', array_keys(config('translation.languages')))],
            'target_language'  => ['required', 'string', 'in:' . implode(',', array_keys(config('translation.languages')))],
        ]);

        // 1) Speech -> Text
        $originalText = $this->stt->transcribe($request->file('audio'), $validated['source_language']);

        if (trim($originalText) === '') {
            return response()->json(['message' => 'No speech detected'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 2) Translate
        $translatedText = $this->translator->translate(
            $originalText,
            $validated['source_language'],
            $validated['target_language'],
        );

        // 3) Text -> Speech
        $audioBinary = $this->tts->synthesize($translatedText, $validated['target_language']);
        $fileName = 'translations/' . $roomId . '/' . Str::uuid() . '.mp3';
        Storage::disk('public')->put($fileName, $audioBinary);
        $audioUrl = Storage::disk('public')->url($fileName);

        // 4) Broadcast to the other participant(s) in the room in real time
        broadcast(new TranslationReady(
            roomId: $roomId,
            speakerId: $validated['speaker_id'],
            originalText: $originalText,
            translatedText: $translatedText,
            sourceLanguage: $validated['source_language'],
            targetLanguage: $validated['target_language'],
            audioUrl: $audioUrl,
        ))->toOthers();

        return response()->json([
            'original_text'   => $originalText,
            'translated_text' => $translatedText,
            'audio_url'       => $audioUrl,
        ]);
    }

    /**
     * GET /api/languages
     * Returns the list of supported languages for the frontend dropdowns.
     */
    public function languages()
    {
        return response()->json(config('translation.languages'));
    }
}
