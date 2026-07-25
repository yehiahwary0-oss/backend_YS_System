<?php

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\LoginDTO;
use App\Domains\Auth\Models\User;
use App\Domains\System\Services\AuditService;
use App\Exceptions\Auth\AccountDisabledException;
use App\Exceptions\Auth\InvalidCredentialsException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginAction
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    /**
     * @throws InvalidCredentialsException
     * @throws AccountDisabledException
     */
    public function execute(LoginDTO $dto): array
    {
        $this->checkRateLimit($dto->ipAddress);

        $user = User::with('role')
            ->where('email', $dto->email)
            ->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            RateLimiter::hit($this->rateLimitKey($dto->ipAddress));

            $this->auditService->log(
                action: 'auth.login_failed',
                resourceType: 'User',
                context: ['email' => $dto->email, 'ip' => $dto->ipAddress],
            );

            throw new InvalidCredentialsException();
        }

        if (! $user->isActive()) {
            throw new AccountDisabledException();
        }

        RateLimiter::clear($this->rateLimitKey($dto->ipAddress));

        // Update login metadata
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $dto->ipAddress,
        ]);

        // Revoke previous tokens to enforce single-session policy
        $user->tokens()->delete();

        $token = $user->createToken(
            name:      'admin-session',
            abilities: ['admin'],
            expiresAt: $dto->remember ? now()->addDays(30) : now()->addHours(8),
        );

        $this->auditService->log(
            action: 'auth.login',
            resourceType: 'User',
            resourceId: $user->id,
            userId: $user->id,
        );

        return [
            'user'       => $user,
            'token'      => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ];
    }

    private function checkRateLimit(string $ip): void
    {
        $key = $this->rateLimitKey($ip);
        $maxAttempts = (int) config('security.rate_limits.auth_attempts', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw new InvalidCredentialsException(
                "Too many login attempts. Try again in {$seconds} seconds.",
                429
            );
        }
    }

    private function rateLimitKey(string $ip): string
    {
        return 'login:' . $ip;
    }
}
