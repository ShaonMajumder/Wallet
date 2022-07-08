<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatementController;
use Illuminate\Support\Facades\Artisan;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/file-import',[StatementController::class,'importView'])->name('import-view');
Route::post('/post-view',[StatementController::class,'import'])->name('import');
Route::get('/export-users',[StatementController::class,'exportUsers'])->name('export-users');

Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
    // php artisan config:clear
    // php artisan config:cache
    // php artisan route:clear
    // php artisan route:cache
    // php artisan view:clear
    // php artisan view:cache
    // php artisan cache:clear
    // php artisan optimize:clear
    return "Cache is cleared";
});