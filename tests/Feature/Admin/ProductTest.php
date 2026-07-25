<?php

namespace Tests\Feature\Admin;

use App\Domains\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ────────────────────────────────────────────────────────

    public function test_super_admin_can_list_products(): void
    {
        $this->actingAsSuperAdmin();
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/products');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_unauthenticated_user_cannot_list_products(): void
    {
        $response = $this->getJson('/api/v1/admin/products');
        $response->assertStatus(401);
    }

    // ── Create ───────────────────────────────────────────────────────

    public function test_super_admin_can_create_product(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson('/api/v1/admin/products', [
            'slug'    => 'ys-matrix',
            'name_en' => 'YS-Matrix',
            'name_ar' => 'واي إس ماتريكس',
            'status'  => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.slug', 'ys-matrix');

        $this->assertDatabaseHas('products', ['slug' => 'ys-matrix']);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $this->actingAsSuperAdmin();
        Product::factory()->create(['slug' => 'ys-matrix']);

        $response = $this->postJson('/api/v1/admin/products', [
            'slug'    => 'ys-matrix',
            'name_en' => 'Another Product',
            'name_ar' => 'منتج آخر',
        ]);

        $response->assertStatus(422);
    }

    public function test_product_requires_bilingual_names(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson('/api/v1/admin/products', [
            'slug'    => 'ys-test',
            'name_en' => 'Test Product',
            // name_ar missing
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name_ar']);
    }

    // ── Update ───────────────────────────────────────────────────────

    public function test_super_admin_can_update_product(): void
    {
        $this->actingAsSuperAdmin();
        $product = Product::factory()->create(['status' => 'planned']);

        $response = $this->putJson("/api/v1/admin/products/{$product->id}", [
            'status' => 'beta',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'beta');
    }

    // ── Delete ───────────────────────────────────────────────────────

    public function test_can_delete_planned_product(): void
    {
        $this->actingAsSuperAdmin();
        $product = Product::factory()->create(['status' => 'planned']);

        $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_cannot_delete_active_product(): void
    {
        $this->actingAsSuperAdmin();
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(422);
        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }
}
