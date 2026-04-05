<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanRequest;
use Illuminate\Http\Request;

class PlanRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $planRequests = PlanRequest::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => PlanRequest::count(),
            'pending' => PlanRequest::where('status', 'pending')->count(),
            'approved' => PlanRequest::where('status', 'approved')->count(),
            'rejected' => PlanRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.plan-requests.index', compact('planRequests', 'counts', 'status'));
    }
}
