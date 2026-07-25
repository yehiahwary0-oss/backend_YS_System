<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Cms\Actions\CreateFaqAction;
use App\Domains\Cms\Actions\UpdateFaqAction;
use App\Domains\Cms\Models\Faq;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    public function __construct(
        private readonly CreateFaqAction $createFaq,
        private readonly UpdateFaqAction $updateFaq,
        private readonly AuditService    $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_faqs');

        $faqs = Faq::with('creator')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('category'), fn ($q, $c) => $q->byCategory($c))
            ->ordered()
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $faqs->items(),
            'meta'    => ['current_page' => $faqs->currentPage(), 'last_page' => $faqs->lastPage(), 'total' => $faqs->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage_faqs');

        $validated = $request->validate([
            'question_en' => ['required', 'string', 'max:500'],
            'question_ar' => ['required', 'string', 'max:500'],
            'answer_en'   => ['required', 'string'],
            'answer_ar'   => ['required', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'status'      => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'sort_order'  => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ]);

        $faq = $this->createFaq->execute($validated);
        $this->auditService->logModelChange('faq.created', $faq);

        return response()->json(['success' => true, 'data' => $faq], Response::HTTP_CREATED);
    }

    public function show(Faq $faq): JsonResponse
    {
        $this->authorize('manage_faqs');

        return response()->json(['success' => true, 'data' => $faq]);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $this->authorize('manage_faqs');

        $validated = $request->validate([
            'question_en' => ['sometimes', 'string', 'max:500'],
            'question_ar' => ['sometimes', 'string', 'max:500'],
            'answer_en'   => ['sometimes', 'string'],
            'answer_ar'   => ['sometimes', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'status'      => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'sort_order'  => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ]);

        $updated = $this->updateFaq->execute($faq, $validated);
        $this->auditService->logModelChange('faq.updated', $updated);

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->authorize('manage_faqs');

        $this->auditService->logModelChange('faq.deleted', $faq);
        $faq->delete();

        return response()->json(['success' => true, 'message' => 'FAQ deleted.']);
    }
}
