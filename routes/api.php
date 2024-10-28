<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SpotifyController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CrmWebhookController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ZiwoDetailController;
use App\Http\Controllers\ZiwoWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/jobs', function () {
    //return \Artisan::call('schedule:run');
});

Route::any('/ziwo-webhook', [ZiwoWebhookController::class, 'callback'])->withoutMiddleware([ \App\Http\Middleware\VerifyCsrfToken::class]);
Route::any('/crm-webhook', [CrmWebhookController::class, 'callback'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/locations/toggle-integration/{id}', [LocationController::class, 'toggleIntegration']);
Route::post('/submit-call-response', [ZiwoDetailController::class, 'submitCallResponse'])->name('get_token');
Route::post('/delete-call-logs', [ZiwoDetailController::class, 'deleteCallLogs'])->name('get_token');
