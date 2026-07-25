<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\DocumentationCategory;

class UpdateDocumentationCategoryAction
{
    public function execute(DocumentationCategory $category, array $data): DocumentationCategory
    {
        $category->update(array_filter($data, fn ($v) => $v !== null));
        return $category->fresh();
    }
}
