<?php

namespace App\Jobs;

use App\Domains\Auth\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Async job: send welcome email to newly created admin user.
 *
 * afterCommit() is enforced at dispatch site in UserController.
 * ShouldBeUnique prevents duplicate welcome emails on retry.
 */
class SendAdminUserCreatedJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;
    public int $backoff = 30;

    public function __construct(
        private readonly string $userId,
        private readonly string $temporaryPassword,
    ) {}

    public function uniqueId(): string
    {
        return "admin-welcome-{$this->userId}";
    }

    public function handle(): void
    {
        $user = User::with('role')->find($this->userId);

        if ($user === null) {
            $this->release(5);
            return;
        }

        Mail::send(
            'emails.admin-welcome',
            [
                'user'     => $user,
                'password' => $this->temporaryPassword,
                'loginUrl' => config('app.url') . '/admin/login',
            ],
            fn ($m) => $m
                ->to($user->email, $user->name)
                ->subject('Your YS Systems Admin Account')
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Admin welcome email failed.', [
            'user_id' => $this->userId,
            'error'   => $exception->getMessage(),
        ]);
    }
}
