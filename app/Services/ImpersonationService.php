<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\ImpersonationAudit;
use App\Models\User;
use Illuminate\Support\Carbon;

class ImpersonationService
{
    public const LIFETIME_MINUTES = 15;

    public function start(User $admin, UserRole $role, ?string $reason): void
    {
        $startedAt = now();
        $expiresAt = $startedAt->copy()->addMinutes(self::LIFETIME_MINUTES);

        ImpersonationAudit::create([
            'admin_user_id' => $admin->id,
            'impersonated_role' => $role->value,
            'action' => 'started',
            'reason' => $reason,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
        ]);

        session()->put([
            'impersonate_role' => $role->value,
            'impersonate_started_at' => $startedAt->timestamp,
            'impersonate_expires_at' => $expiresAt->timestamp,
            'impersonate_scope' => 'read-only',
        ]);
    }

    public function stop(User $admin, string $action = 'stopped'): void
    {
        $role = session('impersonate_role');
        $startedAt = $this->timestamp(session('impersonate_started_at'));
        $expiresAt = $this->timestamp(session('impersonate_expires_at'));

        if ($role && $startedAt && $expiresAt) {
            ImpersonationAudit::create([
                'admin_user_id' => $admin->id,
                'impersonated_role' => $role,
                'action' => $action,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'ended_at' => now(),
            ]);
        }

        session()->forget([
            'impersonate_role',
            'impersonate_started_at',
            'impersonate_expires_at',
            'impersonate_scope',
        ]);
    }

    public function hasExpired(): bool
    {
        $expiresAt = $this->timestamp(session('impersonate_expires_at'));

        return ! $expiresAt || $expiresAt->isPast();
    }

    private function timestamp(mixed $value): ?Carbon
    {
        return is_numeric($value) ? Carbon::createFromTimestamp((int) $value) : null;
    }
}
