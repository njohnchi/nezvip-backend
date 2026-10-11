<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFormDraftRequest;
use App\Models\FormDraft;
use App\Services\CaseVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormDraftController extends Controller
{
    public function __construct(
        public CaseVerificationService $caseVerificationService,
    ) {}

    public function store(StoreFormDraftRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $draft = isset($validated['draft_reference'])
            ? FormDraft::query()->where('draft_reference', $validated['draft_reference'])->first()
            : null;

        $attributes = [
            'form_type' => $validated['form_type'],
            'email' => isset($validated['email']) ? strtolower(trim((string) $validated['email'])) : null,
            'case_reference' => $validated['case_reference'] ?? null,
            'data' => $validated['data'] ?? [],
            'current_step' => $validated['current_step'] ?? null,
            'last_saved_at' => now(),
        ];

        if ($draft) {
            $draft->update($attributes);
        } else {
            $draft = FormDraft::create([
                ...$attributes,
                'draft_reference' => $this->generateReference(),
                'status' => 'draft',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'draft_reference' => $draft->draft_reference,
                'last_saved_at' => $draft->last_saved_at?->toIso8601String(),
            ],
        ]);
    }

    public function show(string $draftReference): JsonResponse
    {
        $draft = FormDraft::query()->where('draft_reference', $draftReference)->first();

        if (! $draft) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->present($draft),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $verification = $this->caseVerificationService->resolve((string) $request->query('token', ''));

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'This continuation link is invalid or has expired.',
            ], 403);
        }

        $drafts = FormDraft::query()
            ->where('email', $verification->email)
            ->where('status', 'draft')
            ->latest('last_saved_at')
            ->get()
            ->map(fn (FormDraft $draft) => $this->present($draft))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => $drafts,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(FormDraft $draft): array
    {
        return [
            'draft_reference' => $draft->draft_reference,
            'form_type' => $draft->form_type,
            'case_reference' => $draft->case_reference,
            'data' => $draft->data ?? [],
            'current_step' => $draft->current_step,
            'last_saved_at' => $draft->last_saved_at?->toIso8601String(),
        ];
    }

    private function generateReference(): string
    {
        do {
            $reference = 'DRF-'.strtoupper(Str::random(10));
        } while (FormDraft::query()->where('draft_reference', $reference)->exists());

        return $reference;
    }
}
