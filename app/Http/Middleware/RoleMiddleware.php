<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $effectiveRole = $request->is('api/*')
            ? $request->user()->role->value
            : session('impersonate_role', $request->user()->role->value);

        if (! in_array($effectiveRole, UserRole::values(), true)
            || ! in_array($effectiveRole, $roles, true)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden.'], 403)
                : abort(403);
        }

        return $next($request);
    }
}
