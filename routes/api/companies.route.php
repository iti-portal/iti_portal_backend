<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// display and management company details from the company
Route::middleware([])->group(function () {
//this route for display company details
    Route::get('/company', function (Request $request) {
        return response()->json(['message' => 'This is the company details route']);
    })->name('company.profile');
    // This route for updating company details
    Route::put('/company', function (Request $request) {
        return response()->json(['message' => 'Company details updated successfully']);
    })->name('company.profile.update');
    // This route for deleting company details
    Route::delete('/company', function (Request $request) {
        return response()->json(['message' => 'Company details deleted successfully']);
    })->name('company.profile.delete');
  });

  
// routes for company's management by admin and staff
Route::middleware([])->group(function () {
    // This route for listing all companies
    Route::get('/companies', function (Request $request) {
        return response()->json(['message' => 'List of companies']);
    })->name('companies.list');
    // This route for listing pending companies
    Route::get('/companies/pending', function (Request $request) {
        return response()->json(['message' => 'List of pending companies']);
    })->name('companies.pending');
    // This route for approving a company
    Route::post('/companies/{id}/approve', function (Request $request, $id) {
        return response()->json(['message' => "Company with ID: $id approved successfully"]);   
    })->name('companies.approve');
    // this route for updating a company details
    Route::put('/companies/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Company with ID: $id updated successfully"]);
    })->name('companies.update');
    // This route for deleting a company
    Route::delete('/companies/{id}', function (Request $request, $id) { 
        return response()->json(['message' => "Company with ID: $id deleted successfully"]);
    })->name('companies.delete');
    
});
// This route for getting featured companies from the users
Route::get('/companies/featured', function (Request $request) {
    return response()->json(['message' => 'List of featured companies']);
})->name('companies.featured');

// This route for getting company details by ID for users
Route::get('/companies/{id}', function (Request $request, $id) {
    return response()->json(['message' => "Details for company with ID: $id"]);
})->name('companies.details');
