<?php

namespace App\Http\Requests\Admin\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_subscriptions') ?? false;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'name'    => ['sometimes', 'string', 'max:150'],
            'email'   => ['sometimes', 'email', 'max:150', Rule::unique('customers', 'email')->ignore($customerId)],
            'company' => ['nullable', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}
