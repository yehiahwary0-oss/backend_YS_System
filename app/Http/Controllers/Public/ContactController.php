<?php

namespace App\Http\Controllers\Public;

use App\Domains\Operations\Actions\SubmitContactRequestAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function __construct(
        private readonly SubmitContactRequestAction $submitContact,
    ) {}

    /**
     * POST /api/v1/public/contact
     * Rate limited: 3 per hour per IP
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email:rfc', 'max:255'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'type'    => ['sometimes', Rule::in(['general', 'sales', 'support', 'partnership'])],
        ]);

        $contactRequest = $this->submitContact->execute($validated, $request);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been received. We will get back to you soon.',
            'data'    => ['id' => $contactRequest->id],
        ]);
    }
}
