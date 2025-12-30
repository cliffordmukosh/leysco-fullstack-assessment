<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * L-SalesPro User Model
 * Handles authentication, roles, permissions for Sales Manager & Sales Representative
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Table name (uses default 'users')
    protected $table = 'users';

    /**
     * Fillable fields from assessment JSON + migrations
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',                    // 'Sales Manager', 'Sales Representative'
        'permissions',             // JSON array: ["view_all_sales", "manage_inventory"]
        'status',                  // 'active', 'inactive'
        'notification_preferences', // JSON: user notification settings
    ];

    /**
     * Casts for JSON fields and password hashing
     */
    protected $casts = [
        'permissions' => 'array',
        'notification_preferences' => 'array',
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 10+ auto-hashing
    ];

    /**
     * Append computed attributes
     */
    protected $appends = [];

    // ================= RELATIONSHIPS =================

    /**
     * Orders created by this sales rep/manager
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Activity logs by this user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Notifications for this user
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Stock transfers initiated by this user
     */
    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'user_id');
    }

    // ================= RBAC METHODS (Required for assessment) =================

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Check if user is Sales Manager (Admin-level access)
     */
    public function isSalesManager(): bool
    {
        return $this->role === 'Sales Manager';
    }

    /**
     * Check if user is Sales Representative
     */
    public function isSalesRepresentative(): bool
    {
        return $this->role === 'Sales Representative';
    }

    /**
     * Check if user can manage inventory (permission + role check)
     */
    public function canManageInventory(): bool
    {
        return $this->isSalesManager() || $this->hasPermission('manage_inventory');
    }

    /**
     * Check if user can approve sales
     */
    public function canApproveSales(): bool
    {
        return $this->isSalesManager() || $this->hasPermission('approve_sales');
    }

    /**
     * Check if account is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ================= SCOPES =================

    /**
     * Scope: Active users only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Sales Managers only
     */
    public function scopeSalesManagers($query)
    {
        return $query->where('role', 'Sales Manager');
    }

    /**
     * Scope: Sales Representatives only
     */
    public function scopeSalesRepresentatives($query)
    {
        return $query->where('role', 'Sales Representative');
    }

    // ================= ACCESSORS/MUTATORS =================

    /**
     * Full name accessor
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Check notification preference
     */
    public function canReceiveNotification(string $type): bool
    {
        $prefs = $this->notification_preferences ?? [];
        return $prefs[$type] ?? true; // Default to true if not set
    }

    // ================= BOOT METHODS =================

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set default notification preferences on create
        static::creating(function ($user) {
            if (is_null($user->notification_preferences)) {
                $user->notification_preferences = [
                    'email_order_confirmation' => true,
                    'email_low_stock' => true,
                    'email_credit_warning' => true,
                ];
            }
        });
    }
}