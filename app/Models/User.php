<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions()->where('slug', $slug)->exists();
    }

    public function permissions()
    {
        return Permission::whereHas('roles', fn ($q) => $q->where('role', $this->role));
    }

    public function getPermissionSlugs(): array
    {
        return Permission::whereHas('roles', fn ($q) => $q->where('role', $this->role))
            ->pluck('slug')
            ->toArray();
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'student_user_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_user_id', 'student_user_id')
            ->withPivot('relation');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_user_id', 'parent_user_id')
            ->withPivot('relation');
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectClassroom::class, 'teacher_user_id');
    }

    public function scheduleSlots(): HasMany
    {
        return $this->hasMany(ScheduleSlot::class, 'teacher_user_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_user_id');
    }

    public function behavioralNotes(): HasMany
    {
        return $this->hasMany(BehavioralNote::class, 'student_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_user_id');
    }

    public function salaryTransfers(): HasMany
    {
        return $this->hasMany(SalaryTransfer::class, 'teacher_user_id');
    }
}
