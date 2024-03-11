<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PlataController;

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
Auth::routes(['register' => false, 'password.request' => false, 'reset' => false]);


Route::redirect('/', '/acasa');

Route::get('/plati/adauga-plata-noua', [PlataController::class, 'adaugaPlataNoua']);
Route::get('/plati/adauga-plata-pasul-1', [PlataController::class, 'adaugaPlataPasul1']);
Route::post('/plati/adauga-plata-pasul-1', [PlataController::class, 'postAdaugaPlataPasul1']);
Route::get('/plati/adauga-plata-pasul-2', [PlataController::class, 'adaugaPlataPasul2']);
Route::post('/plati/adauga-plata-pasul-2', [PlataController::class, 'postAdaugaPlataPasul2']);
Route::get('/plati/adauga-plata-pasul-3', [PlataController::class, 'adaugaPlataPasul3']);

Route::get('/plati/verificare', [PlataController::class, 'verificare']);


Route::group(['middleware' => 'auth'], function () {
    Route::view('/acasa', 'acasa');
});
