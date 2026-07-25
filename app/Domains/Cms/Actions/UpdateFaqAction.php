<?php

namespace App\Domains\Cms\Actions;

use App\Domains\Cms\Models\Faq;

class UpdateFaqAction
{
    public function execute(Faq $faq, array $data): Faq
    {
        $faq->update(array_filter($data, fn ($v) => $v !== null));
        return $faq->fresh();
    }
}
