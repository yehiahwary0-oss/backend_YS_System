<?php

namespace App\Domains\System\Services;

use App\Domains\System\Models\FeatureFlag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Feature Flag Service.
 *
 * Cache strategy:
 * - All flags stored as ONE Redis key (collection) — no per-flag lookups.
 *   This prevents Cache Penetration (missing key → DB hit → cache null).
 * - Stampede protection via atomic Cache::lock() on rebuild.
 *   Only one process rebuilds; others wait 100ms then retry.
 * - Eager invalidation: cache is busted immediately on any admin change.
 *
 * O(1) flag lookup after first warm-up.
 */
class FeatureFlagService
{
    private const CACHE_KEY   = 'ys:feature_flags:all';
    private const LOCK_KEY    = 'ys:feature_flags:rebuilding';
    private const CACHE_TTL   = 300;   // 5 minutes
    private const LOCK_TTL    = 10;    // seconds
    private const RETRY_DELAY = 100_000; // microseconds (100ms)

    /**
     * Get all flags as a keyed collection.
     * Result is cached — DB is only hit on first request or after invalidation.
     *
     * @return Collection<string, FeatureFlag>
     */
    public function all(): Collection
    {
        $cached = Redis::get(self::CACHE_KEY);

        if ($cached !== null) {
            $data = json_decode($cached, true);
            return collect($data)->map(
                fn ($item) => (object) $item
            )->keyBy('key');
        }

        return $this->rebuild();
    }

    /**
     * Check if a feature is enabled.
     * Single-flag lookup from the in-memory collection — O(1).
     */
    public function isEnabled(string $key): bool
    {
        $flag = $this->all()->get($key);

        if ($flag === null) {
            // Flag doesn't exist → treat as disabled (not a DB hit)
            return false;
        }

        return $flag->is_enabled && $this->isActiveForEnvironment($flag);
    }

    /**
     * Check if flag is enabled for a specific user (role or ID targeting).
     */
    public function isEnabledFor(string $key, ?object $user = null): bool
    {
        if (! $this->isEnabled($key)) {
            return false;
        }

        $flag = $this->all()->get($key);

        if (empty($flag->enabled_for)) {
            return true; // no targeting = everyone
        }

        if ($user === null) {
            return false;
        }

        $targeting = is_string($flag->enabled_for)
            ? json_decode($flag->enabled_for, true)
            : (array) $flag->enabled_for;

        // Check role targeting
        if (isset($targeting['roles']) && in_array($user->role?->slug, $targeting['roles'], true)) {
            return true;
        }

        // Check user ID targeting
        if (isset($targeting['users']) && in_array($user->id, $targeting['users'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Invalidate cache immediately after admin changes a flag.
     * Called from admin controllers after any create/update/delete.
     */
    public function invalidate(): void
    {
        Redis::del(self::CACHE_KEY);
    }

    /**
     * Force a warm rebuild (useful after seeding or deployment).
     */
    public function warm(): void
    {
        Redis::del(self::CACHE_KEY);
        $this->rebuild();
    }

    // ── Private ──────────────────────────────────────────────────────

    /**
     * Rebuild the cache with stampede protection.
     *
     * Only one process rebuilds at a time. Others wait and retry.
     *
     * @return Collection<string, FeatureFlag>
     */
    private function rebuild(): Collection
    {
        $lock = Cache::lock(self::LOCK_KEY, self::LOCK_TTL);

        if ($lock->get()) {
            try {
                $flags = FeatureFlag::select([
                    'id', 'key', 'is_enabled', 'environment', 'enabled_for', 'product_id',
                ])->get()->keyBy('key');

                Redis::setex(
                    self::CACHE_KEY,
                    self::CACHE_TTL,
                    $flags->toJson()
                );

                return $flags;
            } finally {
                $lock->release();
            }
        }

        // Another process is rebuilding — wait briefly and retry once
        usleep(self::RETRY_DELAY);

        $cached = Redis::get(self::CACHE_KEY);
        if ($cached !== null) {
            return collect(json_decode($cached, true))
                ->map(fn ($item) => (object) $item)
                ->keyBy('key');
        }

        // Fallback: return empty (safe default — disabled flags)
        return collect();
    }

    private function isActiveForEnvironment(object $flag): bool
    {
        return $flag->environment === 'all'
            || $flag->environment === app()->environment();
    }
}
