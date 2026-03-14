<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CareerController extends Controller
{
    public function index(): Response
    {
        $careers = Career::withCount('applications')
            ->latest()
            ->paginate(20);

        return inertia('admin/careers/Index', [
            'careers' => $careers,
            'stats' => [
                'total' => Career::count(),
                'active' => Career::where('is_active', true)->count(),
                'inactive' => Career::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/careers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'venture_name' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'engagement_type' => ['required', 'string', 'max:100'],
            'venture_context' => ['required', 'string'],
            'responsibilities' => ['required', 'string'],
            'execution_expectations' => ['required', 'string'],
            'is_active' => ['boolean'],
        ]);

        Career::create($validated);

        return redirect()->route('admin.careers.index')
            ->with('success', 'Career opportunity created successfully.');
    }

    public function edit(Career $career): Response
    {
        return inertia('admin/careers/Edit', [
            'career' => $career,
        ]);
    }

    public function update(Request $request, Career $career): RedirectResponse
    {
        $validated = $request->validate([
            'venture_name' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'engagement_type' => ['required', 'string', 'max:100'],
            'venture_context' => ['required', 'string'],
            'responsibilities' => ['required', 'string'],
            'execution_expectations' => ['required', 'string'],
            'is_active' => ['boolean'],
        ]);

        $career->update($validated);

        return redirect()->route('admin.careers.index')
            ->with('success', 'Career opportunity updated successfully.');
    }

    public function destroy(Career $career): RedirectResponse
    {
        $career->delete();

        return redirect()->route('admin.careers.index')
            ->with('success', 'Career opportunity deleted successfully.');
    }
}
