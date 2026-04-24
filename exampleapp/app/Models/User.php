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

    public const OFFICE_ROLE_LIBRARIAN = 'librarian';
    public const OFFICE_ROLE_REGISTRAR = 'registrar';
    public const OFFICE_ROLE_CASHIER = 'cashier';
    public const OFFICE_ROLE_GUIDANCE_COUNSELOR = 'guidance_counselor';
    public const OFFICE_ROLE_DEPARTMENT_CHAIR = 'department_chair';
    public const OFFICE_ROLE_RESEARCH_COORDINATOR = 'research_coordinator';
    public const OFFICE_ROLE_THESIS_ADVISER = 'thesis_adviser';
    public const OFFICE_ROLE_STUDENT_AFFAIRS_OFFICER = 'student_affairs_officer';

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
        'office_role',
        'permissions',
        'profile_photo_path',
    ];

    /**
     * Get the full URL for the user's profile photo.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo_path) {
            return null;
        }

        if (str_starts_with($this->profile_photo_path, 'http://') || str_starts_with($this->profile_photo_path, 'https://')) {
            return $this->profile_photo_path;
        }

        return asset('storage/' . ltrim($this->profile_photo_path, '/'));
    }

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
            'permissions' => 'array',
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

    /**
     * Allowed staff office roles.
     *
     * @return array<string, string>
     */
    public static function officeRoles(): array
    {
        $roles = [
            self::OFFICE_ROLE_LIBRARIAN => 'Librarian',
            self::OFFICE_ROLE_REGISTRAR => 'Registrar',
            self::OFFICE_ROLE_CASHIER => 'Cashier',
            self::OFFICE_ROLE_GUIDANCE_COUNSELOR => 'Guidance Counselor',
            self::OFFICE_ROLE_DEPARTMENT_CHAIR => 'Department Chair',
            self::OFFICE_ROLE_RESEARCH_COORDINATOR => 'Research Coordinator',
            self::OFFICE_ROLE_THESIS_ADVISER => 'Thesis Adviser',
            self::OFFICE_ROLE_STUDENT_AFFAIRS_OFFICER => 'Student Affairs Officer',
        ];

        try {
            $customRoles = static::query()
                ->where('role', 'staff')
                ->whereNotNull('office_role')
                ->pluck('office_role')
                ->filter(fn ($role) => is_string($role) && $role !== '')
                ->unique()
                ->values();

            foreach ($customRoles as $customRole) {
                if (!array_key_exists($customRole, $roles)) {
                    $roles[$customRole] = ucwords(str_replace('_', ' ', $customRole));
                }
            }
        } catch (\Throwable $e) {
            // Keep default roles if tenant DB is unavailable.
        }

        return $roles;
    }

    /**
     * Human-readable office role label for UI.
     */
    public function getOfficeRoleLabelAttribute(): ?string
    {
        if (!$this->office_role) {
            return null;
        }

        return static::officeRoles()[$this->office_role] ?? ucwords(str_replace('_', ' ', $this->office_role));
    }

    /**
     * Resolve RBAC permissions assigned to the user's role.
     *
     * @return array<int, string>
     */
    public function getRolePermissions(): array
    {
        $map = config('rbac.roles', []);
        $permissions = $map[$this->role] ?? [];

        if (!is_array($permissions)) {
            $permissions = [];
        }

        if ($this->role === 'staff') {
            $userPermissions = $this->normalizePermissionList($this->permissions);

            if (!empty($userPermissions)) {
                $permissions = array_merge($permissions, $this->normalizeStaffPermissions($userPermissions));
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Expand stored staff module keys into their configured permission strings.
     *
     * @param array<int, string> $permissions
     * @return array<int, string>
     */
    private function normalizeStaffPermissions(array $permissions): array
    {
        $moduleMap = config('rbac.modules', []);
        $resolved = [];

        foreach ($permissions as $permission) {
            if (!is_string($permission) || $permission === '') {
                continue;
            }

            if (array_key_exists($permission, $moduleMap) && is_array($moduleMap[$permission])) {
                $resolved = array_merge($resolved, $moduleMap[$permission]);
                continue;
            }

            $resolved[] = $permission;
        }

        return array_values(array_unique($resolved));
    }

    /**
     * Normalize a permissions payload coming from the database or request.
     *
     * @param mixed $permissions
     * @return array<int, string>
     */
    private function normalizePermissionList(mixed $permissions): array
    {
        if (is_array($permissions)) {
            $flattened = [];

            foreach ($permissions as $permission) {
                if (is_array($permission)) {
                    $flattened = array_merge($flattened, $this->normalizePermissionList($permission));
                    continue;
                }

                if (is_string($permission) && $permission !== '') {
                    $flattened[] = $permission;
                }
            }

            return array_values(array_unique($flattened));
        }

        if (is_string($permissions) && $permissions !== '') {
            $decoded = json_decode($permissions, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizePermissionList($decoded);
            }

            return array_values(array_filter(array_map('trim', explode(',', $permissions))));
        }

        return [];
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->getRolePermissions();

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    /**
     * @param array<int, string> $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}