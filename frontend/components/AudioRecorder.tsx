"use client";

import { useRef, useState } from "react";
import { sendAudioChunk, PipelineResult } from "@/lib/api";

interface Props {
  roomId: string;
  speakerId: string;
  sourceLanguage: string;
  targetLanguage: string;
  onOwnTranscript: (result: PipelineResult) => void;
}

/**
 * Records the user's microphone in short chunks (silence-based or fixed
 * interval) and sends each chunk to the backend pipeline. For a production
 * system you'd want voice-activity-detection (VAD) to cut chunks on pauses
 * instead of a fixed timer — this fixed-interval version is a solid,
 * simple starting point.
 */
export default function AudioRecorder({
  roomId,
  speakerId,
  sourceLanguage,
  targetLanguage,
  onOwnTranscript,
}: Props) {
  const [isRecording, setIsRecording] = useState(false);
  const [isSending, setIsSending] = useState(false);
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const chunkIntervalMs = 4000; // send a chunk every 4s while recording

  async function startRecording() {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    const recorder = new MediaRecorder(stream, { mimeType: "audio/webm" });
    mediaRecorderRef.current = recorder;

    recorder.ondataavailable = async (event) => {
      if (event.data.size === 0) return;
      setIsSending(true);
      try {
        const result = await sendAudioChunk({
          roomId,
          speakerId,
          sourceLanguage,
          targetLanguage,
          audioBlob: event.data,
        });
        onOwnTranscript(result);
      } catch (err) {
        console.error("Pipeline error:", err);
      } finally {
        setIsSending(false);
      }
    };

    recorder.start();
    setIsRecording(true);

    // Stop & restart every `chunkIntervalMs` to emit a data chunk periodically
    const interval = setInterval(() => {
      if (recorder.state === "recording") {
        recorder.stop();
        recorder.start();
      }
    }, chunkIntervalMs);

    recorder.addEventListener("stop", () => {
      if (mediaRecorderRef.current !== recorder) {
        clearInterval(interval);
        stream.getTracks().forEach((t) => t.stop());
      }
    });
  }

  function stopRecording() {
    const recorder = mediaRecorderRef.current;
    if (recorder) {
      mediaRecorderRef.current = null;
      recorder.stream.getTracks().forEach((t) => t.stop());
      recorder.stop();
    }
    setIsRecording(false);
  }

  return (
    <div className="flex items-center gap-3">
      <button
        onClick={isRecording ? stopRecording : startRecording}
        className={`px-4 py-2 rounded-full font-medium transition ${
          isRecording
            ? "bg-red-600 text-white hover:bg-red-700"
            : "bg-blue-600 text-white hover:bg-blue-700"
        }`}
      >
        {isRecording ? "⏹ Stop" : "🎙 Start Speaking"}
      </button>
      {isSending && <span className="text-sm text-gray-500">Translating…</span>}
    </div>
  );
}
