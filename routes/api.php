<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/users.route.php';
require __DIR__ . '/api/companies.route.php';
require __DIR__ . '/api/eductionAndSkills.route.php';
require __DIR__ . '/api/oldAchievments.route.php';
require __DIR__ . '/api/jobs.route.php';
require __DIR__ . '/api/achievments.route.php';
require __DIR__ . '/api/articles.route.php';
require __DIR__ . '/api/services.route.php';
require __DIR__ . '/api/connections.route.php';




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');







