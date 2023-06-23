<?php

declare(strict_types=1);

namespace AMgrade\SingleRole\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;

use const false;
use const null;

class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        $this->setTable(Config::get('single-role.tables.permissions'));

        parent::__construct($attributes);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            Config::get('single-role.tables.permission_role'),
            null,
            null,
            $this->primaryKey,
            null,
            __FUNCTION__,
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('single-role.models.user'),
            Config::get('single-role.tables.permission_user'),
            null,
            null,
            $this->primaryKey,
            null,
            __FUNCTION__,
        );
    }
}
