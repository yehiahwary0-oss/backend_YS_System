<?php

namespace App\Http\Requests\Admin\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_subscriptions') ?? false;
    }

    public function rules(): array
    {
        return [
            'plan_name'     => ['sometimes', 'string', 'max:100'],
            'price'         => ['sometimes', 'numeric', 'min:0'],
            'currency'      => ['sometimes', 'string', 'size:3'],
            'billing_cycle' => ['sometimes', Rule::in(['monthly', 'quarterly', 'biannual', 'yearly'])],
            'starts_at'     => ['sometimes', 'date'],
            'ends_at'       => ['sometimes', 'date'],
            'status'        => ['sometimes', Rule::in(['active', 'expired', 'cancelled'])],
            // customer_id / product_id are intentionally NOT editable here
            // — reassigning a subscription to a different customer or
            // product after the fact is a data-integrity red flag more
            // than a routine edit. Cancel this one and create a new
            // subscription instead; that keeps an honest history.
        ];
    }
}
