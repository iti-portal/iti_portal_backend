<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// display and apply for jobs from the user
Route::middleware([])->group(function () {
    // this route for listing all jobs
    Route::get('/jobs', function (Request $request) {
        return response()->json(['message' => 'List of user jobs']);
    })->name('jobs.list');
    // get job details by ID
    Route::get('/jobs/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Details for job with ID: $id"]);
    })->name('jobs.details');
    // route for getting featured jobs
    Route::get('/jobs/featured', function (Request $request) {
        return response()->json(['message' => "featured jobs"]);
    })->name('jobs.featured');
    
    // apply for a job
    Route::post('/jobs/{id}/apply', function (Request $request, $id) {
        return response()->json(['message' => "Applied for job with ID: $id successfully"]);
    })->name('jobs.apply');
});
// routes for managent job application from the user
Route::middleware([])->group(function () {
    // get user's jobs applications
    Route::get('/applications', function (Request $request) {
        return response()->json(['message' => 'List of user job applications']);
    })->name('jobs.applications');
    // update job application
    Route::put('/applications/{id}', function (Request $request) {
        return response()->json(['message' => 'update application']);
    })->name('application.update');
    // delete job application
    Route::delete('/applications/{id}', function (Request $request) {
        return response()->json(['message' => 'application was deleted']);
    })->name('application.delete');
});

// routes for jobs management by company
Route::middleware([])->group(function () {
    // this route for listing all jobs of the company
    Route::get('/company/jobs', function (Request $request) {
        return response()->json(['message' => 'List of company jobs']);
    })->name('company.jobs.list');
    // this route for adding a new job
    Route::post('/company/jobs', function (Request $request) {
        return response()->json(['message' => 'Job added successfully']);
    })->name('company.jobs.add');
    // this route for updating an existing job
    Route::put('/company/jobs/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Job with ID: $id updated successfully"]);
    })->name('company.jobs.update');
    // this r]oute for deleting a job
    Route::delete('/company/jobs/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Job with ID: $id deleted successfully"]);
    })->name('company.jobs.delete');
    // route for getting job applications by company
    Route::get('/company/jobs/{id}/applications', function (Request $request, $id) {
        return response()->json(['message' => "List of applications for job with ID: $id"]);
    })->name('company.jobs.applications');
    // route for reviewing job application
    Route::put('/company/applications/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Application with ID: $id reviewed successfully"]);
    })->name('company.applications.review');
    // route for accept application
    Route::post('/company/applications/{id}/accept', function (Request $request, $id) {
        return response()->json(['message' => "Application with ID: $id accepted successfully"]);
    })->name('company.applications.accept');
    // route for reject application
    Route::post('/company/applications/{id}/reject', function (Request $request, $id) {
        return response()->json(['message' => "Application with ID: $id rejected successfully"]);
    })->name('company.applications.reject');

});

// route for getting jobs for specific company from staff
Route::middleware([])->get('/company/{id}/jobs', function (Request $request, $id) {
    return response()->json(['message' => "all jobs for a company with id: $id"]);
    })->name('company.jobs');
    



   