<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Billing\Actions\CreateSubscriptionAction;
use App\Domains\Billing\Models\Subscription;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Billing\CreateSubscriptionRequest;
use App\Http\Requests\Admin\Billing\UpdateSubscriptionRequest;
use App\Http\Resources\Admin\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CreateSubscriptionAction $createSubscription,
        private readonly AuditService              $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_subscriptions');

        $subscriptions = Subscription::with(['customer:id,name,email', 'product:id,name_en,slug'])
            // Financial data is product-scoped the same way everything
            // else product-related is — an admin scoped to Product A has
            // no business seeing Product B's subscribers or revenue.
            ->when(! Auth::user()->isSuperAdmin(), fn ($q) =>
                $q->whereIn('product_id', Auth::user()->products()->pluck('products.id'))
            )
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->orderByDesc('starts_at')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => SubscriptionResource::collection($subscriptions->items()),
            'meta'    => [
                'current_page' => $subscriptions->currentPage(),
                'last_page'    => $subscriptions->lastPage(),
                'total'        => $subscriptions->total(),
            ],
        ]);
    }

    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        abort_unless(Auth::user()->canAccessProduct($validated['product_id']), 403, 'You do not have access to this product.');

        $subscription = $this->createSubscription->execute($validated);

        // Financial record — audit-logged from the very first commit of
        // this feature, not added after the fact. See migration docblock.
        $this->auditService->logModelChange('subscription.created', $subscription);

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully.',
            'data'    => new SubscriptionResource($subscription->load(['customer', 'product'])),
        ], Response::HTTP_CREATED);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        $this->authorize('manage_subscriptions');
        abort_unless(Auth::user()->canAccessProduct($subscription->product_id), 403, 'You do not have access to this product.');

        return response()->json([
            'success' => true,
            'data'    => new SubscriptionResource($subscription->load(['customer', 'product', 'creator'])),
        ]);
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        abort_unless(Auth::user()->canAccessProduct($subscription->product_id), 403, 'You do not have access to this product.');

        $old = $subscription->only(['price', 'status', 'ends_at']);
        $subscription->update($request->validated());

        $this->auditService->log(
            action:       'subscription.updated',
            resourceType: 'Subscription',
            resourceId:   $subscription->id,
            oldValues:    $old,
        );

        return response()->json(['success' => true, 'data' => new SubscriptionResource($subscription->fresh(['customer', 'product']))]);
    }

    public function destroy(Subscription $subscription): JsonResponse
    {
        $this->authorize('manage_subscriptions');
        abort_unless(Auth::user()->canAccessProduct($subscription->product_id), 403, 'You do not have access to this product.');

        // Deleting a financial record entirely (vs marking it cancelled)
        // destroys revenue history — block it. Cancel via update()
        // instead; that keeps the row (and its audit trail) intact.
        abort(422, 'Subscriptions cannot be deleted, only cancelled. Update its status to "cancelled" instead.');
    }
}
