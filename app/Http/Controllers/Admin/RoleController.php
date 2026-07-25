<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Auth\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * GET /api/v1/admin/roles
     *
     * Read-only listing — roles are seeded, not managed via UI in this phase.
     * Used to populate role assignment dropdowns in the Users admin panel.
     */
    public function index(): JsonResponse
    {
        $this->authorize('manage_users');

        $roles = Role::orderBy('name')->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }
}
