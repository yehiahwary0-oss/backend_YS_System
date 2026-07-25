<?php

namespace App\Domains\Cms\Actions;

use App\Domains\Cms\Models\StaticPage;

class UpdateStaticPageAction
{
    public function execute(StaticPage $page, array $data): StaticPage
    {
        $page->update(array_filter($data, fn ($v) => $v !== null));
        return $page->fresh();
    }
}
