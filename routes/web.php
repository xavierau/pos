<?php

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Http\Controllers\StoreController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

//------------------------------------------------------------------\\

Route::get('simple_login', function(){
    $ModulesData = BaseController::get_Module_Info();
    return view('auth.simple_login', [
        'ModulesInstalled' => $ModulesData['ModulesInstalled'],
        'ModulesEnabled' => $ModulesData['ModulesEnabled'],
    ]);
});

Route::post('/login', ['uses' => 'Auth\LoginController@login']);

Route::get('password/find/{token}', 'PasswordResetController@find');


//------------------------------------------------------------------\\

Route::group(['middleware' => ['web', 'auth:web']], function () {

    Route::get('/login', function () {
        $installed = Storage::disk('public')->exists('installed');
        if ($installed === false) {
            return redirect('/setup');
        } else {
            return redirect('/login');
        }
    });


    Route::get('/{vue?}',
        function () {
            $ModulesData = BaseController::get_Module_Info();
            return view('layouts.master', [
                'ModulesInstalled' => $ModulesData['ModulesInstalled'],
                'ModulesEnabled' => $ModulesData['ModulesEnabled'],
            ]);
        })->where('vue', '^(?!api|setup|update|update_database_module|password|module|store|online_store).*$');
});

Route::post('simple_login', "Auth\LoginController@loginWithEmployeeCodeOnly");

Auth::routes(['register' => false]);

//------------------------------------------------------------------\\

Route::group(['middleware' => ['web', 'auth:web']], function () {

    Route::get('update_database_module/{module_name}', 'ModuleSettingsController@update_database_module')->name('update_database_module');

    Route::get('/update', 'UpdateController@viewStep1');

    Route::view('/update/finish', 'update.finishedUpdate');

    Route::post('/update/lastStep', [
        'as' => 'update_lastStep', 'uses' => 'UpdateController@lastStep',
    ]);

});



