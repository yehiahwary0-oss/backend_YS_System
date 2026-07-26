<?php

namespace App\Domains\Product\DTOs;

use App\Domains\System\Services\HtmlSanitizerService;
use App\Http\Requests\Admin\Product\CreateProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;

readonly class CreateProductDTO
{
    public function __construct(
        public string  $slug,
        public string  $nameEn,
        public string  $nameAr,
        public string  $status,
        public ?string $shortDescEn,
        public ?string $shortDescAr,
        public ?string $longDescEn,
        public ?string $longDescAr,
        public ?string $coverImageId,
        public ?string $iconKey,
        public ?string $brandColor,
        public bool    $isFeatured,
        public int     $sortOrder,
        public ?array  $seoMeta,
    ) {}

    public static function fromRequest(CreateProductRequest $request): self
    {
        $v = $request->validated();
        $sanitizer = app(HtmlSanitizerService::class);

        return new self(
            slug:         $v['slug'],
            nameEn:       $v['name_en'],
            nameAr:       $v['name_ar'],
            status:       $v['status'] ?? 'planned',
            shortDescEn:  $v['short_desc_en'] ?? null,
            shortDescAr:  $v['short_desc_ar'] ?? null,
            // long_desc is rich-text HTML from the admin editor — sanitize
            // here, once, so every consumer of this DTO downstream never
            // has to think about it again.
            longDescEn:   $sanitizer->sanitize($v['long_desc_en'] ?? null),
            longDescAr:   $sanitizer->sanitize($v['long_desc_ar'] ?? null),
            coverImageId: $v['cover_image_id'] ?? null,
            iconKey:      $v['icon_key'] ?? null,
            brandColor:   $v['brand_color'] ?? null,
            isFeatured:   (bool) ($v['is_featured'] ?? false),
            sortOrder:    (int) ($v['sort_order'] ?? 0),
            seoMeta:      $v['seo_meta'] ?? null,
        );
    }
}
