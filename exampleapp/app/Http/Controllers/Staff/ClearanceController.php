<?php
namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\ClearanceChecklistItem;
use App\Models\College;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentClearanceApprovedMail;

class ClearanceController extends Controller
{
    private ?bool $supportsApprovedByName = null;

    private function isTenantAdmin(User $user): bool
    {
        return $user->role === 'school_admin';
    }

    private function canPersistApprovedByName(): bool
    {
        if ($this->supportsApprovedByName !== null) {
            return $this->supportsApprovedByName;
        }

        $this->supportsApprovedByName = Schema::hasColumn('clearance_checklist_items', 'approved_by_name');

        return $this->supportsApprovedByName;
    }

    private function canAccessClearance(User $user, Clearance $clearance): bool
    {
        if (!$user->office_role) {
            return false;
        }

        $hasAssignedChecklist = $clearance->checklistItems()
            ->where('office_role', $user->office_role)
            ->exists();

        if (!$hasAssignedChecklist) {
            return false;
        }

        // Office-wide roles (e.g., Registrar, Librarian) can sign across departments.
        if (blank($user->department_id)) {
            return true;
        }

        return (int) $clearance->department_id === (int) $user->department_id;
    }

    private function canManageChecklistItem(User $user, Clearance $clearance, ClearanceChecklistItem $item): bool
    {
        if (!$this->canAccessClearance($user, $clearance)) {
            return false;
        }

        return $item->office_role === $user->office_role;
    }

    /**
     * Show form for staff to add clearance for a student.
     */
    public function create()
    {
        $actor = auth()->user();
        $officeRoles = User::officeRoles();

        $colleges = College::query();
        if ($this->isTenantAdmin($actor) && $actor->college_id) {
            $colleges->where('id', $actor->college_id);
        }

        $colleges = $colleges->orderBy('name')->get(['id', 'name']);

        return view('staff.clearances.create', compact('actor', 'officeRoles', 'colleges'));
    }

    /**
     * Store clearance created by staff for their assigned department.
     */
    public function store(Request $request)
    {
        $actor = auth()->user();
        $isTenantAdmin = $this->isTenantAdmin($actor);

        $collegeExists = Rule::exists('colleges', 'id');
        if ($isTenantAdmin && $actor->college_id) {
            $collegeExists = Rule::exists('colleges', 'id')->where(function ($query) use ($actor) {
                $query->where('id', $actor->college_id);
            });
        }

        $validated = $request->validate([
            'college_id' => [
                'required',
                $collegeExists,
            ],
            'department_id' => ['nullable', 'exists:departments,id'],
            'clearance_title' => ['required', 'string', 'max:255'],
            'checklist_items' => ['nullable', 'array'],
            'checklist_items.*' => ['string', 'max:255'],
            'custom_item_name' => ['nullable', 'string', 'max:255'],
            'custom_item_contact' => ['nullable', 'string', 'max:255'],
            'custom_item_location' => ['nullable', 'string', 'max:255'],
            'custom_item_office_role' => ['nullable', Rule::in(array_keys(User::officeRoles()))],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $items = $this->buildChecklistItems(
            $validated['checklist_items'] ?? [],
            $validated['custom_item_name'] ?? null,
            $validated['custom_item_contact'] ?? null,
            $validated['custom_item_location'] ?? null,
            $validated['custom_item_office_role'] ?? null
        );

        if (empty($items)) {
            return back()->withErrors([
                'checklist_items' => 'Please select at least one checklist item (office or instructor).',
            ])->withInput();
        }

        $selectedCollegeId = (int) $validated['college_id'];
        $selectedDepartmentId = !empty($validated['department_id']) ? (int) $validated['department_id'] : null;
        $collegeDepartmentIds = Department::query()
            ->where('college_id', $selectedCollegeId)
            ->pluck('id');
        $defaultCollegeDepartmentId = $collegeDepartmentIds->first();

        if ($selectedDepartmentId) {
            $departmentInCollege = Department::query()
                ->where('id', $selectedDepartmentId)
                ->where('college_id', $selectedCollegeId)
                ->exists();

            if (!$departmentInCollege) {
                return back()->withErrors([
                    'department_id' => 'Selected department does not belong to the chosen college.',
                ])->withInput();
            }
        }

        $studentsQuery = User::query()
            ->where('role', 'student');

        if ($selectedDepartmentId) {
            $studentsQuery->where('department_id', $selectedDepartmentId);
        } else {
            $studentsQuery->where(function ($query) use ($selectedCollegeId) {
                $query->where('college_id', $selectedCollegeId)
                    ->orWhereHas('department', function ($departmentQuery) use ($selectedCollegeId) {
                        $departmentQuery->where('college_id', $selectedCollegeId);
                    });
            });
        }

        $students = $studentsQuery
            ->orderBy('name')
            ->get(['id', 'department_id']);

        if ($students->isEmpty()) {
            return back()->withErrors([
                'college_id' => 'No students found for the selected college/department.',
            ])->withInput();
        }

        $firstItem = $items[0];
        $processed = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $departmentId = $selectedDepartmentId ?: $student->department_id;

            if (!$departmentId && $selectedDepartmentId === null) {
                // Reuse a department from the student's existing college clearances when possible.
                $departmentId = Clearance::query()
                    ->where('student_id', $student->id)
                    ->whereIn('department_id', $collegeDepartmentIds)
                    ->value('department_id');
            }

            if (!$departmentId && $selectedDepartmentId === null) {
                // Final fallback: default to first department in selected college.
                $departmentId = $defaultCollegeDepartmentId;
            }

            if (!$departmentId) {
                $skipped++;
                continue;
            }

            $clearance = Clearance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'department_id' => $departmentId,
                ],
                [
                    'status' => 'pending',
                    'remarks' => $validated['remarks'] ?? null,
                    'clearance_title' => $validated['clearance_title'],
                    'office_or_instructor' => $firstItem['item_name'],
                    'approval_location' => $firstItem['location'],
                ]
            );

            $clearance->checklistItems()->delete();

            foreach ($items as $index => $item) {
                $clearance->checklistItems()->create([
                    'item_name' => $item['item_name'],
                    'office_role' => $item['office_role'],
                    'contact_person' => $item['contact_person'] ?? null,
                    'location' => $item['location'],
                    'status' => 'pending',
                    'sort_order' => $index,
                ]);
            }

            $processed++;
        }

