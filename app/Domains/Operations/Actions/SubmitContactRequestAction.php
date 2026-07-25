<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\ContactRequest;
use App\Jobs\SendContactRequestNotificationJob;
use Illuminate\Http\Request;

class SubmitContactRequestAction
{
    /**
     * Store contact request and dispatch async notification.
     *
     * The notification job uses ->afterCommit() to guarantee it only
     * enters the queue AFTER the INSERT transaction has committed.
     * This prevents the Race Condition where the Worker runs before
     * the ContactRequest row exists in the database.
     */
    public function execute(array $data, Request $request): ContactRequest
    {
        $spamScore = $this->calculateSpamScore($data);

        $contact = ContactRequest::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'subject'    => $data['subject'] ?? null,
            'message'    => $data['message'],
            'type'       => $data['type'] ?? 'general',
            'status'     => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'spam_score' => $spamScore,
        ]);

        // afterCommit() — job only dispatched after DB transaction commits
        // ShouldBeUnique on the Job — prevents duplicate emails on retry
        dispatch(new SendContactRequestNotificationJob($contact->id))
            ->afterCommit();

        return $contact;
    }

    private function calculateSpamScore(array $data): float
    {
        $score = 0.0;
        $message = $data['message'];

        if (preg_match_all('/https?:\/\//', $message, $matches)) {
            $score += min(0.3, count($matches[0]) * 0.1);
        }

        if (strlen($message) > 20) {
            $capsRatio = strlen(preg_replace('/[^A-Z]/', '', $message)) / strlen($message);
            if ($capsRatio > 0.5) $score += 0.2;
        }

        $spamWords = ['casino', 'viagra', 'crypto', 'investment opportunity', 'click here', 'free money'];
        $lowerMsg  = strtolower($message);
        foreach ($spamWords as $word) {
            if (str_contains($lowerMsg, $word)) $score += 0.2;
        }

        return min(1.0, $score);
    }
}
