<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductRelease;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReleaseController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_products');

        $releases = ProductRelease::with('product:id,name_en,slug')
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('published'), fn ($q, $p) =>
                $p === 'true' ? $q->published() : $q->where('is_published', false)
            )
            ->orderByDesc('release_date')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $releases->items(),
            'meta'    => [
                'current_page' => $releases->currentPage(),
                'last_page'    => $releases->lastPage(),
                'total'        => $releases->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage_products');

        $validated = $request->validate([
            'product_id'       => ['required', 'uuid', 'exists:products,id'],
            'version'          => ['required', 'string', 'max:20', 'regex:/^\d+\.\d+(\.\d+)?(-\w+)?$/'],
            'release_date'     => ['required', 'date', 'before_or_equal:today'],
            'type'             => ['sometimes', Rule::in(['major', 'minor', 'patch', 'hotfix'])],
            'release_notes_en' => ['nullable', 'string'],
            'release_notes_ar' => ['nullable', 'string'],
            'changelog'        => ['nullable', 'array'],
            'changelog.improvements' => ['nullable', 'array'],
            'changelog.fixes'        => ['nullable', 'array'],
            'changelog.breaking'     => ['nullable', 'array'],
            'is_published'     => ['sometimes', 'boolean'],
        ]);

        // Prevent duplicate version for same product
        $exists = ProductRelease::where('product_id', $validated['product_id'])
            ->where('version', $validated['version'])
            ->exists();

        if ($exists) {
            abort(422, "Version {$validated['version']} already exists for this product.");
        }

        $release = ProductRelease::create(array_merge($validated, [
            'created_by' => Auth::id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Release created successfully.',
            'data'    => $release->load('product:id,name_en,slug'),
        ], Response::HTTP_CREATED);
    }

    public function show(ProductRelease $release): JsonResponse
    {
        $this->authorize('manage_products');

        return response()->json([
            'success' => true,
            'data'    => $release->load(['product:id,name_en,slug', 'creator:id,name']),
        ]);
    }

    public function update(Request $request, ProductRelease $release): JsonResponse
    {
        $this->authorize('manage_products');

        $validated = $request->validate([
            'release_notes_en' => ['sometimes', 'string'],
            'release_notes_ar' => ['sometimes', 'string'],
            'changelog'        => ['sometimes', 'array'],
            'type'             => ['sometimes', Rule::in(['major', 'minor', 'patch', 'hotfix'])],
            'is_published'     => ['sometimes', 'boolean'],
            'release_date'     => ['sometimes', 'date'],
        ]);

        $release->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Release updated successfully.',
            'data'    => $release->fresh('product:id,name_en,slug'),
        ]);
    }

    public function destroy(ProductRelease $release): JsonResponse
    {
        $this->authorize('manage_products');

        // Prevent deletion of published releases
        if ($release->is_published) {
            abort(422, 'Cannot delete a published release. Unpublish it first.');
        }

        $this->auditService->logModelChange('product_release.deleted', $release);
        $release->delete();

        return response()->json(['success' => true, 'message' => 'Release deleted successfully.']);
    }
}
