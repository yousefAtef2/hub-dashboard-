"use client";

import { useEffect, useRef, useState } from "react";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

export interface TranslationMessage {
  speaker_id: string;
  original_text: string;
  translated_text: string;
  source_language: string;
  target_language: string;
  audio_url: string;
}

/**
 * Subscribes to the presence channel `room.{roomId}` and calls onMessage
 * every time a `translation.ready` event arrives from the backend
 * (i.e. the other participant said something and it was translated).
 */
export function useRoomSocket(
  roomId: string,
  participantId: string,
  onMessage: (msg: TranslationMessage) => void
) {
  const echoRef = useRef<Echo<any> | null>(null);
  const [connected, setConnected] = useState(false);

  useEffect(() => {
    if (!roomId) return;

    // @ts-expect-error - Echo expects Pusher on window in some setups
    window.Pusher = Pusher;

    const echo = new Echo({
      broadcaster: "reverb",
      key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
      wsHost: process.env.NEXT_PUBLIC_REVERB_HOST || "localhost",
      wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT || 8080),
      wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT || 8080),
      forceTLS: (process.env.NEXT_PUBLIC_REVERB_SCHEME || "http") === "https",
      enabledTransports: ["ws", "wss"],
      authEndpoint: `${process.env.NEXT_PUBLIC_API_BASE_URL}/broadcasting/auth`,
      auth: {
        headers: {
          // Attach your auth token / session here if participants must log in
        },
      },
    });

    echoRef.current = echo;

    const channel = echo.join(`room.${roomId}`)
      .here(() => setConnected(true))
      .listen(".translation.ready", (payload: TranslationMessage) => {
        // Don't echo back the sender's own message
        if (payload.speaker_id !== participantId) {
          onMessage(payload);
        }
      });

    return () => {
      channel.stopListening(".translation.ready");
      echo.leave(`room.${roomId}`);
      echo.disconnect();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [roomId, participantId]);

  return { connected };
}
