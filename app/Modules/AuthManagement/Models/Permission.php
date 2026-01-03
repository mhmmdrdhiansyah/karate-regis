<?php

namespace App\Modules\AuthManagement\Models;

use Database\Factories\AuthManagement\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PermissionFactory
    {
        return PermissionFactory::new();
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }
}
