<?php

declare(strict_types=1);

namespace AMgrade\SingleRole\Middleware;

use AMgrade\SingleRole\Exceptions\RoleDeniedException;
use AMgrade\SingleRole\Services\SingleRoleService;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * @throws \AMgrade\SingleRole\Exceptions\RoleDeniedException
     */
    public function handle(
        Request $request,
        Closure $next,
        string $role,
        ...$guards
    ): mixed {
        /** @var \AMgrade\SingleRole\Services\SingleRoleService $service */
        $service = Container::getInstance()->make(SingleRoleService::class);

        if ($service->hasRole($role, $request, $guards)) {
            return $next($request);
        }

        throw new RoleDeniedException($role);
    }
}
