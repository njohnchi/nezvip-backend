<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RequestCaseLinkRequest;
use App\Services\CaseDiscoveryService;
use App\Services\CaseVerificationService;
use App\Services\ReusableFieldMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function __construct(
        public CaseVerificationService $caseVerificationService,
        public CaseDiscoveryService $caseDiscoveryService,
        public ReusableFieldMapper $reusableFieldMapper,
    ) {}

    public function requestLink(RequestCaseLinkRequest $request): JsonResponse
    {
        $this->caseVerificationService->issue((string) $request->validated('email'));

        return response()->json([
            'success' => true,
            'message' => 'If that email has prior submissions, a continuation link is on its way.',
        ], 202);
    }

    public function index(Request $request): JsonResponse
    {
        $verification = $this->caseVerificationService->resolve((string) $request->query('token', ''));

        if (! $verification) {
            return $this->invalidLink();
        }

        $cases = collect($this->caseDiscoveryService->forEmail($verification->email))
            ->map(fn (array $case) => [
                ...$case,
                'fields' => $this->reusableFieldMapper->forCaseReference($case['case_reference']),
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $verification->email,
                'cases' => $cases,
            ],
        ]);
    }

    public function prefill(Request $request, string $caseReference): JsonResponse
    {
        $verification = $this->caseVerificationService->resolve((string) $request->query('token', ''));

        if (! $verification) {
            return $this->invalidLink();
        }

        $owned = collect($this->caseDiscoveryService->forEmail($verification->email))
            ->contains(fn (array $case) => $case['case_reference'] === $caseReference);

        if (! $owned) {
            return response()->json([
                'success' => false,
                'message' => 'That undertaking is not linked to this email address.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'case_reference' => $caseReference,
                'fields' => $this->reusableFieldMapper->forCaseReference($caseReference),
            ],
        ]);
    }

    private function invalidLink(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'This continuation link is invalid or has expired.',
        ], 403);
    }
}
