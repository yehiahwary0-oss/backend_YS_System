<?php

namespace App\Domains\Cms\Actions;

use App\Domains\Cms\Models\HomepageSection;

class CreateHomepageSectionAction
{
    public function execute(array $data): HomepageSection
    {
        return HomepageSection::create([
            'type'        => $data['type'],
            'title_en'    => $data['title_en'] ?? null,
            'title_ar'    => $data['title_ar'] ?? null,
            'subtitle_en' => $data['subtitle_en'] ?? null,
            'subtitle_ar' => $data['subtitle_ar'] ?? null,
            'content'     => $data['content'] ?? null,
            'is_enabled'  => (bool) ($data['is_enabled'] ?? true),
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);
    }
}
