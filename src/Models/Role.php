<?php

declare(strict_types=1);

namespace McMatters\SingleRole\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use McMatters\SingleRole\Traits\HasPermission;

use const false;

class Role extends Model
{
    use HasPermission;

    public $timestamps = false;

    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        $this->setTable(Config::get('single-role.tables.roles'));

        parent::__construct($attributes);
    }
}
