<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Minimal product data for listing pages.
 * Never exposes admin-only fields.
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'              => $this->id,
            'slug'            => $this->slug,
            'name'            => $locale === 'ar' ? $this->name_ar : $this->name_en,
            'short_desc'      => $locale === 'ar' ? $this->short_desc_ar : $this->short_desc_en,
            'status'          => $this->status,
            'current_version' => $this->current_version,
            'is_featured'     => $this->is_featured,
            'cover_image'     => $this->whenLoaded('coverImage', fn () => [
                'url' => $this->coverImage->url,
                'alt' => $locale === 'ar'
                    ? $this->coverImage->alt_text_ar
                    : $this->coverImage->alt_text_en,
            ]),
        ];
    }
}
