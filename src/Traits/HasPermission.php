<?php

declare(strict_types=1);

namespace AMgrade\SingleRole\Traits;

use AMgrade\SingleRole\Models\Permission;
use AMgrade\SingleRole\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

use function explode;
use function get_class;
use function is_numeric;
use function is_string;

use const false;
use const null;
use const true;

trait HasPermission
{
    protected static array $cachedPermissions = [];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            null,
            null,
            'permission_id',
            null,
            null,
            __FUNCTION__,
        );
    }

    public function hasPermission(string $permission): bool
    {
        return $this->getPermissions()->contains(
            static function ($item) use ($permission) {
                return is_numeric($permission)
                    ? $item->getKey() === (int) $permission
                    : $item->getAttribute('name') === $permission;
            },
        );
    }

    public function hasPermissions(
        array|string $permissions,
        bool $checkAll = false
    ): bool {
        if (is_string($permissions)) {
            $permissions = explode(
                Config::get('single-role.delimiter'),
                $permissions,
            );
        }

        foreach ((array) $permissions as $permission) {
            if ($this->hasPermission($permission)) {
                if (!$checkAll) {
                    return true;
                }
            } elseif ($checkAll) {
                return false;
            }
        }

        return $checkAll;
    }

    public function getPermissions(): Collection
    {
        $class = get_class($this);
        $key = $this->getKey();

        if (!isset(self::$cachedPermissions[$class][$key])) {
            $this->setCachedPermissions($class, $key, $this->getAllPermissions());
        }

        return self::$cachedPermissions[$class][$key];
    }

    public function attachPermissions(
        mixed $id,
        array $attributes = [],
        bool $touch = true
    ): self {
        $this->permissions()->attach($id, $attributes, $touch);
        $this->updateCachedPermissions();

        return $this;
    }

    public function detachPermissions(mixed $ids = null, bool $touch = true): self
    {
        $this->permissions()->detach($ids, $touch);
        $this->updateCachedPermissions();

        return $this;
    }

    public function syncPermissions(mixed $ids, bool $detaching = true): self
    {
        $this->permissions()->sync($ids, $detaching);
        $this->updateCachedPermissions();

        return $this;
    }

    protected function getAllPermissions(): Collection
    {
        if ($this instanceof Role) {
            return $this->getAttribute('permissions');
        }

        /** @var \AMgrade\SingleRole\Models\Role|null $role */
        $role = $this->getRelationValue('role');
        $modelPermissions = $this->getRelationValue('permissions');

        if (null !== $role) {
            return $modelPermissions->merge(
                $role->getRelationValue('permissions'),
            );
        }

        return new Collection();
    }

    protected function setCachedPermissions(
        string $class,
        int|string $key,
        Collection $permissions
    ): void {
        self::$cachedPermissions[$class][$key] = $permissions;
    }

    protected function updateCachedPermissions(): void
    {
        $class = get_class($this);
        $key = $this->getKey();

        unset(self::$cachedPermissions[$class][$key]);

        $this->setCachedPermissions($class, $key, $this->getAllPermissions());
    }
}
