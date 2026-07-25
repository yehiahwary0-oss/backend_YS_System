<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Operations\Models\ContactRequest;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContactRequestController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_contact_requests');

        $requests = ContactRequest::when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('type'), fn ($q, $t) => $q->ofType($t))
            ->orderByDesc('created_at')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $requests->items(),
            'meta'    => ['current_page' => $requests->currentPage(), 'last_page' => $requests->lastPage(), 'total' => $requests->total()],
        ]);
    }

    public function show(ContactRequest $contactRequest): JsonResponse
    {
        $this->authorize('manage_contact_requests');

        // Auto-mark as read on first open
        if ($contactRequest->isNew()) {
            $contactRequest->update(['status' => 'read']);
            $this->auditService->log('contact_request.read', 'ContactRequest', $contactRequest->id);
        }

        return response()->json(['success' => true, 'data' => $contactRequest]);
    }

    public function updateStatus(Request $request, ContactRequest $contactRequest): JsonResponse
    {
        $this->authorize('manage_contact_requests');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'read', 'replied', 'archived'])],
        ]);

        $old = $contactRequest->status;
        $contactRequest->update([
            'status'     => $validated['status'],
            'handled_by' => Auth::id(),
            'handled_at' => now(),
        ]);

        $this->auditService->log(
            action:       'contact_request.status_updated',
            resourceType: 'ContactRequest',
            resourceId:   $contactRequest->id,
            oldValues:    ['status' => $old],
            newValues:    ['status' => $validated['status']],
        );

        return response()->json(['success' => true, 'data' => $contactRequest->fresh()]);
    }
}
