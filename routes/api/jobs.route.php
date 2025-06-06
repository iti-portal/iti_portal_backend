<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// display and management jobs from the user
Route::middleware([])->group(function () {
    // this route for listing all jobs
    Route::get('/jobs', function (Request $request) {
        return response()->json(['message' => 'List of user jobs']);
    })->name('jobs.list');
    // get job details by ID
    Route::get('/jobs/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Details for job with ID: $id"]);
    })->name('jobs.details');
    // get user's jobs applications
    Route::get('/applications', function (Request $request) {
        return response()->json(['message' => 'List of user job applications']);
    })->name('jobs.applications');
    // apply for a job
    Route::post('/jobs/{id}/apply', function (Request $request, $id) {
        return response()->json(['message' => "Applied for job with ID: $id successfully"]);
    })->name('jobs.apply');
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
    // this route for deleting a job
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
    
});


   