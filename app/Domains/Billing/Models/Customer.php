<?php

namespace App\Domains\Billing\Models;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'email', 'company', 'phone', 'notes', 'created_by'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
