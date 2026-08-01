<?php

namespace App\Http\Requests\Admin\Role;

use App\Domains\Auth\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_admins') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'slug'          => ['required', 'string', 'max:100', 'alpha_dash', 'unique:roles,slug'],
            'description'   => ['nullable', 'string', 'max:255'],
            // '*' is deliberately not accepted here — granting full
            // super-admin access is a one-time, deliberate act (seeded /
            // done directly by whoever owns the company), not something
            // assembled by picking permissions from a list. Every value
            // must be a real, enforced permission — see Permission enum.
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ];
    }
}
