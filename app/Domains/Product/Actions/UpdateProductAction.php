<?php

namespace App\Domains\Product\Actions;

use App\Domains\Product\DTOs\UpdateProductDTO;
use App\Domains\Product\Models\Product;
use Illuminate\Validation\ValidationException;

class UpdateProductAction
{
    public function execute(Product $product, UpdateProductDTO $dto): Product
    {
        $changes = $dto->toArray();

        if (empty($changes)) {
            return $product; // Nothing to update
        }

        // Slug uniqueness check — exclude current product
        if (isset($changes['slug'])) {
            $exists = Product::where('slug', $changes['slug'])
                ->where('id', '!=', $product->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'slug' => ['A product with this slug already exists.'],
                ]);
            }
        }

        $product->update($changes);

        return $product->fresh();
    }
}
