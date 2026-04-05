<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClearanceController extends Controller
{
    /**
     * Display student's clearance status
     */
    public function index()
    {
        $student = auth()->user();
        
        $clearances = $student->clearances()
            ->with('department')
            ->orderByRaw("FIELD(status, 'pending', 'rejected', 'approved')")
            ->get();

        $stats = [
            'total' => $clearances->count(),
            'approved' => $clearances->where('status', 'approved')->count(),
            'pending' => $clearances->where('status', 'pending')->count(),
            'rejected' => $clearances->where('status', 'rejected')->count(),
            'progress' => $clearances->count() > 0 
                ? round(($clearances->where('status', 'approved')->count() / $clearances->count()) * 100, 2)
                : 0
        ];

        return view('student.clearances.index', compact('clearances', 'stats'));
    }

    /**
     * Show specific clearance details
     */
    public function show($id)
    {
        $clearance = auth()->user()
            ->clearances()
            ->with('department')
            ->findOrFail($id);

        return view('student.clearances.show', compact('clearance'));
    }

    /**
     * Get clearance summary for API
     */
    public function summary(Request $request)
    {
        $student = auth()->user();
        
        $clearances = $student->clearances()->with('department')->get();
        
        $summary = [
            'total' => $clearances->count(),
            'approved' => $clearances->where('status', 'approved')->count(),
            'pending' => $clearances->where('status', 'pending')->count(),
            'rejected' => $clearances->where('status', 'rejected')->count(),
            'progress' => $clearances->count() > 0 
                ? round(($clearances->where('status', 'approved')->count() / $clearances->count()) * 100, 2)
                : 0,
            'departments' => $clearances->map(function($c) {
                return [
                    'id' => $c->department_id,
                    'name' => $c->department->name,
                    'status' => $c->status,
                    'remarks' => $c->remarks,
                    'updated_at' => $c->updated_at->format('M d, Y')
                ];
            })
        ];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($summary);
        }

        return view('student.clearances.summary', [
            'summary' => $summary,
            'clearances' => $clearances,
            'stats' => [
                'total' => $summary['total'],
                'approved' => $summary['approved'],
                'pending' => $summary['pending'],
                'rejected' => $summary['rejected'],
                'progress' => $summary['progress'],
            ],
        ]);
    }
}