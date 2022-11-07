<?php

declare(strict_types=1);

namespace McMatters\SingleRole\Middleware;

use Closure;
use Illuminate\Http\Request;
use McMatters\SingleRole\Exceptions\PermissionDeniedException;

use const null;

class CheckPermission
{
    /**
     * @throws \McMatters\SingleRole\Exceptions\PermissionDeniedException
     */
    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        $user = $request->user();

        if (null !== $user && $user->hasPermissions($permission)) {
            return $next($request);
        }

        throw new PermissionDeniedException($permission);
    }
}
