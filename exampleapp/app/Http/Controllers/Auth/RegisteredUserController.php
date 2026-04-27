<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly TenantService $tenantService)
    {
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $colleges = College::query()
            ->with(['departments:id,college_id,name'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('auth.register', [
            'colleges' => $colleges,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $currentStudents = User::where('role', 'student')->count();

        if (! $this->tenantService->canAddMoreStudents($currentStudents)) {
            $limit = $this->tenantService->getStudentLimit();
            $planName = (string) $this->tenantService->getCurrentPlan();
            $limitText = is_numeric($limit) ? (string) $limit : '0';

            throw ValidationException::withMessages([
                'email' => "Student limit reached for {$planName} plan ({$limitText} students). Registration is disabled until the plan is upgraded.",
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(
                    fn ($query) => $query->where('college_id', (int) $request->input('college_id'))
                ),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'student',
            'college_id' => (int) $request->college_id,
            'department_id' => (int) $request->department_id,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
