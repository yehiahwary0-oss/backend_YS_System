<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\DocumentationArticle;

class UpdateDocumentationArticleAction
{
    public function execute(DocumentationArticle $article, array $data): DocumentationArticle
    {
        if (isset($data['content_en'])) {
            $wordCount = str_word_count(strip_tags($data['content_en']));
            $data['reading_time_minutes'] = max(1, (int) ceil($wordCount / 200));
        }

        $article->update(array_filter($data, fn ($v) => $v !== null));
        return $article->fresh();
    }
}
