<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insight;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InsightController extends Controller
{
    public function index()
    {
        $insights = Insight::with('author')
            ->latest()
            ->paginate(15);

        return inertia('admin/insights/Index', [
            'insights' => $insights,
            'stats' => [
                'total' => Insight::count(),
                'published' => Insight::where('is_published', true)->count(),
                'drafts' => Insight::where('is_published', false)->count(),
                'premium' => Insight::where('is_premium', true)->count(),
            ],
        ]);
    }

    public function create()
    {
        return inertia('admin/insights/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('insights', 'slug')],
            'excerpt' => ['required', 'string'],
            'content' => ['required', 'string'],
            'preview_chars' => ['nullable', 'integer', 'min:120', 'max:3000'],
            'cover_image_url' => ['nullable', 'url', 'max:2048'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_premium' => ['boolean'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $isPublished = (bool) ($validated['is_published'] ?? false);

        Insight::create([
            ...$validated,
            'slug' => $validated['slug'] ?? Str::slug($validated['title']).'-'.Str::lower(Str::random(6)),
            'preview_chars' => $validated['preview_chars'] ?? 500,
            'is_premium' => (bool) ($validated['is_premium'] ?? false),
            'is_published' => $isPublished,
            'published_at' => $isPublished
                ? ($validated['published_at'] ?? now())
                : null,
            'author_id' => auth()->id(),
        ]);

        return redirect()->route('admin.insights.index')
            ->with('success', 'Insight created successfully.');
    }

    public function edit(Insight $insight)
    {
        return inertia('admin/insights/Edit', [
            'insight' => $insight,
        ]);
    }

    public function update(Request $request, Insight $insight)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('insights', 'slug')->ignore($insight->id)],
            'excerpt' => ['required', 'string'],
            'content' => ['required', 'string'],
            'preview_chars' => ['nullable', 'integer', 'min:120', 'max:3000'],
            'cover_image_url' => ['nullable', 'url', 'max:2048'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_premium' => ['boolean'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $isPublished = (bool) ($validated['is_published'] ?? false);

        $insight->update([
            ...$validated,
            'preview_chars' => $validated['preview_chars'] ?? 500,
            'is_premium' => (bool) ($validated['is_premium'] ?? false),
            'is_published' => $isPublished,
            'published_at' => $isPublished
                ? ($validated['published_at'] ?? $insight->published_at ?? now())
                : null,
        ]);

        return redirect()->route('admin.insights.index')
            ->with('success', 'Insight updated successfully.');
    }

    public function destroy(Insight $insight)
    {
        $insight->delete();

        return redirect()->route('admin.insights.index')
            ->with('success', 'Insight deleted successfully.');
    }
}
