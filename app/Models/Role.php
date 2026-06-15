<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Eloquent-модель роли RBAC (Spatie Permission).
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name'];
}
