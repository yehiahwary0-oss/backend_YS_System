<?php

namespace Tests\Feature\Admin;

use App\Domains\Content\Models\DocumentationArticle;
use App\Domains\Content\Models\DocumentationCategory;
use App\Domains\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    // ── Categories ───────────────────────────────────────────────────

    public function test_can_create_documentation_category(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson('/api/v1/admin/docs/categories', [
            'slug'     => 'getting-started',
            'title_en' => 'Getting Started',
            'title_ar' => 'البدء',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.slug', 'getting-started');
        $this->assertDatabaseHas('documentation_categories', ['slug' => 'getting-started']);
    }

    public function test_category_slug_must_be_unique(): void
    {
        $this->actingAsSuperAdmin();
        DocumentationCategory::factory()->create(['slug' => 'getting-started']);

        $response = $this->postJson('/api/v1/admin/docs/categories', [
            'slug'     => 'getting-started',
            'title_en' => 'Another',
            'title_ar' => 'آخر',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['slug']);
    }

    public function test_cannot_set_category_as_its_own_parent(): void
    {
        $this->actingAsSuperAdmin();
        $category = DocumentationCategory::factory()->create();

        $response = $this->putJson("/api/v1/admin/docs/categories/{$category->id}", [
            'parent_id' => $category->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_delete_category_with_articles(): void
    {
        $this->actingAsSuperAdmin();
        $category = DocumentationCategory::factory()->create();
        DocumentationArticle::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson("/api/v1/admin/docs/categories/{$category->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('documentation_categories', ['id' => $category->id]);
    }

    // ── Articles ─────────────────────────────────────────────────────

    public function test_can_create_documentation_article(): void
    {
        $this->actingAsSuperAdmin();
        $category = DocumentationCategory::factory()->create();

        $response = $this->postJson('/api/v1/admin/docs/articles', [
            'category_id' => $category->id,
            'slug'        => 'installation-guide',
            'title_en'    => 'Installation Guide',
            'title_ar'    => 'دليل التثبيت',
            'content_en'  => 'This guide walks you through the installation process step by step.',
            'content_ar'  => 'يرشدك هذا الدليل خلال عملية التثبيت خطوة بخطوة.',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.slug', 'installation-guide');
    }

    public function test_article_reading_time_is_auto_calculated(): void
    {
        $this->actingAsSuperAdmin();
        $category = DocumentationCategory::factory()->create();

        // ~400 words = ~2 minutes reading time
        $content = implode(' ', array_fill(0, 400, 'word'));

        $response = $this->postJson('/api/v1/admin/docs/articles', [
            'category_id' => $category->id,
            'slug'        => 'long-article',
            'title_en'    => 'Long Article',
            'title_ar'    => 'مقالة طويلة',
            'content_en'  => $content,
            'content_ar'  => 'محتوى',
        ]);

        $response->assertStatus(201);
        $this->assertGreaterThanOrEqual(2, $response->json('data.reading_time_minutes'));
    }

    public function test_published_articles_appear_in_public_api(): void
    {
        $category = DocumentationCategory::factory()->create();
        DocumentationArticle::factory()->create([
            'category_id' => $category->id,
            'slug'        => 'public-article',
            'is_published'=> true,
        ]);

        $response = $this->getJson('/api/v1/public/docs/public-article');

        $response->assertStatus(200)->assertJsonPath('data.slug', 'public-article');
    }

    public function test_unpublished_articles_are_hidden_from_public(): void
    {
        $category = DocumentationCategory::factory()->create();
        DocumentationArticle::factory()->create([
            'category_id' => $category->id,
            'slug'        => 'draft-article',
            'is_published'=> false,
        ]);

        $response = $this->getJson('/api/v1/public/docs/draft-article');

        $response->assertStatus(404);
    }
}
