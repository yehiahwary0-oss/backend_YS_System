<?php

namespace App\Domains\Search\Drivers;

use App\Domains\Search\Contracts\SearchDriver;
use App\Domains\Search\DTOs\SearchResult;
use App\Domains\Search\DTOs\SearchResultCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL Full-Text Search Driver.
 *
 * Uses GENERATED ALWAYS AS tsvector columns per locale.
 * Uses websearch_to_tsquery() — production-safe, handles arbitrary user input.
 * Uses ts_rank_cd() — cover density ranking, better than ts_rank for short texts.
 *
 * Query complexity: O(log n) via GIN indexes on tsvector columns.
 */
class PostgresSearchDriver implements SearchDriver
{
    // Map locale to PostgreSQL text search configuration
    private const LOCALE_CONFIG = [
        'en' => 'english',
        'ar' => 'arabic',
    ];

    public function search(
        string $query,
        array  $types  = [],
        string $locale = 'en',
        int    $limit  = 20,
    ): SearchResultCollection {
        $start  = microtime(true);
        $pgConf = self::LOCALE_CONFIG[$locale] ?? 'english';

        // Sanitize — websearch_to_tsquery handles injection + syntax errors
        $sanitized = $this->sanitizeQuery($query);
        if (empty($sanitized)) {
            return $this->emptyCollection($query, $start);
        }

        $all    = collect();
        $active = empty($types)
            ? ['product', 'article', 'career', 'update']
            : $types;

        // Batch by type — one query per type, then merge + rank
        // This avoids N+1 and allows per-type eager loading
        foreach ($active as $type) {
            $results = match ($type) {
                'product' => $this->searchProducts($sanitized, $pgConf, $locale),
                'article' => $this->searchArticles($sanitized, $pgConf, $locale),
                'career'  => $this->searchCareers($sanitized, $pgConf, $locale),
                'update'  => $this->searchUpdates($sanitized, $pgConf, $locale),
                default   => collect(),
            };

            $all = $all->merge($results);
        }

        // Global rank sort across all types
        $sorted = $all->sortByDesc('rank')->values()->take($limit);

        return new SearchResultCollection(
            results: $sorted->map(fn ($row) => new SearchResult(
                type:    $row['type'],
                id:      $row['id'],
                title:   $row['title'],
                excerpt: $row['excerpt'] ?? null,
                url:     $row['url'],
                rank:    (float) $row['rank'],
                meta:    $row['meta'] ?? [],
            )),
            total:   $all->count(),
            query:   $query,
            driver:  'postgres',
            tookMs:  round((microtime(true) - $start) * 1000, 2),
        );
    }

    // ── Per-type search queries ──────────────────────────────────────

    private function searchProducts(string $q, string $conf, string $locale): Collection
    {
        $col = "search_vector_{$locale}";

        return DB::table('products')
            ->selectRaw("
                'product'           AS type,
                id::text            AS id,
                name_{$locale}      AS title,
                short_desc_{$locale} AS excerpt,
                slug                AS url,
                ts_rank_cd({$col}, websearch_to_tsquery('{$conf}', ?)) AS rank,
                json_build_object('status', status, 'version', current_version) AS meta
            ", [$q])
            ->whereRaw("{$col} @@ websearch_to_tsquery('{$conf}', ?)", [$q])
            ->whereIn('status', ['active', 'beta'])
            ->whereNull('deleted_at')
            ->get()
            ->map(fn ($r) => (array) $r);
    }

    private function searchArticles(string $q, string $conf, string $locale): Collection
    {
        $col = "search_vector_{$locale}";

        return DB::table('documentation_articles as a')
            ->join('documentation_categories as c', 'a.category_id', '=', 'c.id')
            ->selectRaw("
                'article'                   AS type,
                a.id::text                  AS id,
                a.title_{$locale}           AS title,
                LEFT(a.content_{$locale}, 200) AS excerpt,
                a.slug                      AS url,
                ts_rank_cd(a.{$col}, websearch_to_tsquery('{$conf}', ?)) AS rank,
                json_build_object(
                    'category', c.title_{$locale},
                    'reading_time', a.reading_time_minutes
                ) AS meta
            ", [$q])
            ->whereRaw("a.{$col} @@ websearch_to_tsquery('{$conf}', ?)", [$q])
            ->where('a.is_published', true)
            ->whereNull('a.deleted_at')
            ->get()
            ->map(fn ($r) => (array) $r);
    }

    private function searchCareers(string $q, string $conf, string $locale): Collection
    {
        $col = "search_vector_{$locale}";

        return DB::table('careers')
            ->selectRaw("
                'career'             AS type,
                id::text             AS id,
                title_{$locale}      AS title,
                description_{$locale} AS excerpt,
                id::text             AS url,
                ts_rank_cd({$col}, websearch_to_tsquery('{$conf}', ?)) AS rank,
                json_build_object('department', department, 'type', type) AS meta
            ", [$q])
            ->whereRaw("{$col} @@ websearch_to_tsquery('{$conf}', ?)", [$q])
            ->where('status', 'open')
            ->whereNull('deleted_at')
            ->get()
            ->map(fn ($r) => (array) $r);
    }

    private function searchUpdates(string $q, string $conf, string $locale): Collection
    {
        $col = "search_vector_{$locale}";

        return DB::table('updates')
            ->selectRaw("
                'update'             AS type,
                id::text             AS id,
                title_{$locale}      AS title,
                LEFT(content_{$locale}, 200) AS excerpt,
                id::text             AS url,
                ts_rank_cd({$col}, websearch_to_tsquery('{$conf}', ?)) AS rank,
                json_build_object('type', type) AS meta
            ", [$q])
            ->whereRaw("{$col} @@ websearch_to_tsquery('{$conf}', ?)", [$q])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNull('deleted_at')
            ->get()
            ->map(fn ($r) => (array) $r);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Sanitize user input before passing to websearch_to_tsquery.
     * websearch_to_tsquery is already injection-safe, but we strip
     * control characters and limit length.
     */
    private function sanitizeQuery(string $query): string
    {
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $query);
        $clean = trim($clean);
        return mb_substr($clean, 0, 500);
    }

    private function emptyCollection(string $query, float $start): SearchResultCollection
    {
        return new SearchResultCollection(
            results: collect(),
            total:   0,
            query:   $query,
            driver:  'postgres',
            tookMs:  round((microtime(true) - $start) * 1000, 2),
        );
    }
}
