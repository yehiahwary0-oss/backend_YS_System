<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Content\Actions\CreateDocumentationArticleAction;
use App\Domains\Content\Actions\CreateDocumentationCategoryAction;
use App\Domains\Content\Actions\UpdateDocumentationArticleAction;
use App\Domains\Content\Actions\UpdateDocumentationCategoryAction;
use App\Domains\Content\Models\DocumentationArticle;
use App\Domains\Content\Models\DocumentationCategory;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentationController extends Controller
{
    public function __construct(
        private readonly CreateDocumentationCategoryAction $createCategory,
        private readonly UpdateDocumentationCategoryAction $updateCategory,
        private readonly CreateDocumentationArticleAction  $createArticle,
        private readonly UpdateDocumentationArticleAction  $updateArticle,
        private readonly AuditService                      $auditService,
    ) {}

    // ── Categories ───────────────────────────────────────────────────

    public function indexCategories(Request $request): JsonResponse
    {
        $this->authorize('manage_documentation');

        $categories = DocumentationCategory::with(['product:id,name_en,slug', 'parent:id,title_en'])
            ->roots()
            ->when($request->query('product_id'), fn ($q, $id) => $q->forProduct($id))
            ->ordered()
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $this->authorize('manage_documentation');

        $validated = $request->validate([
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'parent_id'  => ['nullable', 'uuid', 'exists:documentation_categories,id'],
            'slug'       => ['required', 'string', 'max:100', 'alpha_dash', 'unique:documentation_categories,slug'],
            'title_en'   => ['required', 'string', 'max:150'],
            'title_ar'   => ['required', 'string', 'max:150'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $category = $this->createCategory->execute($validated);

        $this->auditService->logModelChange('documentation_category.created', $category);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data'    => $category,
        ], Response::HTTP_CREATED);
    }

    public function updateCategory(Request $request, DocumentationCategory $category): JsonResponse
    {
        $this->authorize('manage_documentation');

        $validated = $request->validate([
            'slug'       => ['sometimes', 'string', 'max:100', 'alpha_dash', "unique:documentation_categories,slug,{$category->id}"],
            'title_en'   => ['sometimes', 'string', 'max:150'],
            'title_ar'   => ['sometimes', 'string', 'max:150'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'parent_id'  => ['nullable', 'uuid', 'exists:documentation_categories,id'],
        ]);

        // Prevent circular reference: category can't be its own parent
        if (isset($validated['parent_id']) && $validated['parent_id'] === $category->id) {
            abort(422, 'A category cannot be its own parent.');
        }

        $updated = $this->updateCategory->execute($category, $validated);

        $this->auditService->logModelChange('documentation_category.updated', $updated);

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function destroyCategory(DocumentationCategory $category): JsonResponse
    {
        $this->authorize('manage_documentation');

        // Prevent deletion if category has articles
        if ($category->articles()->exists()) {
            abort(422, 'Cannot delete a category that contains articles. Move or delete the articles first.');
        }

        $this->auditService->logModelChange('documentation_category.deleted', $category);
        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }

    // ── Articles ─────────────────────────────────────────────────────

    public function indexArticles(Request $request): JsonResponse
    {
        $this->authorize('manage_documentation');

        $articles = DocumentationArticle::with(['category:id,title_en,slug', 'author:id,name'])
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->query('published'), fn ($q, $p) => $p === 'true' ? $q->published() : $q)
            ->when($request->query('search'), fn ($q, $s) =>
                $q->where('title_en', 'ilike', "%{$s}%")->orWhere('title_ar', 'ilike', "%{$s}%")
            )
            ->ordered()
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $articles->items(),
            'meta'    => [
                'current_page' => $articles->currentPage(),
                'last_page'    => $articles->lastPage(),
                'total'        => $articles->total(),
            ],
        ]);
    }

    public function storeArticle(Request $request): JsonResponse
    {
        $this->authorize('manage_documentation');

        $validated = $request->validate([
            'category_id'  => ['required', 'uuid', 'exists:documentation_categories,id'],
            'slug'         => ['required', 'string', 'max:150', 'alpha_dash', 'unique:documentation_articles,slug'],
            'title_en'     => ['required', 'string', 'max:200'],
            'title_ar'     => ['required', 'string', 'max:200'],
            'content_en'   => ['required', 'string'],
            'content_ar'   => ['required', 'string'],
            'version_tag'  => ['nullable', 'string', 'max:20'],
            'is_published' => ['sometimes', 'boolean'],
            'sort_order'   => ['sometimes', 'integer', 'min:0'],
        ]);

        $article = $this->createArticle->execute($validated);

        $this->auditService->logModelChange('documentation_article.created', $article);

        return response()->json([
            'success' => true,
            'message' => 'Article created successfully.',
            'data'    => $article,
        ], Response::HTTP_CREATED);
    }

    public function showArticle(DocumentationArticle $article): JsonResponse
    {
        $this->authorize('manage_documentation');

        return response()->json([
            'success' => true,
            'data'    => $article->load(['category:id,title_en,slug', 'author:id,name']),
        ]);
    }

    public function updateArticle(Request $request, DocumentationArticle $article): JsonResponse
    {
        $this->authorize('manage_documentation');

        $validated = $request->validate([
            'slug'         => ['sometimes', 'string', 'max:150', "unique:documentation_articles,slug,{$article->id}"],
            'title_en'     => ['sometimes', 'string', 'max:200'],
            'title_ar'     => ['sometimes', 'string', 'max:200'],
            'content_en'   => ['sometimes', 'string'],
            'content_ar'   => ['sometimes', 'string'],
            'version_tag'  => ['nullable', 'string', 'max:20'],
            'is_published' => ['sometimes', 'boolean'],
            'sort_order'   => ['sometimes', 'integer', 'min:0'],
            'category_id'  => ['sometimes', 'uuid', 'exists:documentation_categories,id'],
        ]);

        $old = $article->only(['title_en', 'is_published']);
        $updated = $this->updateArticle->execute($article, $validated);

        $this->auditService->log(
            action:       'documentation_article.updated',
            resourceType: 'DocumentationArticle',
            resourceId:   $updated->id,
            oldValues:    $old,
        );

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function destroyArticle(DocumentationArticle $article): JsonResponse
    {
        $this->authorize('manage_documentation');

        $this->auditService->logModelChange('documentation_article.deleted', $article);
        $article->delete();

        return response()->json(['success' => true, 'message' => 'Article deleted successfully.']);
    }
}
