<?php

namespace App\Models;


use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id', 'status', 'must_change_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function daPersonnel()
    {
        return $this->hasOne(DAPersonnel::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Existing helper, left in place — used by dashboard scoping logic
     * that predates the Administrator/User account-management system.
     * "Supervisor" no longer exists as a distinct role after the
     * 2026_08_11_000001 migration (merged into "User"), so in practice
     * this now behaves the same as isAdministrator() for role purposes,
     * but is kept for backward compatibility with any existing callers.
     */
    public function isAdminOrSupervisor(): bool
    {
        return in_array($this->role?->name, ['Admin', 'Administrator', 'Supervisor'], true);
    }

    public function isEnumerator(): bool
    {
        return $this->role?->name === 'Enumerator';
    }

    /**
     * The two-role DA Dashboard account-management system.
     * Administrator = full account/system management access.
     * Everyone else is a "User" (view-only dashboard access).
     */
    public function isAdministrator(): bool
    {
        return $this->role?->name === 'Administrator';
    }

    public function isUserRole(): bool
    {
        return ! $this->isAdministrator();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
