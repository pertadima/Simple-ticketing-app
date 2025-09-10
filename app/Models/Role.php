<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_id';
    
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the admin users associated with this role
     */
    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminUser::class,
            'admin_user_roles',
            'role_id',
            'admin_user_id'
        );
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Add permission to role
     */
    public function addPermission(string $permission): self
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
        return $this;
    }

    /**
     * Remove permission from role
     */
    public function removePermission(string $permission): self
    {
        $permissions = $this->permissions ?? [];
        $key = array_search($permission, $permissions);
        if ($key !== false) {
            unset($permissions[$key]);
            $this->update(['permissions' => array_values($permissions)]);
        }
        return $this;
    }

    /**
     * Set permissions for role
     */
    public function setPermissions(array $permissions): self
    {
        $this->update(['permissions' => array_values($permissions)]);
        return $this;
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get display name or fallback to name
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ?? $this->name;
    }
}
