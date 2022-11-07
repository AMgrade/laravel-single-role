<?php

declare(strict_types=1);

namespace McMatters\SingleRole\Middleware;

use Closure;
use Illuminate\Http\Request;
use McMatters\SingleRole\Exceptions\RoleDeniedException;

use const null;

class CheckRole
{
    /**
     * @throws \McMatters\SingleRole\Exceptions\RoleDeniedException
     */
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        $user = $request->user();

        if (null !== $user && $user->hasRole($role)) {
            return $next($request);
        }

        throw new RoleDeniedException($role);
    }
}
