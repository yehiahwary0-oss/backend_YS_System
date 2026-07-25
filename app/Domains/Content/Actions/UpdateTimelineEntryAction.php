<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\TimelineEntry;

class UpdateTimelineEntryAction
{
    public function execute(TimelineEntry $entry, array $data): TimelineEntry
    {
        $entry->update(array_filter($data, fn ($v) => $v !== null));
        return $entry->fresh();
    }
}
