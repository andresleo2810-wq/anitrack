<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Traduce de inglés a español.
     * Intenta Google → si falla, MyMemory → si falla, deja el original.
     * NUNCA cachea fallos (solo traducciones exitosas).
     */
    public function translate(?string $text, string $from = 'en', string $to = 'es'): ?string
    {
        if (!$text) return null;

        $key = 'tr_' . md5($text . $from . $to);

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $translated = $this->google($text, $from, $to)
            ?? $this->myMemory($text, $from, $to);

        // Solo cachea si realmente se tradujo
        if ($translated && trim($translated) !== '' && $translated !== $text) {
            Cache::put($key, $translated, now()->addDays(30));
            return $translated;
        }

        return $text;
    }

    /** Proveedor 1: Google Translate (sin clave) */
    protected function google(string $text, string $from, string $to): ?string
    {
        try {
            $res = Http::timeout(8)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx', 'sl' => $from, 'tl' => $to, 'dt' => 't', 'q' => $text,
            ]);

            if (!$res->successful()) return null;

            $translated = collect($res->json()[0] ?? [])
                ->map(fn($seg) => $seg[0] ?? '')
                ->implode('');

            return $translated !== '' ? $translated : null;
        } catch (\Exception $e) {
            Log::warning('Google Translate falló: ' . $e->getMessage());
            return null;
        }
    }

    /** Proveedor 2: MyMemory (sin clave, máx ~450 caracteres por petición) */
    protected function myMemory(string $text, string $from, string $to): ?string
    {
        try {
            $translated = '';

            foreach ($this->chunkText($text, 450) as $chunk) {
                $res = Http::timeout(8)->get('https://api.mymemory.translated.net/get', [
                    'q' => $chunk,
                    'langpair' => "{$from}|{$to}",
                ]);

                if (!$res->successful()) return null;

                $part = $res->json('responseData.translatedText');
                if (!$part || str_contains($part, 'INVALID')) return null;

                $translated .= $part . ' ';
            }

            return trim($translated) ?: null;
        } catch (\Exception $e) {
            Log::warning('MyMemory falló: ' . $e->getMessage());
            return null;
        }
    }

    /** Divide el texto en trozos por oraciones (para el límite de MyMemory) */
    protected function chunkText(string $text, int $max): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            if (strlen($current) + strlen($sentence) + 1 > $max && $current !== '') {
                $chunks[] = $current;
                $current = $sentence;
            } else {
                $current .= ($current !== '' ? ' ' : '') . $sentence;
            }
        }

        if ($current !== '') $chunks[] = $current;

        return $chunks;
    }
}