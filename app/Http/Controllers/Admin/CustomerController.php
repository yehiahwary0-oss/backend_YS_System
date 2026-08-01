<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Billing\Models\Customer;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Billing\CreateCustomerRequest;
use App\Http\Requests\Admin\Billing\UpdateCustomerRequest;
use App\Http\Resources\Admin\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_subscriptions');

        $customers = Customer::withCount('subscriptions')
            ->when($request->query('search'), fn ($q, $s) =>
                $q->where('name', 'ilike', "%{$s}%")->orWhere('email', 'ilike', "%{$s}%")
            )
            ->orderBy('name')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => CustomerResource::collection($customers->items()),
            'meta'    => [
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'total'        => $customers->total(),
            ],
        ]);
    }

    public function store(CreateCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create([...$request->validated(), 'created_by' => Auth::id()]);

        $this->auditService->logModelChange('customer.created', $customer);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully.',
            'data'    => new CustomerResource($customer),
        ], Response::HTTP_CREATED);
    }

    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('manage_subscriptions');

        return response()->json([
            'success' => true,
            'data'    => new CustomerResource($customer->loadCount('subscriptions')->load('creator')),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        $this->auditService->logModelChange('customer.updated', $customer);

        return response()->json(['success' => true, 'data' => new CustomerResource($customer->fresh())]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('manage_subscriptions');

        if ($customer->subscriptions()->exists()) {
            abort(422, 'Cannot delete a customer with existing subscriptions. Cancel or remove their subscriptions first.');
        }

        $this->auditService->logModelChange('customer.deleted', $customer);
        $customer->delete();

        return response()->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }
}
