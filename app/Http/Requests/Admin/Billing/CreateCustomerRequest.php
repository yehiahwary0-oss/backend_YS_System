<?php

namespace App\Http\Requests\Admin\Billing;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_subscriptions') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'max:150', 'unique:customers,email'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}
