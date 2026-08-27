const API_BASE = process.env.NEXT_PUBLIC_API_BASE_URL || "http://localhost:8000/api";

export interface PipelineResult {
  original_text: string;
  translated_text: string;
  audio_url: string;
}

/**
 * Sends a recorded audio chunk to the backend pipeline:
 * STT -> Translation -> TTS, and receives back the translated
 * text + a URL to the synthesized audio.
 *
 * The result is ALSO broadcast over WebSocket to the other participant
 * in the room (see useRoomSocket hook) — this HTTP response is mainly
 * useful for showing the *sender's own* transcript instantly.
 */
export async function sendAudioChunk(params: {
  roomId: string;
  speakerId: string;
  sourceLanguage: string;
  targetLanguage: string;
  audioBlob: Blob;
}): Promise<PipelineResult> {
  const form = new FormData();
  form.append("audio", params.audioBlob, "chunk.webm");
  form.append("speaker_id", params.speakerId);
  form.append("source_language", params.sourceLanguage);
  form.append("target_language", params.targetLanguage);

  const res = await fetch(`${API_BASE}/rooms/${params.roomId}/pipeline`, {
    method: "POST",
    body: form,
  });

  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || "Translation pipeline failed");
  }

  return res.json();
}

export async function fetchSupportedLanguages() {
  const res = await fetch(`${API_BASE}/languages`);
  if (!res.ok) throw new Error("Failed to load languages");
  return res.json();
}
