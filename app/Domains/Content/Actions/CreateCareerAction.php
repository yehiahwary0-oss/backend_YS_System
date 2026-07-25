<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\Career;
use Illuminate\Support\Facades\Auth;

class CreateCareerAction
{
    public function execute(array $data): Career
    {
        return Career::create([
            'title_en'         => $data['title_en'],
            'title_ar'         => $data['title_ar'],
            'department'       => $data['department'],
            'location'         => $data['location'] ?? 'Remote',
            'type'             => $data['type'] ?? 'full_time',
            'description_en'   => $data['description_en'] ?? null,
            'description_ar'   => $data['description_ar'] ?? null,
            'requirements'     => $data['requirements'] ?? [],
            'responsibilities' => $data['responsibilities'] ?? [],
            'status'           => $data['status'] ?? 'draft',
            'is_featured'      => (bool) ($data['is_featured'] ?? false),
            'sort_order'       => $data['sort_order'] ?? 0,
            'created_by'       => Auth::id(),
        ]);
    }
}
