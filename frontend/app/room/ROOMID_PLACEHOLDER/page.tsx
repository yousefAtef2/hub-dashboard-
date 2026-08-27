"use client";

import { useEffect, useRef, useState } from "react";
import { useParams } from "next/navigation";
import AudioRecorder from "@/components/AudioRecorder";
import LanguageSelector from "@/components/LanguageSelector";
import { useRoomSocket, TranslationMessage } from "@/hooks/useRoomSocket";
import { PipelineResult } from "@/lib/api";

interface TranscriptLine {
  who: "me" | "them";
  original: string;
  translated: string;
}

export default function RoomPage() {
  const { roomId } = useParams<{ roomId: string }>();
  const [speakerId] = useState(() => `p_${Math.random().toString(36).slice(2, 9)}`);
  const [sourceLanguage, setSourceLanguage] = useState("ar");
  const [targetLanguage, setTargetLanguage] = useState("en");
  const [transcript, setTranscript] = useState<TranscriptLine[]>([]);
  const audioPlayerRef = useRef<HTMLAudioElement | null>(null);

  const { connected } = useRoomSocket(roomId, speakerId, (msg: TranslationMessage) => {
    setTranscript((prev) => [
      ...prev,
      { who: "them", original: msg.original_text, translated: msg.translated_text },
    ]);

    // Auto-play the AI-generated voice in the listener's language
    if (audioPlayerRef.current) {
      audioPlayerRef.current.src = msg.audio_url;
      audioPlayerRef.current.play().catch(() => {
        /* autoplay might be blocked until user interacts once — that's OK */
      });
    }
  });

  function handleOwnTranscript(result: PipelineResult) {
    setTranscript((prev) => [
      ...prev,
      { who: "me", original: result.original_text, translated: result.translated_text },
    ]);
  }

  return (
    <main className="max-w-2xl mx-auto p-6 flex flex-col gap-6">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Room: {roomId}</h1>
        <span
          className={`text-xs px-2 py-1 rounded-full ${
            connected ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"
          }`}
        >
          {connected ? "● Connected" : "○ Connecting…"}
        </span>
      </header>

      <div className="flex gap-4">
        <LanguageSelector label="I speak" value={sourceLanguage} onChange={setSourceLanguage} />
        <LanguageSelector label="Translate to" value={targetLanguage} onChange={setTargetLanguage} />
      </div>

      <AudioRecorder
        roomId={roomId}
        speakerId={speakerId}
        sourceLanguage={sourceLanguage}
        targetLanguage={targetLanguage}
        onOwnTranscript={handleOwnTranscript}
      />

      <audio ref={audioPlayerRef} className="hidden" />

      <section className="flex flex-col gap-3">
        {transcript.map((line, i) => (
          <div
            key={i}
            className={`p-3 rounded-lg max-w-[80%] ${
              line.who === "me" ? "bg-blue-50 self-end text-right" : "bg-gray-100 self-start"
            }`}
          >
            <p className="text-xs text-gray-400 mb-1">
              {line.who === "me" ? "You (original)" : "Them (original)"}
            </p>
            <p className="text-sm text-gray-500 italic">{line.original}</p>
            <p className="text-base font-medium mt-1">{line.translated}</p>
          </div>
        ))}
      </section>
    </main>
  );
}
