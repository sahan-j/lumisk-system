<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    public const ROLES = ['super_admin', 'admin', 'staff'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'job_title',
        'avatar',
        'role',
        'is_active',
        'last_login_at',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Default attribute values — mirror the DB column defaults so freshly
     * instantiated (not-yet-refreshed) models resolve role/active correctly.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'super_admin',
        'is_active' => true,
    ];

    /** Per-instance cache of effective permission names. */
    protected ?array $permissionCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        return in_array($permission, $this->effectivePermissions(), true);
    }

    /**
     * Resolve the user's effective permissions: role defaults merged with
     * per-user overrides (granted adds, revoked removes). Cached per request.
     *
     * @return array<int, string>
     */
    public function effectivePermissions(): array
    {
        if ($this->permissionCache !== null) {
            return $this->permissionCache;
        }

        $set = DB::table('role_permissions')
            ->where('role', $this->role)
            ->pluck('permission_name')
            ->flip()
            ->toArray();

        foreach (DB::table('user_permissions')->where('user_id', $this->id)->get() as $override) {
            if ($override->granted) {
                $set[$override->permission_name] = true;
            } else {
                unset($set[$override->permission_name]);
            }
        }

        return $this->permissionCache = array_keys($set);
    }

    public function clearPermissionCache(): void
    {
        $this->permissionCache = null;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'staff' => 'Staff',
            default => 'Unknown',
        };
    }

    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => '#6d5cff',
            'admin' => '#00d4ff',
            'staff' => '#10b981',
            default => '#94a3b8',
        };
    }
}
