<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Jobs\SendAdminUserCreatedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_users');

        $users = User::with('role')
            ->when($request->query('role'), fn ($q, $r) =>
                $q->whereHas('role', fn ($rq) => $rq->where('slug', $r))
            )
            ->when($request->query('search'), fn ($q, $s) =>
                $q->where('name', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%")
            )
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($users),
            'meta'    => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage_users');

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'role_id'  => ['required', 'uuid', 'exists:roles,id'],
        ]);

        // Prevent creating another super admin unless current user is super admin
        $role = Role::findOrFail($validated['role_id']);
        if ($role->slug === 'super_admin' && Auth::user()->role->slug !== 'super_admin') {
            abort(403, 'Only a super admin can create another super admin.');
        }

        $user = User::create($validated);

        SendAdminUserCreatedJob::dispatch($user->id, $validated['password'])->afterCommit();

        $this->auditService->log(
            action:       'user.created',
            resourceType: 'User',
            resourceId:   $user->id,
            newValues:    ['name' => $user->name, 'email' => $user->email, 'role' => $role->slug],
        );

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => new UserResource($user->load('role')),
        ], Response::HTTP_CREATED);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('manage_users');

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user->load('role')),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage_users');

        // Prevent non-super-admins from editing super admin accounts
        if ($user->role->slug === 'super_admin' && Auth::user()->role->slug !== 'super_admin') {
            abort(403, 'Cannot modify a super admin account.');
        }

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:100'],
            'email'     => ['sometimes', 'email:rfc', Rule::unique('users')->ignore($user->id)],
            'role_id'   => ['sometimes', 'uuid', 'exists:roles,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Prevent demoting or disabling own account
        if ($user->id === Auth::id()) {
            unset($validated['role_id'], $validated['is_active']);
        }

        $oldValues = $user->only(['name', 'email', 'is_active']);
        $user->update($validated);

        $this->auditService->log(
            action:       'user.updated',
            resourceType: 'User',
            resourceId:   $user->id,
            oldValues:    $oldValues,
            newValues:    $validated,
        );

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data'    => new UserResource($user->fresh('role')),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('manage_users');

        // Cannot delete own account
        if ($user->id === Auth::id()) {
            abort(422, 'You cannot delete your own account.');
        }

        // Cannot delete super admin accounts
        if ($user->role->slug === 'super_admin') {
            abort(403, 'Super admin accounts cannot be deleted.');
        }

        $user->tokens()->delete();
        $user->delete(); // Soft delete

        $this->auditService->log(
            action:       'user.deleted',
            resourceType: 'User',
            resourceId:   $user->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
