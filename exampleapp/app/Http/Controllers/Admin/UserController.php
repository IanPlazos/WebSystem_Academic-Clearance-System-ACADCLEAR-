<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StaffAccountCreatedMail;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private readonly TenantService $tenantService)
    {
    }

    /**
     * Display list of students
     */
    public function students()
    {
        $students = User::where('role', 'student')
            ->with(['college', 'department', 'clearances'])
            ->latest()
            ->paginate(15);
        
        return view('admin.users.students.index', compact('students'));
    }

    /**
     * Show form to create new student
     */
    public function createStudent()
    {
        $colleges = College::with('departments')->get();
        return view('admin.users.students.create', compact('colleges'));
    }

    /**
     * Store a new student
     */
    public function storeStudent(Request $request)
    {
        $currentStudents = User::where('role', 'student')->count();

        if (! $this->tenantService->canAddMoreStudents($currentStudents)) {
            $limit = $this->tenantService->getStudentLimit();
            $planName = (string) $this->tenantService->getCurrentPlan();
            $limitText = is_numeric($limit) ? (string) $limit : '0';

            throw ValidationException::withMessages([
                'limit' => "Student limit reached for {$planName} plan ({$limitText} students). Upgrade your plan to add more students.",
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => ['required', Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                $query->where('college_id', $request->input('college_id'));
            })],
        ]);

        $validated['role'] = 'student';
        $validated['password'] = Hash::make($validated['password']);

        $student = User::create($validated);

        // Create the student's initial clearance for their assigned department.
        $student->clearances()->create([
            'department_id' => $student->department_id,
            'status' => 'pending'
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created successfully with pending clearances.');
    }

    /**
     * Show form to edit student
     */
    public function editStudent(User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }
        
        $colleges = College::with('departments')->get();
        return view('admin.users.students.edit', compact('user', 'colleges'));
    }

    /**
     * Update student
     */
    public function updateStudent(Request $request, User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'college_id' => 'required|exists:colleges,id',
            'department_id' => ['required', Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                $query->where('college_id', $request->input('college_id'));
            })],
        ]);

        // If college changed, recreate clearances
        $oldCollegeId = $user->college_id;
        $oldDepartmentId = $user->department_id;
        
        $user->update($validated);

        // If college or department changed, keep clearances aligned to the selected department.
        if ($oldCollegeId != $user->college_id || $oldDepartmentId != $user->department_id) {
            $user->clearances()->delete();

            $user->clearances()->create([
                'department_id' => $user->department_id,
                'status' => 'pending'
            ]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Delete student
     */
    public function destroyStudent(User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }

        // Clearances will be deleted automatically (foreign key cascade)
        $user->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Display list of staff
     */
    public function staff(Request $request)
    {
        $staff = User::where('role', 'staff')
            ->with(['college', 'department'])
            ->latest()
            ->paginate(15);

        $colleges = College::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'college_id', 'name']);

        return view('admin.users.staff.index', compact('staff', 'colleges', 'departments'));
    }

    /**
     * Show form to create new staff
     */
    public function createStaff()
    {
        $colleges = College::with('departments')->get();
        $officeRoles = User::officeRoles();
        $availableModules = config('rbac.modules', []);

        return view('admin.users.staff.create', compact('colleges', 'officeRoles', 'availableModules'));
    }

    /**
     * Store a new staff
     */
    public function storeStaff(Request $request)
    {
        $this->ensureStaffPermissionsColumnExists();
        $selectedOfficeRole = (string) $request->input('office_role');
        $requiresAcademicAssignment = blank($selectedOfficeRole);
        $assignmentScope = User::staffAssignmentScope($selectedOfficeRole);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'college_id' => [
                Rule::requiredIf($assignmentScope === 'academic' || $requiresAcademicAssignment),
                'nullable',
                'exists:colleges,id',
            ],
            'department_id' => [
                Rule::requiredIf($assignmentScope === 'academic' || $requiresAcademicAssignment),
                'nullable',
                Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                    $collegeId = $request->input('college_id');

                    if ($collegeId) {
                        $query->where('college_id', $collegeId);
                    }
                }),
            ],
            'office_role' => [
                'nullable',
                Rule::requiredIf(blank($request->input('college_id')) && blank($request->input('department_id'))),
                Rule::in(array_merge(array_keys(User::officeRoles()), ['custom'])),
            ],
            'custom_office_role' => ['nullable', 'required_if:office_role,custom', 'string', 'max:255'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(config('rbac.modules', [])))],
        ]);

        $validated['office_role'] = !empty($validated['office_role'])
            ? $this->normalizeOfficeRole(
                $validated['office_role'],
                $validated['custom_office_role'] ?? null
            )
            : null;

        $validated = $this->normalizeStaffAssignment($validated, $assignmentScope);

        $plainPassword = Str::password(12, letters: true, numbers: true, symbols: false, spaces: false);
        $validated['role'] = 'staff';
        $validated['password'] = Hash::make($plainPassword);

        $validated['permissions'] = $this->resolveStaffPermissions($validated['modules'] ?? []);

        unset($validated['modules'], $validated['custom_office_role']);

        $staff = User::create($validated);

        $loginUrl = route('login');

        try {
            Mail::to($staff->email)->send(new StaffAccountCreatedMail($staff, $plainPassword, $loginUrl));
        } catch (\Throwable $e) {
            Log::warning('Failed to send staff credentials email.', [
                'staff_id' => $staff->id,
                'staff_email' => $staff->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff created successfully.')
                ->with('warning', 'Staff account was created, but the credentials email could not be sent.');
        }

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff created successfully and credentials were sent by email.');
    }

    /**
     * Show form to edit staff
     */
    public function editStaff(User $user)
    {
        if ($user->role !== 'staff') {
            abort(404);
        }
        
        $colleges = College::with('departments')->get();
        $officeRoles = User::officeRoles();
        $availableModules = config('rbac.modules', []);
        $selectedModules = $this->resolveModulesFromPermissions(is_array($user->permissions) ? $user->permissions : []);

        return view('admin.users.staff.edit', compact('user', 'colleges', 'officeRoles', 'availableModules', 'selectedModules'));
    }

    /**
     * Update staff
     */
    public function updateStaff(Request $request, User $user)
    {
        if ($user->role !== 'staff') {
            abort(404);
        }

        $this->ensureStaffPermissionsColumnExists();
        $selectedOfficeRole = (string) $request->input('office_role');
        $requiresAcademicAssignment = blank($selectedOfficeRole);
        $assignmentScope = User::staffAssignmentScope($selectedOfficeRole);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'college_id' => [
                Rule::requiredIf($assignmentScope === 'academic' || $requiresAcademicAssignment),
                'nullable',
                'exists:colleges,id',
            ],
            'department_id' => [
                Rule::requiredIf($assignmentScope === 'academic' || $requiresAcademicAssignment),
                'nullable',
                Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                    $collegeId = $request->input('college_id');

                    if ($collegeId) {
                        $query->where('college_id', $collegeId);
                    }
                }),
            ],
            'office_role' => [
                'nullable',
                Rule::requiredIf(blank($request->input('college_id')) && blank($request->input('department_id'))),
                Rule::in(array_merge(array_keys(User::officeRoles()), ['custom'])),
            ],
            'custom_office_role' => ['nullable', 'required_if:office_role,custom', 'string', 'max:255'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(config('rbac.modules', [])))],
        ]);

        $validated['office_role'] = !empty($validated['office_role'])
            ? $this->normalizeOfficeRole(
                $validated['office_role'],
                $validated['custom_office_role'] ?? null
            )
            : null;

        $validated = $this->normalizeStaffAssignment($validated, $assignmentScope);


        $validated['permissions'] = $this->resolveStaffPermissions($validated['modules'] ?? []);

        unset($validated['modules'], $validated['custom_office_role']);

        $user->update($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff updated successfully.');
    }

    /**
     * Delete staff
     */
    public function destroyStaff(User $user)
    {
        if ($user->role !== 'staff') {
            abort(404);
        }

        $user->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff deleted successfully.');
    }

    /**
     * Get departments by college (AJAX)
     */
    public function getDepartments($college)
    {
        $departments = Department::where('college_id', $college)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($departments);
    }

    /**
     * @param array<int, string> $modules
     * @return array<int, string>
     */
    private function resolveStaffPermissions(array $modules): array
    {
        $moduleMap = config('rbac.modules', []);
        $permissions = [
            'tenant.dashboard.view',
            'tenant.profile.manage',
            'tenant.clearances.view_own',
            'tenant.clearances.update_own',
        ];

        foreach ($modules as $moduleKey) {
            $modulePermissions = $moduleMap[$moduleKey] ?? [];
            if (is_array($modulePermissions)) {
                $permissions = array_merge($permissions, $modulePermissions);
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * @param array<int, string> $permissions
     * @return array<int, string>
     */
    private function resolveModulesFromPermissions(array $permissions): array
    {
        $moduleMap = config('rbac.modules', []);
        $selected = [];

        foreach ($moduleMap as $moduleKey => $modulePermissions) {
            if (!is_array($modulePermissions) || empty($modulePermissions)) {
                continue;
            }

            if (in_array($moduleKey, $permissions, true) || !empty(array_intersect($modulePermissions, $permissions))) {
                $selected[] = $moduleKey;
            }
        }

        return $selected;
    }

    private function ensureStaffPermissionsColumnExists(): void
    {
        if (Schema::hasColumn('users', 'permissions')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('office_role');
        });

        Schema::getConnection()->flushQueryLog();
    }

    private function normalizeOfficeRole(string $officeRole, ?string $customOfficeRole = null): string
    {
        if ($officeRole !== 'custom') {
            return $officeRole;
        }

        $label = trim((string) $customOfficeRole);
        if ($label === '') {
            return 'custom_role';
        }

        $slug = Str::slug($label, '_');

        if ($slug === '') {
            return 'custom_role_' . substr(md5($label), 0, 8);
        }

        return $slug;
    }

    /**
     * Normalize assignment fields according to the selected staff role scope.
     *
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function normalizeStaffAssignment(array $validated, string $assignmentScope): array
    {
        if ($assignmentScope === 'office') {
            $validated['college_id'] = null;
            $validated['department_id'] = null;

            return $validated;
        }

        if ($assignmentScope === 'hybrid') {
            $validated['college_id'] = $validated['college_id'] ?? null;
            $validated['department_id'] = $validated['department_id'] ?? null;
        }

        return $validated;
    }
}