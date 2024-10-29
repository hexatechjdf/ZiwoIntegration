<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AutoAuthController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\CrmWebhookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ZiwoDetailController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home');
});
Route::get('connect', [HomeController::class, 'connect'])->name('connect');
Route::get('send-notes', [HomeController::class, 'send_notes'])->name('send-notes');
Route::get('location-info', [HomeController::class, 'location_info'])->name('location.info');
Route::get('list-all-locations', [HomeController::class, 'addLocation']);

Route::get('/cache', function () {
    return \Artisan::call('optimize:clear');
});

Route::get('/logout', function () {
    \Auth::logout();
    return redirect()->route('home');
});

Auth::routes(['register' => false]);



Route::prefix('authorization')->name('crm.')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/crm/fetch_detail', [CRMController::class, 'crmFetchDetail'])->name('fetchDetail');
        Route::get('/crm/fetchLocations', [CRMController::class, 'fetchLocations'])->name('fetchLocations');
    });
    Route::get('/crm/oauth/callback', [CRMController::class, 'crmCallback'])->name('oauth_callback');
});

Route::group(['as' => 'ziwo.', 'prefix' => 'ziwo'], function () {
            //Route::get('/', [ZiwoDetailController::class, 'index'])->name('index');
            Route::post('/store', [ZiwoDetailController::class, 'store'])->name('save');
            Route::post('/get-token', [ZiwoDetailController::class, 'getToken'])->name('get_token');
            Route::post('/submit-call-response', [ZiwoDetailController::class, 'submitCallResponse'])->name('get_token');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');
    Route::group(['as' => 'admin.', 'prefix' => 'admin'], function () {
        Route::any('dashboard', [AdminController::class, 'index'])->name('dashboard');
       

       
        Route::group(['as' => 'location.', 'prefix' => 'location'], function () {
            Route::get('/', [LocationController::class, 'index'])->name('index');
            Route::post('/get-table-data', [LocationController::class, 'getTableData'])->name('table.data');
            Route::post('/store', [LocationController::class, 'store'])->name('save');
            Route::get('/get-token', [LocationController::class, 'getToken'])->name('get_token');
            Route::post('/submit-call-response', [LocationController::class, 'submitCallResponse'])->name('get_token');
        });

        Route::middleware('admin')->group(function () {
            Route::put('/user/profile/{id}', [SettingController::class, 'userProfile'])->name('user.profile');
            Route::get('setting', [SettingController::class, 'index'])->name('setting');
            Route::post('/setting/save', [SettingController::class, 'save'])->name('setting.save');
            Route::GET('/user', [UserController::class, 'index'])->name('user.index');
        });
    });

});

Route::get('check/auth', [AutoAuthController::class, 'connect'])->name('auth.check');
Route::get('check/auth/error', [AutoAuthController::class, 'authError'])->name('error');
Route::get('checking/auth', [AutoAuthController::class, 'authChecking'])->name('admin.auth.checking');


Route::get('o-auth/{type?}/{id?}', [AutoAuthController::class,'oauth'])->name('oauthcrmconnection');
Route::get('/oauth/{provider}/disconnect', [AutoAuthController::class,'oAuthDisconnect'])->name('oauth.disconnect');