<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Models\DocumentationArticle;
use Illuminate\Support\Facades\Auth;

class CreateDocumentationArticleAction
{
    public function execute(array $data): DocumentationArticle
    {
        $content = $data['content_en'] ?? '';

        return DocumentationArticle::create([
            'category_id'          => $data['category_id'],
            'slug'                 => $data['slug'],
            'title_en'             => $data['title_en'],
            'title_ar'             => $data['title_ar'],
            'content_en'           => $content,
            'content_ar'           => $data['content_ar'] ?? '',
            'version_tag'          => $data['version_tag'] ?? null,
            'reading_time_minutes' => $this->estimateReadingTime($content),
            'is_published'         => (bool) ($data['is_published'] ?? false),
            'sort_order'           => $data['sort_order'] ?? 0,
            'author_id'            => Auth::id(),
        ]);
    }

    /**
     * Public for testability — estimates reading time from word count.
     * ~200 words per minute average reading speed.
     */
    public function estimateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, (int) ceil($wordCount / 200));
    }
}
