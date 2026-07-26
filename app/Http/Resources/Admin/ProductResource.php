<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'slug'            => $this->slug,
            'name_en'         => $this->name_en,
            'name_ar'         => $this->name_ar,
            'short_desc_en'   => $this->short_desc_en,
            'short_desc_ar'   => $this->short_desc_ar,
            'long_desc_en'    => $this->long_desc_en,
            'long_desc_ar'    => $this->long_desc_ar,
            'status'          => $this->status,
            'current_version' => $this->current_version,
            'is_featured'     => $this->is_featured,
            'icon_key'        => $this->icon_key,
            'brand_color'     => $this->brand_color,
            'sort_order'      => $this->sort_order,
            'seo_meta'        => $this->seo_meta,
            'cover_image'     => $this->whenLoaded('coverImage', fn () => [
                'id'  => $this->coverImage->id,
                'url' => $this->coverImage->url,
                'alt' => $this->coverImage->alt_text_en,
            ]),
            'releases_count'  => $this->whenLoaded('releases', fn () => $this->releases->count()),
            'creator'         => $this->whenLoaded('creator', fn () => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at'      => $this->created_at->toIso8601String(),
            'updated_at'      => $this->updated_at->toIso8601String(),
            'deleted_at'      => $this->deleted_at?->toIso8601String(),
        ];
    }
}
