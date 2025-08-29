<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlaController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketAttachmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);

    Route::post('/tickets/{id}/comments', [TicketCommentController::class, 'store']);
    Route::post('/tickets/{id}/attachments', [TicketAttachmentController::class, 'store']);

    // Master Asset
    Route::get('/asset-categories', [AssetCategoryController::class, 'index']);
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/vendors', [VendorController::class, 'index']);
    Route::apiResource('assets', AssetController::class)->only(['index','store','show','update','destroy']);

    // SLA
    Route::get('/settings/sla', [SlaController::class, 'index']);
    Route::put('/settings/sla', [SlaController::class, 'bulkUpdate']); // opsional
});
