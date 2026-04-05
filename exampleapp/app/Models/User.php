<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the connection that should be used for the model.
     */
    public function getConnectionName()
    {
        // Use the current default connection (will be switched to 'tenant' by middleware)
        return config('database.default');
    }

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
        'college_id',
        'department_id',
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

    /**
     * Get the college that the user belongs to.
     */
    public function college()
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the department that the user belongs to (for staff).
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the clearances for the student.
     */
    public function clearances()
    {
        return $this->hasMany(Clearance::class, 'student_id');
    }

    /**
     * Check if the user is a school admin.
     */
    public function isSchoolAdmin()
    {
        return $this->role === 'school_admin';
    }

    /**
     * Check if the user is a staff member.
     */
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /**
     * Check if the user is a student.
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }
}