<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SurveySyncController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| EZ-Seed Farmer Portal offline sync endpoints. These are intentionally
| unauthenticated, matching the existing public /survey/* web routes the
| Farmer Portal already uses (no login is required to take the survey).
| Idempotency is enforced server-side via offline_uuid, not via auth.
|
*/

Route::get('/ping', [SurveySyncController::class, 'ping'])->name('api.ping');

Route::post('/surveys/sync', [SurveySyncController::class, 'sync'])->name('api.surveys.sync');

Route::get('/surveys/sync-status/{offline_uuid}', [SurveySyncController::class, 'syncStatus'])
    ->name('api.surveys.sync-status');