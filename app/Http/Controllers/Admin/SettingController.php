<?php

namespace App\Http\Controllers\Admin;

use App\Domains\System\Models\Setting;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    /**
     * GET /api/v1/admin/settings
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage_settings');

        $settings = Setting::query()
            ->when($request->query('group'), fn ($q, $g) => $q->group($g))
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $settings,
        ]);
    }

    /**
     * GET /api/v1/admin/settings/{setting}
     */
    public function show(Setting $setting): JsonResponse
    {
        $this->authorize('manage_settings');

        return response()->json([
            'success' => true,
            'data'    => $setting,
        ]);
    }

    /**
     * PUT /api/v1/admin/settings/{setting}
     */
    public function update(Request $request, Setting $setting): JsonResponse
    {
        $this->authorize('manage_settings');

        $validated = $request->validate([
            'value' => ['required'],
        ]);

        $oldValue = $setting->value;

        $setting->update([
            'value'      => $validated['value'],
            'updated_by' => Auth::id(),
        ]);

        // Bust public settings cache on any update
        Cache::forget('public_settings');

        $this->auditService->log(
            action:       'setting.updated',
            resourceType: 'Setting',
            resourceId:   $setting->id,
            oldValues:    ['value' => $oldValue],
            newValues:    ['value' => $validated['value']],
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully.',
            'data'    => $setting->fresh(),
        ]);
    }
}
