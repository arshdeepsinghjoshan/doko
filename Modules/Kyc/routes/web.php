<?php

use Illuminate\Support\Facades\Route;
use Modules\Kyc\App\Http\Controllers\KycController;

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

Route::group(['middleware' => 'prevent-back-history'], function () {
    Route::group(['middleware' => 'auth'], function () {
            Route::get('/kyc', [KycController::class, 'index']);
            Route::get('/kyc/create', [KycController::class, 'add']);
            Route::get('/kyc/get-list', [KycController::class, 'getList']);
            Route::get('/kyc/view/{id}', [KycController::class, 'view']);
            Route::get('/kyc/delete/{id}', [KycController::class, 'finalDelete']);
            Route::post('/kyc/Store', [KycController::class, 'Store'])->name('kyc.add');
            Route::post('/kyc/update', [KycController::class, 'update'])->name('kyc.update');
    });
});
