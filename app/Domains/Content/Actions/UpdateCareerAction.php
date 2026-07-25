<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\Career;

class UpdateCareerAction
{
    public function execute(Career $career, array $data): Career
    {
        $career->update(array_filter($data, fn ($v) => $v !== null));
        return $career->fresh();
    }
}
