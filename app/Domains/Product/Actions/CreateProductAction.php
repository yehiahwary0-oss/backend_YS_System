<?php

namespace App\Domains\Product\Actions;

use App\Domains\Product\DTOs\CreateProductDTO;
use App\Domains\Product\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateProductAction
{
    public function execute(CreateProductDTO $dto): Product
    {
        // Extra guard: slug uniqueness (also enforced at DB level)
        if (Product::where('slug', $dto->slug)->exists()) {
            throw ValidationException::withMessages([
                'slug' => ['A product with this slug already exists.'],
            ]);
        }

        return Product::create([
            'slug'           => $dto->slug,
            'name_en'        => $dto->nameEn,
            'name_ar'        => $dto->nameAr,
            'status'         => $dto->status,
            'short_desc_en'  => $dto->shortDescEn,
            'short_desc_ar'  => $dto->shortDescAr,
            'long_desc_en'   => $dto->longDescEn,
            'long_desc_ar'   => $dto->longDescAr,
            'cover_image_id' => $dto->coverImageId,
            'icon_key'       => $dto->iconKey,
            'brand_color'    => $dto->brandColor,
            'is_featured'    => $dto->isFeatured,
            'sort_order'     => $dto->sortOrder,
            'seo_meta'       => $dto->seoMeta,
            'created_by'     => Auth::id(),
        ]);
    }
}
