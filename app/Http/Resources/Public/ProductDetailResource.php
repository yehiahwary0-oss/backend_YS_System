<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'              => $this->id,
            'slug'            => $this->slug,
            'name'            => $locale === 'ar' ? $this->name_ar : $this->name_en,
            'short_desc'      => $locale === 'ar' ? $this->short_desc_ar : $this->short_desc_en,
            'long_desc'       => $locale === 'ar' ? $this->long_desc_ar : $this->long_desc_en,
            'status'          => $this->status,
            'current_version' => $this->current_version,
            'cover_image'     => $this->whenLoaded('coverImage', fn () => [
                'url' => $this->coverImage->url,
                'alt' => $locale === 'ar'
                    ? $this->coverImage->alt_text_ar
                    : $this->coverImage->alt_text_en,
            ]),
            'latest_release'  => $this->whenLoaded('latestRelease', function () use ($locale) {
                $release = $this->latestRelease->first();
                if (! $release) return null;
                return [
                    'version'      => $release->version,
                    'release_date' => $release->release_date->toDateString(),
                    'notes'        => $locale === 'ar'
                        ? $release->release_notes_ar
                        : $release->release_notes_en,
                ];
            }),
            'seo' => [
                'title'       => $this->seo_meta['title_' . $locale]
                    ?? ($locale === 'ar' ? $this->name_ar : $this->name_en),
                'description' => $this->seo_meta['description_' . $locale]
                    ?? ($locale === 'ar' ? $this->short_desc_ar : $this->short_desc_en),
            ],
        ];
    }
}
