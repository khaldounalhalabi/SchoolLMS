<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\ImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImpersonationMiddleware
{
    public function __construct(private ImpersonationService $impersonation) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== UserRole::ADMIN || ! session()->has('impersonate_role')) {
            return $next($request);
        }

        if ($request->routeIs('admin.impersonate.stop')) {
            return $next($request);
        }

        if ($this->impersonation->hasExpired()) {
            $this->impersonation->stop($user, 'expired');

            if ($request->isMethodSafe()) {
                return redirect()->route('dashboard')->with('error', __('The impersonation session expired.'));
            }

            abort(403, __('The impersonation session expired.'));
        }

        if ($request->isMethodSafe() || $request->routeIs('logout')) {
            return $next($request);
        }

        abort(403, __('Impersonation is read-only.'));
    }
}
