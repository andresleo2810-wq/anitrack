<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SpanishSynopsisService
{
    protected string $userAgent = 'AniTrack/1.0 (proyecto educativo; contacto: andresleo2810@gmail.com)';

    /** Umbral mínimo de similitud (0-100) para aceptar un match de Wikipedia */
    protected int $similarityThreshold = 55;

    /** Palabras que indican que la página NO es sobre un anime */
    protected array $personKeywords = ['director', 'animador', 'animadora', 'mangaka', 'seiyuu', 'actor de voz', 'actriz de voz', 'productor', 'productora', 'guionista'];

    public function get(string $title): ?array
    {
        if (!$title) return null;

        $key = 'wiki_es_' . md5(strtolower($title));

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $result = $this->fetch($title);

        if ($result) {
            Cache::put($key, $result, now()->addDays(30));
        } else {
            // Cachea también el null por 6h para no reintentar en vano
            Cache::put($key, null, now()->addHours(6));
        }

        return $result;
    }

    protected function fetch(string $title): ?array
    {
        try {
            // 1️⃣ Buscar en Wikipedia ES añadiendo "anime" para precisión
            $searchTerm = $this->buildSearchTerm($title);

            $search = Http::withHeaders(['User-Agent' => $this->userAgent])
                ->timeout(8)
                ->get('https://es.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $searchTerm,
                    'format' => 'json',
                    'srlimit' => 3, // pedimos 3 para tener opciones
                ]);

            if (!$search->successful()) return null;

            $candidates = $search->json('query.search') ?? [];
            if (empty($candidates)) return null;

            // 2️⃣ Validar cada candidato hasta encontrar uno que pase el filtro
            foreach ($candidates as $candidate) {
                $wikiTitle = $candidate['title'] ?? null;
                $snippet = $candidate['snippet'] ?? '';

                if (!$wikiTitle) continue;

                // 🔒 Filtro 1: similitud con el título original
                $similarity = $this->calculateSimilarity($title, $wikiTitle);
                if ($similarity < $this->similarityThreshold) continue;

                // 🔒 Filtro 2: no debe ser página sobre una persona
                if ($this->looksLikePerson($snippet, $wikiTitle)) continue;

                // ✅ Candidato válido: obtenemos el extract
                $summary = Http::withHeaders(['User-Agent' => $this->userAgent])
                    ->timeout(8)
                    ->get('https://es.wikipedia.org/api/rest_v1/page/summary/' . rawurlencode($wikiTitle));

                if (!$summary->successful()) continue;

                $extract = $summary->json('extract');
                if (!$extract) continue;

                // 🔒 Filtro 3: el extract también debe pasar el check de persona
                if ($this->looksLikePerson($extract, $wikiTitle)) continue;

                return ['title' => $wikiTitle, 'synopsis' => $extract, 'similarity' => $similarity];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('SpanishSynopsisService falló: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Construye el término de búsqueda añadiendo "anime" si el título es corto/medio
     */
    protected function buildSearchTerm(string $title): string
    {
        // Si ya contiene "anime" o es muy largo, no tocar
        if (stripos($title, 'anime') !== false) return $title;

        // Títulos muy largos (>50 chars) ya son bastante específicos
        if (strlen($title) > 50) return $title;

        return $title . ' anime';
    }

    /**
     * Calcula similitud entre dos títulos (0-100)
     * Normaliza: minúsculas, quita artículos, quita puntuación
     */
    protected function calculateSimilarity(string $a, string $b): int
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        if (empty($a) || empty($b)) return 0;

        similar_text($a, $b, $percent);

        // Bonus: si comparten una palabra significativa, sumar 10 puntos
        if ($this->shareSignificantWord($a, $b)) {
            $percent = min(100, $percent + 10);
        }

        return (int) $percent;
    }

    /**
     * Normaliza título: minúsculas, sin acentos, sin artículos, sin puntuación
     */
    protected function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
        $text = preg_replace('/\b(el|la|los|las|un|una|unos|unas|de|del|y|o|e|u|the|a|an)\b/i', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Verifica si dos textos comparten al menos una palabra de 3+ letras
     */
    protected function shareSignificantWord(string $a, string $b): bool
    {
        $wordsA = array_filter(explode(' ', $a), fn($w) => mb_strlen($w) >= 3);
        $wordsB = array_filter(explode(' ', $b), fn($w) => mb_strlen($w) >= 3);

        return !empty(array_intersect($wordsA, $wordsB));
    }

    /**
     * Detecta si el texto es sobre una persona en lugar de una obra
     */
    protected function looksLikePerson(string $text, string $title): bool
    {
        $lower = mb_strtolower($text, 'UTF-8');

        // Si el título de Wikipedia NO contiene "anime", "manga" ni "serie",
        // y el texto menciona roles de persona, es sospechoso
        $titleLower = mb_strtolower($title, 'UTF-8');
        $hasAnimeMention = (
            stripos($titleLower, 'anime') !== false ||
            stripos($titleLower, 'manga') !== false ||
            stripos($titleLower, 'serie') !== false ||
            stripos($titleLower, 'novela') !== false
        );

        if ($hasAnimeMention) return false;

        foreach ($this->personKeywords as $keyword) {
            if (stripos($lower, $keyword) !== false) {
                // Pero si también menciona "anime/manga" en el texto, aceptar
                if (stripos($lower, 'anime') !== false || stripos($lower, 'manga') !== false) {
                    return false;
                }
                return true;
            }
        }

        return false;
    }
}