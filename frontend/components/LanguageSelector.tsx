"use client";

import { SUPPORTED_LANGUAGES } from "@/lib/languages";

interface Props {
  label: string;
  value: string;
  onChange: (code: string) => void;
}

export default function LanguageSelector({ label, value, onChange }: Props) {
  return (
    <label className="flex flex-col text-sm gap-1">
      <span className="text-gray-600">{label}</span>
      <select
        className="border rounded-lg px-3 py-2"
        value={value}
        onChange={(e) => onChange(e.target.value)}
      >
        {SUPPORTED_LANGUAGES.map((lang) => (
          <option key={lang.code} value={lang.code}>
            {lang.label}
          </option>
        ))}
      </select>
    </label>
  );
}
