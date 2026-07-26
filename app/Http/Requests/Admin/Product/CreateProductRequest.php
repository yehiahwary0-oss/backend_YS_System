<?php

namespace App\Http\Requests\Admin\Product;

use App\Domains\Product\Enums\ProductIcon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_products') ?? false;
    }

    public function rules(): array
    {
        return [
            'slug'           => ['required', 'string', 'max:100', 'alpha_dash', 'unique:products,slug'],
            'name_en'        => ['required', 'string', 'max:150'],
            'name_ar'        => ['required', 'string', 'max:150'],
            'status'         => ['sometimes', Rule::in(['active', 'beta', 'planned', 'archived'])],
            'short_desc_en'  => ['nullable', 'string', 'max:500'],
            'short_desc_ar'  => ['nullable', 'string', 'max:500'],
            'long_desc_en'   => ['nullable', 'string'],
            'long_desc_ar'   => ['nullable', 'string'],
            'cover_image_id' => ['nullable', 'uuid', 'exists:media,id'],
            // Card visual identity — closed list + strict hex format so a
            // bad value is impossible to save, not just impossible to see.
            'icon_key'       => ['nullable', 'string', Rule::in(ProductIcon::values())],
            'brand_color'    => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_featured'    => ['sometimes', 'boolean'],
            'sort_order'     => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'seo_meta'               => ['nullable', 'array'],
            'seo_meta.title_en'      => ['nullable', 'string', 'max:70'],
            'seo_meta.title_ar'      => ['nullable', 'string', 'max:70'],
            'seo_meta.description_en'=> ['nullable', 'string', 'max:160'],
            'seo_meta.description_ar'=> ['nullable', 'string', 'max:160'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->slug) {
            $this->merge(['slug' => strtolower(trim($this->slug))]);
        }
    }
}
