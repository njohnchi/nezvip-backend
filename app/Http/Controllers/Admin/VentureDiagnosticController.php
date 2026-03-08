<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VentureDiagnostic;
use Illuminate\Http\Request;

class VentureDiagnosticController extends Controller
{
    public function index()
    {
        $diagnostics = VentureDiagnostic::with('reviewer')
            ->latest()
            ->paginate(20);

        return inertia('admin/diagnostics/Index', [
            'diagnostics' => $diagnostics,
            'stats' => [
                'total' => VentureDiagnostic::count(),
                'pending' => VentureDiagnostic::where('status', 'pending')->count(),
                'reviewing' => VentureDiagnostic::where('status', 'reviewing')->count(),
                'approved' => VentureDiagnostic::where('status', 'approved')->count(),
                'rejected' => VentureDiagnostic::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function show(VentureDiagnostic $diagnostic)
    {
        $diagnostic->load('reviewer');

        return inertia('admin/diagnostics/Show', [
            'diagnostic' => $diagnostic,
        ]);
    }

    public function update(Request $request, VentureDiagnostic $diagnostic)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewing,approved,rejected,re-architect,kill',
            'admin_notes' => 'nullable|string',
            'viability_score' => 'nullable|integer|min:0|max:100',
            'risk_assessment' => 'nullable|array',
            'recommended_action' => 'nullable|string',
        ]);

        $diagnostic->update([
            ...$validated,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Diagnostic updated successfully');
    }

    public function destroy(VentureDiagnostic $diagnostic)
    {
        $diagnostic->delete();

        return redirect()->route('admin.diagnostics.index')
            ->with('success', 'Diagnostic deleted successfully');
    }
}
