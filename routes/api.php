<?php

use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\FormSubmissionController;
use App\Http\Controllers\Api\InsightController;
use App\Http\Controllers\Api\VentureDiagnosticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes for form submissions and insights
Route::prefix('v1')->group(function () {
    // Venture Diagnostic form (complex multi-step form)
    Route::post('venture-diagnostic', [VentureDiagnosticController::class, 'store']);

    // General form submissions (all other forms)
    Route::post('form-submission', [FormSubmissionController::class, 'store']);

    // Insights/blog feed
    Route::get('insights', [InsightController::class, 'index']);
    Route::get('insights/{slug}', [InsightController::class, 'show']);

    // Subscription endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('insights/subscription/status', [InsightController::class, 'subscriptionStatus']);
        Route::post('insights/subscription/activate', [InsightController::class, 'activateSubscription']);
    });

    // Careers / opportunities
    Route::get('careers', [CareerController::class, 'index']);
    Route::get('careers/{career}', [CareerController::class, 'show']);
    Route::post('careers/{career}/apply', [CareerController::class, 'apply']);
});
