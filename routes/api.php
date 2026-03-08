<?php

use App\Http\Controllers\Api\FormSubmissionController;
use App\Http\Controllers\Api\VentureDiagnosticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes for form submissions (no authentication required)
Route::prefix('v1')->group(function () {
    // Venture Diagnostic form (complex multi-step form)
    Route::post('venture-diagnostic', [VentureDiagnosticController::class, 'store']);

    // General form submissions (all other forms)
    Route::post('form-submission', [FormSubmissionController::class, 'store']);
});

