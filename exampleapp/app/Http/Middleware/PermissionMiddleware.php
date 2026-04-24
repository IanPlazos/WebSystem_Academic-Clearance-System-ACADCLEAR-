<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $permissionList = array_filter(array_map('trim', explode('|', $permissions)));

        if (empty($permissionList)) {
            return $next($request);
        }

        $user = auth()->user();

        if (!method_exists($user, 'hasAnyPermission') || !$user->hasAnyPermission($permissionList)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
