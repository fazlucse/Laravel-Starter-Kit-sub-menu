<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'department_id',
        'division_id',
        'role',
        'status',
        'mobile_access',
        'access_days'
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'access_days' => 'array',
            'mobile_access' => 'boolean',
        ];
    }
    public function canApproveLeave(LeaveRequest $leaveRequest): bool
    {
        if ($this->hasAnyRole(['Developer', 'Super Admin', 'Admin', 'HR Manager'])) {
            return true;
        }
        return $this->employee_id && (int)$this->employee_id === (int)$leaveRequest->inline_supervisor_id;
    }
    public function employee(): BelongsTo // This now points to the correct class
    {
        // 'employee_id' is the foreign key in your users table
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function getGenderAttribute(): string
    {
        // If employee relationship exists, return gender, else 'other'
        return $this->employee?->gender ?? 'other';
    }
}
