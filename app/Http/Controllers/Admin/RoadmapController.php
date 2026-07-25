<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Content\Actions\CreateRoadmapItemAction;
use App\Domains\Content\Actions\UpdateRoadmapItemAction;
use App\Domains\Content\Models\RoadmapItem;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RoadmapController extends Controller
{
    public function __construct(
        private readonly CreateRoadmapItemAction $createItem,
        private readonly UpdateRoadmapItemAction $updateItem,
        private readonly AuditService            $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_roadmap');

        $items = RoadmapItem::with('product:id,name_en,slug')
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->byStatus($s))
            ->when($request->query('priority'), fn ($q, $p) => $q->where('priority', $p))
            ->ordered()
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $items->items(),
            'meta'    => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage_roadmap');

        $validated = $request->validate([
            'product_id'     => ['nullable', 'uuid', 'exists:products,id'],
            'title_en'       => ['required', 'string', 'max:200'],
            'title_ar'       => ['required', 'string', 'max:200'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'status'         => ['sometimes', Rule::in(['planned', 'in_progress', 'completed', 'cancelled'])],
            'priority'       => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'target_version' => ['nullable', 'string', 'max:20'],
            'target_quarter' => ['nullable', 'string', 'max:20'],
            'is_public'      => ['sometimes', 'boolean'],
            'sort_order'     => ['sometimes', 'integer', 'min:0'],
        ]);

        $item = $this->createItem->execute($validated);
        $this->auditService->logModelChange('roadmap_item.created', $item);

        return response()->json(['success' => true, 'data' => $item], Response::HTTP_CREATED);
    }

    public function show(RoadmapItem $roadmapItem): JsonResponse
    {
        $this->authorize('manage_roadmap');

        return response()->json([
            'success' => true,
            'data'    => $roadmapItem->load('product:id,name_en,slug'),
        ]);
    }

    public function update(Request $request, RoadmapItem $roadmapItem): JsonResponse
    {
        $this->authorize('manage_roadmap');

        $validated = $request->validate([
            'title_en'       => ['sometimes', 'string', 'max:200'],
            'title_ar'       => ['sometimes', 'string', 'max:200'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'status'         => ['sometimes', Rule::in(['planned', 'in_progress', 'completed', 'cancelled'])],
            'priority'       => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'target_version' => ['nullable', 'string', 'max:20'],
            'target_quarter' => ['nullable', 'string', 'max:20'],
            'is_public'      => ['sometimes', 'boolean'],
            'sort_order'     => ['sometimes', 'integer', 'min:0'],
        ]);

        $updated = $this->updateItem->execute($roadmapItem, $validated);
        $this->auditService->logModelChange('roadmap_item.updated', $updated);

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function destroy(RoadmapItem $roadmapItem): JsonResponse
    {
        $this->authorize('manage_roadmap');

        $this->auditService->logModelChange('roadmap_item.deleted', $roadmapItem);
        $roadmapItem->delete();

        return response()->json(['success' => true, 'message' => 'Roadmap item deleted.']);
    }
}
