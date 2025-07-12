<?php

use App\Http\Controllers\JobController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// display and apply for jobs from the user
Route::middleware(['auth:sanctum'])->group(function () {
    // this route for listing all jobs
    Route::get('/jobs', [JobController::class,'availableJobs'])->name('jobs.list');
    // route for getting featured jobs
    Route::get('/jobs/featured', [JobController::class, 'featuredJobs'])->name('jobs.featured');
    // get job details by ID
    Route::get('/jobs/{id}', [JobController::class,'show'])->name('jobs.details');
    // this route is for users to get jobs by company id
    Route::get('/public/company/{id}/jobs', [JobController::class, 'jobsByCompanyIdForUsers'])->name('company.jobs');

    // apply for a job
    Route::post('/jobs/{id}/apply', function (Request $request, $id) {
        return response()->json(['message' => "Applied for job with ID: $id successfully"]);
    })->name('jobs.apply');
});
// // routes for managent job application from the user
// Route::middleware([])->group(function () {
//     // get user's jobs applications
//     Route::get('/applications', function (Request $request) {
//         return response()->json(['message' => 'List of user job applications']);
//     })->name('jobs.applications');
//     // update job application
//     Route::put('/applications/{id}', function (Request $request) {
//         return response()->json(['message' => 'update application']);
//     })->name('application.update');
//     // delete job application
//     Route::delete('/applications/{id}', function (Request $request) {
//         return response()->json(['message' => 'application was deleted']);
//     })->name('application.delete');
// });

// routes for jobs management by company
Route::middleware(['auth:sanctum'])->group(function () {
    // this route for listing all jobs of the company
    Route::get('/company/jobs', [JobController::class,'companyJobs'])->name('company.jobs.list');
    // this route for adding a new job
    Route::post('/company/jobs', [JobController::class,'createJob'])->name('company.jobs.add');
    // this route for updating an existing job
    Route::put('/company/jobs/{id}', [JobController::class,'updateJob'])->name('company.jobs.update');
    // this r]oute for deleting a job
    Route::delete('/company/jobs/{id}', [JobController::class,'destroy'])->name('company.jobs.delete');
    //this route for changeStatus
     Route::patch('/company/jobs/{id}/status', [JobController::class, 'changeStatus'])->name('company.jobs.changeStatus');
     // Admin can view all jobs
  Route::get('/admin/jobs', [JobController::class, 'adminJobs']);
    // // route for getting job applications by company
    // Route::get('/company/jobs/{id}/applications', function (Request $request, $id) {
    //     return response()->json(['message' => "List of applications for job with ID: $id"]);
    // })->name('company.jobs.applications');
    // // route for reviewing job application
    // Route::put('/company/applications/{id}', function (Request $request, $id) {
    //     return response()->json(['message' => "Application with ID: $id reviewed successfully"]);
    // })->name('company.applications.review');
    // // route for accept application
    // Route::post('/company/applications/{id}/accept', function (Request $request, $id) {
    //     return response()->json(['message' => "Application with ID: $id accepted successfully"]);
    // })->name('company.applications.accept');
    // // route for reject application
    // Route::post('/company/applications/{id}/reject', function (Request $request, $id) {
    //     return response()->json(['message' => "Application with ID: $id rejected successfully"]);
    // })->name('company.applications.reject');

});

// route for getting jobs for specific company from staff
Route::middleware(['auth:sanctum'])->get('/company/{id}/jobs', [JobController::class, 'jobsByCompanyId'])->name('company.jobs');