        if ($processed === 0) {
            return back()->withErrors([
                'college_id' => 'No clearance was created. Please ensure the selected college has at least one department and matching students.',
            ])->withInput();
        }

        $message = "Clearance assigned to {$processed} student(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} student(s) were skipped because no department is assigned.";
        }

        return redirect()->route('admin.clearances.index')
            ->with('success', $message);
    }

    /**
     * Mark a checklist item as pending/approved.
     */
    public function updateChecklistItem(Request $request, Clearance $clearance, ClearanceChecklistItem $item)
    {
        $actor = auth()->user();
        $canPersistApprovedByName = $this->canPersistApprovedByName();

        if ($item->clearance_id !== $clearance->id || !$this->canManageChecklistItem($actor, $clearance, $item)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved'])],
            'approved_by_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($canPersistApprovedByName && $request->input('status') === 'approved'),
            ],
        ]);

        $signerName = trim((string) ($validated['approved_by_name'] ?? $actor->name));

        $payload = [
            'status' => $validated['status'],
            'approved_at' => $validated['status'] === 'approved' ? now() : null,
        ];

        if ($validated['status'] === 'approved') {
            // Keep signer visible in legacy tenant schemas that don't yet have approved_by_name.
            $payload['contact_person'] = $signerName;
        }

        if ($canPersistApprovedByName) {
            $payload['approved_by_name'] = $validated['status'] === 'approved'
                ? $signerName
                : null;
        }

        $item->update($payload);

        $this->recalculateClearanceStatus($clearance);

        return back()->with('success', 'Checklist item status updated.');
    }

    /**
     * Display list of clearances for staff's department
     */
    public function index(Request $request)
    {
        $actor = auth()->user();
        $isTenantAdmin = $this->isTenantAdmin($actor);

        if (!$isTenantAdmin && !$actor->office_role) {
            $clearances = Clearance::query()->whereRaw('1 = 0')->paginate(20);
            session()->flash('warning', 'Your account has no office role assigned yet. Please contact your school admin.');

            return view('staff.clearances.index', compact('clearances'));
        }

        $query = Clearance::with(['student', 'department']);

        if ($isTenantAdmin) {
            // Show all recent clearances for the tenant admin.
        } else {
            $query->whereHas('checklistItems', function ($itemQuery) use ($actor) {
                    $itemQuery->where('office_role', $actor->office_role);
                });

            if (filled($actor->department_id)) {
                $query->where('department_id', $actor->department_id);
            }
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search by student name
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $allowedSorts = ['created_at', 'updated_at', 'status'];
        $sort = $request->get('sort', 'created_at');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $order = strtolower((string) $request->get('order', 'desc'));
        if (!in_array($order, ['asc', 'desc'], true)) {
            $order = 'desc';
        }

        $query->orderBy($sort, $order);

        $clearances = $query
            ->with(['checklistItems' => function ($itemQuery) use ($actor, $isTenantAdmin) {
                if (!$isTenantAdmin) {
                    $itemQuery->where('office_role', $actor->office_role);
                }
            }])
            ->paginate(20)
            ->withQueryString();

        return view('staff.clearances.index', compact('clearances'));
    }

    /**
     * Approve a clearance
     */
    public function approve(Clearance $clearance)
    {
        $actor = auth()->user();

        if (!$this->canAccessClearance($actor, $clearance)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payload = [
            'status' => 'approved',
            'approved_at' => now(),
            'contact_person' => $actor->name,
        ];

        if ($this->canPersistApprovedByName()) {
            $payload['approved_by_name'] = $actor->name;
        }

        $affected = $clearance->checklistItems()
            ->where('office_role', $actor->office_role)
            ->update($payload);

        if ($affected === 0) {
            return response()->json(['error' => 'No clearance item assigned to your office role.'], 403);
        }

        $this->recalculateClearanceStatus($clearance);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Clearance approved successfully']);
        }

        return back()->with('success', 'Clearance approved successfully.');
    }

    /**
     * Reject a clearance with remarks
     */
    public function reject(Request $request, Clearance $clearance)
    {
        $actor = auth()->user();

        if (!$this->canAccessClearance($actor, $clearance)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:500'
        ]);

        $hasRoleItem = $clearance->checklistItems()
            ->where('office_role', $actor->office_role)
            ->exists();

        if (!$hasRoleItem) {
            return response()->json(['error' => 'No clearance item assigned to your office role.'], 403);
        }

        $checklistQuery = $clearance->checklistItems()
            ->where('office_role', $actor->office_role);

        $payload = [
            'status' => 'pending',
            'approved_at' => null,
        ];

        if ($this->canPersistApprovedByName()) {
            $payload['approved_by_name'] = null;
        }

        $checklistQuery->update($payload);

        $clearance->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks']
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Clearance rejected successfully']);
        }

        return back()->with('success', 'Clearance rejected successfully.');
    }

    /**
     * Bulk approve clearances
     */
    public function bulkApprove(Request $request)
    {
        $actor = auth()->user();
        if (!$actor->office_role) {
            return response()->json([
                'success' => false,
                'message' => 'No office role assigned to your account.'
            ], 403);
        }
        
        $validated = $request->validate([
            'clearance_ids' => 'required|array',
            'clearance_ids.*' => 'exists:clearances,id'
        ]);

        $query = Clearance::whereIn('id', $validated['clearance_ids']);

        $query->whereHas('checklistItems', function ($itemQuery) use ($actor) {
            $itemQuery->where('office_role', $actor->office_role);
        });

        if (filled($actor->department_id)) {
            $query->where('department_id', $actor->department_id);
        }

        $clearances = $query->get();

        $count = 0;
        foreach ($clearances as $clearance) {
            $checklistQuery = $clearance->checklistItems()
                ->where('office_role', $actor->office_role);

            $payload = [
                    'status' => 'approved',
                    'approved_at' => now(),
                    'contact_person' => $actor->name,
                ];

            if ($this->canPersistApprovedByName()) {
                $payload['approved_by_name'] = $actor->name;
            }

            $affected = $checklistQuery->update($payload);

            if ($affected > 0) {
                $this->recalculateClearanceStatus($clearance);
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} clearances approved successfully"
        ]);
    }

    /**
     * Export clearances to CSV
     */
    public function export(Request $request)
    {
        $actor = auth()->user();
        $isTenantAdmin = $this->isTenantAdmin($actor);

        if (!$isTenantAdmin && !$actor->office_role) {
            return back()->with('warning', 'No office role assigned to your account.');
        }

        $query = Clearance::with(['student', 'department', 'checklistItems']);

        if ($isTenantAdmin) {
            $query->whereHas('student', function ($studentQuery) use ($actor) {
                $studentQuery->where('college_id', $actor->college_id);
            });
        } else {
            $query->whereHas('checklistItems', function ($itemQuery) use ($actor) {
                    $itemQuery->where('office_role', $actor->office_role);
                });

            if (filled($actor->department_id)) {
                $query->where('department_id', $actor->department_id);
            }
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $clearances = $query->get();

        $filename = "clearances-" . now()->format('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add headers
        fputcsv($handle, ['Student Name', 'Student Email', 'Department', 'Clearance Title', 'Office/Instructor', 'Approval Location', 'Checklist Items', 'Status', 'Remarks', 'Request Date', 'Last Updated']);

        // Add data
        foreach ($clearances as $clearance) {
            $checklistItems = $isTenantAdmin
                ? $clearance->checklistItems
                : $clearance->checklistItems->where('office_role', $actor->office_role);

            $checklistSummary = $checklistItems
                ->map(function ($item) {
                    $location = $item->location ? ' (' . $item->location . ')' : '';
                    return $item->item_name . $location . ' [' . ucfirst($item->status) . ']';
                })
                ->implode('; ');

            fputcsv($handle, [
                $clearance->student?->name,
                $clearance->student?->email,
                $clearance->department?->name,
                $clearance->clearance_title ?? '',
                $clearance->office_or_instructor ?? '',
                $clearance->approval_location ?? '',
                $checklistSummary,
                ucfirst($clearance->status),
                $clearance->remarks ?? '',
                $clearance->created_at->format('M d, Y H:i'),
                $clearance->updated_at->format('M d, Y H:i')
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Build checklist items from selected checkbox values plus optional custom item.
     */
    private function buildChecklistItems(
        array $selectedItems,
        ?string $customItemName,
        ?string $customItemContact,
        ?string $customItemLocation,
        ?string $customItemOfficeRole
    ): array
    {
        $items = [];

        foreach ($selectedItems as $entry) {
            $entry = trim((string) $entry);
            if ($entry === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $entry, 4));

            $officeRole = $parts[0] ?? null;
            $itemName = $parts[1] ?? null;
            $contactPerson = $parts[2] ?? null;
            $location = $parts[3] ?? null;

            // Backward compatibility with old "item|contact|location" values.
            if (!array_key_exists($officeRole, User::officeRoles())) {
                $itemName = $parts[0] ?? null;
                $contactPerson = $parts[1] ?? null;
                $location = $parts[2] ?? null;
                $officeRole = $this->guessOfficeRole($itemName);
            }

            if (!$itemName) {
                continue;
            }

            $items[] = [
                'item_name' => $itemName,
                'office_role' => $officeRole,
                'contact_person' => $contactPerson,
                'location' => $location,
            ];
        }

        $customItemName = trim((string) $customItemName);
        $customItemContact = trim((string) $customItemContact);
        $customItemLocation = trim((string) $customItemLocation);
        $customItemOfficeRole = trim((string) $customItemOfficeRole);

        if ($customItemName !== '') {
            $items[] = [
                'item_name' => $customItemName,
                'office_role' => array_key_exists($customItemOfficeRole, User::officeRoles())
                    ? $customItemOfficeRole
                    : $this->guessOfficeRole($customItemName),
                'contact_person' => $customItemContact !== '' ? $customItemContact : null,
                'location' => $customItemLocation !== '' ? $customItemLocation : null,
            ];
        }

        return $items;
    }

    /**
     * Infer office role from checklist item text.
     */
    private function guessOfficeRole(?string $itemName): ?string
    {
        $value = strtolower((string) $itemName);

        if (str_contains($value, 'library') || str_contains($value, 'librarian')) {
            return User::OFFICE_ROLE_LIBRARIAN;
        }

        if (str_contains($value, 'registrar')) {
            return User::OFFICE_ROLE_REGISTRAR;
        }

        if (str_contains($value, 'cashier') || str_contains($value, 'account')) {
            return User::OFFICE_ROLE_CASHIER;
        }

        if (str_contains($value, 'guidance')) {
            return User::OFFICE_ROLE_GUIDANCE_COUNSELOR;
        }

        if (str_contains($value, 'chair')) {
            return User::OFFICE_ROLE_DEPARTMENT_CHAIR;
        }

        if (str_contains($value, 'research')) {
            return User::OFFICE_ROLE_RESEARCH_COORDINATOR;
        }

        if (str_contains($value, 'thesis') || str_contains($value, 'adviser') || str_contains($value, 'advisor')) {
            return User::OFFICE_ROLE_THESIS_ADVISER;
        }

        if (str_contains($value, 'student affairs') || str_contains($value, 'osa')) {
            return User::OFFICE_ROLE_STUDENT_AFFAIRS_OFFICER;
        }

        return null;
    }

    /**
     * Recompute clearance status based on all checklist items.
     */
    private function recalculateClearanceStatus(Clearance $clearance): void
    {
        $clearance->load('checklistItems', 'student');

        if ($clearance->checklistItems->isEmpty()) {
            return;
        }

        $oldStatus = $clearance->status;

        $allApproved = $clearance->checklistItems->every(fn ($item) => $item->status === 'approved');

        $newStatus = $allApproved ? 'approved' : 'pending';

        $clearance->update([
            'status' => $newStatus,
            'remarks' => $allApproved ? null : $clearance->remarks,
        ]);

        // If clearance just transitioned to approved, notify the student.
        if ($oldStatus !== 'approved' && $newStatus === 'approved' && $clearance->student && filled($clearance->student->email)) {
            try {
                Mail::to($clearance->student->email)->send(new StudentClearanceApprovedMail($clearance));
            } catch (\Exception $e) {
                // Swallow mail errors to avoid breaking approval flow; consider logging in the future.
            }
        }
    }
}