<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\DomainToScanController;
use App\Http\Controllers\Api\ComplianceNotificationController;
use App\Http\Controllers\Api\PostbackLogController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\DemandController;
use App\Http\Controllers\NotificationController;

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



Route::apiResource('domains-to-scan', DomainToScanController::class);
Route::apiResource('compliance-notifications', ComplianceNotificationController::class);
Route::apiResource('postback-logs', PostbackLogController::class);
