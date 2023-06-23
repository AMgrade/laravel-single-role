<?php

declare(strict_types=1);

namespace AMgrade\SingleRole\Middleware;

use AMgrade\SingleRole\Exceptions\PermissionDeniedException;
use AMgrade\SingleRole\Services\SingleRoleService;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * @throws \AMgrade\SingleRole\Exceptions\PermissionDeniedException
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission,
        ...$guards
    ): mixed {
        /** @var \AMgrade\SingleRole\Services\SingleRoleService $service */
        $service = Container::getInstance()->make(SingleRoleService::class);

        if ($service->hasPermission($permission, $request, $guards)) {
            return $next($request);
        }

        throw new PermissionDeniedException($permission);
    }
}
