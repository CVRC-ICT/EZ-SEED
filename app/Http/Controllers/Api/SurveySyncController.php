<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SurveyController;
use App\Models\SurveySyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SurveySyncController extends Controller
{
    /**
     * POST /api/surveys/sync
     *
     * Idempotent: if offline_uuid was already synced, returns the existing
     * result instead of creating a duplicate survey. Safe to call more than
     * once for the same offline_uuid (e.g. after a dropped connection right
     * after a successful upload).
     */
    public function sync(Request $request, SurveyController $surveyController)
    {
        $request->validate([
            'offline_uuid' => ['required', 'uuid'],
            'payload' => ['required', 'array'],
        ]);

        $offlineUuid = $request->input('offline_uuid');
        $payload = $request->input('payload');

        // 1. Idempotency check — already synced? Return the existing result,
        // do NOT create anything new.
        $existingLog = SurveySyncLog::where('offline_uuid', $offlineUuid)->first();

        if ($existingLog && $existingLog->synced_at) {
            $existingLog->load('survey');
            return response()->json([
                'status' => 'already_synced',
                'reference_number' => optional($existingLog->survey)->reference_number,
                'survey_id' => $existingLog->survey_id,
            ]);
        }

        // 2. Validate the full payload using the exact same per-step rules
        // the web wizard already uses — no duplicated validation logic.
        $validator = Validator::make($payload, $surveyController->allStepRules());

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (empty($validated['certification'] ?? $payload['certification'] ?? null)) {
            return response()->json([
                'status' => 'validation_failed',
                'errors' => ['certification' => ['The farmer must certify the information is true before submitting.']],
            ], 422);
        }

        // 3. Create (transactional) — find-or-create farmer, create survey +
        // seed preferences, log the offline_uuid so repeat calls are safe.
        try {
            $result = DB::transaction(function () use ($surveyController, $payload, $offlineUuid, $existingLog) {
                $farmer = $surveyController->findOrCreateFarmer($payload);
                $created = $surveyController->createSurveyFromPayload($farmer, $payload);

                if ($existingLog) {
                    $existingLog->update([
                        'survey_id' => $created['survey']->id,
                        'synced_at' => now(),
                    ]);
                } else {
                    SurveySyncLog::create([
                        'offline_uuid' => $offlineUuid,
                        'survey_id' => $created['survey']->id,
                        'synced_at' => now(),
                    ]);
                }

                return $created;
            });

            return response()->json([
                'status' => 'synced',
                'reference_number' => $result['reference_number'],
                'survey_id' => $result['survey']->id,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'The server could not process this survey right now. Your answers are safe on this device — it will retry automatically.',
            ], 500);
        }
    }

    /**
     * GET /api/surveys/sync-status/{offline_uuid}
     * Lets the client re-check sync state after an interrupted batch sync
     * or app relaunch, without re-submitting the payload.
     */
    public function syncStatus(string $offlineUuid)
    {
        $log = SurveySyncLog::where('offline_uuid', $offlineUuid)->first();

        if (! $log || ! $log->synced_at) {
            return response()->json(['status' => 'not_synced']);
        }

        $log->load('survey');

        return response()->json([
            'status' => 'synced',
            'reference_number' => optional($log->survey)->reference_number,
            'survey_id' => $log->survey_id,
        ]);
    }

    /**
     * GET /api/ping
     * Lightweight, cheap endpoint the client hits to verify the EZ-Seed
     * server (not just "some" internet) is actually reachable before
     * attempting a sync batch.
     */
    public function ping()
    {
        return response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]);
    }
}