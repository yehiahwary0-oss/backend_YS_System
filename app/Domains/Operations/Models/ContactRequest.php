<?php

namespace App\Domains\Operations\Models;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactRequest extends Model
{
    use HasFactory, HasUuids;

    // No soft deletes — contact requests are operational records
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'type',
        'status',
        'ip_address',
        'user_agent',
        'spam_score',
        'handled_by',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'spam_score' => 'float',
            'handled_at' => 'datetime',
        ];
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isNew(): bool
    {
        return $this->status === 'new';
    }
}
