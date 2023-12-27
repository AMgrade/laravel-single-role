<?php

declare(strict_types=1);

namespace AMgrade\SingleRole\Services;

use Illuminate\Container\Container;
use Illuminate\Http\Request;

class SingleRoleService
{
    public function hasRole(
        string $role,
        ?Request $request = null,
        array $guards = [],
    ): bool {
        return $this->has($role, 'hasRole', $request, $guards);
    }

    public function hasPermission(
        string $permission,
        ?Request $request = null,
        array $guards = [],
    ): bool {
        return $this->has($permission, 'hasPermissions', $request, $guards);
    }

    protected function has(
        string $ability,
        string $method,
        ?Request $request = null,
        array $guards = [],
    ): bool {
        $guards = empty($guards) ? [null] : $guards;
        $request ??= Container::getInstance()->make('request');

        foreach ($guards as $guard) {
            if (null === ($user = $request->user($guard))) {
                continue;
            }

            if ($user->{$method}($ability)) {
                return true;
            }
        }

        return false;
    }
}
