<?php

declare(strict_types=1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use McMatters\SingleRole\Models\Role;

use function array_merge;
use function explode;
use function is_array;
use function is_numeric;
use function is_string;
use function str_contains;

use const false;
use const null;
use const true;

trait HasRole
{
    protected static array $cachedRoles = [];

    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), ['role_id' => 'int']);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'role_id',
            $this->primaryKey,
            __FUNCTION__,
        );
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function scopeRole(Builder $builder, mixed $role): Builder
    {
        return $builder->whereIn(
            "{$this->table}.role_id",
            $this->parseRoles($role),
        );
    }

    public function hasRole(mixed $role): bool
    {
        $currentRole = $this->getAttribute('role_id');
        $delimiter = Config::get('single-role.delimiter');

        if (!is_numeric($role) && is_string($role)) {
            if (isset(self::$cachedRoles[$role])) {
                return self::$cachedRoles[$role]->getKey() === $currentRole;
            }

            if (str_contains($role, $delimiter)) {
                $role = explode($delimiter, $role);
            } else {
                /** @var \McMatters\SingleRole\Models\Role $roleModel */
                $roleModel = Role::query()->where('name', $role)->first();

                if (null === $roleModel) {
                    return false;
                }

                self::$cachedRoles[$role] = $roleModel;

                return $currentRole === $roleModel->getKey();
            }
        }

        if (is_array($role)) {
            foreach ($role as $item) {
                if (is_numeric($item) && ((int) $item) === $currentRole) {
                    return true;
                }

                if (is_string($item)) {
                    $item = self::$cachedRoles[$item] ?? Role::query()
                        ->where('name', $item)
                        ->first();
                }

                if (
                    $item instanceof Model &&
                    $item->getKey() === $currentRole
                ) {
                    return true;
                }
            }

            return false;
        }

        if ($role instanceof Collection) {
            return $role->contains(
                static fn (Role $role) => $role->getKey() === $currentRole,
            );
        }

        return $currentRole === $role;
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function attachRole(mixed $role): self
    {
        $this->forceFill(['role_id' => $this->parseRole($role)])->save();

        return $this;
    }

    public function detachRole(): self
    {
        $this->forceFill(['role_id' => null])->save();

        return $this;
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function parseRole(mixed $role): ?int
    {
        if (null === $role) {
            return null;
        }

        if (is_string($role) && !is_numeric($role)) {
            if (isset(self::$cachedRoles[$role])) {
                return self::$cachedRoles[$role]->getKey();
            }

            self::$cachedRoles[$role] = Role::query()
                ->where('name', $role)
                ->firstOrFail();

            $role = self::$cachedRoles[$role]->getKey();
        } elseif ($role instanceof Model) {
            $role = $role->getKey();
        }

        return (int) $role;
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function parseRoles(mixed $roles): array
    {
        if (!is_array($roles)) {
            return [$this->parseRole($roles)];
        }

        $roleIds = [];
        $roleNames = [];

        foreach ($roles as $role) {
            if (is_numeric($role)) {
                $roleIds[] = (int) $role;
            } elseif (is_string($role)) {
                $roleNames[] = $role;
            }
        }

        if (!empty($roleNames)) {
            $roleIds = array_merge(
                $roleIds,
                Role::query()->whereIn('name', $roleNames)->pluck('id')->all(),
            );
        }

        return $roleIds;
    }
}
