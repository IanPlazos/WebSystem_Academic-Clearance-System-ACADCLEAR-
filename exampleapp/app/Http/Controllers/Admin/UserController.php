<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display list of students
     */
    public function students()
    {
        $students = User::where('role', 'student')
            ->with('college')
            ->latest()
            ->paginate(15);
        
        return view('admin.users.students.index', compact('students'));
    }

    /**
     * Show form to create new student
     */
    public function createStudent()
    {
        $colleges = College::all();
        return view('admin.users.students.create', compact('colleges'));
    }

    /**
     * Store a new student
     */
    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'college_id' => 'required|exists:colleges,id'
        ]);

        $validated['role'] = 'student';
        $validated['password'] = Hash::make($validated['password']);

        $student = User::create($validated);

        // Auto-create clearances for all departments in student's college
        $departments = Department::where('college_id', $student->college_id)->get();
        foreach ($departments as $department) {
            $student->clearances()->create([
                'department_id' => $department->id,
                'status' => 'pending'
            ]);
        }

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
        
        $colleges = College::all();
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
            'college_id' => 'required|exists:colleges,id'
        ]);

        // If college changed, recreate clearances
        $oldCollegeId = $user->college_id;
        
        $user->update($validated);

        // If college changed, delete old clearances and create new ones
        if ($oldCollegeId != $user->college_id) {
            // Delete old clearances
            $user->clearances()->delete();
            
            // Create new clearances for new college
            $departments = Department::where('college_id', $user->college_id)->get();
            foreach ($departments as $department) {
                $user->clearances()->create([
                    'department_id' => $department->id,
                    'status' => 'pending'
                ]);
            }
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
    public function staff()
    {
        $staff = User::where('role', 'staff')
            ->with(['college', 'department'])
            ->latest()
            ->paginate(15);
        
        return view('admin.users.staff.index', compact('staff'));
    }

    /**
     * Show form to create new staff
     */
    public function createStaff()
    {
        $colleges = College::with('departments')->get();
        return view('admin.users.staff.create', compact('colleges'));
    }

    /**
     * Store a new staff
     */
    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id'
        ]);

        $validated['role'] = 'staff';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff created successfully.');
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
        return view('admin.users.staff.edit', compact('user', 'colleges'));
    }

    /**
     * Update staff
     */
    public function updateStaff(Request $request, User $user)
    {
        if ($user->role !== 'staff') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id'
        ]);

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
}