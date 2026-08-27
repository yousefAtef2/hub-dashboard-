<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TranslationReady implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomId,
        public string $speakerId,
        public string $originalText,
        public string $translatedText,
        public string $sourceLanguage,
        public string $targetLanguage,
        public string $audioUrl, // URL where the synthesized mp3 can be streamed from
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.' . $this->roomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'translation.ready';
    }

    public function broadcastWith(): array
    {
        return [
            'speaker_id'      => $this->speakerId,
            'original_text'   => $this->originalText,
            'translated_text' => $this->translatedText,
            'source_language' => $this->sourceLanguage,
            'target_language' => $this->targetLanguage,
            'audio_url'       => $this->audioUrl,
        ];
    }
}
