<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\RoadmapItem;

class UpdateRoadmapItemAction
{
    public function execute(RoadmapItem $item, array $data): RoadmapItem
    {
        $item->update(array_filter($data, fn ($v) => $v !== null));
        return $item->fresh();
    }
}
