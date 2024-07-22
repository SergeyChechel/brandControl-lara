<?php

use App\Http\Controllers\DemandController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scans', [ScanController::class, 'index']);
Route::get('/demand', [DemandController::class, 'index']);
Route::post('/notification', [NotificationController::class, 'index']);
