<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TicketWebController;
use App\Http\Controllers\Web\AssetWebController;
use App\Http\Controllers\Web\AssetCategoryWebController;
use App\Http\Controllers\Web\LocationWebController;
use App\Http\Controllers\Web\VendorWebController;
use App\Http\Controllers\Web\SlaWebController;
use App\Http\Controllers\Web\UserWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\AssetLookupController;

require __DIR__.'/auth.php'; // route login/logout dari Breeze (Blade)

Route::view('/offline', 'offline')->name('offline');

// AJAX lookup aset (dropdown dengan pencarian & filter kategori)
Route::middleware('auth')->get('/assets/lookup', AssetLookupController::class)
    ->name('assets.lookup');

Route::middleware(['auth'])->group(function () {
    // Home / Dashboard
    Route::get('/', [DashboardWebController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');

    // Tickets
    Route::get('/tickets', [TicketWebController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketWebController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketWebController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [TicketWebController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{id}/edit', [TicketWebController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{id}', [TicketWebController::class, 'update'])->name('tickets.update');

    // Ticket actions
    Route::post('/tickets/{id}/status', [TicketWebController::class, 'updateStatus'])->name('tickets.updateStatus');
    Route::post('/tickets/{id}/assign', [TicketWebController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{id}/comments', [TicketWebController::class, 'comment'])->name('tickets.comment');
    Route::post('/tickets/{id}/attachments', [TicketWebController::class, 'attach'])->name('tickets.attach');
    Route::delete('/tickets/{id}/attachments/{attId}', [TicketWebController::class, 'detach'])->name('tickets.attachments.destroy');

    // Assets
    Route::get('/assets', [AssetWebController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetWebController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetWebController::class, 'store'])->name('assets.store');
    Route::get('/assets/{id}/edit', [AssetWebController::class, 'edit'])->name('assets.edit');
    Route::put('/assets/{id}', [AssetWebController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{id}', [AssetWebController::class, 'destroy'])->name('assets.destroy');
    Route::get('/assets/{id}/peek',  [AssetWebController::class, 'peek'])->name('assets.peek');
    Route::get('/assets/{id}/print', [AssetWebController::class, 'print'])->name('assets.print');

    // Master: Kategori Aset
    Route::get('/master/asset-categories', [AssetCategoryWebController::class, 'index'])->name('master.asset_categories.index');
    Route::post('/master/asset-categories', [AssetCategoryWebController::class, 'store'])->name('master.asset_categories.store');
    Route::get('/master/asset-categories/{id}/edit', [AssetCategoryWebController::class, 'edit'])->name('master.asset_categories.edit');
    Route::put('/master/asset-categories/{id}', [AssetCategoryWebController::class, 'update'])->name('master.asset_categories.update');
    Route::delete('/master/asset-categories/{id}', [AssetCategoryWebController::class, 'destroy'])->name('master.asset_categories.destroy');

    // Master: Lokasi
    Route::get('/master/locations', [LocationWebController::class, 'index'])->name('master.locations.index');
    Route::post('/master/locations', [LocationWebController::class, 'store'])->name('master.locations.store');
    Route::get('/master/locations/{id}/edit', [LocationWebController::class, 'edit'])->name('master.locations.edit');
    Route::put('/master/locations/{id}', [LocationWebController::class, 'update'])->name('master.locations.update');
    Route::delete('/master/locations/{id}', [LocationWebController::class, 'destroy'])->name('master.locations.destroy');

    // Master: Vendor
    Route::get('/master/vendors', [VendorWebController::class, 'index'])->name('master.vendors.index');
    Route::post('/master/vendors', [VendorWebController::class, 'store'])->name('master.vendors.store');
    Route::get('/master/vendors/{id}/edit', [VendorWebController::class, 'edit'])->name('master.vendors.edit');
    Route::put('/master/vendors/{id}', [VendorWebController::class, 'update'])->name('master.vendors.update');
    Route::delete('/master/vendors/{id}', [VendorWebController::class, 'destroy'])->name('master.vendors.destroy');

    // Settings: SLA
    Route::get('/settings/sla', [SlaWebController::class, 'index'])->name('settings.sla.index');
    Route::post('/settings/sla', [SlaWebController::class, 'update'])->name('settings.sla.update');

    // Users (SUPERADMIN only – validasi di controller)
    Route::get('/admin/users', [UserWebController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserWebController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserWebController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [UserWebController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [UserWebController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/{id}/toggle', [UserWebController::class, 'toggle'])->name('admin.users.toggle'); // aktif/non-aktif

    // Reports
    Route::get('/reports/tickets', [ReportWebController::class, 'tickets'])->name('reports.tickets');
    Route::get('/reports/tickets/export', [ReportWebController::class, 'exportTickets'])->name('reports.tickets.export');

        // ==== PM Plans
    Route::get('/pm/plans',             [\App\Http\Controllers\Web\PmPlanWebController::class, 'index'])->name('pm.plans.index');
    Route::get('/pm/plans/create',      [\App\Http\Controllers\Web\PmPlanWebController::class, 'create'])->name('pm.plans.create');
    Route::post('/pm/plans',            [\App\Http\Controllers\Web\PmPlanWebController::class, 'store'])->name('pm.plans.store');
    Route::get('/pm/plans/{id}/edit',   [\App\Http\Controllers\Web\PmPlanWebController::class, 'edit'])->name('pm.plans.edit');
    Route::put('/pm/plans/{id}',        [\App\Http\Controllers\Web\PmPlanWebController::class, 'update'])->name('pm.plans.update');
    Route::delete('/pm/plans/{id}',     [\App\Http\Controllers\Web\PmPlanWebController::class, 'destroy'])->name('pm.plans.destroy');

    // ==== PM Schedules
    Route::get('/pm/schedules',             [\App\Http\Controllers\Web\PmScheduleWebController::class, 'index'])->name('pm.schedules.index');
    Route::get('/pm/schedules/create',      [\App\Http\Controllers\Web\PmScheduleWebController::class, 'create'])->name('pm.schedules.create');
    Route::post('/pm/schedules',            [\App\Http\Controllers\Web\PmScheduleWebController::class, 'store'])->name('pm.schedules.store');
    Route::get('/pm/schedules/{id}/edit',   [\App\Http\Controllers\Web\PmScheduleWebController::class, 'edit'])->name('pm.schedules.edit');
    Route::put('/pm/schedules/{id}',        [\App\Http\Controllers\Web\PmScheduleWebController::class, 'update'])->name('pm.schedules.update');
    Route::delete('/pm/schedules/{id}',     [\App\Http\Controllers\Web\PmScheduleWebController::class, 'destroy'])->name('pm.schedules.destroy');

    // ==== PM Executions
    Route::get('/pm/schedules/{scheduleId}/exec/create',  [\App\Http\Controllers\Web\PmExecutionWebController::class, 'create'])->name('pm.exec.create');
    Route::post('/pm/schedules/{scheduleId}/exec',        [\App\Http\Controllers\Web\PmExecutionWebController::class, 'store'])->name('pm.exec.store');

    // ==== Work Orders
    Route::get('/wo',               [\App\Http\Controllers\Web\WorkOrderWebController::class, 'index'])->name('wo.index');
    Route::get('/wo/create',        [\App\Http\Controllers\Web\WorkOrderWebController::class, 'create'])->name('wo.create');
    Route::post('/wo',              [\App\Http\Controllers\Web\WorkOrderWebController::class, 'store'])->name('wo.store');
    Route::get('/wo/{id}',          [\App\Http\Controllers\Web\WorkOrderWebController::class, 'show'])->name('wo.show');
    Route::get('/wo/{id}/edit',     [\App\Http\Controllers\Web\WorkOrderWebController::class, 'edit'])->name('wo.edit');
    Route::put('/wo/{id}',          [\App\Http\Controllers\Web\WorkOrderWebController::class, 'update'])->name('wo.update');
    Route::delete('/wo/{id}',       [\App\Http\Controllers\Web\WorkOrderWebController::class, 'destroy'])->name('wo.destroy');

    Route::post('/wo/{id}/items',        [\App\Http\Controllers\Web\WorkOrderWebController::class, 'addItem'])->name('wo.items.add');
    Route::delete('/wo/{id}/items/{it}', [\App\Http\Controllers\Web\WorkOrderWebController::class, 'removeItem'])->name('wo.items.remove');
    Route::post('/wo/{id}/start',        [\App\Http\Controllers\Web\WorkOrderWebController::class, 'start'])->name('wo.start');
    Route::post('/wo/{id}/done',         [\App\Http\Controllers\Web\WorkOrderWebController::class, 'done'])->name('wo.done');

});
