<?php

namespace App\Domains\System\Models;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog — Immutable Event Record.
 *
 * Immutability is enforced at TWO independent layers:
 *
 * Layer 1 (Eloquent): save() blocks updates, delete() always returns false.
 *   → Prevents ORM-level tampering.
 *
 * Layer 2 (PostgreSQL RLS): Row-Level Security policy denies UPDATE/DELETE
 *   at the database engine level — even raw Query Builder calls are blocked.
 *   → Prevents bypassing Eloquent via DB::table() or Tinker.
 *
 * The Audit log user_id is passed explicitly in job payloads because
 * Auth::id() returns null inside async queue workers.
 */
class AuditLog extends Model
{
    use HasUuids;

    public const UPDATED_AT = null; // No updated_at column

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'context'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Layer 1: Block all mutations via Eloquent events
        static::updating(fn () => throw new \LogicException(
            'AuditLog records are immutable. Updates are not permitted.'
        ));

        static::deleting(fn () => throw new \LogicException(
            'AuditLog records are immutable. Deletion is not permitted.'
        ));
    }

    // ── Explicit overrides as additional safety ───────────────────────

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new \LogicException('AuditLog records cannot be updated.');
        }
        return parent::save($options);
    }

    public function delete(): bool|null
    {
        throw new \LogicException('AuditLog records cannot be deleted.');
    }

    public function forceDelete(): bool|null
    {
        throw new \LogicException('AuditLog records cannot be force deleted.');
    }

    // ── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
