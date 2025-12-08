<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Spatie\Permission\Traits\HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'user_type',
        'phone',
        'address',
        'bio',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    // Roles relationship is provided by HasRoles trait
    // public function roles(): BelongsToMany
    // {
    //    return $this->belongsToMany(Role::class, 'role_user')->with('permissions');
    // }

    /**
     * Check if the user is an admin.
     */
    public function isAdminUser(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if the user is a customer.
     */
    public function isCustomerUser(): bool
    {
        return $this->user_type === 'customer';
    }

    /**
     * Get the user type badge class.
     */
    public function getUserTypeBadgeClass(): string
    {
        return $this->user_type === 'admin' ? 'bg-primary' : 'bg-success';
    }

    /**
     * Get the user type display name.
     */
    public function getUserTypeDisplayName(): string
    {
        return $this->user_type === 'admin' ? 'Admin' : 'Customer';
    }
}
