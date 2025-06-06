<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// display articles
Route::middleware([])->group(function () {
    // this route for listing all articles
    Route::get('/articles', function (Request $request) {
        return response()->json(['message' => 'List of articles']);
    })->name('articles.list');
    // get article details by ID
    Route::get('/articles/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Details for article with ID: $id"]);
    })->name('articles.details');
    // like an article
    Route::post('/articles/{id}/like', function (Request $request, $id) {
        return response()->json(['message' => "Article with ID: $id liked successfully"]);
    })->name('articles.like');
});

// route for unlike an article
Route::middleware([])->delete('/articles/like/{id}', function (Request $request, $id) {
    return response()->json(['message' => "Like on article with ID: $id cancelled successfully"]);
})->name('articles.cancel.like');

// route for managing articles
Route::middleware([])->group(function () {
    // this route for adding a new article
    Route::post('/articles', function (Request $request) {
        return response()->json(['message' => 'Article added successfully']);
    })->name('articles.add');
    // this route for updating an existing article
    Route::put('/articles/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Article with ID: $id updated successfully"]);
    })->name('articles.update');
    // this route for deleting an article
    Route::delete('/articles/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Article with ID: $id deleted successfully"]);
    })->name('articles.delete');
});