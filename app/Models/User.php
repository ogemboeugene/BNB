<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 * 
 * Represents a user in the BNB Management System.
 * This model handles user authentication, authorization, and role management.
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * User roles constants.
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    /**
     * Available user roles.
     */
    public const AVAILABLE_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'role' => self::ROLE_USER,
    ];

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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Check if the user has a specific role.
     * 
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user is an admin.
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if the user is a regular user.
     * 
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->hasRole(self::ROLE_USER);
    }

    /**
     * Assign a role to the user.
     * 
     * @param string $role
     * @return bool
     */
    public function assignRole(string $role): bool
    {
        if (!in_array($role, self::AVAILABLE_ROLES)) {
            return false;
        }

        return $this->update(['role' => $role]);
    }

    /**
     * Get the user's role display name.
     * 
     * @return string
     */
    public function getRoleDisplayName(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_USER => 'User',
            default => 'Unknown'
        };
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Scope a query to only include regular users.
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('role', self::ROLE_USER);
    }

    /**
     * Boot the model.
     * 
     * Sets up model events for logging and audit trail.
     */
    protected static function boot()
    {
        parent::boot();

        // Log user events for audit trail
        static::created(function ($model) {
            logger()->info("User created: {$model->email} (ID: {$model->id})");
        });

        static::updated(function ($model) {
            logger()->info("User updated: {$model->email} (ID: {$model->id})");
        });

        static::deleted(function ($model) {
            logger()->info("User deleted: {$model->email} (ID: {$model->id})");
        });
    }
}
