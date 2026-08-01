<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'              => $this->email,
            'company'            => $this->company,
            'phone'              => $this->phone,
            'notes'              => $this->notes,
            'subscriptions_count' => $this->whenCounted('subscriptions'),
            'creator'            => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ] : null),
            'created_at'         => $this->created_at->toIso8601String(),
        ];
    }
}
