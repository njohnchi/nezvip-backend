<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = FormSubmission::with('reviewer')->latest();

        if ($request->has('form_type') && $request->form_type !== 'all') {
            $query->where('form_type', $request->form_type);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(20);

        $formTypes = FormSubmission::select('form_type')
            ->distinct()
            ->pluck('form_type');

        return inertia('admin/submissions/Index', [
            'submissions' => $submissions,
            'formTypes' => $formTypes,
            'filters' => [
                'form_type' => $request->form_type ?? 'all',
                'status' => $request->status ?? 'all',
            ],
            'stats' => [
                'total' => FormSubmission::count(),
                'pending' => FormSubmission::where('status', 'pending')->count(),
                'reviewing' => FormSubmission::where('status', 'reviewing')->count(),
                'contacted' => FormSubmission::where('status', 'contacted')->count(),
                'closed' => FormSubmission::where('status', 'closed')->count(),
            ],
        ]);
    }

    public function show(FormSubmission $submission)
    {
        $submission->load('reviewer');

        return inertia('admin/submissions/Show', [
            'submission' => $submission,
        ]);
    }

    public function update(Request $request, FormSubmission $submission)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewing,contacted,rejected,closed',
            'admin_notes' => 'nullable|string',
        ]);

        $submission->update([
            ...$validated,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Submission updated successfully');
    }

    public function destroy(FormSubmission $submission)
    {
        $submission->delete();

        return redirect()->route('admin.submissions.index')
            ->with('success', 'Submission deleted successfully');
    }
}
