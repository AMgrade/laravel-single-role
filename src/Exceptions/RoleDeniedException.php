<?php

declare(strict_types=1);

namespace McMatters\SingleRole\Exceptions;

use Illuminate\Support\Facades\Lang;

class RoleDeniedException extends AccessDeniedException
{
    public function __construct(string $role)
    {
        parent::__construct(
            Lang::get(
                'single-role::single-role.exceptions.role',
                ['role' => $role],
            ),
        );
    }
}
