<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\DAPersonnelController;
use App\Http\Controllers\SurveyController;


/*
|--------------------------------------------------------------------------
| Local-First Survey Flow (IndexedDB-driven, replaces the old session-based
| {farmer} wizard). Add this block to routes/web.php, near the existing
| /survey/start and farmer/survey/{farmer} routes. The old routes can stay
| in place untouched for now — nothing here conflicts with them.
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\LocalSurveyController;

Route::get('/survey/local/{uuid}', function (string $uuid) {
    // Landing point after "Continue" is clicked from My Surveys — JS on
    // this tiny shell reads the survey's current_step from IndexedDB and
    // redirects straight there, so people always resume exactly where
    // they left off rather than at Step 1.
    return view('surveys.local-redirect', ['uuid' => $uuid]);
})->name('surveys.local.start');

Route::get('/survey/local/{uuid}/step/{step}', [LocalSurveyController::class, 'showStep'])
    ->whereNumber('step')
    ->name('surveys.local.step.show');

Route::get('/survey/local/{uuid}/review', [LocalSurveyController::class, 'review'])
    ->name('surveys.local.review');

// "My Surveys" — lists surveys saved on this device (IndexedDB). All data
// is read client-side via EZSeedOffline in the view, so this is just a
// static shell, same pattern as surveys.local.start above.
Route::get('/survey/my-surveys', function () {
    return view('surveys.my-surveys');
})->name('surveys.my-surveys');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/


// Landing Page
Route::get('/', function () {
    return view('landing.index');
})->name('landing');



// Start a new survey — creates a blank Farmer record and goes straight
// to Step 1 of the wizard. Replaces the old standalone /farmer
// registration form entirely; the farmer's profile is now collected on
// Step 2 and their farm/address details on Step 3.
Route::get('/survey/start', [SurveyController::class, 'start'])
    ->name('surveys.start');


/*
|--------------------------------------------------------------------------
| Farmer Survey Wizard (EZ-Seed) — nested under the farmer
|--------------------------------------------------------------------------
|
| A 10-step wizard driven by SurveyController. All routes are scoped to
| a specific farmer via route model binding.
|
*/

Route::prefix('farmer/survey/{farmer}')->group(function () {

    // Step 1–12
    Route::get('/step/{step}',
        [SurveyController::class, 'showStep']
    )->whereNumber('step')->name('surveys.step.show');

    Route::post('/step/{step}',
        [SurveyController::class, 'storeStep']
    )->whereNumber('step')->name('surveys.step.store');

    // Review (grouped summary of all 12 steps)
    Route::get('/review',
        [SurveyController::class, 'review']
    )->name('surveys.review');

    // Final Submission
    Route::post('/submit',
        [SurveyController::class, 'submit']
    )->name('surveys.submit');

    // Success Page
    Route::get('/completed',
        [SurveyController::class, 'completed']
    )->name('surveys.completed');

    // Restart / Abandon In-Progress Survey
    Route::post('/restart',
        [SurveyController::class, 'restart']
    )->name('surveys.restart');

});


/*
|--------------------------------------------------------------------------
| AJAX Location Routes
|--------------------------------------------------------------------------
*/


// Get all Provinces (id + display name). Added so client-side code (the
// Review page and Step 10 summary) can resolve a saved province_id back
// into a human-readable name — offline records only ever store the ID,
// never the name, so without this endpoint there was no way to show
// "Cagayan" instead of "3" once you're back online.
Route::get('/provinces', function () {
    return response()->json(
        \App\Models\Province::orderBy('name')->get(['id', 'name'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => \App\Support\PlaceName::clean($p->name)])
            ->values()
    );
})->name('provinces.index');


// Get Municipalities by Province
Route::get('/municipalities/{province}', 
    [FarmerController::class, 'getMunicipalities']
)->name('municipalities.byProvince');


// Get Barangays by Municipality
Route::get('/barangays/{municipality}', 
    [FarmerController::class, 'getBarangays']
)->name('barangays.byMunicipality');





/*
|--------------------------------------------------------------------------
| Protected Routes (Department of Agriculture)
|--------------------------------------------------------------------------
*/


Route::middleware(['auth'])->group(function () {


    /*
    |----------------------------------------------------------------
    | DA Dashboard — 7 pages
    |----------------------------------------------------------------
    */

    Route::get('/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::get('/dashboard/farmers',
        [DashboardController::class, 'farmers']
    )->name('dashboard.farmers');

    Route::get('/dashboard/seed-monitoring',
        [DashboardController::class, 'seedMonitoring']
    )->name('dashboard.seed-monitoring');

    Route::get('/dashboard/seasonal-analysis',
        [DashboardController::class, 'seasonalAnalysis']
    )->name('dashboard.seasonal-analysis');

    Route::get('/dashboard/province-maps',
        [DashboardController::class, 'provinceMaps']
    )->name('dashboard.province-maps');

    Route::get('/dashboard/province-maps/data',
    [DashboardController::class, 'provinceMapData']
    )->name('dashboard.province-maps.data');

    Route::get('/dashboard/reports',
        [ReportController::class, 'index']
    )->name('dashboard.reports');

    Route::get('/dashboard/reports/export/{format}',
        [ReportController::class, 'export']
    )->name('dashboard.reports.export');

    Route::get('/dashboard/export-pdf',
    [DashboardController::class, 'exportPdf']
    )->name('dashboard.export-pdf');


    /*
    |----------------------------------------------------------------
    | Settings
    |----------------------------------------------------------------
    */

    Route::get('/settings',
        [SettingsController::class, 'edit']
    )->name('settings.edit');

    Route::patch('/settings/account',
        [SettingsController::class, 'updateAccount']
    )->name('settings.account.update');

    Route::patch('/settings/notifications',
        [SettingsController::class, 'updateNotifications']
    )->name('settings.notifications.update');

    Route::patch('/settings/data-sync',
        [SettingsController::class, 'updateDataSync']
    )->name('settings.data-sync.update');

    Route::patch('/settings/export-preferences',
        [SettingsController::class, 'updateExportPreferences']
    )->name('settings.export-preferences.update');

    Route::patch('/settings/password',
        [SettingsController::class, 'updatePassword']
    )->name('settings.password.update');



    // Farmer Management CRUD
    Route::resource('farmers', FarmerController::class);



    // DA Personnel Management CRUD
    Route::resource('da-personnels', DAPersonnelController::class);



    // Profile
    Route::get('/profile', 
        [ProfileController::class, 'edit']
    )->name('profile.edit');


    Route::patch('/profile', 
        [ProfileController::class, 'update']
    )->name('profile.update');


    Route::delete('/profile', 
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

});



/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Administrator Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/admin.php';

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';