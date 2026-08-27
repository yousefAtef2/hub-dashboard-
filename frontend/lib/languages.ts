export interface LanguageOption {
  code: string;
  label: string;
}

// Keep this in sync with backend config/translation.php -> languages
export const SUPPORTED_LANGUAGES: LanguageOption[] = [
  { code: "ar", label: "Arabic" },
  { code: "en", label: "English" },
  { code: "es", label: "Spanish" },
  { code: "de", label: "German" },
  { code: "fr", label: "French" },
  { code: "it", label: "Italian" },
  { code: "nl", label: "Dutch" },
  { code: "ru", label: "Russian" },
];
