<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\TimelineEntry;

class CreateTimelineEntryAction
{
    public function execute(array $data): TimelineEntry
    {
        return TimelineEntry::create([
            'title_en'       => $data['title_en'],
            'title_ar'       => $data['title_ar'],
            'description_en' => $data['description_en'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'event_date'     => $data['event_date'],
            'type'           => $data['type'] ?? 'milestone',
            'product_id'     => $data['product_id'] ?? null,
            'is_public'      => (bool) ($data['is_public'] ?? true),
            'sort_order'     => $data['sort_order'] ?? 0,
        ]);
    }
}
