<?php

namespace App\Domains\Product\DTOs;

use App\Domains\System\Services\HtmlSanitizerService;

readonly class UpdateProductDTO
{
    public function __construct(
        public ?string $slug,
        public ?string $nameEn,
        public ?string $nameAr,
        public ?string $status,
        public ?string $shortDescEn,
        public ?string $shortDescAr,
        public ?string $longDescEn,
        public ?string $longDescAr,
        public ?string $coverImageId,
        public ?string $iconKey,
        public ?string $brandColor,
        public ?bool   $isFeatured,
        public ?int    $sortOrder,
        public ?array  $seoMeta,
    ) {}

    public static function fromArray(array $validated): self
    {
        $sanitizer = app(HtmlSanitizerService::class);

        return new self(
            slug:         $validated['slug'] ?? null,
            nameEn:       $validated['name_en'] ?? null,
            nameAr:       $validated['name_ar'] ?? null,
            status:       $validated['status'] ?? null,
            shortDescEn:  $validated['short_desc_en'] ?? null,
            shortDescAr:  $validated['short_desc_ar'] ?? null,
            longDescEn:   $sanitizer->sanitize($validated['long_desc_en'] ?? null),
            longDescAr:   $sanitizer->sanitize($validated['long_desc_ar'] ?? null),
            coverImageId: $validated['cover_image_id'] ?? null,
            iconKey:      $validated['icon_key'] ?? null,
            brandColor:   $validated['brand_color'] ?? null,
            isFeatured:   isset($validated['is_featured']) ? (bool) $validated['is_featured'] : null,
            sortOrder:    isset($validated['sort_order']) ? (int) $validated['sort_order'] : null,
            seoMeta:      $validated['seo_meta'] ?? null,
        );
    }

    /**
     * Return only fields that were explicitly provided (for partial updates).
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->slug !== null)         $data['slug']           = $this->slug;
        if ($this->nameEn !== null)       $data['name_en']        = $this->nameEn;
        if ($this->nameAr !== null)       $data['name_ar']        = $this->nameAr;
        if ($this->status !== null)       $data['status']         = $this->status;
        if ($this->shortDescEn !== null)  $data['short_desc_en']  = $this->shortDescEn;
        if ($this->shortDescAr !== null)  $data['short_desc_ar']  = $this->shortDescAr;
        if ($this->longDescEn !== null)   $data['long_desc_en']   = $this->longDescEn;
        if ($this->longDescAr !== null)   $data['long_desc_ar']   = $this->longDescAr;
        if ($this->coverImageId !== null) $data['cover_image_id'] = $this->coverImageId;
        if ($this->iconKey !== null)      $data['icon_key']       = $this->iconKey;
        if ($this->brandColor !== null)   $data['brand_color']    = $this->brandColor;
        if ($this->isFeatured !== null)   $data['is_featured']    = $this->isFeatured;
        if ($this->sortOrder !== null)    $data['sort_order']     = $this->sortOrder;
        if ($this->seoMeta !== null)      $data['seo_meta']       = $this->seoMeta;

        return $data;
    }
}
