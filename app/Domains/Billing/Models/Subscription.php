<?php

namespace App\Domains\Billing\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id', 'product_id', 'plan_name', 'price', 'currency',
        'billing_cycle', 'starts_at', 'ends_at', 'status',
        'is_manual_entry', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'       => 'date',
            'ends_at'         => 'date',
            'price'           => 'decimal:2',
            'is_manual_entry' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('ends_at', '>=', now()->toDateString());
    }

    /**
     * Normalizes any billing_cycle to a per-month price — the one
     * consistent unit the future dashboard's MRR figure can sum across
     * mixed monthly/quarterly/biannual/yearly rows without special-casing
     * each cycle at every call site.
     */
    public function monthlyEquivalent(): float
    {
        return match ($this->billing_cycle) {
            'monthly'  => (float) $this->price,
            'quarterly' => (float) $this->price / 3,
            'biannual'  => (float) $this->price / 6,
            'yearly'    => (float) $this->price / 12,
        };
    }
}
