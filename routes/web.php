<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatementController;

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
Route::post('/import',[StatementController::class,'import'])->name('import');
Route::get('/export-users',[StatementController::class,'exportUsers'])->name('export-users');