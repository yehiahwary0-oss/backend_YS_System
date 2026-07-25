<?php

namespace App\Domains\Cms\Actions;

use App\Domains\Cms\Models\HomepageSection;

class UpdateHomepageSectionAction
{
    public function execute(HomepageSection $section, array $data): HomepageSection
    {
        $section->update(array_filter($data, fn ($v) => $v !== null));
        return $section->fresh();
    }
}
