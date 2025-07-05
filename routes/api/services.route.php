<?php

use App\Http\Controllers\ServicesController;
use App\Models\AlumniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// This route for adding a new service
Route::middleware(['auth:sanctum', 'role:alumni'])->post('/service', 
[ServicesController::class, 'createService'])->name('services.add');

// adding service and manage it for alunmini
Route::middleware(['auth:sanctum', 'role:alumni'])->group(function () {
    // this route for listing all user services
    Route::get('/service', [ServicesController::class, 'listUserServices'])
    ->name('services.list');
    // route for update existing service
    Route::put(
        '/service',
        [ServicesController::class, 'updateService']
    )->name('services.update');
    // route for delete service
    Route::delete('/service/{id}', [ServicesController::class, 'deleteService'])->name('services.delete');
});

// route for managing service by admin and staff
Route::middleware(['auth:sanctum', 'role:admin|staff'])->group(function () {
    // this route for listing all services
    Route::get('/used-services', [ServicesController::class, 'listusedServices'])->name('used-services.list');

    Route::get('/unused-services', [ServicesController::class, 'listUnusedServices'])->name('unused-services.list');

    // route for getting service by id
    Route::get('/alumni-service/{id}', [ServicesController::class, 'getServiceDetails'])->name('services.get');
    // this route for updating service
    Route::put('/evaluate-service/{id}', [ServicesController::class, 'evaluateService'])->name('services.admin.update');
    //route for deleting service can be as for alumini above

});
