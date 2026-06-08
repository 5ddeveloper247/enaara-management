<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'username',
        'phone_number',
        'employee_id',
        'is_active',
        'password',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'is_active'             => 'boolean',
            'must_change_password'  => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function routeNotificationForMail(): ?string
    {
        $email = trim((string) ($this->email ?? ''));

        if ($email !== '') {
            return $email;
        }

        $this->loadMissing('employee');

        return $this->employee?->routeNotificationForMail();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->whereNull('user_roles.deleted_at')
            ->withTimestamps();
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function scopeExcludingSystemAdmin(Builder $query): Builder
    {
        return $query->whereDoesntHave('roles', function (Builder $q) {
            $q->where(function (Builder $inner) {
                $inner->where('is_system_admin', true)
                    ->orWhere('slug', 'super-admin');
            });
        });
    }

    public function isSystemAdminUser(): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains(
            fn (Role $role) => $role->is_system_admin || $role->slug === 'super-admin'
        );
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }
}
