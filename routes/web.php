<?php

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

use App\Mail\ForgotPasswordMail;
use App\User;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::get('/', function () {
    return view('auth.login');
Route::get('/activity-logs', 'ActivityLogController@index')->name('activity.logs');
});

Route::get('error', function () {
    return view('errors.admin');
})->name('errors.admin');

Route::get('/about', function(){
return view('about');
});

Route::get('/forgot-password', function () {
    return view('auth.forgot_password');
})->name('auth.forgot_password');

Route::post('/forgot-password', 'ForgotPasswordController@send')->name('auth.send_code');
Route::get('/forgot-password/verify/{email}', 'ForgotPasswordController@verify')->name('auth.verify');
Route::get('/test', function () {
    return new ForgotPasswordMail(User::first());
});

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'showChangePasswordForm'])
        ->name('password.change');
    Route::post('/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'updatePassword'])
        ->name('password.update');
});


Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

	Route::get('labreports', function () {
		return view('patients.labreport');
    });    

    Route::get('profile/{user}', 'ProfileController@show')->name('profile.show');

    Route::middleware('admin')->group(function () {
        Route::resource('users', 'UserController');
        Route::get('users/archive/index', 'UserController@archive')->name('users.archive');
        Route::delete('users/force-delete/{id}', 'UserController@deleteUser')->name('users.delete');
        Route::get('users/restore/{id}', 'UserController@restoreUser')->name('users.restore');
        Route::post('users/search', 'UserController@search')->name('users.search');
        Route::post('users/archive/search', 'UserController@archive_search')->name('users.archive_search');
    });
    
    // Patients
    Route::resource('patients', 'PatientController');
    Route::get('patients/archive/index', 'PatientController@archive')->name('patients.archive');
    Route::delete('patients/force-delete/{id}', 'PatientController@deletePatient')->name('patients.delete');
    Route::get('patients/restore/{id}', 'PatientController@restorePatient')->name('patients.restore');
    Route::post('patients/search', 'PatientController@search')->name('patients.search');
    Route::post('patients/archive/search', 'PatientController@archive_search')->name('patients.archive_search');
    
    Route::resource('services', 'ServiceController');
    Route::get('services/archive/index', 'ServiceController@archive')->name('services.archive');
    Route::delete('services/force-delete/{id}', 'ServiceController@forceDestroy')->name('services.delete');
    Route::get('services/restore/{id}', 'ServiceController@restore')->name('services.restore');
    Route::post('services/search', 'ServiceController@search')->name('services.search');
    Route::post('services/archive/search', 'ServiceController@archive_search')->name('services.archive_search');

    Route::get('help', 'HelpController@index')->name('help');
    Route::get('doctors', 'DoctorsController@index')->name('doctors');
    Route::get('contact', 'ContactController@index')->name('contact');
    Route::resource('medical-records', 'MedicalRecordController');
});

