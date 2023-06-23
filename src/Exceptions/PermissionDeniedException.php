<?php

declare(strict_types=1);

namespace AMgrade\SingleRole\Exceptions;

use Illuminate\Support\Facades\Lang;

class PermissionDeniedException extends AccessDeniedException
{
    public function __construct(string $permission)
    {
        parent::__construct(
            Lang::get(
                'single-role::single-role.exceptions.permission',
                ['permission' => $permission],
            ),
        );
    }
}
