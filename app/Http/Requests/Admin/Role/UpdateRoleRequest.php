<?php

namespace App\Http\Requests\Admin\Role;

use App\Domains\Auth\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_admins') ?? false;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name'          => ['sometimes', 'string', 'max:100'],
            'slug'          => ['sometimes', 'string', 'max:100', 'alpha_dash', Rule::unique('roles', 'slug')->ignore($roleId)],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['sometimes', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ];
    }
}
