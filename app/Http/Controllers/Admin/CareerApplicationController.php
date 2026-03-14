<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\CareerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CareerApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = CareerApplication::with(['career', 'reviewer'])->latest();

        if ($request->filled('career_id')) {
            $query->where('career_id', $request->career_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $applications = $query->paginate(20);

        return inertia('admin/career-applications/Index', [
            'applications' => $applications,
            'careers' => Career::select('id', 'role_title', 'venture_name')->get(),
            'filters' => [
                'career_id' => $request->career_id ?? 'all',
                'status' => $request->status ?? 'all',
            ],
            'stats' => [
                'total' => CareerApplication::count(),
                'pending' => CareerApplication::where('status', 'pending')->count(),
                'reviewing' => CareerApplication::where('status', 'reviewing')->count(),
                'shortlisted' => CareerApplication::where('status', 'shortlisted')->count(),
                'rejected' => CareerApplication::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function show(CareerApplication $careerApplication): Response
    {
        $careerApplication->load(['career', 'reviewer']);

        return inertia('admin/career-applications/Show', [
            'application' => $careerApplication,
        ]);
    }

    public function update(Request $request, CareerApplication $careerApplication): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewing,shortlisted,rejected,closed'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $careerApplication->update([
            ...$validated,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Application updated successfully.');
    }

    public function destroy(CareerApplication $careerApplication): RedirectResponse
    {
        $careerApplication->delete();

        return redirect()->route('admin.career-applications.index')
            ->with('success', 'Application deleted successfully.');
    }
}
