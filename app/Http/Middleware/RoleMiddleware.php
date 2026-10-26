<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Role names used by older routes and newer seed data share the same
     * permissions. Keep the route-level role checks while accepting both names.
     */
    private const ROLE_ALIASES = [
        'pemilik' => 'owner',
        'kasir' => 'cashier',
        'pelanggan' => 'customer',
        'koki' => 'chef',
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->user()?->role?->name;
        $userRole = self::ROLE_ALIASES[$userRole] ?? $userRole;
        $allowedRoles = array_map(
            static fn (string $role): string => self::ROLE_ALIASES[$role] ?? $role,
            $roles,
        );

        abort_unless($userRole !== null && in_array($userRole, $allowedRoles, true), 403);

        return $next($request);
    }
}
