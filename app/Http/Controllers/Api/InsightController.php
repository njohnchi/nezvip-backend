<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class InsightController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->resolveApiUser($request);
        $hasSubscription = $user?->hasActiveInsightsSubscription() ?? false;

        $insights = Insight::query()
            ->published()
            ->latest('published_at')
            ->paginate(12)
            ->through(function (Insight $insight) use ($hasSubscription) {
                $isLocked = $insight->is_premium && ! $hasSubscription;

                return [
                    'id' => $insight->id,
                    'title' => $insight->title,
                    'slug' => $insight->slug,
                    'excerpt' => $insight->excerpt,
                    'preview_content' => $insight->preview_content,
                    'cover_image_url' => $insight->cover_image_url,
                    'tags' => $insight->tags ?? [],
                    'is_premium' => $insight->is_premium,
                    'is_locked' => $isLocked,
                    'published_at' => $insight->published_at,
                    'author' => [
                        'name' => $insight->author?->name,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $insights,
            'subscription' => [
                'has_active_subscription' => $hasSubscription,
            ],
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $insight = Insight::query()
            ->with('author:id,name')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $user = $this->resolveApiUser($request);
        $hasSubscription = $user?->hasActiveInsightsSubscription() ?? false;
        $isLocked = $insight->is_premium && ! $hasSubscription;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $insight->id,
                'title' => $insight->title,
                'slug' => $insight->slug,
                'excerpt' => $insight->excerpt,
                'content' => $isLocked ? null : $insight->content,
                'preview_content' => $insight->preview_content,
                'cover_image_url' => $insight->cover_image_url,
                'tags' => $insight->tags ?? [],
                'is_premium' => $insight->is_premium,
                'is_locked' => $isLocked,
                'lock_message' => $isLocked
                    ? 'This is a premium insight. Subscribe to continue reading.'
                    : null,
                'published_at' => $insight->published_at,
                'author' => [
                    'name' => $insight->author?->name,
                ],
            ],
            'subscription' => [
                'has_active_subscription' => $hasSubscription,
            ],
        ]);
    }

    public function subscriptionStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'has_active_subscription' => $user->hasActiveInsightsSubscription(),
                'subscribed_at' => $user->insights_subscribed_at,
                'expires_at' => $user->insights_subscription_expires_at,
            ],
        ]);
    }

    public function activateSubscription(Request $request)
    {
        $validated = $request->validate([
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'insights_subscribed_at' => now(),
            'insights_subscription_expires_at' => $validated['expires_at'] ?? now()->addMonth(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Insights subscription activated.',
            'data' => [
                'has_active_subscription' => $user->hasActiveInsightsSubscription(),
                'subscribed_at' => $user->insights_subscribed_at,
                'expires_at' => $user->insights_subscription_expires_at,
            ],
        ]);
    }

    private function resolveApiUser(Request $request): ?User
    {
        if ($request->user()) {
            return $request->user();
        }

        $token = $request->bearerToken();

        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken || ! $accessToken->tokenable instanceof User) {
            return null;
        }

        return $accessToken->tokenable;
    }
}
