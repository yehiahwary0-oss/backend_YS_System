<?php

namespace App\Http\Controllers\Auth;

use App\Domains\Auth\Actions\LoginAction;
use App\Domains\Auth\DTOs\LoginDTO;
use App\Domains\System\Services\AuditService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\UserResource;
use App\Exceptions\Auth\AccountDisabledException;
use App\Exceptions\Auth\InvalidCredentialsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginAction  $loginAction,
        private readonly AuditService $auditService,
    ) {}

    /**
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->loginAction->execute(
                LoginDTO::fromRequest($request)
            );

            $response = response()->json([
                'success' => true,
                'data'    => [
                    'user'       => new UserResource($result['user']),
                    'token'      => $result['token'],
                    'expires_at' => $result['expires_at'],
                ],
            ]);

            $response->cookie(Cookie::make(
                name:     'ys_admin_token',
                value:    $result['token'],
                minutes:  abs($result['expires_at']?->diffInMinutes(now()) ?? 480),
                path:     '/',
                domain:   null,
                secure:   app()->isProduction(),
                httpOnly: true,
                sameSite: 'strict',
            ));

            return $response;

        } catch (InvalidCredentialsException $e) {
            $status = $e->getCode() === 429
                ? Response::HTTP_TOO_MANY_REQUESTS
                : Response::HTTP_UNAUTHORIZED;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code'    => 'INVALID_CREDENTIALS',
            ], $status);

        } catch (AccountDisabledException) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been disabled. Contact support.',
                'code'    => 'ACCOUNT_DISABLED',
            ], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token only
        $request->user()->currentAccessToken()->delete();

        $this->auditService->log(
            action:       'auth.logout',
            resourceType: 'User',
            resourceId:   $user->id,
            userId:       $user->id,
        );

        $response = response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);

        $response->cookie(Cookie::make(
            name:     'ys_admin_token',
            value:    '',
            minutes:  -1,
            path:     '/',
            domain:   null,
            secure:   app()->isProduction(),
            httpOnly: true,
            sameSite: 'strict',
        ));

        return $response;
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource($request->user()->load('role')),
        ]);
    }
}
