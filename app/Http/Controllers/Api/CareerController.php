<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\CareerApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index(): JsonResponse
    {
        $careers = Career::query()
            ->where('is_active', true)
            ->withCount('applications')
            ->latest()
            ->get()
            ->map(fn (Career $career) => [
                'id' => $career->id,
                'venture_name' => $career->venture_name,
                'role_title' => $career->role_title,
                'location' => $career->location,
                'engagement_type' => $career->engagement_type,
                'venture_context' => $career->venture_context,
                'created_at' => $career->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data' => $careers,
        ]);
    }

    public function show(Career $career): JsonResponse
    {
        if (! $career->is_active) {
            return response()->json(['success' => false, 'message' => 'This opportunity is no longer available.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $career->id,
                'venture_name' => $career->venture_name,
                'role_title' => $career->role_title,
                'location' => $career->location,
                'engagement_type' => $career->engagement_type,
                'venture_context' => $career->venture_context,
                'responsibilities' => $career->responsibilities,
                'execution_expectations' => $career->execution_expectations,
                'created_at' => $career->created_at,
            ],
        ]);
    }

    public function apply(Request $request, Career $career): JsonResponse
    {
        if (! $career->is_active) {
            return response()->json(['success' => false, 'message' => 'This opportunity is no longer available.'], 422);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'relevant_experience' => ['required', 'string', 'max:5000'],
            'profile_link' => ['required', 'url', 'max:2048'],
        ]);

        CareerApplication::create([
            ...$validated,
            'career_id' => $career->id,
            'role_applied_for' => "{$career->role_title} — {$career->venture_name}",
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your application has been received. Only shortlisted candidates will be contacted.',
        ], 201);
    }
}
