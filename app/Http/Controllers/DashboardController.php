<?php

namespace App\Http\Controllers;

use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\FormSubmission;
use App\Models\Insight;
use App\Models\User;
use App\Models\VentureDiagnostic;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $permissions = [
            'users' => $user->can('view users'),
            'roles' => $user->can('view roles'),
            'insights' => $user->can('view insights'),
            'careers' => $user->can('manage careers'),
            'diagnostics' => $user->can('view diagnostics'),
            'submissions' => $user->can('view submissions'),
        ];

        $months = collect(range(0, 5))
            ->map(fn (int $offset): CarbonImmutable => now()->startOfMonth()->subMonths(5 - $offset)->toImmutable());

        $monthLabels = $months
            ->map(fn (CarbonImmutable $month): string => $month->format('M'))
            ->values()
            ->all();

        $summaries = [];
        $pipelines = [];
        $highlights = [];
        $analyticsSeries = [];
        $quickLinks = [];
        $recentActivity = collect();
        $topFormTypes = [];

        $activeSubscribers = 0;
        $insightTotals = null;
        $careerTotals = null;
        $applicationTotals = null;
        $diagnosticTotals = null;
        $submissionTotals = null;

        if ($permissions['users']) {
            $activeSubscribers = User::query()
                ->whereNotNull('insights_subscribed_at')
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('insights_subscription_expires_at')
                        ->orWhere('insights_subscription_expires_at', '>', now());
                })
                ->count();

            $totalUsers = User::query()->count();

            $summaries['users'] = [
                'label' => 'Users',
                'value' => $totalUsers,
                'description' => $activeSubscribers.' active insight subscribers',
                'href' => '/admin/users',
            ];

            $analyticsSeries['users'] = $this->buildTrendSeries(
                label: 'New users',
                color: 'bg-sky-500',
                query: User::query(),
                months: $months,
            );

            $quickLinks['users'] = [
                'label' => 'Manage users',
                'description' => 'Invite teammates and review assigned access.',
                'href' => '/admin/users',
            ];
        }

        if ($permissions['roles']) {
            $assignableRoles = Role::query()
                ->where('name', '!=', 'Super Admin')
                ->count();

            $summaries['roles'] = [
                'label' => 'Roles',
                'value' => $assignableRoles,
                'description' => 'Assignable roles configured for the admin team',
                'href' => '/admin/roles',
            ];

            $quickLinks['roles'] = [
                'label' => 'Review roles',
                'description' => 'Keep role access aligned with operational responsibilities.',
                'href' => '/admin/roles',
            ];
        }

        if ($permissions['insights']) {
            $insightTotals = [
                'total' => Insight::query()->count(),
                'published' => Insight::query()->where('is_published', true)->count(),
                'drafts' => Insight::query()->where('is_published', false)->count(),
                'premium' => Insight::query()->where('is_premium', true)->count(),
            ];

            $summaries['insights'] = [
                'label' => 'Published insights',
                'value' => $insightTotals['published'],
                'description' => $insightTotals['drafts'].' drafts and '.$insightTotals['premium'].' premium posts',
                'href' => '/admin/insights',
            ];

            $pipelines['insights'] = [
                'label' => 'Content pipeline',
                'href' => '/admin/insights',
                'total' => $insightTotals['total'],
                'statuses' => [
                    ['label' => 'Published', 'value' => $insightTotals['published']],
                    ['label' => 'Drafts', 'value' => $insightTotals['drafts']],
                    ['label' => 'Premium', 'value' => $insightTotals['premium']],
                ],
            ];

            $analyticsSeries['insights'] = $this->buildTrendSeries(
                label: 'Insights created',
                color: 'bg-violet-500',
                query: Insight::query(),
                months: $months,
            );

            $quickLinks['insights'] = [
                'label' => 'Publish insights',
                'description' => 'Create new thought leadership and premium reports.',
                'href' => '/admin/insights/create',
            ];

            $recentActivity = $recentActivity->merge($this->recentInsights());
        }

        if ($permissions['careers']) {
            $careerTotals = [
                'total' => Career::query()->count(),
                'active' => Career::query()->where('is_active', true)->count(),
                'inactive' => Career::query()->where('is_active', false)->count(),
            ];

            $applicationTotals = [
                'total' => CareerApplication::query()->count(),
                'pending' => CareerApplication::query()->where('status', 'pending')->count(),
                'reviewing' => CareerApplication::query()->where('status', 'reviewing')->count(),
                'shortlisted' => CareerApplication::query()->where('status', 'shortlisted')->count(),
                'rejected' => CareerApplication::query()->where('status', 'rejected')->count(),
            ];

            $summaries['careers'] = [
                'label' => 'Active openings',
                'value' => $careerTotals['active'],
                'description' => $applicationTotals['total'].' applications across '.$careerTotals['total'].' opportunities',
                'href' => '/admin/careers',
            ];

            $pipelines['applications'] = [
                'label' => 'Talent pipeline',
                'href' => '/admin/career-applications',
                'total' => $applicationTotals['total'],
                'statuses' => [
                    ['label' => 'Pending', 'value' => $applicationTotals['pending']],
                    ['label' => 'Reviewing', 'value' => $applicationTotals['reviewing']],
                    ['label' => 'Shortlisted', 'value' => $applicationTotals['shortlisted']],
                    ['label' => 'Rejected', 'value' => $applicationTotals['rejected']],
                ],
            ];

            $analyticsSeries['applications'] = $this->buildTrendSeries(
                label: 'Applications received',
                color: 'bg-emerald-500',
                query: CareerApplication::query(),
                months: $months,
            );

            $quickLinks['careers'] = [
                'label' => 'Manage careers',
                'description' => 'Keep opportunities active and aligned with talent demand.',
                'href' => '/admin/careers',
            ];

            $quickLinks['applications'] = [
                'label' => 'Review applications',
                'description' => 'Triage the latest candidate submissions.',
                'href' => '/admin/career-applications',
            ];

            $recentActivity = $recentActivity->merge($this->recentCareerApplications());
        }

        if ($permissions['diagnostics']) {
            $diagnosticTotals = [
                'total' => VentureDiagnostic::query()->count(),
                'pending' => VentureDiagnostic::query()->where('status', 'pending')->count(),
                'reviewing' => VentureDiagnostic::query()->where('status', 'reviewing')->count(),
                'approved' => VentureDiagnostic::query()->where('status', 'approved')->count(),
                'rejected' => VentureDiagnostic::query()->where('status', 'rejected')->count(),
                'average_score' => round((float) VentureDiagnostic::query()->whereNotNull('viability_score')->avg('viability_score'), 1),
            ];

            $pipelines['diagnostics'] = [
                'label' => 'Diagnostic queue',
                'href' => '/admin/diagnostics',
                'total' => $diagnosticTotals['total'],
                'statuses' => [
                    ['label' => 'Pending', 'value' => $diagnosticTotals['pending']],
                    ['label' => 'Reviewing', 'value' => $diagnosticTotals['reviewing']],
                    ['label' => 'Approved', 'value' => $diagnosticTotals['approved']],
                    ['label' => 'Rejected', 'value' => $diagnosticTotals['rejected']],
                ],
            ];

            $analyticsSeries['diagnostics'] = $this->buildTrendSeries(
                label: 'Diagnostics submitted',
                color: 'bg-amber-500',
                query: VentureDiagnostic::query(),
                months: $months,
            );

            $quickLinks['diagnostics'] = [
                'label' => 'Assess diagnostics',
                'description' => 'Review new venture diagnostics and update recommendations.',
                'href' => '/admin/diagnostics',
            ];

            $recentActivity = $recentActivity->merge($this->recentDiagnostics());
        }

        if ($permissions['submissions']) {
            $submissionTotals = [
                'total' => FormSubmission::query()->count(),
                'pending' => FormSubmission::query()->where('status', 'pending')->count(),
                'reviewing' => FormSubmission::query()->where('status', 'reviewing')->count(),
                'contacted' => FormSubmission::query()->where('status', 'contacted')->count(),
                'closed' => FormSubmission::query()->where('status', 'closed')->count(),
            ];

            $pipelines['submissions'] = [
                'label' => 'Form submissions',
                'href' => '/admin/submissions',
                'total' => $submissionTotals['total'],
                'statuses' => [
                    ['label' => 'Pending', 'value' => $submissionTotals['pending']],
                    ['label' => 'Reviewing', 'value' => $submissionTotals['reviewing']],
                    ['label' => 'Contacted', 'value' => $submissionTotals['contacted']],
                    ['label' => 'Closed', 'value' => $submissionTotals['closed']],
                ],
            ];

            $analyticsSeries['submissions'] = $this->buildTrendSeries(
                label: 'Forms received',
                color: 'bg-rose-500',
                query: FormSubmission::query(),
                months: $months,
            );

            $quickLinks['submissions'] = [
                'label' => 'Manage submissions',
                'description' => 'Stay on top of inbound requests from the client app.',
                'href' => '/admin/submissions',
            ];

            $topFormTypes = $this->topFormTypes();
            $recentActivity = $recentActivity->merge($this->recentSubmissions());
        }

        $pendingReviewCount = ($submissionTotals['pending'] ?? 0)
            + ($submissionTotals['reviewing'] ?? 0)
            + ($diagnosticTotals['pending'] ?? 0)
            + ($diagnosticTotals['reviewing'] ?? 0)
            + ($applicationTotals['pending'] ?? 0)
            + ($applicationTotals['reviewing'] ?? 0);

        if ($pendingReviewCount > 0) {
            $highlights['review_queue'] = [
                'title' => 'Pending review workload',
                'value' => $pendingReviewCount,
                'description' => 'Records currently awaiting action across your visible intake pipelines.',
            ];
        }

        if ($insightTotals !== null && $insightTotals['total'] > 0) {
            $premiumShare = (int) round(($insightTotals['premium'] / max($insightTotals['total'], 1)) * 100);

            $highlights['content_mix'] = [
                'title' => 'Premium content mix',
                'value' => $premiumShare.'%',
                'description' => $insightTotals['premium'].' of '.$insightTotals['total'].' insights are marked premium.',
            ];
        }

        if ($diagnosticTotals !== null && $diagnosticTotals['average_score'] > 0) {
            $highlights['diagnostic_score'] = [
                'title' => 'Average diagnostic score',
                'value' => $diagnosticTotals['average_score'].'/100',
                'description' => 'Average viability score across reviewed venture diagnostics.',
            ];
        }

        if ($applicationTotals !== null && $careerTotals !== null && $careerTotals['active'] > 0) {
            $applicationsPerRole = round($applicationTotals['total'] / max($careerTotals['active'], 1), 1);

            $highlights['talent_density'] = [
                'title' => 'Applications per active role',
                'value' => $applicationsPerRole,
                'description' => 'Average talent demand coverage across currently active opportunities.',
            ];
        }

        if ($permissions['users'] && isset($summaries['users'])) {
            $subscriptionCoverage = (int) round(($activeSubscribers / max($summaries['users']['value'], 1)) * 100);

            $highlights['subscriber_coverage'] = [
                'title' => 'Subscriber coverage',
                'value' => $subscriptionCoverage.'%',
                'description' => 'Share of users with an active insights subscription.',
            ];
        }

        if ($pendingReviewCount > 0) {
            $summaries['review_queue'] = [
                'label' => 'Review queue',
                'value' => $pendingReviewCount,
                'description' => 'Pending and in-progress items awaiting admin attention',
                'href' => $this->reviewQueueHref($permissions),
            ];
        }

        return Inertia::render('Dashboard', [
            'permissions' => $permissions,
            'account' => [
                'roles' => $user->getRoleNames()->values()->all(),
                'permissions_count' => $user->getAllPermissions()->count(),
                'email_verified' => $user->hasVerifiedEmail(),
                'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
                'insights_subscription_active' => $user->hasActiveInsightsSubscription(),
                'is_super_admin' => $user->hasRole('Super Admin'),
            ],
            'summaries' => $summaries,
            'pipelines' => $pipelines,
            'highlights' => $highlights,
            'analytics' => [
                'months' => $monthLabels,
                'series' => $analyticsSeries,
                'top_form_types' => $topFormTypes,
            ],
            'quick_links' => $quickLinks,
            'recent_activity' => $recentActivity
                ->sortByDesc('occurred_at')
                ->take(8)
                ->values()
                ->all(),
        ]);
    }

    private function buildTrendSeries(string $label, string $color, Builder $query, Collection $months, string $dateColumn = 'created_at'): array
    {
        $counts = $query
            ->where($dateColumn, '>=', $months->first()->startOfMonth())
            ->get([$dateColumn])
            ->countBy(fn ($model): string => CarbonImmutable::parse($model->{$dateColumn})->format('Y-m'));

        $values = $months
            ->map(fn (CarbonImmutable $month): int => (int) ($counts[$month->format('Y-m')] ?? 0))
            ->values()
            ->all();

        return [
            'label' => $label,
            'color' => $color,
            'values' => $values,
            'total' => array_sum($values),
        ];
    }

    private function topFormTypes(): array
    {
        return FormSubmission::query()
            ->get(['form_type'])
            ->countBy(fn (FormSubmission $submission): string => $submission->form_type)
            ->sortDesc()
            ->take(5)
            ->map(fn (int $count, string $formType): array => [
                'form_type' => $formType,
                'label' => $this->formTypeLabel($formType),
                'count' => $count,
            ])
            ->values()
            ->all();
    }

    private function recentInsights(): Collection
    {
        return Insight::query()
            ->with('author:id,name')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Insight $insight): array => [
                'id' => 'insight-'.$insight->id,
                'category' => 'Insight',
                'title' => $insight->title,
                'subtitle' => $insight->author?->name ?? 'Unknown author',
                'status' => $insight->is_published ? 'published' : 'draft',
                'occurred_at' => $insight->created_at?->toIso8601String(),
                'href' => '/admin/insights/'.$insight->id.'/edit',
            ]);
    }

    private function recentCareerApplications(): Collection
    {
        return CareerApplication::query()
            ->with('career:id,role_title')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (CareerApplication $application): array => [
                'id' => 'application-'.$application->id,
                'category' => 'Application',
                'title' => $application->full_name.' applied',
                'subtitle' => $application->career?->role_title ?? $application->role_applied_for,
                'status' => $application->status,
                'occurred_at' => $application->created_at?->toIso8601String(),
                'href' => '/admin/career-applications/'.$application->id,
            ]);
    }

    private function recentDiagnostics(): Collection
    {
        return VentureDiagnostic::query()
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (VentureDiagnostic $diagnostic): array => [
                'id' => 'diagnostic-'.$diagnostic->id,
                'category' => 'Diagnostic',
                'title' => $diagnostic->venture_name,
                'subtitle' => $diagnostic->full_name,
                'status' => $diagnostic->status,
                'occurred_at' => $diagnostic->created_at?->toIso8601String(),
                'href' => '/admin/diagnostics/'.$diagnostic->id,
            ]);
    }

    private function recentSubmissions(): Collection
    {
        return FormSubmission::query()
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (FormSubmission $submission): array => [
                'id' => 'submission-'.$submission->id,
                'category' => 'Submission',
                'title' => $this->formTypeLabel($submission->form_type),
                'subtitle' => $submission->data['full_name'] ?? $submission->data['email'] ?? 'Unknown submitter',
                'status' => $submission->status,
                'occurred_at' => $submission->created_at?->toIso8601String(),
                'href' => '/admin/submissions/'.$submission->id,
            ]);
    }

    private function formTypeLabel(string $formType): string
    {
        return match ($formType) {
            'scope_review' => 'Scope Review',
            'institutional_brief' => 'Institutional Brief',
            'licensing_review' => 'Licensing Review',
            'investor_brief' => 'Investor Brief',
            'production_partner' => 'Production Partner',
            'distribution_partner' => 'Distribution Partner',
            'insights_subscribe' => 'Newsletter',
            'insights_request_report' => 'Report Request',
            'media_inquiry' => 'Media Inquiry',
            'career_application' => 'Career Application',
            default => str($formType)->replace('_', ' ')->title()->toString(),
        };
    }

    private function reviewQueueHref(array $permissions): ?string
    {
        if ($permissions['submissions']) {
            return '/admin/submissions';
        }

        if ($permissions['diagnostics']) {
            return '/admin/diagnostics';
        }

        if ($permissions['careers']) {
            return '/admin/career-applications';
        }

        return null;
    }
}
